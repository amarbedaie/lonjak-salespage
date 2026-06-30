<x-layouts.base>
    <div class="bg-bg">
        {{-- Nav --}}
        <header class="sticky top-0 z-[1100] border-b border-border bg-bg/80 backdrop-blur-md" x-data="{ open: false }">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
                <a href="{{ route('landing') }}"><x-logo /></a>
                <nav class="hidden items-center gap-1 md:flex">
                    @foreach (['#platform' => 'Platform', '#ciri' => 'Ciri', '#harga' => 'Harga', '#soalan' => 'Soalan'] as $href => $lbl)
                        <a href="{{ $href }}" class="rounded-md px-3 py-2 text-sm font-medium text-ink-soft transition-colors hover:bg-muted-surface hover:text-ink">{{ $lbl }}</a>
                    @endforeach
                </nav>
                <div class="ml-auto flex items-center gap-2">
                    <x-theme-toggle />
                    <x-ui.button href="{{ route('login') }}" variant="ghost" size="sm" class="hidden sm:inline-flex">Log masuk</x-ui.button>
                    <x-ui.button href="{{ route('register') }}" size="sm" class="hidden sm:inline-flex">Langgan</x-ui.button>
                </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 -z-10 opacity-60" style="background: radial-gradient(60% 50% at 70% 0%, var(--primary-soft), transparent 70%)"></div>
            <div class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-24">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-border bg-surface px-3 py-1 text-xs font-medium text-ink-soft"><x-lucide-sparkles class="size-3.5 text-primary" /> Platform salespage untuk usahawan Malaysia</span>
                    <h1 class="mt-5 text-4xl font-bold leading-[1.05] tracking-tight text-ink text-balance sm:text-5xl">Brief produk anda. <span class="text-primary">Salespage keluar.</span></h1>
                    <p class="mt-5 max-w-md text-lg leading-relaxed text-ink-soft text-pretty">Bina salespage convert tinggi, terima bayaran, hantar parcel & urus order — semua dalam satu dashboard. Jual dalam 10 minit, bukan 10 hari.</p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <x-ui.button href="{{ route('register') }}" size="lg">Mula sekarang <x-lucide-arrow-right class="size-4" /></x-ui.button>
                        <x-ui.button href="{{ route('login') }}" variant="outline" size="lg">Log masuk</x-ui.button>
                    </div>
                    <div class="mt-6 flex items-center gap-4 text-sm text-muted">
                        <span class="flex items-center gap-1.5"><x-lucide-check class="size-4 text-success" /> Tiada yuran transaksi</span>
                        <span class="flex items-center gap-1.5"><x-lucide-check class="size-4 text-success" /> Batal bila-bila</span>
                    </div>
                </div>
                <div class="relative mx-auto w-full max-w-sm">
                    <div class="overflow-hidden rounded-[28px] border-[7px] border-ink/90 bg-bg elev-3">
                        <div class="max-h-[540px] overflow-hidden">@include('partials.salespage', ['page' => $demo])</div>
                    </div>
                    <div class="absolute -bottom-4 -right-3 z-10 rounded-[var(--radius-lg)] border border-border bg-surface px-3.5 py-2.5 elev-2"><p class="text-[0.6875rem] text-muted">Jualan hari ini</p><p class="text-base font-semibold text-ink tnum">RM8,740</p></div>
                </div>
            </div>
            <div class="border-y border-border bg-surface/60">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-x-8 gap-y-2 px-6 py-5 text-sm text-muted">
                    <span class="text-xs font-medium uppercase tracking-wide">Disepadukan dengan</span>
                    @foreach (['BayarCash', 'ToyyibPay', 'CHIP', 'EasyParcel', 'J&T', 'Pos Laju', 'Meta Pixel'] as $n)<span class="font-semibold text-ink-soft">{{ $n }}</span>@endforeach
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section id="platform" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="max-w-2xl"><h2 class="text-3xl font-bold tracking-tight text-ink text-balance">Dari idea ke jualan dalam 3 langkah</h2><p class="mt-3 text-lg text-ink-soft">Tak perlu developer, tak perlu designer. Cuma anda dan produk anda.</p></div>
            <ol class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([['1', 'Brief produk', 'Masukkan nama, harga & masalah yang produk selesaikan. Itu sahaja.', 'sparkles'], ['2', 'AI jana salespage', 'Framework direct-response terbukti tulis copy convert tinggi dalam BM — sedia diedit.', 'zap'], ['3', 'Terbit & jual', 'Sambung gateway & kurier, pasang pixel, dan terima order serta-merta.', 'credit-card']] as [$n, $t, $d, $ic])
                    <li class="relative rounded-[var(--radius-lg)] border border-border bg-surface p-6">
                        <div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-fg">{{ $n }}</span><x-dynamic-component :component="'lucide-'.$ic" class="size-5 text-primary" /></div>
                        <h3 class="mt-4 text-lg font-semibold text-ink">{{ $t }}</h3><p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Features --}}
        <section id="ciri" class="bg-surface/50 py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="max-w-2xl"><h2 class="text-3xl font-bold tracking-tight text-ink text-balance">Semua yang kedai online perlukan</h2><p class="mt-3 text-lg text-ink-soft">Satu langganan. Tiada tool berselerak, tiada bayaran tersembunyi.</p></div>
                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    <div class="rounded-[var(--radius-lg)] border border-border bg-bg p-6 md:col-span-2">
                        <x-ui.badge tone="primary"><x-lucide-sparkles class="size-3.5" /> AI Builder</x-ui.badge>
                        <h3 class="mt-3 text-xl font-semibold text-ink">Salespage convert tinggi, tanpa menaip dari kosong</h3>
                        <p class="mt-2 max-w-lg text-sm leading-relaxed text-ink-soft">Hook, masalah, offer stack, bukti sosial, jaminan, urgency & FAQ — disusun ikut framework yang terbukti convert, terus boleh edit.</p>
                    </div>
                    @foreach ([['credit-card', 'Checkout & bayaran', 'BayarCash, ToyyibPay, CHIP. 3 gaya checkout + order bump.'], ['truck', 'Penghantaran', 'EasyParcel, J&T, Pos Laju. Jana AWB & cetak label terus.'], ['message-circle-heart', 'Recovery WhatsApp', 'Pulihkan checkout ditinggalkan secara automatik.'], ['bar-chart-3', 'Analitik & profit', 'ROAS, CPA & untung bersih — kira ad spend & COGS automatik.']] as [$ic, $t, $d])
                        <div class="rounded-[var(--radius-lg)] border border-border bg-bg p-6">
                            <span class="flex size-10 items-center justify-center rounded-[var(--radius-md)] bg-primary-soft text-primary"><x-dynamic-component :component="'lucide-'.$ic" class="size-5" /></span>
                            <h3 class="mt-4 font-semibold text-ink">{{ $t }}</h3><p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section id="harga" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="mx-auto max-w-2xl text-center"><h2 class="text-3xl font-bold tracking-tight text-ink text-balance">Satu harga, semua ciri</h2><p class="mt-3 text-lg text-ink-soft">Mula hari ini. Naik taraf bila kedai anda membesar.</p></div>
            <div class="mx-auto mt-10 grid max-w-3xl gap-6 sm:grid-cols-2">
                <div class="rounded-[var(--radius-xl)] border-2 border-primary bg-surface p-7 elev-2">
                    <div class="flex items-center justify-between"><h3 class="text-lg font-semibold text-ink">Pro</h3><x-ui.badge tone="primary">Popular</x-ui.badge></div>
                    <p class="mt-4"><span class="text-sm text-muted line-through tnum">RM199</span><span class="ml-2 text-4xl font-bold text-ink tnum">RM89</span><span class="text-muted">/bulan</span></p>
                    <x-ui.button href="{{ route('register') }}" size="lg" class="mt-5 w-full">Langgan Pro</x-ui.button>
                    <ul class="mt-6 space-y-2.5 text-sm">@foreach (['1 salespage aktif', '3 generasi AI / bulan', 'Semua gateway & kurier', 'Recovery WhatsApp', 'Analitik & profit tracker', 'Domain tersuai'] as $f)<li class="flex items-center gap-2 text-ink-soft"><x-lucide-check class="size-4 shrink-0 text-success" /> {{ $f }}</li>@endforeach</ul>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-border bg-surface p-7">
                    <h3 class="text-lg font-semibold text-ink">Scale</h3>
                    <p class="mt-4"><span class="text-4xl font-bold text-ink tnum">RM189</span><span class="text-muted">/bulan</span></p>
                    <x-ui.button href="{{ route('register') }}" variant="outline" size="lg" class="mt-5 w-full">Pilih Scale</x-ui.button>
                    <ul class="mt-6 space-y-2.5 text-sm">@foreach (['5 salespage aktif', '15 generasi AI / bulan', 'Split test angle & produk', 'Integrasi OMS', 'Sokongan keutamaan', 'Semua dalam Pro'] as $f)<li class="flex items-center gap-2 text-ink-soft"><x-lucide-check class="size-4 shrink-0 text-success" /> {{ $f }}</li>@endforeach</ul>
                </div>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="soalan" class="bg-surface/50 py-20">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <h2 class="text-center text-3xl font-bold tracking-tight text-ink text-balance">Soalan lazim</h2>
                <div class="mt-8 divide-y divide-border rounded-[var(--radius-lg)] border border-border bg-bg">
                    @foreach ([['Perlu pandai coding atau design?', 'Tak perlu langsung. Brief produk anda, AI jana salespage penuh.'], ['Gateway & kurier apa disokong?', 'Bayaran: BayarCash, ToyyibPay, CHIP, BillPlz. Kurier: EasyParcel, J&T, Pos Laju.'], ['Ada yuran transaksi?', 'Tiada yuran dari Mendap. Anda hanya bayar caj pemprosesan gateway.'], ['Boleh guna domain sendiri?', 'Boleh. Hosting subdomain percuma, atau sambung domain sendiri.']] as [$q, $a])
                        <details class="group px-5 py-4"><summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-ink marker:hidden">{{ $q }}<span class="ml-4 text-muted transition-transform group-open:rotate-45">+</span></summary><p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $a }}</p></details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
            <div class="overflow-hidden rounded-[var(--radius-xl)] bg-primary px-8 py-14 text-center text-primary-fg">
                <h2 class="mx-auto max-w-xl text-3xl font-bold tracking-tight text-balance">Mula jual hari ini dengan Mendap</h2>
                <p class="mx-auto mt-3 max-w-md text-primary-fg/85">Sertai ribuan usahawan Malaysia yang scale offer mereka dengan lebih pantas.</p>
                <div class="mt-7 flex flex-wrap justify-center gap-3"><x-ui.button href="{{ route('register') }}" variant="secondary" size="lg" class="!text-ink">Langgan sekarang</x-ui.button></div>
            </div>
        </section>

        <footer class="border-t border-border">
            <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div><x-logo /><p class="mt-2 max-w-xs text-sm text-muted">Platform salespage all-in-one untuk usahawan Malaysia.</p></div>
                <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm text-ink-soft"><a href="#platform" class="hover:text-primary">Platform</a><a href="#harga" class="hover:text-primary">Harga</a><a href="#soalan" class="hover:text-primary">Soalan</a></div>
            </div>
            <div class="border-t border-border py-5 text-center text-xs text-muted">© {{ date('Y') }} Mendap · ADFusion Marketing. Hak cipta terpelihara.</div>
        </footer>
    </div>
</x-layouts.base>
