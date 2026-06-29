<x-layouts.app title="Recovery">
    @php
        $rm = fn ($n, $c = false) => $c && $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2);
        $waNum = function ($p) { $d = preg_replace('/\D/', '', $p ?? ''); if (!$d) return ''; if (str_starts_with($d, '60')) return $d; if (str_starts_with($d, '0')) return '60'.substr($d, 1); return $d; };
    @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Recovery" description="Follow-up order yang belum disahkan terus melalui WhatsApp — pulihkan jualan yang hampir terlepas." />

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.metric label="Perlu follow-up" :value="$pending->count()" />
            <x-ui.metric label="Nilai belum disahkan" :value="$rm($pendingValue, true)" />
            <x-ui.metric label="Order baru" :value="$newCount" />
            <x-ui.metric label="Selesai" :value="$doneCount" />
        </div>

        <x-ui.card class="bg-success-soft/40">
            <x-ui.card-body class="text-sm text-ink-soft">💬 Klik <strong class="text-ink">WhatsApp</strong> pada mana-mana order untuk hantar mesej pengesahan (nama & produk dah diisi automatik). Tiada setup diperlukan.</x-ui.card-body>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card-header title="Order perlu follow-up" subtitle="Baru & sedang diproses" />
            <x-ui.card-body class="p-0">
                @if ($pending->isEmpty())
                    <div class="px-5 py-10"><x-ui.empty-state icon="message-circle" title="Semua order diuruskan 🎉" description="Tiada order tertangguh buat masa ni." /></div>
                @else
                    <div class="overflow-x-auto scroll-thin"><table class="w-full text-sm">
                        <thead><tr class="border-b border-border text-left text-xs text-muted"><th class="px-5 py-2.5 font-medium">Pelanggan</th><th class="px-5 py-2.5 font-medium">Produk</th><th class="px-5 py-2.5 text-right font-medium">Nilai</th><th class="px-5 py-2.5 font-medium">Status</th><th class="px-5 py-2.5 text-right font-medium">Masa</th><th class="px-5 py-2.5 text-right font-medium">Tindakan</th></tr></thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($pending as $o)
                                @php $num = $waNum($o->phone); $msg = rawurlencode("Hai {$o->customer}, terima kasih order {$o->product_name}! Nak sahkan tempahan anda & maklumat pembayaran/penghantaran. — {$business}"); @endphp
                                <tr class="hover:bg-muted-surface/50">
                                    <td class="px-5 py-3"><div class="font-medium text-ink">{{ $o->customer }}</div><div class="text-xs text-muted">{{ $o->phone }}</div></td>
                                    <td class="px-5 py-3 text-ink-soft">{{ $o->product_name }}</td>
                                    <td class="px-5 py-3 text-right font-medium text-ink tnum">{{ $rm($o->total) }}</td>
                                    <td class="px-5 py-3"><x-ui.status-pill :status="$o->status" /></td>
                                    <td class="px-5 py-3 text-right text-xs text-muted">{{ $o->created_at->diffForHumans() }}</td>
                                    <td class="px-5 py-3 text-right">
                                        @if ($num)
                                            <a href="https://wa.me/{{ $num }}?text={{ $msg }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] bg-success px-3 py-1.5 text-xs font-medium text-white hover:opacity-90"><x-lucide-message-circle class="size-3.5" /> WhatsApp</a>
                                        @else<span class="text-xs text-muted">Tiada telefon</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </x-ui.card-body>
        </x-ui.card>
    </div>
</x-layouts.app>
