<?php

namespace App\Http\Controllers;

use App\Models\Salespage;
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
        ]);
        $salespage->update($data);

        return back()->with('ok', 'Salespage dikemas kini.');
    }

    public function destroy(Salespage $salespage)
    {
        $this->authorizeOwner($salespage);
        $salespage->delete();

        return redirect()->route('salespages.index');
    }

    private function authorizeOwner(Salespage $salespage): void
    {
        abort_unless($salespage->user_id === Auth::id(), 403);
    }
}
