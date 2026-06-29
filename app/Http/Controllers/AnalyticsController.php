<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $orders = $user->orders()->get();
        $pages = $user->salespages()->get();
        $paid = $orders->where('status', '!=', 'batal');

        $revenue = $paid->sum('total');
        $aov = $paid->count() ? $revenue / $paid->count() : 0;
        $visits = $pages->sum('visits');
        $cr = $visits > 0 ? ($orders->count() / $visits) * 100 : 0;

        $series = array_fill(0, 14, 0);
        $todayTs = now()->startOfDay()->getTimestamp();
        foreach ($paid as $o) {
            $idx = 13 - intdiv($todayTs - $o->created_at->copy()->startOfDay()->getTimestamp(), 86400);
            if ($idx >= 0 && $idx < 14) {
                $series[$idx] += (float) $o->total;
            }
        }

        $statuses = ['baru' => 'Baru', 'diproses' => 'Diproses', 'dihantar' => 'Dihantar', 'selesai' => 'Selesai', 'batal' => 'Batal'];
        $statusCounts = collect($statuses)->map(fn ($label, $key) => [
            'label' => $label, 'n' => $orders->where('status', $key)->count(),
        ])->values();

        $revByPage = $paid->groupBy('salespage_id');
        $top = $pages->map(function ($p) use ($revByPage) {
            $g = $revByPage[$p->id] ?? collect();
            $p->rev = $g->sum('total');
            $p->ord = $g->count();

            return $p;
        })->filter(fn ($p) => $p->rev > 0 || $p->visits > 0)->sortByDesc('rev')->values();

        return view('dashboard.analytics', compact('revenue', 'aov', 'orders', 'visits', 'cr', 'series', 'statusCounts', 'top'));
    }
}
