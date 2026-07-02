<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Per-merchant video library (Pustaka Video) with product auto-detect — the video twin
 * of MockupLibrary. Hybrid sources, both fuzzy-matched by product name:
 *
 *   1. Local clips  : storage/app/public/videos/{user}/<judul-buku>-NN.mp4 (short promos)
 *   2. Link map     : storage/app/public/videos/{user}/links.json  {"Judul Buku": "https://youtu.be/..."}
 *                     (long videos hosted on YouTube — streamed via embed, zero server load)
 *
 * Builder auto-attaches the best match when the merchant leaves the video field empty.
 */
class VideoLibrary
{
    private const DIR = 'videos';

    /** Best-matching video URL (local storage URL or embed link) for a product name, or null. */
    public static function match(string $productName, int|string $userId): ?string
    {
        $want = self::collapse($productName);
        if (strlen($want) < 4) {
            return null;
        }

        $dir = self::DIR.'/'.$userId;
        $best = null;
        $bestScore = 0.0;

        foreach (Storage::disk('public')->files($dir) as $f) {
            if (! preg_match('/\.(mp4|mov|m4v|webm)$/i', $f)) {
                continue;
            }
            $base = preg_replace('/-\d+$/', '', pathinfo($f, PATHINFO_FILENAME));
            $score = self::score($want, self::collapse($base));
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = asset('storage/'.$f);
            }
        }

        $mapPath = $dir.'/links.json';
        if (Storage::disk('public')->exists($mapPath)) {
            $map = json_decode(Storage::disk('public')->get($mapPath), true) ?: [];
            foreach ($map as $title => $url) {
                if (! is_string($url) || ! str_starts_with($url, 'http')) {
                    continue;
                }
                $score = self::score($want, self::collapse((string) $title));
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $url;
                }
            }
        }

        return $bestScore >= 0.55 ? $best : null;
    }

    private static function collapse(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower($s)) ?? '';
    }

    private static function score(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }
        if (str_contains($b, $a) || str_contains($a, $b)) {
            return 0.9;
        }
        similar_text($a, $b, $pct);

        return $pct / 100;
    }
}
