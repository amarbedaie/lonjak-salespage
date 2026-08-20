<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Sebelum ini laluan POST /login hanya membawa middleware `web` dan
 * `RedirectIfAuthenticated` — tiada had kadar langsung, jadi kata laluan
 * peniaga boleh diteka tanpa henti. Ujian ini mengunci pembaikan itu.
 */
class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private function peniaga(): User
    {
        return User::factory()->create([
            'email' => 'peniaga@ujian.test',
            'password' => 'kata-laluan-betul',
        ]);
    }

    public function test_login_dikunci_selepas_lima_percubaan_gagal(): void
    {
        $this->peniaga();

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'peniaga@ujian.test', 'password' => 'salah'])
                ->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        $this->post('/login', ['email' => 'peniaga@ujian.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Terlalu banyak percubaan',
            session('errors')->first('email')
        );
    }

    public function test_kata_laluan_betul_ditolak_semasa_dikunci(): void
    {
        $this->peniaga();

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'peniaga@ujian.test', 'password' => 'salah']);
        }

        // Inti pembaikan: walaupun kata laluan betul, kunci masih berkuat kuasa.
        $this->post('/login', ['email' => 'peniaga@ujian.test', 'password' => 'kata-laluan-betul']);

        $this->assertGuest();
    }

    public function test_log_masuk_berjaya_mengosongkan_kiraan(): void
    {
        $this->peniaga();
        $kunci = 'login:peniaga@ujian.test|127.0.0.1';

        for ($i = 1; $i <= 4; $i++) {
            $this->post('/login', ['email' => 'peniaga@ujian.test', 'password' => 'salah']);
        }
        $this->assertSame(4, RateLimiter::attempts($kunci));

        $this->post('/login', ['email' => 'peniaga@ujian.test', 'password' => 'kata-laluan-betul']);
        $this->assertAuthenticated();

        $this->assertSame(0, RateLimiter::attempts($kunci));
    }
}
