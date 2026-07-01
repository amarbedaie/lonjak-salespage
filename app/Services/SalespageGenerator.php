<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * The "brain" of the salespage builder. Encodes the 12-block direct-response
 * framework. Generates via OpenRouter/Anthropic when keys are set, else a local
 * mock — same shape either way, so the UI never changes.
 */
class SalespageGenerator
{
    public const FRAMEWORK = <<<'TXT'
Anda copywriter direct-response & da'i digital TERBAIK Malaysia (gaya BeDaie "1 Rumah, 1 Daie") — pakar salespage yang CONVERT gila TAPI jujur & berakar pada ilmu.

PRINSIP BEDAIE (WAJIB):
1. TULIS PANJANG & BERLAPIS — JANGAN ringkas/simple. Setiap blok kena cukup "daging": body 3-5 ayat penuh emosi & cerita, bullets 4-6. Pembaca rasa dapat banyak nilai sebelum beli. Salespage ini LONG-FORM.
2. Bahasa Melayu santai Malaysia — mesra, bersemangat, code-switch English ringan. Suara "saya"/"kami", sapa "anda"/"korang". Selit tic natural: "Betul tak?", "Nampak tak?", "Rugi tak kalau...", "Jujur je...".
3. HOOK = ANGKA TERSEMBUNYI / "rare count" yang menyentak — janji angka di headline & buka gap penasaran (cth "30 kesalahan lazim", "16 perkara wajib tahu", "5 silap paling ramai buat tanpa sedar"). Letak insight paling mengejut di hujung.
4. PAIN diimbang HARAPAN (khauf + raja') — sentuh kesakitan dalam-dalam, TAPI sentiasa beri jalan keluar & harapan. HARAM buat putus asa atau takut-takutkan untuk paksa beli.
5. FAEDAH RARE — selit 2-3 insight "baru aku tahu" yang BENAR & masyhur. JANGAN reka fakta/angka pelik semata nampak power.
6. OTORITI jujur — untuk produk ilmu/agama, sandar pada sumber sahih secara UMUM (cth "berdasarkan Mazhab Syafi'i", "rujukan ulama muktabar"). JANGAN sekali-kali reka nombor hadis, petikan, atau nama kitab yang tak pasti.
7. JUALAN JUJUR — harga benar (TIADA "harga asal" palsu), urgency/stok benar sahaja, testimoni munasabah. Tipu = rosak barakah.

STRUKTUR 12-BLOK (formula N.I.A.G.A — setiap satu PANJANG, berlapis & menyentuh):
- hero: badge = hook provokatif SANGAT pendek (soalan menusuk, cth "Masih tertangguh?"). headline = janji hasil + spesifik/angka (4U). body = 3-4 ayat perkukuh janji + untuk siapa + kenapa beza. bullets = 3-4 kemenangan konkrit. meta.customers.
- problem: "Anda kenal situasi ni?" body 3-4 ayat sentuh hati. bullets = 4-5 pain SANGAT spesifik (balut frasa emosi penting dalam **bold**).
- agitate: kos kalau dibiar (emosi + masa + rohani + keluarga) — naik beransur, TAPI akhiri dengan secebis harapan. body 3-5 ayat + bullets bila sesuai (**bold**).
- solution: perkenal produk + MEKANISME UNIK kenapa ia berkesan walau cara lain gagal. body 3-4 ayat. bullets = 4-6 apa ia ajar/selesaikan (boleh gaya "rare count").
- offer: offer stack PENUH — bullets = 5-7 item (apa dapat + nilai tersirat RM). body 2 ayat. meta.price + meta.compare.
- bonus: 2-3 bonus bernilai (cth "Bonus: Checklist (nilai RM47)").
- proof: 3 testimoni. items[].q = "Nama, Bandar" Malaysia. items[].a = hasil spesifik & jujur, 2-3 ayat.
- guarantee: jaminan kukuh (wang dikembalikan / ganti rosak) + tenangkan hati.
- urgency: scarcity JUJUR.
- faq: 4-5 objection sebenar (original?, berapa lama sampai?, ada COD?, sesuai untuk saya?, cara bayar?).
- cta: tutup KUAT + ulang offer + value-stack ringkas. body 2-3 ayat. meta.price + meta.compare.
- ps: 2-3 ayat — ingatkan jaminan + urgency + harapan/doa ringkas.

EMAS: PANJANG > ringkas. Berlapis > satu nota. Spesifik & beremosi > fakta kering. Jujur > bombastik kosong. Setiap ayat ada sebab buat pembaca terus baca ke bawah.
TXT;

    public const BLOCK_LABELS = [
        'hero' => 'Hero / Headline', 'problem' => 'Masalah', 'agitate' => 'Agitate',
        'solution' => 'Solusi + Mekanisme', 'offer' => 'Tawaran', 'bonus' => 'Bonus',
        'proof' => 'Bukti Sosial', 'guarantee' => 'Jaminan', 'urgency' => 'Urgency',
        'faq' => 'Soalan Lazim', 'cta' => 'Call To Action', 'ps' => 'P.S.',
    ];

    /** @return array{page: array, source: string} */
    public function generate(array $brief): array
    {
        if ($key = config('services.openrouter.key')) {
            try {
                return ['page' => self::normalizeBlocks($this->viaOpenRouter($brief, $key)), 'source' => 'openrouter'];
            } catch (Throwable $e) {
                report($e);
            }
        }
        return ['page' => self::normalizeBlocks($this->mock($brief)), 'source' => 'mock'];
    }

    /**
     * Extract a structured brief from a free-form (typed or voiced) product
     * description. Returns keys: name, price, comparePrice, category, audience,
     * problem, benefits, tone — any may be null if not inferable.
     */
    public function extractBrief(string $description): array
    {
        $empty = ['name' => null, 'price' => null, 'comparePrice' => null, 'category' => null, 'audience' => null, 'problem' => null, 'benefits' => null, 'tone' => null];
        if (! ($key = config('services.openrouter.key'))) {
            return $empty;
        }
        try {
            $system = 'Anda pembantu yang ekstrak butiran produk daripada penerangan pengguna (Bahasa Melayu). '
                . 'PULANGKAN HANYA satu objek JSON sah dengan kunci: name (nama produk), price (nombor RM jualan), '
                . 'comparePrice (nombor RM harga asal/coret atau null), category, audience (target pelanggan), '
                . 'problem (masalah diselesaikan), benefits (kelebihan, pisah koma), tone (santai|profesional|agresif). '
                . 'Teka nilai munasabah jika tak dinyatakan. Jangan reka harga jika tiada — letak null.';
            $res = Http::withToken($key)
                ->timeout(60)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => config('services.openrouter.model', 'anthropic/claude-haiku-4.5'),
                    'max_tokens' => 1200,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $description],
                    ],
                ]);
            $data = json_decode($this->extractJson($res->json('choices.0.message.content') ?? '{}'), true) ?: [];

            return [
                'name' => $data['name'] ?? null,
                'price' => isset($data['price']) && is_numeric($data['price']) ? (float) $data['price'] : null,
                'comparePrice' => isset($data['comparePrice']) && is_numeric($data['comparePrice']) ? (float) $data['comparePrice'] : null,
                'category' => $data['category'] ?? null,
                'audience' => $data['audience'] ?? null,
                'problem' => $data['problem'] ?? null,
                'benefits' => $data['benefits'] ?? null,
                'tone' => in_array($data['tone'] ?? '', ['santai', 'profesional', 'agresif'], true) ? $data['tone'] : null,
            ];
        } catch (Throwable $e) {
            report($e);

            return $empty;
        }
    }

    private function viaOpenRouter(array $brief, string $key): array
    {
        $model = config('services.openrouter.model', 'anthropic/claude-haiku-4.5');
        $system = $this->systemPrompt();

        $res = Http::withToken($key)
            ->withHeaders(['HTTP-Referer' => config('app.url'), 'X-Title' => 'Mendap'])
            ->timeout(90)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $model,
                'max_tokens' => 8000,
                'reasoning' => ['effort' => 'low'],
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $this->prompt($brief)],
                ],
            ]);

        $content = $res->json('choices.0.message.content');
        if (! $content) {
            throw new \RuntimeException('OpenRouter: tiada output.');
        }
        $json = $this->extractJson($content);
        $data = json_decode($json, true);
        if (! isset($data['blocks']) || ! is_array($data['blocks'])) {
            throw new \RuntimeException('OpenRouter: format tak sah.');
        }
        return $data;
    }

    private function prompt(array $brief): string
    {
        $compare = $brief['comparePrice'] ?? $brief['compare_price'] ?? null;
        return collect([
            'Jana salespage lengkap untuk produk berikut. Ikut struktur 12-blok.',
            '',
            'Nama produk: ' . ($brief['name'] ?? ''),
            'Harga jualan: RM' . ($brief['price'] ?? 0),
            $compare ? "Harga coret: RM{$compare}" : '',
            'Kategori: ' . ($brief['category'] ?? ''),
            'Target audiens: ' . ($brief['audience'] ?? ''),
            'Masalah yang diselesaikan: ' . ($brief['problem'] ?? ''),
            'Kelebihan / selling point: ' . ($brief['benefits'] ?? ''),
            'Tona: ' . ($brief['tone'] ?? 'santai'),
        ])->filter()->implode("\n");
    }

    private function systemPrompt(): string
    {
        return self::FRAMEWORK
            . "\n\nPULANGKAN HANYA satu objek JSON sah: {\"blocks\": [ ... ]}. "
            . 'Setiap blok MESTI guna kunci ARAS ATAS ini secara LANGSUNG — JANGAN sarang dalam objek lain (cth JANGAN guna "kandungan"): '
            . 'type (salah satu: ' . implode(', ', array_keys(self::BLOCK_LABELS)) . '), label, headline, body, '
            . 'bullets (array string), items (array objek {q,a} untuk proof & faq), meta ({price, compare} nombor untuk offer & cta). '
            . 'Guna nama kunci TEPAT ini sahaja — JANGAN guna subheadline/subtitle/cta_text/description. '
            . 'Contoh: {"type":"hero","label":"Hero","headline":"Tajuk utama","body":"Ayat sokongan","bullets":["poin 1","poin 2"]}. '
            . 'TAMBAHAN GAYA (penting untuk convert): '
            . 'hero MESTI ada "badge" = hook provokatif SANGAT pendek untuk pill merah atas (cth soalan menusuk "Masih bertangguh?", "Penat tapi tak menjadi?"). '
            . 'Untuk blok problem & agitate, dalam setiap bullet, BALUT 2-3 perkataan/frasa emosi PALING penting dalam **bold** (guna tanda bintang dua, cth: "rasa **sangat bersalah** setiap kali"). Ini buat ayat hidup.';
    }

    /** Generate N salespage variants in parallel, each led by a different angle. */
    public function generateVariants(array $brief, int $count = 3): array
    {
        $angles = [
            'VARIASI EMOSI & CERITA — Mulakan dengan kisah/kesakitan emosi yang sangat peribadi. Headline = soalan yang menusuk hati. Fokus transformasi dalaman & rasa lega. Tona lembut, mendalam, naratif. Headline & sudut MESTI berbeza dari variasi lain.',
            'VARIASI NILAI & TAWARAN — Mulakan dengan hasil konkrit & offer stack. Headline = janji hasil spesifik + berbaloi. Fokus apa pembeli DAPAT, perbandingan nilai, bundle. Tona yakin, padat, logik. Headline & sudut MESTI berbeza dari variasi lain.',
            'VARIASI URGENCY & BUKTI SOSIAL — Mulakan dengan bukti sosial & FOMO. Headline = social proof / "ramai dah berubah". Fokus testimoni, jumlah terjual, scarcity jujur, urgency. Tona bertenaga, pantas. Headline & sudut MESTI berbeza dari variasi lain.',
        ];
        $angles = array_slice($angles, 0, max(1, $count));

        if (! ($key = config('services.openrouter.key'))) {
            return array_map(fn () => self::normalizeBlocks($this->mock($brief)), $angles);
        }

        try {
            $model = config('services.openrouter.copy_model') ?: 'anthropic/claude-sonnet-4.6';
            $sys = $this->systemPrompt();
            $userPrompt = $this->prompt($brief);
            $responses = Http::pool(fn ($pool) => array_map(
                fn ($angle) => $pool->withToken($key)->withHeaders(['X-Title' => 'Mendap'])->timeout(120)
                    ->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => $model,
                        'max_tokens' => 16000,
                        'response_format' => ['type' => 'json_object'],
                        'messages' => [
                            ['role' => 'system', 'content' => $sys . "\n\nSUDUT VARIASI INI — " . $angle],
                            ['role' => 'user', 'content' => $userPrompt],
                        ],
                    ]),
                $angles
            ));

            $out = [];
            foreach ($responses as $res) {
                try {
                    $data = json_decode($this->extractJson($res->json('choices.0.message.content') ?? '{}'), true);
                    if (isset($data['blocks']) && is_array($data['blocks'])) {
                        $out[] = self::normalizeBlocks($data);
                    }
                } catch (Throwable $e) {
                    // skip a failed variant
                }
            }
            if ($out) {
                return $out;
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [self::normalizeBlocks($this->mock($brief))];
    }

    /**
     * AI "media director" — looks at the merchant's images + the salespage story and
     * decides the genius placement (which image in which block, where the video goes,
     * whether an AI poster is still needed). Thinks like a senior DR marketer.
     * Returns the page with per-block 'image' set, plus 'video_block', 'gallery', 'need_poster'.
     */
    public function directMedia(array $page, array $imagePaths, ?string $video = null): array
    {
        $blocks = $page['blocks'] ?? [];
        $urls = array_map(fn ($p) => asset('storage/' . $p), $imagePaths);

        // No images & no video → maybe a poster is needed for the hero.
        if (empty($imagePaths)) {
            $page['video_block'] = $video ? 'hero' : null;
            $page['gallery'] = [];
            $page['need_poster'] = true;

            return $page;
        }

        $plan = null;
        if ($key = config('services.openrouter.key')) {
            $plan = $this->planMediaWithVision($blocks, $imagePaths, $video, $key);
        }

        if (! $plan) {
            return $this->basicMediaPlacement($page, $urls, $video);
        }

        // Apply the AI plan: assign each image to the first matching block.
        $assign = is_array($plan['assign'] ?? null) ? $plan['assign'] : [];
        $used = [];
        foreach ($assign as $idx => $blockType) {
            $idx = (int) $idx;
            if (! isset($urls[$idx])) {
                continue;
            }
            foreach ($blocks as $i => $b) {
                if (($b['type'] ?? '') === $blockType && empty($blocks[$i]['image'])) {
                    $blocks[$i]['image'] = $urls[$idx];
                    $used[$idx] = true;
                    break;
                }
            }
        }

        // Generate photorealistic EMOTIONAL photos for scene-marked blocks with no image yet.
        $scenes = is_array($plan['scenes'] ?? null) ? $plan['scenes'] : [];
        foreach ($scenes as $blockType => $scenePrompt) {
            if (! is_string($scenePrompt) || strlen(trim($scenePrompt)) < 10) {
                continue;
            }
            foreach ($blocks as $i => $b) {
                if (($b['type'] ?? '') === $blockType && empty($blocks[$i]['image'])) {
                    if ($img = $this->imageFromPrompt($scenePrompt . '. Photorealistic candid documentary photo of a real person, emotional & authentic, soft natural light. Absolutely NOT a cartoon/illustration/3D-render. No text, no words, no watermark.')) {
                        $blocks[$i]['image'] = asset('storage/' . $img);
                    }
                    break;
                }
            }
        }

        $page['blocks'] = $blocks;
        $page['video_block'] = $plan['video_block'] ?? ($video ? 'hero' : null);
        $page['gallery'] = array_values(array_filter($urls, fn ($u, $i) => ! isset($used[$i]), ARRAY_FILTER_USE_BOTH));
        // Need a poster only if nothing was placed in the hero.
        $heroHasImage = collect($blocks)->first(fn ($b) => ($b['type'] ?? '') === 'hero' && ! empty($b['image']));
        $page['need_poster'] = ! $heroHasImage && (bool) ($plan['need_poster'] ?? false);

        return $page;
    }

    private function planMediaWithVision(array $blocks, array $imagePaths, ?string $video, string $key): ?array
    {
        try {
            $summary = collect($blocks)
                ->map(fn ($b) => '- ' . ($b['type'] ?? '') . ': ' . \Illuminate\Support\Str::limit($b['headline'] ?? ($b['body'] ?? ''), 70))
                ->implode("\n");
            $instruction = "Anda pengarah kreatif & marketer direct-response SENIOR (fikir macam Alex Hormozi — apa yang CONVERT). "
                . "Salespage ini ada blok berikut (ikut urutan):\n{$summary}\n\n"
                . 'Di bawah ada ' . count($imagePaths) . ' gambar (Gambar #0, #1, ...)' . ($video ? ' dan ada 1 video' : '') . ". "
                . 'Tengok SETIAP gambar, faham apa ia, dan tugaskan ke blok PALING SESUAI untuk menjual: '
                . 'gambar produk/mockup → hero atau offer; gambar emosi/lifestyle/orang → problem atau agitate; '
                . 'gambar produk digunakan/demo → solution; screenshot bukti/testimoni → proof. '
                . 'Satu gambar satu blok. Kalau gambar tak sesuai untuk hero & tiada mockup produk, set need_poster=true. '
                . 'PULANGKAN HANYA JSON sah: {"assign": {"0":"hero","1":"problem"}, "video_block": "hero|solution|null", "need_poster": false, "scenes": {"problem":"...","agitate":"..."}}. '
                . 'Jenis blok sah: hero, problem, agitate, solution, offer, bonus, proof, guarantee, urgency, faq, cta, ps. '
                . 'TAMBAHAN PENTING — "scenes": untuk seksyen EMOSI (hero jika tiada gambar produk, problem, agitate) yang TIADA gambar ditugaskan, tulis satu ayat English ringkas untuk FOTO EMOSI photorealistic (ORANG sebenar dalam situasi emosi cerita — bukan produk, bukan teks). '
                . 'Contoh scene tajwid: "a worried Malaysian Muslim man in his 30s holding an open Quran at home, uncertain expression, soft natural window light, candid documentary photo, photorealistic". Buat scene yang MATCH emosi & audiens salespage.';

            $parts = [['type' => 'text', 'text' => $instruction]];
            foreach ($imagePaths as $i => $path) {
                $bytes = \Illuminate\Support\Facades\Storage::disk('public')->get($path);
                if (! $bytes) {
                    continue;
                }
                $parts[] = ['type' => 'text', 'text' => "Gambar #{$i}:"];
                $parts[] = ['type' => 'image_url', 'image_url' => ['url' => 'data:image/png;base64,' . base64_encode($bytes)]];
            }

            $res = Http::withToken($key)->timeout(90)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'google/gemini-2.5-flash',
                'max_tokens' => 800,
                'response_format' => ['type' => 'json_object'],
                'messages' => [['role' => 'user', 'content' => $parts]],
            ]);
            $data = json_decode($this->extractJson($res->json('choices.0.message.content') ?? '{}'), true);

            return is_array($data) ? $data : null;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Fallback placement when AI is unavailable: hero, problem, solution, then gallery. */
    private function basicMediaPlacement(array $page, array $urls, ?string $video): array
    {
        $order = ['hero', 'problem', 'solution'];
        $blocks = $page['blocks'] ?? [];
        $u = 0;
        foreach ($order as $type) {
            if (! isset($urls[$u])) {
                break;
            }
            foreach ($blocks as $i => $b) {
                if (($b['type'] ?? '') === $type) {
                    $blocks[$i]['image'] = $urls[$u++];
                    break;
                }
            }
        }
        $page['blocks'] = $blocks;
        $page['video_block'] = $video ? 'hero' : null;
        $page['gallery'] = array_values(array_slice($urls, $u));
        $page['need_poster'] = empty($urls);

        return $page;
    }

    /** Generate an AI product poster image; returns a stored public-disk path or null. */
    public function generatePoster(array $brief): ?string
    {
        if (! ($key = config('services.openrouter.key'))) {
            return null;
        }
        try {
            $name = $brief['name'] ?? 'produk';
            $cat = $brief['category'] ?? '';
            $audience = $brief['audience'] ?? '';
            $prompt = "Professional PHOTOREALISTIC product photograph for a high-converting e-commerce salespage. "
                . "Product: \"{$name}\"" . ($cat ? " (category: {$cat})" : '') . ($audience ? ", for {$audience}" : '') . '. '
                . 'Render an accurate, believable real-life product/mockup that fits this exact product, with tasteful props & setting that match its theme. '
                . 'Style: realistic commercial product photography, soft natural lighting, clean elegant background, shallow depth of field, sharp focus, premium, high detail. '
                . 'STRICT: photorealistic only — absolutely NOT a cartoon, NOT an illustration, NOT a stylised 3D cartoon. No text/words/watermark in the image.';

            return $this->imageFromPrompt($prompt);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Generate a photorealistic image from a prompt via gemini-3-pro-image; returns a stored path or null. */
    public function imageFromPrompt(string $prompt): ?string
    {
        if (! ($key = config('services.openrouter.key'))) {
            return null;
        }
        try {
            $res = Http::withToken($key)->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'google/gemini-3-pro-image',
                'modalities' => ['image', 'text'],
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);
            $url = data_get($res->json(), 'choices.0.message.images.0.image_url.url');
            if (! is_string($url) || ! str_starts_with($url, 'data:image')) {
                return null;
            }
            [$meta, $b64] = explode(',', $url, 2);
            $ext = str_contains($meta, 'jpeg') || str_contains($meta, 'jpg') ? 'jpg' : 'png';
            $path = 'posters/' . (auth()->id() ?: 'x') . '/' . \Illuminate\Support\Str::random(24) . '.' . $ext;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($b64));

            return $path;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    private function extractJson(string $text): string
    {
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $text, $m)) {
            $text = $m[1];
        }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        return ($start !== false && $end !== false) ? substr($text, $start, $end - $start + 1) : $text;
    }

    /**
     * Normalize block output to the canonical flat schema the Blade views expect.
     * Models vary: some nest content under a "kandungan"/"content" wrapper or use
     * aliases like "subheadline". This flattens + remaps so rendering never breaks.
     */
    public static function normalizeBlocks(array $page): array
    {
        $blocks = $page['blocks'] ?? (array_is_list($page) ? $page : []);

        return ['blocks' => array_values(array_filter(array_map(
            fn ($b) => is_array($b) ? self::normalizeBlock($b) : null,
            $blocks
        )))];
    }

    private static function normalizeBlock(array $b): array
    {
        // Flatten a nested content wrapper if the model used one.
        foreach (['kandungan', 'content', 'isi', 'data', 'fields'] as $wrap) {
            if (isset($b[$wrap]) && is_array($b[$wrap])) {
                $b = array_merge($b[$wrap], array_diff_key($b, [$wrap => true]));
                unset($b[$wrap]);
            }
        }

        // Remap common aliases to canonical names (only when the canonical is empty).
        $aliases = [
            'headline' => ['heading', 'title', 'tajuk', 'header'],
            'body' => ['subheadline', 'sub_headline', 'subheading', 'subtitle', 'description', 'desc', 'text', 'paragraph'],
            'bullets' => ['bullet', 'points', 'list', 'features', 'kelebihan'],
            'items' => ['faqs', 'questions', 'testimonials', 'qa', 'soalan'],
        ];
        foreach ($aliases as $canon => $variants) {
            if (empty($b[$canon])) {
                foreach ($variants as $v) {
                    if (! empty($b[$v])) {
                        $b[$canon] = $b[$v];
                        break;
                    }
                }
            }
        }

        if (isset($b['bullets'])) {
            $b['bullets'] = self::toStringList($b['bullets']);
        }
        if (isset($b['items']) && is_array($b['items'])) {
            $b['items'] = array_values(array_filter(array_map(function ($it) {
                if (! is_array($it)) {
                    return null;
                }

                return [
                    'q' => (string) ($it['q'] ?? $it['question'] ?? $it['name'] ?? $it['title'] ?? ''),
                    'a' => (string) ($it['a'] ?? $it['answer'] ?? $it['text'] ?? $it['body'] ?? ''),
                ];
            }, $b['items'])));
        }
        if (isset($b['meta']) && is_array($b['meta'])) {
            $b['meta'] = [
                'price' => $b['meta']['price'] ?? $b['meta']['harga'] ?? null,
                'compare' => $b['meta']['compare'] ?? $b['meta']['compare_price'] ?? $b['meta']['coret'] ?? null,
            ];
        } elseif (isset($b['price']) || isset($b['compare'])) {
            $b['meta'] = ['price' => $b['price'] ?? null, 'compare' => $b['compare'] ?? null];
        }

        return $b;
    }

    private static function toStringList(mixed $v): array
    {
        if (is_string($v)) {
            $parts = preg_split('/\r?\n|•|;/', $v);
            $v = count($parts) > 1 ? $parts : [$v];
        }
        if (! is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $item) {
            if (is_string($item)) {
                $out[] = trim($item);
            } elseif (is_array($item)) {
                $out[] = trim((string) ($item['text'] ?? $item['title'] ?? $item['label'] ?? reset($item)));
            }
        }

        return array_values(array_filter($out, fn ($s) => $s !== ''));
    }

    /** Deterministic local mock — same shape as the AI output. */
    public function mock(array $brief): array
    {
        $name = $brief['name'] ?: 'Produk Anda';
        $price = (float) ($brief['price'] ?: 89);
        $compare = (float) ($brief['comparePrice'] ?? $brief['compare_price'] ?? round($price * 1.8));
        $audience = $brief['audience'] ?: 'anda';
        $problem = $brief['problem'] ?? '';
        $benefits = $brief['benefits'] ?: 'Mudah,Cepat,Berkesan';
        $L = self::BLOCK_LABELS;

        return ['blocks' => [
            ['type' => 'hero', 'label' => $L['hero'],
                'headline' => "Akhirnya — Cara {$audience} Selesaikan " . ($problem ?: 'masalah harian') . " Tanpa Buang Masa & Duit",
                'body' => "{$name} direka khas untuk {$audience} yang dah penat cuba macam-macam tapi tak menjadi. Dalam beberapa hari, anda akan nampak bezanya.",
                'bullets' => ['Hasil pantas', 'Senang guna', 'Diuji & dipercayai ribuan pengguna']],
            ['type' => 'problem', 'label' => $L['problem'], 'headline' => 'Anda kenal situasi ni?',
                'body' => $problem ? "{$problem} — dan ia makin mengganggu hari-hari anda." : 'Setiap hari masalah yang sama berulang, dan ia mula menjejaskan keyakinan anda.',
                'bullets' => ['Dah cuba pelbagai cara tapi tak kekal', 'Buang duit pada benda yang tak menjadi', 'Rasa macam tiada jalan keluar']],
            ['type' => 'agitate', 'label' => $L['agitate'], 'headline' => 'Kalau dibiarkan, ia jadi lebih teruk',
                'body' => 'Makin lama ditangguh, makin susah nak pulih. Bukan setakat masa & duit — keyakinan diri pun terjejas. Tapi ia tak perlu jadi begitu.'],
            ['type' => 'solution', 'label' => $L['solution'], 'headline' => "Perkenalkan {$name}",
                'body' => "{$benefits}. Mekanisme uniknya buat ia berkesan walaupun cara lain dah gagal.",
                'bullets' => array_map('trim', explode(',', $benefits))],
            ['type' => 'offer', 'label' => $L['offer'], 'headline' => 'Inilah yang anda akan dapat',
                'body' => "Pakej penuh {$name} — semua yang anda perlukan untuk mula hari ini.",
                'bullets' => ["{$name} (nilai RM{$compare})", 'Panduan penggunaan langkah demi langkah', 'Sokongan WhatsApp keutamaan'],
                'meta' => ['price' => $price, 'compare' => $compare]],
            ['type' => 'bonus', 'label' => $L['bonus'], 'headline' => 'Bonus eksklusif (untuk tempahan hari ini)',
                'bullets' => ['Bonus #1: Checklist pantas (nilai RM47)', 'Bonus #2: Sesi panduan ringkas (nilai RM97)']],
            ['type' => 'proof', 'label' => $L['proof'], 'headline' => 'Apa kata mereka yang dah cuba',
                'items' => [['q' => 'Siti, Shah Alam', 'a' => 'Tak sangka secepat ni nampak hasil. Berbaloi sangat!'],
                    ['q' => 'Amir, Johor Bahru', 'a' => 'Mula-mula ragu, tapi sekarang saya repeat order untuk keluarga.']]],
            ['type' => 'guarantee', 'label' => $L['guarantee'], 'headline' => 'Jaminan Wang Dikembalikan 30 Hari',
                'body' => 'Cuba tanpa risiko. Kalau anda tak berpuas hati dalam 30 hari, kami pulangkan 100% wang anda. Risiko di pihak kami, bukan anda.'],
            ['type' => 'urgency', 'label' => $L['urgency'], 'headline' => 'Stok terhad untuk batch ini',
                'body' => 'Harga promosi ini hanya untuk tempahan terawal. Bila stok habis, harga kembali normal.'],
            ['type' => 'faq', 'label' => $L['faq'], 'headline' => 'Soalan Lazim',
                'items' => [['q' => 'Produk ni original?', 'a' => 'Ya, 100% original dengan jaminan penuh.'],
                    ['q' => 'Berapa lama sampai?', 'a' => 'Biasanya 1–3 hari bekerja seluruh Semenanjung.'],
                    ['q' => 'Ada COD?', 'a' => 'Ada — pilih sahaja semasa checkout di kawasan yang layak.']]],
            ['type' => 'cta', 'label' => $L['cta'], 'headline' => "Dapatkan {$name} Sekarang — RM{$price}",
                'body' => 'Jangan tangguh lagi. Klik butang di bawah dan mula rasai bezanya hari ini.',
                'meta' => ['price' => $price, 'compare' => $compare]],
            ['type' => 'ps', 'label' => $L['ps'],
                'body' => "P.S. Ingat — anda dilindungi jaminan 30 hari. Tiada sebab untuk tunggu. Stok promosi {$name} terhad."],
        ]];
    }
}
