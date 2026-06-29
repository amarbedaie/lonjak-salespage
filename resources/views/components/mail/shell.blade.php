@props(['title' => 'Lonjak'])
<!DOCTYPE html>
<html lang="ms">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>{{ $title }}</title></head>
<body style="margin:0;background:#f6f3f4;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#2a2228;">
    <div style="max-width:520px;margin:0 auto;padding:24px 16px;">
        <div style="padding:8px 4px 20px;font-size:18px;font-weight:700;color:#c81e6a;">↗ Lonjak</div>
        <div style="background:#ffffff;border:1px solid #ece7ea;border-radius:14px;padding:28px 24px;">
            {{ $slot }}
        </div>
        <p style="text-align:center;color:#9b9097;font-size:12px;margin-top:18px;">© {{ date('Y') }} Lonjak · ADFusion Marketing</p>
    </div>
</body>
</html>
