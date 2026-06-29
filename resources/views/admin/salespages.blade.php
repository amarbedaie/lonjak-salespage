<x-layouts.admin title="Salespage">
    @php $rm = fn ($n) => 'RM'.number_format($n, 2); @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Semua salespage" description="{{ $pages->count() }} salespage di platform" />
        <form method="GET" class="relative max-w-xs"><x-lucide-search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted" /><x-ui.input name="q" value="{{ $q }}" class="pl-9" placeholder="Cari tajuk / slug…" /></form>
        @if ($pages->isEmpty())
            <x-ui.empty-state icon="file-text" title="Belum ada salespage" description="Salespage yang merchant cipta akan muncul di sini." />
        @else
            <x-ui.card class="overflow-hidden">
                <div class="overflow-x-auto scroll-thin"><table class="w-full text-sm">
                    <thead><tr class="border-b border-border bg-surface text-left text-xs text-muted"><th class="px-5 py-3 font-medium">Tajuk</th><th class="px-5 py-3 font-medium">Slug</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 text-right font-medium">Harga</th><th class="px-5 py-3 text-right font-medium">Pelawat</th><th class="px-5 py-3 text-right font-medium">Tindakan</th></tr></thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($pages as $s)
                            <tr class="hover:bg-muted-surface/50">
                                <td class="px-5 py-3 font-medium text-ink">{{ $s->title }}</td>
                                <td class="px-5 py-3">@if ($s->status === 'live')<a href="{{ url('/s/'.$s->slug) }}" target="_blank" class="font-mono text-xs text-primary hover:underline">/s/{{ $s->slug }}</a>@else<span class="font-mono text-xs text-muted">/s/{{ $s->slug }}</span>@endif</td>
                                <td class="px-5 py-3"><x-ui.status-pill :status="$s->status" /></td>
                                <td class="px-5 py-3 text-right text-ink-soft tnum">{{ $rm($s->price) }}</td>
                                <td class="px-5 py-3 text-right text-ink-soft tnum">{{ number_format($s->visits) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.salespage.status', $s) }}"><input type="hidden" name="status" value="{{ $s->status === 'live' ? 'dijeda' : 'live' }}">@csrf
                                        <x-ui.button type="submit" size="sm" :variant="$s->status === 'live' ? 'outline' : 'ghost'">{{ $s->status === 'live' ? 'Take down' : 'Pulihkan' }}</x-ui.button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            </x-ui.card>
        @endif
    </div>
</x-layouts.admin>
