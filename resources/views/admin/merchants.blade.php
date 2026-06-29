<x-layouts.admin title="Merchant">
    <div class="space-y-6">
        <x-ui.page-header title="Merchant" description="{{ $profiles->count() }} akaun di platform" />
        <form method="GET" class="relative max-w-xs">
            <x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" />
            <x-ui.input name="q" value="{{ $q }}" class="pl-9" placeholder="Cari nama / emel…" />
        </form>
        <x-ui.card class="overflow-hidden">
            <div class="overflow-x-auto scroll-thin">
                <table class="w-full text-sm">
                    <thead><tr class="border-b border-border bg-surface text-left text-xs text-muted"><th class="px-5 py-3 font-medium">Perniagaan</th><th class="px-5 py-3 font-medium">Emel</th><th class="px-5 py-3 font-medium">Peranan</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Sertai</th><th class="px-5 py-3 text-right font-medium">Tindakan</th></tr></thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($profiles as $p)
                            <tr class="hover:bg-muted-surface/50">
                                <td class="px-5 py-3"><a href="{{ route('admin.merchant', $p) }}" class="font-medium text-ink hover:text-primary">{{ $p->business_name ?: '—' }}</a></td>
                                <td class="px-5 py-3 text-ink-soft">{{ $p->email }}</td>
                                <td class="px-5 py-3"><x-ui.badge :tone="$p->role === 'admin' ? 'warning' : 'muted'">{{ $p->role }}</x-ui.badge></td>
                                <td class="px-5 py-3"><x-ui.status-pill :status="$p->status" /></td>
                                <td class="px-5 py-3 text-xs text-muted">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-ui.button href="{{ route('admin.merchant', $p) }}" size="sm" variant="ghost">Urus</x-ui.button>
                                        @if ($p->role !== 'admin')
                                            <form method="POST" action="{{ route('admin.merchant.control', $p) }}">@csrf<input type="hidden" name="action" value="{{ $p->status === 'aktif' ? 'suspend' : 'activate' }}">
                                                <x-ui.button type="submit" size="sm" :variant="$p->status === 'aktif' ? 'outline' : 'primary'">{{ $p->status === 'aktif' ? 'Gantung' : 'Aktifkan' }}</x-ui.button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
