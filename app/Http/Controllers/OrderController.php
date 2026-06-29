<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('status', 'all');
        $q = trim($request->query('q', ''));

        $query = $user->orders()->latest();
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('customer', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }
        $orders = $query->get();
        $total = $user->orders()->where('status', '!=', 'batal')->sum('total');

        return view('dashboard.orders', compact('orders', 'filter', 'q', 'total'));
    }

    public function setStatus(Order $order, Request $request)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $status = $request->validate(['status' => 'required|in:baru,diproses,dihantar,selesai,batal'])['status'];
        $order->update(['status' => $status]);

        return back();
    }
}
