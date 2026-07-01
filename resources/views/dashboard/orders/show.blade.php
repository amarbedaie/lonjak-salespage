<x-layouts.app :title="'Order · '.$order->customer">
    @php
        $rm = fn ($n) => 'RM'.number_format((float) $n, 2);
        $subtotal = (float) $order->total - (float) $order->bump_price + (float) $order->discount;
        $unit = $order->qty > 0 ? $subtotal / $order->qty : $subtotal;
        $digits = preg_replace('/\D/', '', $order->phone);
        $wa = str_starts_with($digits, '60') ? $digits : '60'.ltrim($digits, '0');
        $hasEP = ! empty(auth()->user()->easyparcel_api_key);
        $destPostcode = preg_match('/\b(\d{5})\b/', $order->address, $mm) ? $mm[1] : '';
    @endphp
    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('ok'))
            <div class="flex items-center gap-2.5 rounded-[var(--radius-md)] border border-success/30 bg-success-soft/50 px-4 py-3 text-sm text-success"><x-lucide-circle-check class="size-5 shrink-0" /> {{ session('ok') }}</div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('orders.index') }}" class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-muted-surface"><x-lucide-arrow-left class="size-4.5" /></a>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="truncate text-xl font-semibold tracking-tight text-ink">Order #{{ $order->id }}</h1>
                    <x-ui.status-pill :status="$order->status" />
                </div>
                <p class="mt-0.5 text-xs text-muted">{{ $order->created_at->translatedFormat('l, d F Y · h:i A') }}</p>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <a href="https://wa.me/{{ $wa }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] border border-border px-3 py-2 text-sm font-medium text-ink-soft hover:bg-muted-surface"><x-lucide-message-circle class="size-4 text-success" /> WhatsApp</a>
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] bg-primary px-3.5 py-2 text-sm font-semibold text-primary-fg hover:opacity-90"><x-lucide-file-text class="size-4" /> Invois</a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            {{-- Ringkasan order --}}
            <x-ui.card class="lg:col-span-3">
                <x-ui.card-header title="Ringkasan order" />
                <x-ui.card-body>
                    <div class="divide-y divide-border text-sm">
                        <div class="flex items-start justify-between py-3">
                            <div><p class="font-medium text-ink">{{ $order->product_name }}</p><p class="text-xs text-muted">{{ $rm($unit) }} × {{ $order->qty }}</p></div>
                            <span class="tnum font-medium text-ink">{{ $rm($subtotal) }}</span>
                        </div>
                        @if ($order->discount > 0)
                            <div class="flex items-center justify-between py-3 text-success">
                                <span>Diskaun @if ($order->coupon_code)<span class="ml-1 rounded bg-success-soft px-1.5 py-0.5 text-xs font-semibold">{{ $order->coupon_code }}</span>@endif</span>
                                <span class="tnum">−{{ $rm($order->discount) }}</span>
                            </div>
                        @endif
                        @if ($order->bump_price > 0)
                            <div class="flex items-start justify-between py-3">
                                <div class="flex items-center gap-1.5"><x-lucide-circle-plus class="size-4 text-primary" /><span class="text-ink-soft">{{ $order->bump_title ?: 'Tambahan' }}</span></div>
                                <span class="tnum text-ink-soft">+{{ $rm($order->bump_price) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between py-3 text-base font-bold text-ink">
                            <span>Jumlah</span><span class="tnum">{{ $rm($order->total) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">
                        <span class="text-xs text-muted">Status bayaran:</span>
                        <x-ui.badge :tone="$order->payment_status === 'lunas' ? 'success' : 'muted'">{{ $order->payment_status === 'lunas' ? 'Lunas' : 'Belum bayar' }}</x-ui.badge>
                        @if ($order->payment_ref)<span class="text-xs text-muted">Ruj: {{ $order->payment_ref }}</span>@endif
                        @if ($order->salespage)<a href="{{ url('/s/'.$order->salespage->slug) }}" target="_blank" class="ml-auto text-xs text-primary hover:underline">Lihat salespage →</a>@endif
                    </div>
                </x-ui.card-body>
            </x-ui.card>

            {{-- Pelanggan + status --}}
            <div class="space-y-6 lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-header title="Pelanggan" />
                    <x-ui.card-body class="space-y-3 text-sm">
                        <div><p class="text-xs text-muted">Nama</p><p class="font-medium text-ink">{{ $order->customer }}</p></div>
                        <div><p class="text-xs text-muted">Telefon</p><p class="text-ink">{{ $order->phone }}</p></div>
                        @if ($order->email)<div><p class="text-xs text-muted">Emel</p><p class="text-ink">{{ $order->email }}</p></div>@endif
                        <div><p class="text-xs text-muted">Alamat</p><p class="text-ink">{{ $order->address }}</p><p class="text-ink-soft">{{ $order->state }}</p></div>
                    </x-ui.card-body>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card-header title="Kemas kini status" />
                    <x-ui.card-body>
                        <form method="POST" action="{{ route('orders.status', $order) }}" class="flex items-center gap-2">@csrf
                            <select name="status" class="h-10 flex-1 rounded-[var(--radius-md)] border border-border bg-bg px-3 text-sm text-ink focus:border-primary focus:outline-none">
                                @foreach (['baru' => 'Baru', 'diproses' => 'Diproses', 'dihantar' => 'Dihantar', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $st => $lbl)<option value="{{ $st }}" @selected($order->status === $st)>{{ $lbl }}</option>@endforeach
                            </select>
                            <x-ui.button type="submit" size="sm">Simpan</x-ui.button>
                        </form>
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        </div>

        {{-- Penghantaran (EasyParcel) --}}
        <x-ui.card>
            <x-ui.card-header title="Penghantaran">
                <x-slot:action>@if ($order->awb)<x-ui.badge tone="success">Ditempah</x-ui.badge>@endif</x-slot:action>
            </x-ui.card-header>
            <x-ui.card-body>
                @error('ship')<p class="mb-3 rounded-[var(--radius-md)] border border-danger/30 bg-danger-soft px-3 py-2 text-sm text-danger">{{ $message }}</p>@enderror
                @if ($order->awb)
                    <div class="grid gap-3 text-sm sm:grid-cols-2">
                        <div><p class="text-xs text-muted">Kurier</p><p class="font-medium text-ink">{{ $order->courier ?: '—' }}</p></div>
                        <div><p class="text-xs text-muted">No. AWB</p><p class="font-mono text-ink">{{ $order->awb }}</p></div>
                        @if ($order->ship_price)<div><p class="text-xs text-muted">Kos hantar</p><p class="text-ink">RM{{ number_format((float) $order->ship_price, 2) }}</p></div>@endif
                        @if ($order->ship_weight)<div><p class="text-xs text-muted">Berat</p><p class="text-ink">{{ rtrim(rtrim(number_format((float) $order->ship_weight, 2), '0'), '.') }} kg</p></div>@endif
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($order->awb_link)<a href="{{ $order->awb_link }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] bg-primary px-3.5 py-2 text-sm font-semibold text-primary-fg hover:opacity-90"><x-lucide-printer class="size-4" /> Cetak label</a>@endif
                        @if ($order->tracking_url)<a href="{{ $order->tracking_url }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] border border-border px-3.5 py-2 text-sm font-medium text-ink-soft hover:bg-muted-surface"><x-lucide-map-pin class="size-4" /> Jejak parcel</a>@endif
                    </div>
                @elseif ($hasEP)
                    <div x-data="{ weight: 1, postcode: '{{ $destPostcode }}', rates: [], selected: '', loading: false, error: '', booking: false,
                        async check() {
                            this.loading = true; this.error = ''; this.rates = []; this.selected = '';
                            try {
                                const r = await fetch('{{ route('orders.rates', $order) }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ weight: this.weight, postcode: this.postcode }) });
                                const d = await r.json();
                                if (d.ok) { this.rates = d.rates; if (! d.rates.length) this.error = 'Tiada kurier untuk destinasi ini.'; } else { this.error = d.error; }
                            } catch (e) { this.error = 'Ralat rangkaian. Cuba lagi.'; }
                            this.loading = false;
                        } }">
                        <div class="flex flex-wrap items-end gap-3">
                            <div><label class="mb-1 block text-xs text-muted">Berat (kg)</label><input type="number" step="0.1" min="0.1" x-model.number="weight" class="h-10 w-24 rounded-[var(--radius-md)] border border-border bg-bg px-3 text-sm text-ink focus:border-primary focus:outline-none"></div>
                            <div><label class="mb-1 block text-xs text-muted">Poskod destinasi</label><input type="text" x-model="postcode" placeholder="43000" class="h-10 w-32 rounded-[var(--radius-md)] border border-border bg-bg px-3 text-sm text-ink focus:border-primary focus:outline-none"></div>
                            <button type="button" @click="check()" :disabled="loading" class="h-10 rounded-[var(--radius-md)] border border-primary px-4 text-sm font-semibold text-primary hover:bg-primary/5 disabled:opacity-50" x-text="loading ? 'Menyemak…' : 'Semak kadar'"></button>
                        </div>
                        <p x-show="error" x-cloak class="mt-2 text-sm text-danger" x-text="error"></p>
                        <div x-show="rates.length" x-cloak class="mt-4 space-y-2">
                            <template x-for="r in rates" :key="r.service_id">
                                <label class="flex cursor-pointer items-center gap-3 rounded-[var(--radius-md)] border border-border p-3 text-sm" :class="selected === r.service_id && '!border-primary bg-primary/5'">
                                    <input type="radio" name="svc" :value="r.service_id" x-model="selected" class="accent-primary">
                                    <span class="min-w-0 flex-1"><span class="font-medium text-ink" x-text="r.courier"></span> <span class="text-xs text-muted" x-text="r.delivery"></span></span>
                                    <span class="shrink-0 font-bold text-ink">RM<span x-text="r.price.toFixed(2)"></span></span>
                                </label>
                            </template>
                            <form method="POST" action="{{ route('orders.book', $order) }}" @submit="booking = true">@csrf
                                <input type="hidden" name="service_id" :value="selected">
                                <input type="hidden" name="weight" :value="weight">
                                <input type="hidden" name="postcode" :value="postcode">
                                <button type="submit" :disabled="! selected || booking" class="mt-2 inline-flex items-center gap-1.5 rounded-[var(--radius-md)] bg-primary px-4 py-2.5 text-sm font-semibold text-primary-fg hover:opacity-90 disabled:opacity-50"><x-lucide-truck class="size-4" /> <span x-text="booking ? 'Menempah…' : 'Book & bayar penghantaran'"></span></button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="rounded-[var(--radius-md)] bg-muted-surface/60 px-4 py-3 text-sm text-ink-soft">
                        Sambung <a href="{{ route('shipping') }}" class="font-medium text-primary hover:underline">EasyParcel</a> untuk semak kadar, tempah kurier & jana AWB terus dari sini.
                    </div>
                @endif
            </x-ui.card-body>
        </x-ui.card>
    </div>
</x-layouts.app>
