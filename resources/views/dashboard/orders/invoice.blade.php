<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $rm = fn ($n) => 'RM'.number_format((float) $n, 2);
        $subtotal = (float) $order->total - (float) $order->bump_price + (float) $order->discount;
        $unit = $order->qty > 0 ? $subtotal / $order->qty : $subtotal;
        $inv = 'INV-'.str_pad($order->id, 5, '0', STR_PAD_LEFT);
    @endphp
    <title>{{ $inv }} · {{ $order->customer }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1a1a1a;background:#f4f4f5;padding:32px 16px;line-height:1.5}
        .sheet{max-width:720px;margin:0 auto;background:#fff;border-radius:14px;box-shadow:0 4px 24px rgba(0,0,0,.08);overflow:hidden}
        .pad{padding:40px}
        .top{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;flex-wrap:wrap}
        .brand{font-size:20px;font-weight:800;letter-spacing:-.02em}
        .muted{color:#71717a;font-size:13px}
        .tag{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.03em}
        .paid{background:#dcfce7;color:#166534}.unpaid{background:#fef3c7;color:#92400e}
        h1{font-size:26px;font-weight:800;letter-spacing:-.02em;margin-bottom:2px}
        .grid{display:flex;gap:40px;flex-wrap:wrap;margin-top:32px}
        .grid>div{min-width:180px}
        .label{font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#a1a1aa;font-weight:700;margin-bottom:6px}
        table{width:100%;border-collapse:collapse;margin-top:32px;font-size:14px}
        th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#a1a1aa;font-weight:700;padding:0 0 10px;border-bottom:2px solid #f0f0f0}
        th.r,td.r{text-align:right}
        td{padding:14px 0;border-bottom:1px solid #f4f4f5}
        .totals{margin-top:20px;margin-left:auto;width:280px;font-size:14px}
        .totals .row{display:flex;justify-content:space-between;padding:7px 0}
        .totals .grand{border-top:2px solid #1a1a1a;margin-top:6px;padding-top:12px;font-size:18px;font-weight:800}
        .green{color:#16a34a}
        .foot{background:#fafafa;padding:20px 40px;font-size:12px;color:#a1a1aa;text-align:center;border-top:1px solid #f0f0f0}
        .btnbar{max-width:720px;margin:0 auto 16px;display:flex;justify-content:flex-end;gap:8px}
        .btn{border:0;cursor:pointer;font:inherit;font-weight:600;font-size:14px;padding:9px 16px;border-radius:9px;background:#e4177c;color:#fff}
        .btn.ghost{background:#fff;color:#3f3f46;border:1px solid #e4e4e7}
        @media print{body{background:#fff;padding:0}.sheet{box-shadow:none;border-radius:0;max-width:none}.btnbar{display:none}.foot{background:#fff}}
    </style>
</head>
<body>
    <div class="btnbar">
        <a class="btn ghost" href="{{ route('orders.show', $order) }}">← Kembali</a>
        <button class="btn" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <div class="sheet">
        <div class="pad">
            <div class="top">
                <div>
                    <div class="brand">{{ $order->user->name }}</div>
                    <div class="muted">{{ $order->user->email }}</div>
                </div>
                <div style="text-align:right">
                    <h1>INVOIS</h1>
                    <div class="muted">{{ $inv }}</div>
                    <div style="margin-top:8px"><span class="tag {{ $order->payment_status === 'lunas' ? 'paid' : 'unpaid' }}">{{ $order->payment_status === 'lunas' ? 'Lunas' : 'Belum Bayar' }}</span></div>
                </div>
            </div>

            <div class="grid">
                <div>
                    <div class="label">Invois kepada</div>
                    <div style="font-weight:600">{{ $order->customer }}</div>
                    <div class="muted">{{ $order->phone }}</div>
                    @if ($order->email)<div class="muted">{{ $order->email }}</div>@endif
                    <div class="muted" style="margin-top:4px">{{ $order->address }}</div>
                    <div class="muted">{{ $order->state }}</div>
                </div>
                <div>
                    <div class="label">Tarikh</div>
                    <div>{{ $order->created_at->translatedFormat('d F Y') }}</div>
                    <div class="label" style="margin-top:16px">No. Rujukan</div>
                    <div>{{ $order->payment_ref ?: '—' }}</div>
                </div>
            </div>

            <table>
                <thead><tr><th>Perihal</th><th class="r">Kuantiti</th><th class="r">Harga</th><th class="r">Jumlah</th></tr></thead>
                <tbody>
                    <tr>
                        <td><strong>{{ $order->product_name }}</strong></td>
                        <td class="r">{{ $order->qty }}</td>
                        <td class="r">{{ $rm($unit) }}</td>
                        <td class="r">{{ $rm($subtotal) }}</td>
                    </tr>
                    @if ($order->bump_price > 0)
                        <tr>
                            <td>{{ $order->bump_title ?: 'Tambahan' }}</td>
                            <td class="r">1</td>
                            <td class="r">{{ $rm($order->bump_price) }}</td>
                            <td class="r">{{ $rm($order->bump_price) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="totals">
                <div class="row"><span class="muted">Subtotal</span><span>{{ $rm($subtotal + (float) $order->bump_price) }}</span></div>
                @if ($order->discount > 0)
                    <div class="row green"><span>Diskaun{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span><span>−{{ $rm($order->discount) }}</span></div>
                @endif
                <div class="row grand"><span>Jumlah</span><span>{{ $rm($order->total) }}</span></div>
            </div>
        </div>
        <div class="foot">Terima kasih atas pesanan anda. · Invois dijana oleh Mendap</div>
    </div>
</body>
</html>
