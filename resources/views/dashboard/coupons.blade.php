<x-layouts.app title="Kupon">
    <div class="space-y-6">
        <x-ui.page-header title="Kupon & Diskaun" description="{{ $coupons->count() }} kod · gunakan dalam checkout salespage anda." />

        @if (session('ok'))<x-ui.card class="bg-success-soft/40"><x-ui.card-body class="text-sm text-success">✅ {{ session('ok') }}</x-ui.card-body></x-ui.card>@endif

        <x-ui.card>
            <x-ui.card-header title="Cipta kupon" subtitle="Pelanggan masukkan kod ini semasa checkout untuk dapat diskaun." />
            <x-ui.card-body>
                <form method="POST" action="{{ route('coupons.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">@csrf
                    <x-ui.field label="Kod kupon"><x-ui.input name="code" placeholder="RAYA20" required class="uppercase" /></x-ui.field>
                    <x-ui.field label="Jenis">
                        <x-ui.select name="type">
                            <option value="percent">Peratus (%)</option>
                            <option value="fixed">Tetap (RM)</option>
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="Nilai" hint="cth 20 = 20% / RM20"><x-ui.input name="value" type="number" step="0.01" placeholder="20" required /></x-ui.field>
                    <x-ui.field label="Had guna" hint="kosong = tanpa had"><x-ui.input name="usage_limit" type="number" placeholder="100" /></x-ui.field>
                    <x-ui.field label="Tamat" hint="pilihan"><x-ui.input name="expires_at" type="date" /></x-ui.field>
                    @error('code')<p class="text-xs text-danger sm:col-span-2 lg:col-span-5">{{ $message }}</p>@enderror
                    <x-ui.button type="submit" class="lg:col-span-5 sm:w-fit"><x-lucide-plus class="size-4" /> Cipta kupon</x-ui.button>
                </form>
            </x-ui.card-body>
        </x-ui.card>

        @if ($coupons->isEmpty())
            <x-ui.empty-state icon="ticket-percent" title="Belum ada kupon" description="Cipta kod diskaun pertama anda untuk kempen ads atau pelanggan setia." />
        @else
            <x-ui.card class="overflow-hidden">
                <div class="overflow-x-auto scroll-thin">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-border bg-surface text-left text-xs text-muted">
                            <th class="px-5 py-3 font-medium">Kod</th><th class="px-5 py-3 font-medium">Diskaun</th>
                            <th class="px-5 py-3 text-right font-medium">Guna</th><th class="px-5 py-3 font-medium">Tamat</th>
                            <th class="px-5 py-3 font-medium">Status</th><th></th>
                        </tr></thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($coupons as $c)
                                <tr class="hover:bg-muted-surface/50">
                                    <td class="px-5 py-3.5"><span class="rounded-md bg-muted-surface px-2 py-1 font-mono text-xs font-semibold text-ink">{{ $c->code }}</span></td>
                                    <td class="px-5 py-3.5 font-medium text-ink">{{ $c->type === 'percent' ? rtrim(rtrim(number_format($c->value, 2), '0'), '.').'%' : 'RM'.number_format($c->value, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right text-ink-soft tnum">{{ $c->used_count }}{{ $c->usage_limit ? '/'.$c->usage_limit : '' }}</td>
                                    <td class="px-5 py-3.5 text-xs text-muted">{{ $c->expires_at ? $c->expires_at->format('d M Y') : '—' }}</td>
                                    <td class="px-5 py-3.5">
                                        <form method="POST" action="{{ route('coupons.toggle', $c) }}">@csrf
                                            <button type="submit"><x-ui.badge :tone="$c->isValid() ? 'success' : 'muted'">{{ $c->active ? ($c->isValid() ? 'Aktif' : 'Tamat/penuh') : 'Dimatikan' }}</x-ui.badge></button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <form method="POST" action="{{ route('coupons.destroy', $c) }}" onsubmit="return confirm('Padam kupon {{ $c->code }}?')">@csrf @method('DELETE')
                                            <button type="submit" class="text-muted hover:text-danger"><x-lucide-trash-2 class="size-4" /></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.app>
