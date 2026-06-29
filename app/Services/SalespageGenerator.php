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
Anda copywriter direct-response pakar pasaran Malaysia.
Jana salespage convert tinggi dalam Bahasa Melayu (boleh campur English natural / Manglish).
Ikut struktur 12-blok: hero (headline 4U) -> problem -> agitate -> solution+mekanisme unik
-> offer (offer stack + harga) -> bonus -> proof (testimoni/before-after) -> guarantee (risk reversal)
-> urgency (scarcity jujur) -> faq (handle objection lokal) -> cta -> ps.
Tulis spesifik, elak ayat bombastik kosong, fokus pada outcome & objection sebenar pelanggan.
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
                return ['page' => $this->viaOpenRouter($brief, $key), 'source' => 'openrouter'];
            } catch (Throwable $e) {
                report($e);
            }
        }
        return ['page' => $this->mock($brief), 'source' => 'mock'];
    }

    private function viaOpenRouter(array $brief, string $key): array
    {
        $model = config('services.openrouter.model', 'anthropic/claude-3.5-haiku');
        $system = self::FRAMEWORK
            . "\n\nPULANGKAN HANYA satu objek JSON sah dengan kunci \"blocks\" (array). "
            . "Setiap blok ada: type (salah satu: " . implode(', ', array_keys(self::BLOCK_LABELS)) . "), "
            . "label, dan kandungan sesuai (headline, body, bullets[] , items[{q,a}], meta{price,compare}). "
            . "Untuk offer & cta, isi meta.price & meta.compare dengan nombor.";

        $res = Http::withToken($key)
            ->withHeaders(['HTTP-Referer' => config('app.url'), 'X-Title' => 'Lonjak'])
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

    private function extractJson(string $text): string
    {
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $text, $m)) {
            $text = $m[1];
        }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        return ($start !== false && $end !== false) ? substr($text, $start, $end - $start + 1) : $text;
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
