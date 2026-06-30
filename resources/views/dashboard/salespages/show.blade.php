<x-layouts.app :title="$salespage->title">
    @php $rm = fn ($n) => 'RM'.number_format($n, 2); $page = array_merge($salespage->blocks ?? ['blocks' => []], ['images' => $salespage->imageUrls(), 'video' => $salespage->video_url]); @endphp
    <div class="space-y-6" x-data="{ tab: 'design' }">
        @if (session('saved'))
            <div class="flex items-center gap-2.5 rounded-[var(--radius-md)] border border-success/30 bg-success-soft/50 px-4 py-3 text-sm text-success">
                <x-lucide-circle-check class="size-5 shrink-0" />
                <span><strong>Salespage disimpan!</strong> Klik <strong>Terbitkan</strong> untuk go live, atau buka pautan untuk pratonton. Semua salespage anda ada di <a href="{{ route('salespages.index') }}" class="underline">Salespage</a>.</span>
            </div>
        @endif
        @if (session('ok'))
            <div class="flex items-center gap-2.5 rounded-[var(--radius-md)] border border-success/30 bg-success-soft/50 px-4 py-3 text-sm text-success"><x-lucide-circle-check class="size-5 shrink-0" /> {{ session('ok') }}</div>
        @endif
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('salespages.index') }}" class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-muted-surface"><x-lucide-arrow-left class="size-4.5" /></a>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="truncate text-xl font-semibold tracking-tight text-ink">{{ $salespage->title }}</h1>
                    <x-ui.status-pill :status="$salespage->status" />
                </div>
                <a href="{{ url('/s/'.$salespage->slug) }}" target="_blank" class="mt-0.5 flex items-center gap-1 text-xs text-muted hover:text-primary"><x-lucide-globe class="size-3" /> /s/{{ $salespage->slug }} <x-lucide-external-link class="size-3" /></a>
            </div>
            <div class="ml-auto flex items-center gap-2">
                @if ($salespage->status === 'live')
                    <form method="POST" action="{{ route('salespages.status', $salespage) }}">@csrf<input type="hidden" name="status" value="dijeda">
                        <x-ui.button type="submit" variant="outline" size="sm">Jeda</x-ui.button>
                    </form>
                @else
                    <form method="POST" action="{{ route('salespages.status', $salespage) }}">@csrf<input type="hidden" name="status" value="live">
                        <x-ui.button type="submit" size="sm">Terbitkan</x-ui.button>
                    </form>
                @endif
                <form method="POST" action="{{ route('salespages.destroy', $salespage) }}" onsubmit="return confirm('Padam salespage ini?')">@csrf @method('DELETE')
                    <x-ui.button type="submit" variant="outline" size="icon"><x-lucide-trash-2 class="size-4 text-danger" /></x-ui.button>
                </form>
            </div>
        </div>

        <div class="flex items-center gap-1 border-b border-border">
            @foreach (['design' => ['Reka bentuk', 'eye'], 'checkout' => ['Checkout', 'shopping-cart'], 'settings' => ['Tetapan', 'settings-2'], 'analytics' => ['Analitik', 'bar-chart-3']] as $k => [$lbl, $ic])
                <button @click="tab='{{ $k }}'" :class="tab==='{{ $k }}' ? 'text-primary' : 'text-muted hover:text-ink'" class="relative flex items-center gap-2 px-3.5 py-2.5 text-sm font-medium transition-colors">
                    <x-dynamic-component :component="'lucide-'.$ic" class="size-4" /> {{ $lbl }}
                    <span x-show="tab==='{{ $k }}'" class="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-primary"></span>
                </button>
            @endforeach
        </div>

        {{-- Design --}}
        <div x-show="tab==='design'" class="grid gap-6 lg:grid-cols-5">
            <x-ui.card class="lg:col-span-2">
                <x-ui.card-header title="Blok salespage" subtitle="Susunan 12-blok direct-response" />
                <x-ui.card-body class="space-y-1.5">
                    @foreach ($page['blocks'] ?? [] as $b)
                        <div class="flex items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2 text-sm">
                            <span class="flex-1 font-medium text-ink">{{ $b['label'] ?? $b['type'] }}</span><x-ui.badge tone="success">✓</x-ui.badge>
                        </div>
                    @endforeach
                </x-ui.card-body>
            </x-ui.card>
            <div class="lg:col-span-3">
                <x-ui.card class="overflow-hidden">
                    <x-ui.card-header title="Pratonton"><x-slot:action><x-ui.status-pill :status="$salespage->status" /></x-slot:action></x-ui.card-header>
                    <x-ui.card-body class="bg-muted-surface/60 p-5">
                        <div class="mx-auto max-w-[380px] overflow-hidden rounded-[24px] border-[6px] border-ink/90 bg-bg shadow-2xl">
                            <div class="max-h-[600px] overflow-y-auto scroll-thin">@include('partials.salespage', ['page' => $page])</div>
                        </div>
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        </div>

        {{-- Checkout --}}
        <div x-show="tab==='checkout'" x-cloak>
            <x-ui.card>
                <x-ui.card-header title="Gateway pembayaran" subtitle="Per-salespage" />
                <x-ui.card-body class="max-w-md space-y-4">
                    <form method="POST" action="{{ route('salespages.update', $salespage) }}" class="space-y-4">@csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $salespage->title }}"><input type="hidden" name="price" value="{{ $salespage->price }}">
                        <x-ui.field label="Gateway aktif">
                            <x-ui.select name="gateway">
                                @foreach (['BayarCash', 'ToyyibPay', 'CHIP', 'BillPlz', 'Manual / COD'] as $g)<option @selected($salespage->gateway === $g)>{{ $g }}</option>@endforeach
                            </x-ui.select>
                        </x-ui.field>
                        <x-ui.button type="submit" size="sm">Simpan</x-ui.button>
                    </form>
                    <p class="text-xs text-muted">Checkout awam di <a href="{{ url('/s/'.$salespage->slug) }}" class="text-primary hover:underline">/s/{{ $salespage->slug }}</a>.</p>
                </x-ui.card-body>
            </x-ui.card>
        </div>

        {{-- Settings --}}
        <div x-show="tab==='settings'" x-cloak class="space-y-6">
            <x-ui.card>
                <x-ui.card-header title="Maklumat asas" />
                <x-ui.card-body>
                    <form method="POST" action="{{ route('salespages.update', $salespage) }}" class="grid max-w-2xl gap-5 sm:grid-cols-2">@csrf @method('PUT')
                        <x-ui.field label="Tajuk salespage"><x-ui.input name="title" value="{{ $salespage->title }}" /></x-ui.field>
                        <x-ui.field label="Harga (RM)"><x-ui.input name="price" type="number" value="{{ $salespage->price }}" /></x-ui.field>
                        <x-ui.field label="Harga coret (RM)"><x-ui.input name="compare_price" type="number" value="{{ $salespage->compare_price }}" /></x-ui.field>
                        <input type="hidden" name="gateway" value="{{ $salespage->gateway }}">
                        <div class="sm:col-span-2"><x-ui.button type="submit">Simpan perubahan</x-ui.button></div>
                    </form>
                </x-ui.card-body>
            </x-ui.card>
            <x-ui.card>
                <x-ui.card-header title="Tracking & Pemasaran" subtitle="Pixel untuk ukur ads + countdown untuk urgency" />
                <x-ui.card-body>
                    <form method="POST" action="{{ route('salespages.update', $salespage) }}" class="grid max-w-2xl gap-5 sm:grid-cols-2">@csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $salespage->title }}">
                        <input type="hidden" name="price" value="{{ $salespage->price }}">
                        <input type="hidden" name="compare_price" value="{{ $salespage->compare_price }}">
                        <input type="hidden" name="gateway" value="{{ $salespage->gateway }}">
                        <x-ui.field label="Facebook / Meta Pixel ID" hint="auto-fire PageView + Purchase"><x-ui.input name="fb_pixel" value="{{ $salespage->fb_pixel }}" placeholder="cth. 1023456789012345" /></x-ui.field>
                        <x-ui.field label="TikTok Pixel ID" hint="auto-fire ViewContent + CompletePayment"><x-ui.input name="tiktok_pixel" value="{{ $salespage->tiktok_pixel }}" placeholder="cth. C1ABCD2EF3GH4..." /></x-ui.field>
                        <x-ui.field label="Google Analytics ID" hint="GA4 / Google tag"><x-ui.input name="ga_id" value="{{ $salespage->ga_id }}" placeholder="cth. G-XXXXXXXXXX" /></x-ui.field>
                        <x-ui.field label="Tawaran tamat (countdown)" hint="kosong = tiada timer"><x-ui.input name="offer_ends_at" type="datetime-local" value="{{ $salespage->offer_ends_at?->format('Y-m-d\TH:i') }}" /></x-ui.field>
                        <div class="sm:col-span-2"><x-ui.button type="submit">Simpan tetapan pemasaran</x-ui.button></div>
                    </form>
                </x-ui.card-body>
            </x-ui.card>
        </div>

        {{-- Analytics --}}
        <div x-show="tab==='analytics'" x-cloak class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-ui.metric label="Pelawat" :value="number_format($stats['visits'])" />
            <x-ui.metric label="Order" :value="$stats['orders']" />
            <x-ui.metric label="Conversion" :value="($stats['visits'] ? number_format($stats['orders'] / $stats['visits'] * 100, 1) : '0').'%'" />
            <x-ui.metric label="Jualan" :value="$rm($stats['revenue'])" />
        </div>
    </div>
</x-layouts.app>
