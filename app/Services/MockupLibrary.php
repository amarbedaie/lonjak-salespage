<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Shared product-mockup library (public/mockups/bedaie — 59 BeDaie book covers,
 * named "<Tajuk-Slug>-NN.jpeg" and grouped by title).
 *
 * Given a product name, fuzzy-matches the right book and returns its mockups, so a
 * salespage can be generated with the real covers WITHOUT the merchant uploading anything.
 */
class MockupLibrary
{
    private const DIR = 'mockups/bedaie';

    /** Public web paths (relative to /public) of the best-matching title's mockups. */
    public static function match(string $productName, int $limit = 6): array
    {
        $dir = public_path(self::DIR);
        $want = self::collapse($productName);
        if (! is_dir($dir) || strlen($want) < 4) {
            return [];
        }

        // Group files by title (strip the trailing -NN shot number).
        $groups = [];
        foreach (scandir($dir) as $f) {
            if (! preg_match('/\.(jpe?g|png|webp)$/i', $f)) {
                continue;
            }
            $base = preg_replace('/-\d+\.(jpe?g|png|webp)$/i', '', $f);
            $groups[self::collapse($base)][] = $f;
        }

        $best = null;
        $bestScore = 0.0;
        foreach ($groups as $slug => $files) {
            $score = self::score($want, $slug);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $files;
            }
        }
        if ($bestScore < 0.55 || ! $best) {
            return [];
        }
        sort($best);

        return array_map(fn ($f) => self::DIR.'/'.$f, array_slice($best, 0, $limit));
    }

    /**
     * Auto-attach for a merchant: copy the matched mockups onto the public storage disk
     * (products/{user}) so the whole existing pipeline — vision director, holding-shot
     * reference, imageUrls() — works unchanged. Returns storage-disk paths.
     */
    public static function attachFor(string $productName, int|string $userId, int $limit = 6): array
    {
        $paths = [];
        foreach (self::match($productName, $limit) as $pub) {
            $dest = 'products/'.$userId.'/lib-'.strtolower(basename($pub));
            if (! Storage::disk('public')->exists($dest)) {
                $bytes = @file_get_contents(public_path($pub));
                if ($bytes === false) {
                    continue;
                }
                Storage::disk('public')->put($dest, $bytes);
            }
            $paths[] = $dest;
        }

        return $paths;
    }

    /** Lowercase + strip everything non-alphanumeric, so word breaks & dashes never matter. */
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
