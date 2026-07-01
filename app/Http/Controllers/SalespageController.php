<?php

namespace App\Http\Controllers;

use App\Models\Salespage;
use App\Services\SalespageGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalespageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $pages = $user->salespages()->latest('updated_at')->get();
        $orders = $user->orders()->where('status', '!=', 'batal')->get();

        $agg = $orders->groupBy('salespage_id')->map(fn ($g) => [
            'orders' => $g->count(), 'revenue' => $g->sum('total'),
        ]);

        return view('dashboard.salespages.index', compact('pages', 'agg'));
    }

    public function show(Salespage $salespage)
    {
        $this->authorizeOwner($salespage);
        $orders = $salespage->orders()->where('status', '!=', 'batal')->get();
        $stats = [
            'visits' => $salespage->visits,
            'orders' => $orders->count(),
            'revenue' => $orders->sum('total'),
        ];

        return view('dashboard.salespages.show', compact('salespage', 'stats'));
    }

    public function setStatus(Salespage $salespage, Request $request)
    {
        $this->authorizeOwner($salespage);
        $status = $request->validate(['status' => 'required|in:live,draf,dijeda'])['status'];
        $salespage->update(['status' => $status]);

        return back();
    }

    public function update(Salespage $salespage, Request $request)
    {
        $this->authorizeOwner($salespage);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'gateway' => 'nullable|string',
            'theme' => 'nullable|in:default,hijau,biru,oren,ungu,gelap',
            'fb_pixel' => 'nullable|string|max:50',
            'tiktok_pixel' => 'nullable|string|max:50',
            'ga_id' => 'nullable|string|max:50',
            'offer_ends_at' => 'nullable|date',
            'bump_enabled' => 'nullable|boolean',
            'bump_title' => 'nullable|string|max:120',
            'bump_desc' => 'nullable|string|max:200',
            'bump_price' => 'nullable|numeric|min:0',
        ]);
        // Checkboxes only submit when checked — normalise to an explicit boolean when the bump form is posted.
        if ($request->has('bump_enabled') || $request->has('bump_title') || $request->has('bump_price')) {
            $data['bump_enabled'] = $request->boolean('bump_enabled');
        }
        $salespage->update($data);

        return back()->with('ok', 'Salespage dikemas kini.');
    }

    public function destroy(Salespage $salespage)
    {
        $this->authorizeOwner($salespage);
        $salespage->delete();

        return redirect()->route('salespages.index');
    }

    public function duplicate(Salespage $salespage)
    {
        $this->authorizeOwner($salespage);
        $copy = $salespage->replicate(['visits']);
        $copy->title = $salespage->title.' (salinan)';
        $copy->slug = \Illuminate\Support\Str::slug($salespage->title).'-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));
        $copy->status = 'draf';
        $copy->visits = 0;
        $copy->save();

        return redirect()->route('salespages.show', $copy)->with('ok', 'Salespage diduplikasi sebagai draf baharu.');
    }

    /** Switch which generated variant is the live one (places media on first use). */
    public function setVariant(Salespage $salespage, Request $request, SalespageGenerator $gen)
    {
        $this->authorizeOwner($salespage);
        $i = (int) $request->input('index', 0);
        $variants = $salespage->variants ?? [];
        if (! isset($variants[$i])) {
            return back();
        }

        $page = $variants[$i];
        // First time this variant is chosen, run the AI media director so it looks complete.
        $hasImage = collect($page['blocks'] ?? [])->contains(fn ($b) => ! empty($b['image']));
        if (! $hasImage) {
            $page = $gen->directMedia($page, $salespage->images ?? [], $salespage->video_url);
            $variants[$i] = $page;
        }

        $salespage->update([
            'blocks' => $page,
            'variants' => $variants,
            'variant_index' => $i,
            'theme' => $page['theme'] ?? $salespage->theme,
        ]);

        return back()->with('ok', 'Variasi '.($i + 1).' kini aktif & dipaparkan.');
    }

    private function authorizeOwner(Salespage $salespage): void
    {
        abort_unless($salespage->user_id === Auth::id(), 403);
    }
}
