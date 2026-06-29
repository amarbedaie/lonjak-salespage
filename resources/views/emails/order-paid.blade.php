<x-mail.shell title="Bayaran diterima">
    <h1 style="margin:0 0 8px;font-size:20px;">Bayaran diterima ✅</h1>
    <p style="color:#5a5159;line-height:1.6;">Order daripada <strong>{{ $order->customer }}</strong> telah dibayar melalui BayarCash. Anda boleh proses penghantaran sekarang.</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;">
        <tr><td style="padding:6px 0;color:#9b9097;">Produk</td><td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->product_name }} × {{ $order->qty }}</td></tr>
        <tr><td style="padding:6px 0;color:#9b9097;">Rujukan bayaran</td><td style="padding:6px 0;text-align:right;">{{ $order->payment_ref }}</td></tr>
        <tr><td style="padding:10px 0;border-top:1px solid #ece7ea;color:#9b9097;">Jumlah dibayar</td><td style="padding:10px 0;border-top:1px solid #ece7ea;text-align:right;font-weight:700;color:#16a34a;font-size:16px;">RM{{ number_format($order->total, 2) }}</td></tr>
    </table>
    <p style="margin:20px 0;"><a href="{{ route('orders.index') }}" style="background:#c81e6a;color:#fff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:600;display:inline-block;">Proses order</a></p>
</x-mail.shell>
