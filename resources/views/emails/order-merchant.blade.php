<x-mail.shell title="Order baru">
    <h1 style="margin:0 0 8px;font-size:20px;">Order baru masuk! 🛒</h1>
    <p style="color:#5a5159;line-height:1.6;">Anda ada tempahan baru di Lonjak:</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;">
        <tr><td style="padding:6px 0;color:#9b9097;">Pelanggan</td><td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->customer }}</td></tr>
        <tr><td style="padding:6px 0;color:#9b9097;">Telefon</td><td style="padding:6px 0;text-align:right;">{{ $order->phone }}</td></tr>
        <tr><td style="padding:6px 0;color:#9b9097;">Produk</td><td style="padding:6px 0;text-align:right;">{{ $order->product_name }} × {{ $order->qty }}</td></tr>
        <tr><td style="padding:6px 0;color:#9b9097;">Negeri</td><td style="padding:6px 0;text-align:right;">{{ $order->state }}</td></tr>
        <tr><td style="padding:10px 0;border-top:1px solid #ece7ea;color:#9b9097;">Jumlah</td><td style="padding:10px 0;border-top:1px solid #ece7ea;text-align:right;font-weight:700;color:#c81e6a;font-size:16px;">RM{{ number_format($order->total, 2) }}</td></tr>
    </table>
    <p style="margin:20px 0;"><a href="{{ route('orders.index') }}" style="background:#c81e6a;color:#fff;text-decoration:none;padding:11px 20px;border-radius:8px;font-weight:600;display:inline-block;">Lihat order</a></p>
    <p style="color:#9b9097;font-size:13px;">Tip: follow-up pelanggan via WhatsApp di tab Recovery untuk pengesahan.</p>
</x-mail.shell>
