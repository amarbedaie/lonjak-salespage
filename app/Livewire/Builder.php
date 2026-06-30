<?php

namespace App\Livewire;

use App\Models\Salespage;
use App\Services\SalespageGenerator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Salespage baru')]
class Builder extends Component
{
    use WithFileUploads;

    public string $name = '';
    public ?float $price = null;
    public ?float $comparePrice = null;
    public string $category = 'Kecantikan';
    public string $audience = '';
    public string $problem = '';
    public string $benefits = '';
    public string $tone = 'santai';

    public string $stage = 'brief';   // brief | result
    public string $source = 'mock';
    public array $page = [];
    public array $variants = [];      // multiple generated variants
    public int $selectedVariant = 0;
    public bool $posterDone = false;
    public ?int $productId = null;

    public array $images = [];        // stored image paths
    public string $videoUrl = '';
    public $newImages = [];           // temp uploads (Livewire)
    public string $description = '';  // free-form / voiced product description

    /** Pre-fill the brief from a saved product (Pustaka Produk → Jana Salespage). */
    public function mount(): void
    {
        if ($id = (int) request()->query('product')) {
            $p = \App\Models\Product::where('user_id', auth()->id())->find($id);
            if ($p) {
                $this->productId = $p->id;
                $this->name = $p->name;
                $this->price = (float) $p->price;
                $this->comparePrice = $p->compare_price ? (float) $p->compare_price : null;
                $this->category = $p->category ?: $this->category;
                $this->audience = $p->audience ?: '';
                $this->problem = $p->problem ?: '';
                $this->benefits = $p->benefits ?: '';
                $this->tone = $p->tone ?: 'santai';
                $this->images = is_array($p->images) ? $p->images : [];
                $this->videoUrl = $p->video_url ?: '';
            }
        }
    }

    /** Store newly uploaded images immediately (so previews + saving are reliable). */
    public function updatedNewImages(): void
    {
        $this->validate(['newImages.*' => 'image|max:5120']);
        foreach ((array) $this->newImages as $file) {
            $this->images[] = $file->store('products/'.auth()->id(), 'public');
        }
        $this->newImages = [];
    }

    public function removeImage(int $i): void
    {
        unset($this->images[$i]);
        $this->images = array_values($this->images);
    }

    /** "Terangkan je" — AI fills the brief fields from a free-form/voiced description. */
    public function fillFromDescription(SalespageGenerator $gen): void
    {
        $desc = trim($this->description);
        if (mb_strlen($desc) < 10) {
            $this->addError('description', 'Terangkan produk anda sedikit lagi (taip atau tekan 🎤).');

            return;
        }
        $b = $gen->extractBrief($desc);
        foreach (['name', 'audience', 'problem', 'benefits'] as $k) {
            if (! empty($b[$k])) {
                $this->$k = $b[$k];
            }
        }
        if ($b['price'] !== null) {
            $this->price = $b['price'];
        }
        if ($b['comparePrice'] !== null) {
            $this->comparePrice = $b['comparePrice'];
        }
        if (! empty($b['tone'])) {
            $this->tone = $b['tone'];
        }
        if (! empty($b['category'])) {
            $cats = ['Kecantikan', 'Kesihatan', 'Fesyen', 'Makanan', 'Gadget', 'Rumah', 'Lain-lain'];
            $this->category = in_array($b['category'], $cats, true) ? $b['category'] : 'Lain-lain';
        }
    }

    public function generate(SalespageGenerator $gen): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'audience' => 'required|string|max:255',
        ], [], [
            'name' => 'nama produk', 'price' => 'harga', 'audience' => 'audiens',
        ]);

        $brief = [
            'name' => $this->name, 'price' => $this->price, 'comparePrice' => $this->comparePrice,
            'category' => $this->category, 'audience' => $this->audience,
            'problem' => $this->problem, 'benefits' => $this->benefits, 'tone' => $this->tone,
        ];

        // 3 variants (different angles), in parallel.
        $this->variants = $gen->generateVariants($brief, 3);
        $this->selectedVariant = 0;
        $this->page = $this->variants[0] ?? [];
        $this->source = config('services.openrouter.key') ? 'openrouter' : 'mock';

        $this->posterDone = false;
        $this->stage = 'result';

        $user = auth()->user();
        if ($user->ai_credits > 0) {
            $user->decrement('ai_credits');
        }
    }

    /** Auto-fired after variants render — AI poster ONLY if merchant has no real product image (smart). */
    public function makePoster(SalespageGenerator $gen): void
    {
        if ($this->posterDone || ! empty($this->images)) {
            $this->posterDone = true;

            return;
        }
        $this->genPoster($gen);
    }

    /** Manual button — force an AI poster even when product images already exist. */
    public function forcePoster(SalespageGenerator $gen): void
    {
        $this->genPoster($gen);
    }

    private function genPoster(SalespageGenerator $gen): void
    {
        $this->posterDone = true;
        if ($poster = $gen->generatePoster(['name' => $this->name, 'category' => $this->category, 'audience' => $this->audience])) {
            array_unshift($this->images, $poster);
        }
    }

    public function selectVariant(int $i): void
    {
        if (isset($this->variants[$i])) {
            $this->selectedVariant = $i;
            $this->page = $this->variants[$i];
        }
    }

    public function publish()
    {
        $sp = Salespage::create([
            'user_id' => auth()->id(),
            'title' => $this->name ?: 'Salespage baru',
            'slug' => Str::slug($this->name).'-'.Str::lower(Str::random(4)),
            'product_name' => $this->name,
            'price' => $this->price ?? 0,
            'compare_price' => $this->comparePrice,
            'category' => $this->category,
            'status' => 'draf',
            'brief' => [
                'name' => $this->name, 'price' => $this->price, 'comparePrice' => $this->comparePrice,
                'category' => $this->category, 'audience' => $this->audience,
                'problem' => $this->problem, 'benefits' => $this->benefits, 'tone' => $this->tone,
            ],
            'blocks' => $this->page,
            'images' => $this->images,
            'video_url' => $this->videoUrl,
        ]);

        session()->flash('saved', true);

        return $this->redirectRoute('salespages.show', $sp, navigate: true);
    }

    public function back(): void
    {
        $this->stage = 'brief';
    }

    public function render()
    {
        return view('livewire.builder');
    }
}
