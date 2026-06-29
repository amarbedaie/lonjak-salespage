<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        return view('dashboard.settings', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);
        Auth::user()->update($data);

        return back()->with('ok', 'Profil dikemas kini.');
    }
}
