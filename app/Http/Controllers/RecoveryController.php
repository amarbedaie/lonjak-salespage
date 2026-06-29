<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class RecoveryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders()->get();
        $pending = $orders->whereIn('status', ['baru', 'diproses'])->values();

        return view('dashboard.recovery', [
            'pending' => $pending,
            'pendingValue' => $pending->sum('total'),
            'newCount' => $orders->where('status', 'baru')->count(),
            'doneCount' => $orders->where('status', 'selesai')->count(),
            'business' => $user->business_name ?: 'kedai kami',
        ]);
    }
}
