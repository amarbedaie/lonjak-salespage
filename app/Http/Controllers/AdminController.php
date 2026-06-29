<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Salespage;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $merchants = User::where('role', 'merchant')->count();
        $stats = [
            'merchants' => $merchants,
            'mrr' => $merchants * 89,
            'gmv' => Order::where('status', '!=', 'batal')->sum('total'),
            'orders' => Order::count(),
            'salespages' => Salespage::count(),
            'liveSalespages' => Salespage::where('status', 'live')->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => Order::latest()->take(6)->get(),
            'recentMerchants' => User::where('role', 'merchant')->latest()->take(6)->get(),
        ]);
    }

    public function merchants(Request $request)
    {
        $q = trim($request->query('q', ''));
        $query = User::latest();
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('business_name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        return view('admin.merchants', ['profiles' => $query->get(), 'q' => $q]);
    }

    public function merchant(User $user)
    {
        $orders = $user->orders()->where('status', '!=', 'batal')->get();

        return view('admin.merchant', [
            'merchant' => $user,
            'pages' => $user->salespages()->latest()->get(),
            'orders' => $user->orders()->latest()->get(),
            'revenue' => $orders->sum('total'),
        ]);
    }

    public function control(User $user, Request $request)
    {
        $action = $request->validate(['action' => 'required|string'])['action'];
        match ($action) {
            'suspend' => $user->update(['status' => 'digantung']),
            'activate' => $user->update(['status' => 'aktif']),
            'make_admin' => $user->update(['role' => 'admin']),
            'make_merchant' => $user->update(['role' => 'merchant']),
            'credits' => $user->update(['ai_credits' => max(0, (int) $request->input('ai_credits', 0))]),
            'plan' => $user->update(['plan' => $request->input('plan', 'pro')]),
            default => null,
        };

        return back()->with('ok', 'Dikemas kini.');
    }

    public function salespages(Request $request)
    {
        $q = trim($request->query('q', ''));
        $query = Salespage::latest();
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"));
        }

        return view('admin.salespages', ['pages' => $query->get(), 'q' => $q]);
    }

    public function salespageStatus(Salespage $salespage, Request $request)
    {
        $status = $request->validate(['status' => 'required|in:live,draf,dijeda'])['status'];
        $salespage->update(['status' => $status]);

        return back();
    }

    public function orders(Request $request)
    {
        $filter = $request->query('status', 'all');
        $q = trim($request->query('q', ''));
        $query = Order::latest();
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('customer', 'like', "%{$q}%")->orWhere('product_name', 'like', "%{$q}%"));
        }
        $gmv = Order::where('status', '!=', 'batal')->sum('total');

        return view('admin.orders', ['orders' => $query->limit(200)->get(), 'filter' => $filter, 'q' => $q, 'gmv' => $gmv]);
    }
}
