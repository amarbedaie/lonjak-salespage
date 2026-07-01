<x-layouts.app title="Salespage">
    @php $rm = fn ($n) => $n >= 1000 ? 'RM'.number_format($n / 1000, 1).'k' : 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Salespage" description="{{ $pages->count() }} salespage · {{ $pages->where('status', 'live')->count() }} live">
            <x-slot:action><x-ui.button href="{{ route('salespages.create') }}"><x-lucide-plus class="size-4" /> Salespage baru</x-ui.button></x-slot:action>
        </x-ui.page-header>

        @if ($pages->isEmpty())
            <x-ui.empty-state icon="file-text" title="Belum ada salespage"
                description="Brief produk anda dan biar AI jana salespage convert tinggi dalam beberapa saat."
                actionLabel="Jana salespage pertama" :actionHref="route('salespages.create')" />
        @else
            <x-ui.card class="overflow-hidden">
                <div class="overflow-x-auto scroll-thin">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-border bg-surface text-left text-xs text-muted">
                            <th class="px-5 py-3 font-medium">Salespage</th><th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Pelawat</th><th class="px-5 py-3 text-right font-medium">Order</th>
                            <th class="px-5 py-3 text-right font-medium">Jualan</th><th class="px-5 py-3 font-medium">Dikemas kini</th><th></th>
                        </tr></thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($pages as $s)
                                @php $a = $agg[$s->id] ?? ['orders' => 0, 'revenue' => 0]; @endphp
                                <tr class="group transition-colors hover:bg-muted-surface/50">
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('salespages.show', $s) }}" class="block">
                                            <span class="font-medium text-ink group-hover:text-primary">{{ $s->title }}</span>
                                            <span class="mt-0.5 flex items-center gap-1 text-xs text-muted"><x-lucide-globe class="size-3" /> /s/{{ $s->slug }}</span>
                                        </a>
                                    </td>
                                    <td class="px-5 py-3.5"><x-ui.status-pill :status="$s->status" /></td>
                                    <td class="px-5 py-3.5 text-right text-ink-soft tnum">{{ $s->visits ? number_format($s->visits) : '—' }}</td>
                                    <td class="px-5 py-3.5 text-right text-ink-soft tnum">{{ $a['orders'] ?: '—' }}</td>
                                    <td class="px-5 py-3.5 text-right font-medium text-ink tnum">{{ $rm($a['revenue']) }}</td>
                                    <td class="px-5 py-3.5 text-xs text-muted">{{ $s->updated_at->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            @if ($s->status === 'live')
                                                <a href="{{ url('/s/'.$s->slug) }}" target="_blank" title="Buka" class="inline-flex size-7 items-center justify-center rounded-md text-muted opacity-0 transition-opacity hover:bg-bg hover:text-ink group-hover:opacity-100"><x-lucide-external-link class="size-4" /></a>
                                            @endif
                                            <form method="POST" action="{{ route('salespages.duplicate', $s) }}">@csrf
                                                <button type="submit" title="Duplikasi" class="inline-flex size-7 items-center justify-center rounded-md text-muted opacity-0 transition-opacity hover:bg-bg hover:text-ink group-hover:opacity-100"><x-lucide-copy class="size-4" /></button>
                                            </form>
                                        </div>
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
