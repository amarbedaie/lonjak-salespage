<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Auth::user()->coupons()->latest()->get();

        return view('dashboard.coupons', compact('coupons'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);
        $data['code'] = Str::upper(preg_replace('/\s+/', '', $data['code']));

        if (Auth::user()->coupons()->where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'Kod kupon ini sudah wujud.']);
        }

        Auth::user()->coupons()->create($data + ['active' => true]);

        return back()->with('ok', 'Kupon dicipta.');
    }

    public function toggle(Coupon $coupon)
    {
        $this->authorizeOwner($coupon);
        $coupon->update(['active' => ! $coupon->active]);

        return back();
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorizeOwner($coupon);
        $coupon->delete();

        return back()->with('ok', 'Kupon dipadam.');
    }

    private function authorizeOwner(Coupon $coupon): void
    {
        abort_unless($coupon->user_id === Auth::id(), 403);
    }
}
