<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Percubaan gagal dibenarkan sebelum dikunci. */
    private const MAX_ATTEMPTS = 5;

    /** Tempoh kunci selepas had dicapai, dalam saat. */
    private const DECAY_SECONDS = 60;

    /**
     * Diikat kepada emel DAN alamat IP, supaya penyerang tidak boleh mengunci
     * peniaga sah keluar hanya dengan membanjiri emel mereka dari tempat lain.
     */
    private function throttleKey(Request $request): string
    {
        return 'login:'.Str::transliterate(Str::lower((string) $request->input('email'))).'|'.$request->ip();
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // mendap.com memegang salespage, checkout dan rekod affiliate setiap
        // peniaga. Tanpa had ini, kata laluan boleh diteka tanpa henti.
        $kunci = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($kunci, self::MAX_ATTEMPTS)) {
            $saat = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percubaan log masuk. Cuba lagi dalam {$saat} saat.",
            ]);
        }

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            RateLimiter::hit($kunci, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => 'Emel atau kata laluan salah.']);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        if (Auth::user()->isSuspended()) {
            return redirect('/suspended');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'business_name' => $data['business_name'],
            'name' => $data['business_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'merchant',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        \App\Services\Mailer::welcome($user);

        return redirect(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
