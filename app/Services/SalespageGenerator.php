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
Anda copywriter direct-response & da'i digital TERBAIK Malaysia (gaya BeDaie "1 Rumah, 1 Daie") — pakar salespage yang CONVERT gila TAPI jujur & berakar pada ilmu. Sasaran: copy sekuat qadha.my.

⭐ 5 SENJATA WAJIB (kalau tiada, copy GAGAL — mesti ada SEMUA dalam setiap salespage):
1. HOOK ANGKA (enjin utama) — hero MESTI ada ANGKA spesifik "rare count" yang menyentak, dalam badge ATAU headline. Cth: "27 Hukum...", "23 Kesilapan Ramai Buat Tanpa Sedar", "9 Perkara Yang Ramai Terlepas". Angka buka gap penasaran. TIADA angka = tak lepas.
2. SEKSYEN LISTICLE BERNOMBOR (blok "listicle") — WAJIB satu blok senarai bernombor gaya TAJAM: headline "[N] [Kesilapan/Hukum/Tanda/Rahsia/Sebab] ...", 5-9 point. Tiap point: tajuk pendek menyentak + huraian 1-2 ayat. Point PALING mengejut letak di NOMBOR AKHIR. Setiap point buka rasa "eh betul jugak / aku pun macam tu".
3. FAEDAH RARE — selit 2-3 insight "baru aku tahu" yang BENAR & masyhur (bukan reka). Buat pembaca rasa dapat ilmu, bukan dijual.
4. JAMBATAN "KENA ADA GURU" — sebelum offer, jelaskan hasil/ilmu ni PAYAH dicapai sendiri (dah cuba YouTube/buku sendiri tapi tak menjadi) — sebab tu perlu panduan/sistem tersusun. Bukan "beli sebab wajib", tapi "sebab kena ada pembimbing yang betul".
5. OFFER SPESIFIK — nyatakan TEPAT apa DALAM produk: berapa bab/modul/muka surat, apa disertakan (audio, checklist, akses grup, sokongan), setiap satu dengan nilai jujur. BUKAN ayat kabur "kuasai semua ilmu".

