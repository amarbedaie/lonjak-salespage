<x-mail.shell title="Selamat datang">
    <h1 style="margin:0 0 8px;font-size:20px;">Selamat datang, {{ $user->business_name }}! 🎉</h1>
    <p style="color:#5a5159;line-height:1.6;">Akaun Mendap anda dah aktif. Anda kini boleh bina salespage convert tinggi dengan AI, terima order, dan urus semua dari satu dashboard.</p>
    <p style="margin:24px 0;"><a href="{{ route('dashboard') }}" style="background:#c81e6a;color:#fff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:600;display:inline-block;">Buka Dashboard</a></p>
    <p style="color:#9b9097;font-size:13px;">Plan Pro · 3 generasi AI sebulan · Tiada yuran transaksi.</p>
</x-mail.shell>
