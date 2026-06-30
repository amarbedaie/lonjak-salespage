<?php

namespace App\Http\Controllers;

use App\Services\BayarcashGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        return view('dashboard.payments', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'bayarcash_pat' => 'nullable|string',
            'bayarcash_portal_key' => 'nullable|string|max:255',
            'bayarcash_api_secret' => 'nullable|string|max:255',
            'bayarcash_sandbox' => 'nullable|boolean',
            'bayarcash_active' => 'nullable|boolean',
        ]);

        // Blank secret fields keep the existing stored value.
        $pat = $data['bayarcash_pat'] ?: $user->bayarcash_pat;
        $secret = $data['bayarcash_api_secret'] ?: $user->bayarcash_api_secret;
        $portal = $data['bayarcash_portal_key'] ?: $user->bayarcash_portal_key;
        $sandbox = (bool) ($data['bayarcash_sandbox'] ?? false);
        $active = (bool) ($data['bayarcash_active'] ?? false);

        // Verify credentials before enabling.
        if ($active) {
            if (! $pat || ! $secret || ! $portal) {
                return back()->withErrors(['bayarcash' => 'Lengkapkan PAT, Portal Key & API Secret untuk aktifkan.']);
            }
            if (! BayarcashGateway::validateCredentials($pat, $sandbox)) {
                return back()->withErrors(['bayarcash' => 'PAT tidak sah (gagal authenticate dengan BayarCash). Semak token & mod sandbox/live.']);
            }
        }

        $user->update([
            'bayarcash_pat' => $pat,
            'bayarcash_portal_key' => $portal,
            'bayarcash_api_secret' => $secret,
            'bayarcash_sandbox' => $sandbox,
            'bayarcash_active' => $active,
        ]);

        return back()->with('ok', $active ? 'BayarCash diaktifkan! Salespage anda kini terima bayaran online.' : 'Tetapan bayaran disimpan.');
    }
}