PRINSIP SOKONGAN:
- SKIMMABLE, BUKAN ESEI (PALING PENTING) — page boleh PANJANG, tapi TIADA tembok teks. "body" SETIAP blok MAKSIMUM 2 ayat pendek (≤18 patah setiap ayat). Semua isi selebihnya WAJIB ditukar bentuk visual: bullets (4-6, pendek), senarai bernombor (listicle items), jadual (compare items), atau angka (stats). Pembaca SKIM salespage — point yang menang, bukan perenggan.
- Melayu santai Malaysia — "saya"/"kami", sapa "anda". Tic natural: "Betul tak?", "Nampak tak?", "Jujur je...", "Rugi tak kalau...".
- PAIN + HARAPAN (khauf+raja') — sentuh sakit dalam-dalam TAPI sentiasa beri harapan & jalan keluar. HARAM putus asa / takut-takutkan untuk paksa beli.
- OTORITI jujur — sandar sumber sahih secara UMUM ("ikut Mazhab Syafi'i", "rujukan ulama muktabar"). JANGAN reka nombor hadis, petikan, atau nama kitab yang tak pasti.
- JUALAN JUJUR — harga benar (TIADA "harga asal" palsu), urgency/stok benar sahaja, testimoni munasabah. Tipu = rosak barakah.

STRUKTUR BLOK (ikut turutan; page panjang sebab BANYAK blok — bukan sebab perenggan panjang):
- hero: badge = hook SANGAT pendek (soalan/angka menusuk). headline = janji + ANGKA/spesifik (5-9 patah, MAX 12). body MAX 2 ayat pendek. bullets 3-4 kemenangan. meta.customers.
- stats: WAJIB. jalur 3-4 angka BESAR bina kredibiliti pantas. items[]: q = angka pendek (cth "5,000+", "28", "100%", "4.9★"), a = label pendek. Angka munasabah & jujur.
- problem: "Anda kenal situasi ni?" body MAX 2 ayat pendek. bullets 4-5 pain SANGAT spesifik (balut frasa emosi penting dalam **bold**) — pain dalam BULLETS, bukan perenggan.
- agitate: kos kalau dibiar. body MAX 2 ayat (akhiri dengan harapan). Senarai kos = bullets (**bold**), BUKAN perenggan.
- listicle: WAJIB bernombor. headline "[N] [Kesilapan/Hukum/Tanda...] ...". items[]: 5-9 point, q = tajuk point pendek menyentak, a = huraian MAX 2 ayat pendek (+ faedah rare bila sesuai). Point paling kuat di NOMBOR AKHIR.
- solution: perkenal produk + jambatan "kena ada guru/panduan tersusun". body MAX 2 ayat. MEKANISME ditulis sebagai bullets "Langkah 1/2/3: ..." (3-4 langkah pendek) — JANGAN terangkan mekanisme dalam perenggan.
- compare: WAJIB. jadual "cara biasa vs [produk]". items[]: q = cara lama/biasa yang menyakitkan (negatif), a = penyelesaian dengan produk anda (positif). 4-5 baris, setiap sel ≤10 patah. Jujur, bukan memperlekeh produk lain.
- author: kredibiliti — kenapa boleh percaya. headline pendek. body 2-3 ayat. bullets 3-4 kelayakan/sandaran (untuk buku agama: sebut pegangan Ahli Sunnah wal Jamaah / Mazhab Syafi'i & proses semakan secara UMUM & jujur; JANGAN reka nama atau kelayakan spesifik yang tak pasti).
- offer: offer stack SPESIFIK — bullets 5-7 (apa TEPAT dalam produk + nilai RM tersirat). body 2 ayat. meta.price + meta.compare.
- bonus: 2-3 bonus bernilai (cth "Bonus: Audio MP3 sebutan (nilai RM47)").
- proof: 3 testimoni jujur. items[].q = "Nama, Bandar", items[].a = hasil spesifik 2-3 ayat.
- guarantee: jaminan kukuh + tenangkan hati.
- urgency: scarcity JUJUR.
- faq: 4-5 objection sebenar (original?, berapa lama sampai?, ada COD?, sesuai untuk saya?, cara bayar?).
- cta: tutup KUAT + ulang offer + value-stack ringkas. body 2-3 ayat. meta.price + meta.compare.
- ps: 2-3 ayat — jaminan + urgency + harapan/doa ringkas.

EMAS: ANGKA & spesifik MENANG. Point & jadual > perenggan. Banyak blok pendek > sedikit blok panjang. Jujur > bombastik kosong. Setiap baris ada sebab buat pembaca terus skim ke bawah.
TXT;

    public const BLOCK_LABELS = [
        'hero' => 'Hero / Headline', 'stats' => 'Statistik', 'problem' => 'Masalah', 'agitate' => 'Agitate',
        'listicle' => 'Senarai Bernombor', 'solution' => 'Solusi + Mekanisme', 'compare' => 'Perbandingan',
        'author' => 'Penulis / Kredibiliti', 'offer' => 'Tawaran', 'bonus' => 'Bonus', 'proof' => 'Bukti Sosial',
        'guarantee' => 'Jaminan', 'urgency' => 'Urgency', 'faq' => 'Soalan Lazim', 'cta' => 'Call To Action', 'ps' => 'P.S.',
    ];

    /** @return array{page: array, source: string} */
    public function generate(array $brief): array
    {
        if ($this->claudeEnabled()) {
            try {
                return ['page' => self::normalizeBlocks($this->viaClaude($brief)), 'source' => 'claude'];
            } catch (Throwable $e) {
                report($e);
            }
        }
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
     * Claude is the active text/LLM provider only when selected AND a key is
     * present. Falls back to OpenRouter otherwise, so every environment works.
     */
    private function claudeEnabled(): bool
    {
        return config('ai.provider') === 'claude' && filled(config('ai.claude.api_key'));
    }

    /** Build a configured Claude Messages-API provider (text copy generation). */
    private function claude(): \App\Services\AI\ClaudeProvider
    {
        return new \App\Services\AI\ClaudeProvider(
            (string) config('ai.claude.base_url'),
            config('ai.claude.api_key'),
            (string) config('ai.claude.model'),
            (int) config('ai.claude.timeout'),
            (string) config('ai.claude.version'),
            (int) config('ai.claude.max_tokens'),
        );
    }

    /** Main long-form salespage generation via Claude (mirrors viaOpenRouter). */
    private function viaClaude(array $brief): array
    {
        $content = $this->claude()->chat([
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $this->prompt($brief)],
        ], ['json' => true, 'max_tokens' => 8000]);

        if (! $content) {
            throw new \RuntimeException('Claude: tiada output.');
        }
        $data = json_decode($this->extractJson($content), true);
        if (! isset($data['blocks']) || ! is_array($data['blocks'])) {
            throw new \RuntimeException('Claude: format tak sah.');
        }

        return $data;
    }

    /**
     * Extract a structured brief from a free-form (typed or voiced) product
     * description. Returns keys: name, price, comparePrice, category, audience,
     * problem, benefits, tone — any may be null if not inferable.
     */
    public function extractBrief(string $description): array
    {
        $empty = ['name' => null, 'price' => null, 'comparePrice' => null, 'category' => null, 'audience' => null, 'problem' => null, 'benefits' => null, 'tone' => null];
        $useClaude = $this->claudeEnabled();
        if (! $useClaude && ! ($key = config('services.openrouter.key'))) {
            return $empty;
        }
        try {
            $system = 'Anda pembantu yang ekstrak butiran produk daripada penerangan pengguna (Bahasa Melayu). '
                . 'PULANGKAN HANYA satu objek JSON sah dengan kunci: name (nama produk), price (nombor RM jualan), '
                . 'comparePrice (nombor RM harga asal/coret atau null), category, audience (target pelanggan), '
                . 'problem (masalah diselesaikan), benefits (kelebihan, pisah koma), tone (santai|profesional|agresif). '
                . 'Teka nilai munasabah jika tak dinyatakan. Jangan reka harga jika tiada — letak null.';
            if ($useClaude) {
                // Cheap structured extraction → Haiku override (keeps copy on Sonnet).
                $content = $this->claude()->chat([
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $description],
                ], ['json' => true, 'max_tokens' => 1200, 'model' => 'claude-haiku-5-5']);
            } else {
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
                $content = $res->json('choices.0.message.content');
            }
            $data = json_decode($this->extractJson($content ?? '{}'), true) ?: [];

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
            'Jana salespage LONG-FORM lengkap untuk produk berikut. Ikut struktur blok penuh & WAJIB ada 5 senjata (hook angka di hero, blok listicle bernombor, faedah rare, jambatan "kena ada guru", offer spesifik).',
            '',
            'Nama produk: ' . ($brief['name'] ?? ''),
            'Harga jualan: RM' . ($brief['price'] ?? 0),
            $compare ? "Harga coret: RM{$compare}" : '',
            'Kategori: ' . ($brief['category'] ?? ''),
            'Target audiens: ' . ($brief['audience'] ?? ''),
            'Masalah yang diselesaikan: ' . ($brief['problem'] ?? ''),
            'Kelebihan / selling point: ' . ($brief['benefits'] ?? ''),
            'Tona: ' . ($brief['tone'] ?? 'santai'),
            '',
            $this->productTypeHint($brief),
        ])->filter()->implode("\n");
    }

    /** Tailor emphasis + block selection to the product type (book / course / physical). */
    private function productTypeHint(array $brief): string
    {
        $hay = mb_strtolower(($brief['category'] ?? '') . ' ' . ($brief['name'] ?? ''));
        if (preg_match('/buku|book|panduan|kitab|ebook|e-book|novel|majalah|tafsir|quran/u', $hay)) {
            return 'JENIS PRODUK = BUKU / ILMU. Tekankan: isi kandungan (bab, bilangan muka surat, topik utama), penulis/penerbit & kredibiliti, ilmu & transformasi yang pembaca dapat, format (cetak/PDF/audio). Offer mesti senaraikan APA DALAM buku + bonus ilmu. Blok "author" WAJIB ada. Gaya bahasa lebih ilmiah tapi mesra.';
        }
        if (preg_match('/kelas|kursus|course|online|webinar|bootcamp|latihan|coaching|mentor|akademi/u', $hay)) {
            return 'JENIS PRODUK = KELAS / KURSUS. Tekankan: modul & silibus (senarai apa diajar), akses (seumur hidup/tempoh), format (video/live/grup), sokongan mentor & komuniti, sijil, kohort/intake terhad (urgency JUJUR). Offer = modul + akses + rakaman + grup + bonus.';
        }

        return 'JENIS PRODUK = FIZIKAL. Tekankan: bahan/kualiti, hasil yang boleh dilihat & dirasa, cara guna mudah, penghantaran laju (COD), jaminan/pemulangan. Offer = produk + apa disertakan + penghantaran + bonus. Bukti sosial (gambar hasil) penting.';
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
            . 'Untuk blok problem & agitate, dalam setiap bullet, BALUT 2-3 perkataan/frasa emosi PALING penting dalam **bold** (guna tanda bintang dua, cth: "rasa **sangat bersalah** setiap kali"). Ini buat ayat hidup. '
            . 'WAJIB ADA satu blok "listicle" (letak selepas agitate, sebelum solution): headline bermula dengan ANGKA (cth "7 Kesilapan Tajwid Ramai Buat"), dan guna "items" = array objek {q,a} di mana q = tajuk point pendek & menyentak (renderer akan nomborkan 1,2,3…), a = huraian 1-2 ayat. Beri 5-9 point; point paling mengejut di NOMBOR AKHIR. '
            . 'WAJIB hero ada ANGKA spesifik (dalam badge atau headline). WAJIB blok solution ada jambatan "kena ada guru/panduan tersusun". WAJIB blok offer nyatakan TEPAT apa dalam produk (bab/modul/audio/bonus + nilai). '
            . 'Blok "stats" guna "items" [{q,a}]: q = angka pendek (cth "5,000+"), a = label pendek. Blok "compare" guna "items" [{q,a}]: q = cara biasa/lama (negatif), a = dengan produk (positif). Blok "author" guna headline + body + bullets (kelayakan). Sertakan blok stats (selepas hero), compare & author (sebelum offer) bila munasabah. '
            . 'GAYA HERO — blok hero BOLEH ada "style": "classic" | "image-first" | "bold-number". Pilih ikut sudut variasi: "bold-number" bila headline MULA dengan angka besar & kau mahu angka jadi fokus visual; "image-first" bila mahu gambar orang-pegang-produk memimpin di paling atas; "classic" = seimbang. Setiap variasi ELOK guna style berbeza supaya 3 variasi nampak berlainan. '
            . 'TIPOGRAFI (WAJIB) — headline (hero & SETIAP seksyen) mesti PENDEK, PADAT & bertenaga: idealnya 5-9 patah perkataan, MAKSIMUM 12. JANGAN headline panjang berjela yang jadi 6-8 baris & nampak sesak. Headline = pukulan. '
            . 'HAD KERAS BODY (WAJIB PATUH) — setiap "body" MAKSIMUM 2 ayat pendek (≤18 patah/ayat, jumlah ≤160 aksara). Kalau isi lebih dari itu, JANGAN panjangkan body — pindahkan isi ke bullets / items / jadual compare / stats. Body yang melebihi had = output GAGAL. Blok stats, compare & listicle WAJIB ada dalam setiap salespage.';
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

        if ($this->claudeEnabled()) {
            $out = $this->variantsViaClaude($brief, $angles);
            if ($out) {
                return $out;
            }
        }

        if (! ($key = config('services.openrouter.key'))) {
            return array_map(fn () => self::normalizeBlocks($this->mock($brief)), $angles);
        }

        try {
            $model = config('services.openrouter.copy_model') ?: 'anthropic/claude-sonnet-4.6';
            $sys = $this->systemPrompt();
            $userPrompt = $this->prompt($brief);
            $responses = Http::pool(fn ($pool) => array_map(
                fn ($angle) => $pool->withToken($key)->withHeaders(['X-Title' => 'Mendap'])->timeout(240)
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
     * Generate the N angled variants via Claude, still in parallel (Http::pool
     * against the Anthropic Messages API). Returns [] on total failure so the
     * caller can fall back to OpenRouter / mock.
     */
    private function variantsViaClaude(array $brief, array $angles): array
    {
        try {
            $cfg = config('ai.claude');
            $model = $cfg['model'] ?: 'claude-sonnet-5-5';
            $sys = $this->systemPrompt();
            $userPrompt = $this->prompt($brief);
            $jsonNudge = "\n\nBalas dengan SATU objek JSON sah sahaja. Jangan tambah teks, ulasan, atau pagar kod (```).";

            $responses = Http::pool(fn ($pool) => array_map(
                fn ($angle) => $pool
                    ->withHeaders(['x-api-key' => $cfg['api_key'], 'anthropic-version' => $cfg['version']])
                    ->acceptJson()
                    ->timeout(240)
                    ->post(rtrim($cfg['base_url'], '/') . '/messages', [
                        'model' => $model,
                        'max_tokens' => 16000,
                        'system' => $sys . "\n\nSUDUT VARIASI INI — " . $angle . $jsonNudge,
                        'messages' => [['role' => 'user', 'content' => $userPrompt]],
                    ]),
                $angles
            ));

            $out = [];
            foreach ($responses as $res) {
                try {
                    if (! ($res instanceof \Illuminate\Http\Client\Response) || $res->failed()) {
                        continue;
                    }
                    $text = '';
                    foreach ((array) data_get($res->json(), 'content', []) as $block) {
                        if (($block['type'] ?? null) === 'text') {
                            $text .= (string) ($block['text'] ?? '');
                        }
                    }
                    $data = json_decode($this->extractJson($text ?: '{}'), true);
                    if (isset($data['blocks']) && is_array($data['blocks'])) {
                        $out[] = self::normalizeBlocks($data);
                    }
                } catch (Throwable $e) {
                    // skip a failed variant
                }
            }

            return $out;
        } catch (Throwable $e) {
            report($e);

            return [];
        }
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

        // A real person READING/USING the actual product — lifestyle proof shot (fills proof/solution gap).
        if (! empty($imagePaths[0]) && config('services.openrouter.key')) {
            foreach (['proof', 'solution', 'guarantee'] as $target) {
                $slot = null;
                foreach ($blocks as $i => $b) {
                    if (($b['type'] ?? '') === $target && empty($blocks[$i]['image'])) {
                        $slot = $i;
                        break;
                    }
                }
                if ($slot !== null) {
                    $img = $this->readingProductImage($imagePaths[0]) ?? $this->readingProductImage($imagePaths[0]);
                    if ($img) {
                        $blocks[$slot]['image'] = asset('storage/' . $img);
                    }
                    break;
                }
            }
        }

        // A real person holding the ACTUAL product, presented to the camera — hero trust shot.
        // Image generation can transiently fail — retry once so the mandate (every salespage
        // carries a person holding the real product) actually holds.
        if (! empty($imagePaths[0]) && config('services.openrouter.key')) {
            $held = $this->holdingProductImage($imagePaths[0]) ?? $this->holdingProductImage($imagePaths[0]);
            if ($held) {
                $heldUrl = asset('storage/' . $held);
                foreach ($blocks as $i => $b) {
                    if (($b['type'] ?? '') === 'hero') {
                        $flat = $blocks[$i]['image'] ?? null;   // existing flat mockup, if any
                        $blocks[$i]['image'] = $heldUrl;
                        if ($flat) {                            // keep the clean cover in the solution section
                            foreach ($blocks as $j => $bj) {
                                if (($bj['type'] ?? '') === 'solution' && empty($blocks[$j]['image'])) {
                                    $blocks[$j]['image'] = $flat;
                                    break;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }

        $page['blocks'] = $blocks;
        $page['video_block'] = $plan['video_block'] ?? ($video ? 'hero' : null);
        $page['gallery'] = array_values(array_filter($urls, fn ($u, $i) => ! isset($used[$i]), ARRAY_FILTER_USE_BOTH));
        // Need a poster only if nothing was placed in the hero.
        $heroHasImage = collect($blocks)->first(fn ($b) => ($b['type'] ?? '') === 'hero' && ! empty($b['image']));
        $page['need_poster'] = ! $heroHasImage && (bool) ($plan['need_poster'] ?? false);
        if (in_array($plan['theme'] ?? '', ['default', 'hijau', 'biru', 'oren', 'ungu', 'gelap'], true)) {
            $page['theme'] = $plan['theme'];
        }

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
                . 'Tengok SETIAP gambar, faham apa ia, dan tugaskan ke blok PALING SESUAI untuk menjual. '
                . 'PENTING — salespage yang convert di Malaysia penuh dengan gambar (20+): AGIHKAN SEMUA gambar yang ada, JANGAN tinggalkan mana-mana tanpa blok. '
                . 'Panduan: mockup produk terbaik → hero; mockup lain → solution, offer, bonus, cta (satu setiap blok); gambar emosi/orang → problem/agitate; screenshot bukti → proof. '
                . 'Satu gambar satu blok, ikut keutamaan menjual. Kalau gambar tak sesuai untuk hero & tiada mockup produk, set need_poster=true. '
                . 'PULANGKAN HANYA JSON sah: {"assign": {"0":"hero","1":"solution","2":"offer"}, "video_block": "hero|solution|null", "need_poster": false, "scenes": {"problem":"...","agitate":"...","cta":"..."}, "theme": "hijau"}. '
                . 'TAMBAHAN "theme" (PENTING) — pilih SATU tema warna yang paling MATCH warna dominan gambar produk & vibe niche, dari: default (pink), hijau (hijau+emas, Islamik/rohani), biru (teal), oren (hangat), ungu (purple), gelap. Cth: cover buku ungu/teal → "ungu"; produk Islamik hijau → "hijau"; skincare pink → "default". JANGAN pilih warna yang MELAWAN warna gambar produk. '
                . 'Jenis blok sah: hero, problem, agitate, solution, offer, bonus, proof, guarantee, urgency, faq, cta, ps. '
                . 'TAMBAHAN PENTING — "scenes": untuk blok problem & agitate (emosi gelap→cerah) DAN blok cta (emosi harapan/lega/berjaya selepas guna produk), tulis satu ayat English ringkas setiap satu untuk FOTO EMOSI photorealistic (ORANG sebenar Malaysia dalam situasi cerita — bukan produk, bukan teks). Sentiasa beri KETIGA-TIGA scene ini walaupun blok itu sudah ada gambar lain. '
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

    /** Fallback placement when AI is unavailable: spread across the page, then gallery. */
    private function basicMediaPlacement(array $page, array $urls, ?string $video): array
    {
        $order = ['hero', 'solution', 'offer', 'problem', 'bonus', 'cta', 'proof', 'agitate'];
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

    /**
     * Photorealistic image of a real person HOLDING the actual product, presented toward the camera.
     * Uses the product image as a reference (image-to-image) so the real cover is kept — builds trust.
     */
    public function holdingProductImage(string $productPath): ?string
    {
        $prompt = 'PHOTOREALISTIC photograph. A warm, friendly Malaysian Muslim person in modest, respectful attire '
            . 'holds up THIS EXACT book (from the reference image) toward the camera with both hands at chest height — '
            . 'the cover faces the camera FLAT, FULLY VISIBLE, large and centred in the frame, in sharp focus. '
            . 'CRITICAL — the book cover must be REPRODUCED EXACTLY as in the reference image: identical title wording, '
            . 'exact same fonts & letter shapes, same logo, same illustration/artwork, same layout and same colours. '
            . 'Do NOT redraw, restyle, re-letter, translate, simplify, crop, or alter ANY element of the cover. '
            . 'Treat the reference image as the real printed cover of the physical book placed into her hands. '
            . 'Her face, hands and body are real and photographic; genuine smile, looking straight at the camera, upper-body shot. '
            . 'Bright, clean, softly-lit indoor background, natural window light, shallow depth of field, authentic UGC / commercial look. '
            . 'STRICT: must look like a REAL photograph — absolutely NOT a cartoon, illustration, painting, anime, or 3D render. '
            . 'No added text, captions, extra logos, or watermark anywhere in the image.';

        return $this->imageFromPrompt($prompt, [$productPath]);
    }

    /**
     * Photorealistic candid shot of a person actually READING/USING the exact product at home —
     * the lifestyle proof shot every high-converting Malaysian salespage carries.
     */
    public function readingProductImage(string $productPath): ?string
    {
        $prompt = 'PHOTOREALISTIC candid lifestyle photograph. A Malaysian Muslim person in modest attire sits comfortably '
            . 'at home (sofa or prayer mat area) reading THIS EXACT book from the reference image — the book is open or held '
            . 'naturally, its front cover clearly visible and angled toward the camera. Reproduce the cover EXACTLY as in the '
            . 'reference (same title text, artwork, colours); do NOT redraw or alter it. Warm serene expression, absorbed in '
            . 'reading. Soft natural window light, cosy authentic Malaysian home interior, shallow depth of field, candid '
            . 'documentary style. STRICT: real photograph look — NOT cartoon/illustration/3D. No added text or watermark.';

        return $this->imageFromPrompt($prompt, [$productPath]);
    }

    /**
     * Generate a photorealistic image via gemini-3-pro-image; returns a stored path or null.
     * Pass $refPaths (public-disk paths) to do image-to-image (e.g. keep a real product/book in the shot).
     */
    public function imageFromPrompt(string $prompt, array $refPaths = []): ?string
    {
        if (! ($key = config('services.openrouter.key'))) {
            return null;
        }
        try {
            $content = $prompt;
            if (! empty($refPaths)) {
                $content = [['type' => 'text', 'text' => $prompt]];
                foreach (array_slice($refPaths, 0, 2) as $p) {
                    try {
                        $bytes = \Illuminate\Support\Facades\Storage::disk('public')->get($p);
                        if ($bytes) {
                            $content[] = ['type' => 'image_url', 'image_url' => ['url' => 'data:image/png;base64,' . base64_encode($bytes)]];
                        }
                    } catch (Throwable $e) {
                        // skip an unreadable reference
                    }
                }
            }
            $res = Http::withToken($key)->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'google/gemini-3-pro-image',
                'modalities' => ['image', 'text'],
                'messages' => [['role' => 'user', 'content' => $content]],
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
            ['type' => 'stats', 'label' => $L['stats'], 'items' => [
                ['q' => '5,000+', 'a' => 'pelanggan berpuas hati'],
                ['q' => '4.9★', 'a' => 'purata penilaian'],
                ['q' => '100%', 'a' => 'jaminan wang dikembalikan'],
            ]],
            ['type' => 'problem', 'label' => $L['problem'], 'headline' => 'Anda kenal situasi ni?',
                'body' => $problem ? "{$problem} — dan ia makin mengganggu hari-hari anda." : 'Setiap hari masalah yang sama berulang, dan ia mula menjejaskan keyakinan anda.',
                'bullets' => ['Dah cuba pelbagai cara tapi tak kekal', 'Buang duit pada benda yang tak menjadi', 'Rasa macam tiada jalan keluar']],
            ['type' => 'agitate', 'label' => $L['agitate'], 'headline' => 'Kalau dibiarkan, ia jadi lebih teruk',
                'body' => 'Makin lama ditangguh, makin susah nak pulih. Bukan setakat masa & duit — keyakinan diri pun terjejas. Tapi ia tak perlu jadi begitu.'],
            ['type' => 'listicle', 'label' => $L['listicle'], 'headline' => '5 Silap Yang Ramai Buat Tanpa Sedar',
                'body' => 'Sebelum kita masuk penyelesaian — kenali dulu punca sebenar. Nombor 5 paling ramai terlepas.',
                'items' => [
                    ['q' => 'Ingat semua cara sama sahaja', 'a' => 'Sebab tu dah cuba banyak benda tapi tak menjadi — asasnya tak kena dari mula.'],
                    ['q' => 'Belajar berterabur, tiada susunan', 'a' => 'Tangkap sikit sana sini, akhirnya keliru dan cepat lupa.'],
                    ['q' => 'Tiada orang betulkan kesilapan', 'a' => 'Belajar sendiri buat silap yang sama berulang tanpa sedar.'],
                    ['q' => 'Tangguh sebab rasa "nanti-nanti"', 'a' => 'Makin lama ditangguh, makin hilang momentum & keyakinan.'],
                    ['q' => 'Fikir ia rumit & bukan untuk saya', 'a' => 'Padahal dengan panduan yang betul, ia jauh lebih mudah daripada yang disangka.'],
                ]],
            ['type' => 'solution', 'label' => $L['solution'], 'headline' => "Perkenalkan {$name}",
                'body' => "{$benefits}. Mekanisme uniknya buat ia berkesan walaupun cara lain dah gagal.",
                'bullets' => array_map('trim', explode(',', $benefits))],
            ['type' => 'compare', 'label' => $L['compare'], 'headline' => 'Kenapa cara ini lebih baik',
                'items' => [
                    ['q' => 'Belajar sendiri — keliru & cepat lupa', 'a' => 'Panduan tersusun, terus faham & ingat'],
                    ['q' => 'Kelas mahal & jadual terikat', 'a' => 'Belajar ikut masa sendiri, sekali beli'],
                    ['q' => 'Tiada orang betulkan kesilapan', 'a' => 'Contoh jelas untuk semak sendiri'],
                    ['q' => 'Bahan berselerak di internet', 'a' => 'Semua dalam satu, tersusun rapi'],
                ]],
            ['type' => 'author', 'label' => $L['author'], 'headline' => 'Disusun dengan teliti & amanah',
                'body' => 'Setiap bahagian disemak supaya tepat, mudah difahami, dan selamat diamalkan — bukan sekadar himpunan nota.',
                'bullets' => ['Berpegang pada sumber muktabar', 'Bahasa mudah untuk semua peringkat', 'Disusun oleh pasukan berpengalaman']],
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
