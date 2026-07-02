<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Pustaka Media — self-service per-merchant media library.
 *
 * Merchants upload their product mockups & video clips ONCE, tagged with the product
 * name; from then on every salespage generate auto-detects and attaches the right
 * media (MockupLibrary / VideoLibrary fuzzy match) — no re-uploading, everything auto.
 */
#[Layout('components.layouts.app')]
#[Title('Pustaka Media')]
class MediaLibrary extends Component
{
    use WithFileUploads;

    public string $mockupTitle = '';
    public $newMockups = [];

    public string $videoTitle = '';
    public $newVideo = null;

    public string $linkTitle = '';
    public string $linkUrl = '';

    public function saveMockups(): void
    {
        $this->validate([
            'mockupTitle' => 'required|string|min:3|max:120',
            'newMockups' => 'required|array|min:1',
            'newMockups.*' => 'image|max:8192',
        ], [], ['mockupTitle' => 'nama produk', 'newMockups' => 'gambar', 'newMockups.*' => 'gambar']);

        $slug = Str::slug($this->mockupTitle);
        $dir = 'mockups/'.auth()->id();
        $n = collect(Storage::disk('public')->files($dir))
            ->filter(fn ($f) => str_starts_with(basename($f), $slug.'-'))
            ->count();
        foreach ((array) $this->newMockups as $file) {
            $n++;
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $file->storeAs($dir, $slug.'-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT).'.'.$ext, 'public');
        }
        $this->reset('mockupTitle', 'newMockups');
        session()->flash('ok', 'Mockup disimpan! Lepas ni taip nama produk je masa jana salespage — gambar masuk sendiri.');
    }

    public function saveVideo(): void
    {
        $this->validate([
            'videoTitle' => 'required|string|min:3|max:120',
            'newVideo' => 'required|file|mimes:mp4,mov,webm,m4v|max:102400', // 100MB
        ], [], ['videoTitle' => 'nama produk', 'newVideo' => 'video']);

        $slug = Str::slug($this->videoTitle);
        $dir = 'videos/'.auth()->id();
        $n = collect(Storage::disk('public')->files($dir))
            ->filter(fn ($f) => str_starts_with(basename($f), $slug))
            ->count() + 1;
        $ext = strtolower($this->newVideo->getClientOriginalExtension() ?: 'mp4');
        $this->newVideo->storeAs($dir, $slug.'-'.str_pad((string) $n, 2, '0', STR_PAD_LEFT).'.'.$ext, 'public');
        $this->reset('videoTitle', 'newVideo');
        session()->flash('ok', 'Video disimpan! Ia akan dipasang sendiri pada salespage produk ini.');
    }

    public function addLink(): void
    {
        $this->validate([
            'linkTitle' => 'required|string|min:3|max:120',
            'linkUrl' => 'required|url|max:300',
        ], [], ['linkTitle' => 'nama produk', 'linkUrl' => 'link video']);

        $path = 'videos/'.auth()->id().'/links.json';
        $map = Storage::disk('public')->exists($path)
            ? (json_decode(Storage::disk('public')->get($path), true) ?: [])
            : [];
        $map[trim($this->linkTitle)] = trim($this->linkUrl);
        Storage::disk('public')->put($path, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->reset('linkTitle', 'linkUrl');
        session()->flash('ok', 'Link video disimpan!');
    }

    public function deleteFile(string $path): void
    {
        $uid = auth()->id();
        abort_unless(
            str_starts_with($path, 'mockups/'.$uid.'/') || str_starts_with($path, 'videos/'.$uid.'/'),
            403
        );
        if (basename($path) === 'links.json') {
            return;
        }
        Storage::disk('public')->delete($path);
    }

    public function deleteLink(string $title): void
    {
        $path = 'videos/'.auth()->id().'/links.json';
        if (! Storage::disk('public')->exists($path)) {
            return;
        }
        $map = json_decode(Storage::disk('public')->get($path), true) ?: [];
        unset($map[$title]);
        Storage::disk('public')->put($path, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function render()
    {
        $uid = auth()->id();

        $mockups = collect(Storage::disk('public')->files('mockups/'.$uid))
            ->filter(fn ($f) => preg_match('/\.(jpe?g|png|webp)$/i', $f))
            ->groupBy(fn ($f) => (string) preg_replace('/-\d+$/', '', pathinfo($f, PATHINFO_FILENAME)))
            ->sortKeys();

        $videos = collect(Storage::disk('public')->files('videos/'.$uid))
            ->filter(fn ($f) => preg_match('/\.(mp4|mov|webm|m4v)$/i', $f))
            ->values();

        $linksPath = 'videos/'.$uid.'/links.json';
        $links = Storage::disk('public')->exists($linksPath)
            ? (json_decode(Storage::disk('public')->get($linksPath), true) ?: [])
            : [];

        return view('livewire.media-library', compact('mockups', 'videos', 'links'));
    }
}
