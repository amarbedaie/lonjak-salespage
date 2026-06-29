<x-mail.shell title="Terima kasih">
    <h1 style="margin:0 0 8px;font-size:20px;">Terima kasih, {{ $order->customer }}! 🎉</h1>
    <p style="color:#5a5159;line-height:1.6;">Tempahan anda untuk <strong>{{ $order->product_name }}</strong> (×{{ $order->qty }}) telah kami terima. Kami akan hubungi anda tidak lama lagi untuk pengesahan & penghantaran.</p>
    <table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;">
        <tr><td style="padding:6px 0;color:#9b9097;">Produk</td><td style="padding:6px 0;text-align:right;font-weight:600;">{{ $order->product_name }} × {{ $order->qty }}</td></tr>
        <tr><td style="padding:10px 0;border-top:1px solid #ece7ea;color:#9b9097;">Jumlah</td><td style="padding:10px 0;border-top:1px solid #ece7ea;text-align:right;font-weight:700;color:#c81e6a;font-size:16px;">RM{{ number_format($order->total, 2) }}</td></tr>
    </table>
    <p style="color:#9b9097;font-size:13px;">Sebarang pertanyaan, balas emel ini atau hubungi kedai melalui WhatsApp.</p>
</x-mail.shell>
