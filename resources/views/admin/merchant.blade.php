<x-layouts.admin :title="$merchant->business_name">
    @php $rm = fn ($n, $c = false) => $c && $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.merchants') }}" class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-muted-surface"><x-lucide-arrow-left class="size-4.5" /></a>
            <span class="flex size-11 items-center justify-center rounded-full bg-primary text-base font-semibold text-primary-fg">{{ strtoupper(substr($merchant->business_name ?: $merchant->email, 0, 1)) }}</span>
            <div class="min-w-0">
                <div class="flex items-center gap-2"><h1 class="truncate text-xl font-semibold tracking-tight text-ink">{{ $merchant->business_name ?: '—' }}</h1><x-ui.status-pill :status="$merchant->status" /><x-ui.badge :tone="$merchant->role === 'admin' ? 'warning' : 'muted'">{{ $merchant->role }}</x-ui.badge></div>
                <p class="text-sm text-muted">{{ $merchant->email }} · sertai {{ $merchant->created_at->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.metric label="Jualan" :value="$rm($revenue, true)" />
            <x-ui.metric label="Order" :value="number_format($orders->count())" />
            <x-ui.metric label="Salespage" :value="number_format($pages->count())" />
            <x-ui.metric label="Plan / Kredit AI" :value="$merchant->plan" :footnote="$merchant->ai_credits.' kredit'" />
        </div>

        <x-ui.card>
            <x-ui.card-header title="Kawalan admin" subtitle="Urus akaun merchant ini" />
            <x-ui.card-body class="space-y-5">
                @if (session('ok'))<p class="rounded-[var(--radius-md)] border border-success/30 bg-success-soft px-3 py-2 text-sm text-success">{{ session('ok') }}</p>@endif
                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('admin.merchant.control', $merchant) }}">@csrf<input type="hidden" name="action" value="{{ $merchant->status === 'aktif' ? 'suspend' : 'activate' }}"><x-ui.button type="submit" :variant="$merchant->status === 'aktif' ? 'danger' : 'primary'" size="sm">{{ $merchant->status === 'aktif' ? 'Gantung akaun' : 'Aktifkan semula' }}</x-ui.button></form>
                    <form method="POST" action="{{ route('admin.merchant.control', $merchant) }}">@csrf<input type="hidden" name="action" value="{{ $merchant->role === 'admin' ? 'make_merchant' : 'make_admin' }}"><x-ui.button type="submit" variant="outline" size="sm">{{ $merchant->role === 'admin' ? 'Tukar ke merchant' : 'Jadikan admin' }}</x-ui.button></form>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <form method="POST" action="{{ route('admin.merchant.control', $merchant) }}">@csrf<input type="hidden" name="action" value="credits">
                        <x-ui.field label="Kredit AI"><div class="flex gap-2"><x-ui.input name="ai_credits" type="number" value="{{ $merchant->ai_credits }}" class="w-24" /><x-ui.button type="submit" variant="outline" size="sm">Simpan</x-ui.button></div></x-ui.field>
                    </form>
                    <form method="POST" action="{{ route('admin.merchant.control', $merchant) }}">@csrf<input type="hidden" name="action" value="plan">
                        <x-ui.field label="Plan"><div class="flex gap-2"><x-ui.select name="plan" class="w-32"><option value="free" @selected($merchant->plan === 'free')>Free</option><option value="pro" @selected($merchant->plan === 'pro')>Pro</option><option value="scale" @selected($merchant->plan === 'scale')>Scale</option></x-ui.select><x-ui.button type="submit" variant="outline" size="sm">Simpan</x-ui.button></div></x-ui.field>
                    </form>
                </div>
            </x-ui.card-body>
        </x-ui.card>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card>
                <x-ui.card-header title="Salespage merchant" />
                <x-ui.card-body class="p-0">
                    @forelse ($pages as $s)
                        <div class="flex items-center gap-3 border-b border-border px-5 py-3 last:border-0"><div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-ink">{{ $s->title }}</p><p class="font-mono text-xs text-muted">/s/{{ $s->slug }}</p></div><x-ui.status-pill :status="$s->status" /></div>
                    @empty<p class="px-5 py-8 text-center text-sm text-muted">Tiada salespage.</p>@endforelse
                </x-ui.card-body>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-header title="Order terkini" />
                <x-ui.card-body class="p-0">
                    @forelse ($orders->take(8) as $o)
                        <div class="flex items-center gap-3 border-b border-border px-5 py-3 last:border-0"><div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-ink">{{ $o->customer }}</p><p class="text-xs text-muted">{{ $o->product_name }}</p></div><x-ui.status-pill :status="$o->status" /><span class="text-sm font-semibold text-ink tnum">{{ $rm($o->total) }}</span></div>
                    @empty<p class="px-5 py-8 text-center text-sm text-muted">Tiada order.</p>@endforelse
                </x-ui.card-body>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
