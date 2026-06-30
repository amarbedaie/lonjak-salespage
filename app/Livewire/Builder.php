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
    public ?int $productId = null;

    public array $images = [];        // stored image paths
    public string $videoUrl = '';
    public $newImages = [];           // temp uploads (Livewire)

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

    public function generate(SalespageGenerator $gen): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'audience' => 'required|string|max:255',
        ], [], [
            'name' => 'nama produk', 'price' => 'harga', 'audience' => 'audiens',
        ]);

        $result = $gen->generate([
            'name' => $this->name, 'price' => $this->price, 'comparePrice' => $this->comparePrice,
            'category' => $this->category, 'audience' => $this->audience,
            'problem' => $this->problem, 'benefits' => $this->benefits, 'tone' => $this->tone,
        ]);

        $this->page = $result['page'];
        $this->source = $result['source'];
        $this->stage = 'result';

        $user = auth()->user();
        if ($user->ai_credits > 0) {
            $user->decrement('ai_credits');
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
