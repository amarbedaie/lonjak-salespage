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

    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('salespage');

        return view('dashboard.orders.show', compact('order'));
    }

    public function invoice(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('salespage', 'user');

        return view('dashboard.orders.invoice', compact('order'));
    }

    /** Stream the currently-filtered orders as a CSV download. */
    public function export(Request $request)
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

        $filename = 'order-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads Malay characters correctly
            fputcsv($out, ['Tarikh', 'Nama', 'Telefon', 'Emel', 'Alamat', 'Negeri', 'Produk', 'Kuantiti', 'Kupon', 'Diskaun', 'Tambahan', 'Harga tambahan', 'Jumlah', 'Status', 'Bayaran']);
            $query->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $o) {
                    fputcsv($out, [
                        $o->created_at->format('Y-m-d H:i'), $o->customer, $o->phone, $o->email, $o->address, $o->state,
                        $o->product_name, $o->qty, $o->coupon_code, $o->discount, $o->bump_title, $o->bump_price,
                        $o->total, $o->status, $o->payment_status,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function setStatus(Order $order, Request $request)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $status = $request->validate(['status' => 'required|in:baru,diproses,dihantar,selesai,batal'])['status'];
        $order->update(['status' => $status]);

        return back();
    }
}
