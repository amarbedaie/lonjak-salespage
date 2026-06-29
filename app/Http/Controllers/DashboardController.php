<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders()->latest()->get();
        $pages = $user->salespages()->latest('updated_at')->get();

        $paid = $orders->where('status', '!=', 'batal');
        $overview = [
            'revenue' => $paid->sum('total'),
            'orders' => $orders->count(),
            'liveSalespages' => $pages->where('status', 'live')->count(),
            'visitors' => $pages->sum('visits'),
        ];

        // 14-day revenue series
        $series = array_fill(0, 14, 0);
        $todayTs = now()->startOfDay()->getTimestamp();
        foreach ($paid as $o) {
            $daysAgo = intdiv($todayTs - $o->created_at->copy()->startOfDay()->getTimestamp(), 86400);
            $idx = 13 - $daysAgo;
            if ($idx >= 0 && $idx < 14) {
                $series[$idx] += (float) $o->total;
            }
        }

        $revByPage = $paid->groupBy('salespage_id')->map(fn ($g) => $g->sum('total'));
        $topPages = $pages->map(fn ($p) => tap($p, fn ($p) => $p->rev = $revByPage[$p->id] ?? 0))
            ->sortByDesc('rev')->take(4)->values();

        return view('dashboard.index', [
            'user' => $user,
            'overview' => $overview,
            'series' => $series,
            'recent' => $orders->take(6),
            'topPages' => $topPages,
            'empty' => $pages->isEmpty() && $orders->isEmpty(),
        ]);
    }
}
