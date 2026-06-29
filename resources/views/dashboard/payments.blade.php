<x-layouts.app title="Bayaran">
    <div class="space-y-6">
        <x-ui.page-header title="Bayaran" description="Cara pelanggan bayar di salespage anda." />
        <x-ui.card class="bg-success-soft/40">
            <x-ui.card-body class="text-sm text-ink-soft">✅ <strong class="text-ink">COD / pengesahan manual aktif secara lalai.</strong> Pelanggan checkout, order masuk dashboard, anda sahkan & kutip bayaran via WhatsApp (Recovery). Untuk bayaran online automatik (FPX/kad), <strong class="text-ink">BayarCash</strong> dah disepadukan — isi kunci API di bawah.</x-ui.card-body>
        </x-ui.card>
        <x-ui.card>
            <x-ui.card-header title="Gateway pembayaran online" subtitle="Masukkan kunci API untuk aktifkan FPX, kad & e-wallet" />
            <x-ui.card-body class="divide-y divide-border p-0">
                @foreach ([['BC', 'BayarCash', 'FPX & DuitNow QR — disepadukan', config('services.bayarcash.api_secret') ? true : false], ['TP', 'ToyyibPay', 'FPX & kad kredit', false], ['CH', 'CHIP', 'FPX, kad, e-wallet, ansuran', false], ['BP', 'BillPlz', 'FPX & DuitNow', false]] as [$logo, $name, $desc, $on])
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-border bg-bg text-sm font-bold tracking-tight text-ink">{{ $logo }}</div>
                        <div class="min-w-0 flex-1"><div class="flex items-center gap-2"><h3 class="font-medium text-ink">{{ $name }}</h3>@if ($on)<x-ui.badge tone="success" dot>Disambung</x-ui.badge>@endif</div><p class="mt-0.5 text-sm text-muted">{{ $desc }}</p></div>
                        <x-ui.button :variant="$on ? 'outline' : 'primary'" size="sm">{{ $on ? 'Urus' : 'Sambung' }}</x-ui.button>
                    </div>
                @endforeach
            </x-ui.card-body>
        </x-ui.card>
        <p class="px-1 text-xs text-muted">Tiada yuran transaksi dari Lonjak — anda hanya bayar caj pemprosesan gateway. Settlement terus ke akaun bank anda.</p>
    </div>
</x-layouts.app>
