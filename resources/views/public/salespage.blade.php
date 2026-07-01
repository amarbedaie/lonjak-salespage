<x-layouts.base :title="$salespage->title" :force-light="true">
    @php $page = array_merge($salespage->blocks ?? ['blocks' => []], ['images' => $salespage->imageUrls(), 'video' => $salespage->video_url, 'theme' => $salespage->theme]); $price = (float) $salespage->price;
    $states = ['Selangor', 'Kuala Lumpur', 'Johor', 'Pulau Pinang', 'Perak', 'Kedah', 'Kelantan', 'Terengganu', 'Pahang', 'Melaka', 'Negeri Sembilan', 'Perlis', 'Sabah', 'Sarawak', 'Putrajaya', 'Labuan'];
    $ordered = session('ordered'); @endphp

    {{-- Tracking pixels --}}
    @if ($salespage->fb_pixel)
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($salespage->fb_pixel)); fbq('track', 'PageView');
            @if ($ordered) fbq('track', 'Purchase', {value: {{ $price }}, currency: 'MYR'}); @endif
        </script>
    @endif
    @if ($salespage->tiktok_pixel)
        <script>
            !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load(@json($salespage->tiktok_pixel));ttq.page();
            @if ($ordered) ttq.track('CompletePayment', {value: {{ $price }}, currency: 'MYR'}); @endif
            }(window,document,'ttq');
        </script>
    @endif
    @if ($salespage->ga_id)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $salespage->ga_id }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($salespage->ga_id));@if ($ordered)gtag('event','purchase',{value:{{ $price }},currency:'MYR'});@endif</script>
    @endif

    {{-- Countdown urgency bar --}}
    @if ($salespage->offer_ends_at && $salespage->offer_ends_at->isFuture())
        <div x-data="{ end: {{ $salespage->offer_ends_at->getTimestamp() }} * 1000, label: 'Tawaran tamat dalam', t: '',
                tick() { var d = this.end - Date.now(); if (d <= 0) { this.label = 'Tawaran tamat'; this.t = ''; return; } var h = Math.floor(d/3.6e6), m = Math.floor(d%3.6e6/6e4), s = Math.floor(d%6e4/1e3); this.t = (h<10?'0':'')+h+':'+(m<10?'0':'')+m+':'+(s<10?'0':'')+s; } }"
             x-init="tick(); setInterval(() => tick(), 1000)"
             class="sticky top-0 z-40 flex items-center justify-center gap-2 bg-primary px-4 py-2.5 text-center text-sm font-semibold text-primary-fg shadow-md">
            <x-lucide-clock class="size-4 shrink-0" /> <span x-text="label"></span> <span x-text="t" class="font-bold tabular-nums"></span>
        </div>
    @endif

    <div class="min-h-screen bg-muted-surface/40 pb-28 pt-6">
        <div class="mx-auto max-w-md overflow-hidden rounded-[var(--radius-xl)] border border-border bg-bg elev-2">
            @include('partials.salespage', ['page' => $page])

            <section id="checkout" class="border-t border-border bg-surface px-6 py-8">
                <h2 class="mb-4 text-center text-lg font-bold text-ink">Lengkapkan tempahan anda</h2>

                @if (session('ordered'))
                    <div class="rounded-[var(--radius-lg)] border border-success/30 bg-success-soft/50 p-8 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-success text-white"><x-lucide-check class="size-6" /></div>
                        <h3 class="mt-4 text-lg font-bold text-ink">Order diterima! 🎉</h3>
                        <p class="mt-1 text-sm text-ink-soft">Terima kasih. Kami akan WhatsApp anda untuk pengesahan & pembayaran.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('salespage.order', $salespage->slug) }}" class="space-y-4" x-data="{
                        qty: 1, unit: {{ $price }}, coupon: '', applied: '', discount: 0, msg: '', busy: false,
                        bump: false, bumpPrice: {{ (float) ($salespage->bump_price ?? 0) }},
                        get subtotal() { return this.unit * this.qty },
                        get total() { return Math.max(0, this.subtotal - this.discount) + (this.bump ? this.bumpPrice : 0) },
                        async apply() {
                            if (! this.coupon.trim()) return;
                            this.busy = true; this.msg = '';
                            try {
                                const r = await fetch('{{ route('salespage.coupon', $salespage->slug) }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                    body: JSON.stringify({ code: this.coupon, qty: this.qty }),
                                });
                                const d = await r.json();
                                if (d.valid) { this.discount = d.discount; this.applied = d.code; this.msg = ''; }
                                else { this.discount = 0; this.applied = ''; this.msg = d.message || 'Kod tidak sah.'; }
                            } catch (e) { this.msg = 'Ralat rangkaian. Cuba lagi.'; }
                            this.busy = false;
                        },
                    }" x-init="$watch('qty', () => { if (applied) apply() })">@csrf
                        <div class="flex items-center justify-between rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3">
                            <div><p class="text-sm font-medium text-ink">{{ $salespage->product_name ?: $salespage->title }}</p><p class="text-xs text-muted">RM{{ number_format($price, 2) }} / unit</p></div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="qty = Math.max(1, qty-1)" class="size-8 rounded-md border border-border text-ink-soft hover:bg-muted-surface">−</button>
                                <span class="w-6 text-center text-sm font-semibold text-ink tnum" x-text="qty"></span>
                                <button type="button" @click="qty++" class="size-8 rounded-md border border-border text-ink-soft hover:bg-muted-surface">+</button>
                                <input type="hidden" name="qty" :value="qty">
                            </div>
                        </div>
                        @if ($errors->any())<p class="rounded-[var(--radius-md)] border border-danger/30 bg-danger-soft px-3 py-2 text-sm text-danger">{{ $errors->first() }}</p>@endif
                        <x-ui.field label="Nama penuh"><x-ui.input name="customer" placeholder="Nama anda" required /></x-ui.field>
                        <x-ui.field label="No. telefon (WhatsApp)"><x-ui.input name="phone" type="tel" placeholder="012-3456789" required /></x-ui.field>
                        <x-ui.field label="Emel" hint="pilihan — untuk resit"><x-ui.input name="email" type="email" placeholder="anda@email.com" /></x-ui.field>
                        <x-ui.field label="Alamat penghantaran"><x-ui.input name="address" placeholder="No, jalan, poskod, bandar" required /></x-ui.field>
                        <x-ui.field label="Negeri">
                            <x-ui.select name="state" required>
                                <option value="" disabled selected>Pilih negeri</option>
                                @foreach ($states as $s)<option>{{ $s }}</option>@endforeach
                            </x-ui.select>
                        </x-ui.field>

                        {{-- Kupon diskaun --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Kod diskaun <span class="font-normal text-muted">(pilihan)</span></label>
                            <div class="flex gap-2">
                                <input type="text" x-model="coupon" @keydown.enter.prevent="apply()" placeholder="cth. RAYA10"
                                    :disabled="applied"
                                    class="h-11 flex-1 rounded-[var(--radius-md)] border border-border bg-surface px-3.5 text-sm uppercase tracking-wide text-ink placeholder:normal-case placeholder:tracking-normal placeholder:text-muted focus:border-primary focus:outline-none disabled:opacity-60" />
                                <button type="button" @click="applied ? (applied='', discount=0, coupon='', msg='') : apply()" :disabled="busy"
                                    class="h-11 shrink-0 rounded-[var(--radius-md)] border border-primary px-4 text-sm font-semibold text-primary hover:bg-primary/5 disabled:opacity-50"
                                    x-text="applied ? 'Buang' : (busy ? '...' : 'Guna')"></button>
                            </div>
                            <p x-show="msg" x-cloak class="mt-1.5 text-xs text-danger" x-text="msg"></p>
                            <p x-show="applied" x-cloak class="mt-1.5 flex items-center gap-1 text-xs font-medium text-success">
                                <x-lucide-badge-check class="size-3.5" /> Kod <span class="font-bold" x-text="applied"></span> digunakan — jimat RM<span x-text="discount.toFixed(2)"></span>
                            </p>
                            <input type="hidden" name="coupon_code" :value="applied">
                        </div>

                        {{-- Order bump (tawaran tambahan) --}}
                        @if ($salespage->bump_enabled && (float) $salespage->bump_price > 0)
                            <label class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-md)] border-2 border-dashed border-primary/50 bg-primary/5 p-3.5 transition-colors" :class="bump && '!border-solid border-primary bg-primary/10'">
                                <input type="checkbox" name="bump" value="1" x-model="bump" class="mt-0.5 size-5 shrink-0 accent-primary" />
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-1.5 text-sm font-bold text-ink"><x-lucide-circle-plus class="size-4 shrink-0 text-primary" /> {{ $salespage->bump_title ?: 'Tambah pakej istimewa' }}</span>
                                    @if ($salespage->bump_desc)<span class="mt-0.5 block text-xs leading-relaxed text-ink-soft">{{ $salespage->bump_desc }}</span>@endif
                                    <span class="mt-1 block text-sm font-extrabold text-primary">+ RM{{ number_format((float) $salespage->bump_price, 2) }}</span>
                                </span>
                            </label>
                        @endif

                        {{-- Ringkasan harga --}}
                        <div class="space-y-1 rounded-[var(--radius-md)] border border-border bg-muted-surface/50 px-4 py-3 text-sm">
                            <div class="flex justify-between text-ink-soft"><span>Subtotal (<span x-text="qty"></span> unit)</span><span class="tnum" x-text="'RM' + subtotal.toFixed(2)"></span></div>
                            <div x-show="discount > 0" x-cloak class="flex justify-between text-success"><span>Diskaun</span><span class="tnum">−RM<span x-text="discount.toFixed(2)"></span></span></div>
                            <div x-show="bump" x-cloak class="flex justify-between text-ink-soft"><span>{{ $salespage->bump_title ?: 'Tambahan' }}</span><span class="tnum">+RM<span x-text="bumpPrice.toFixed(2)"></span></span></div>
                            <div class="flex justify-between border-t border-border pt-1.5 text-base font-bold text-ink"><span>Jumlah</span><span class="tnum" x-text="'RM' + total.toFixed(2)"></span></div>
                        </div>

                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-md)] bg-primary px-6 py-3.5 text-base font-semibold text-primary-fg shadow-lg shadow-primary/20">
                            Sahkan Order — <span x-text="'RM' + total.toFixed(2)"></span>
                        </button>
                        <p class="flex items-center justify-center gap-1.5 text-xs text-muted"><x-lucide-shield-check class="size-3.5 text-success" /> Bayaran selamat · COD tersedia</p>
                    </form>
                @endif
            </section>
        </div>
        <div class="mx-auto mt-4 flex max-w-md items-center justify-center gap-1.5 text-xs text-muted">Dikuasakan oleh <x-logo class="scale-90" /></div>
    </div>

    {{-- Sticky mobile buy bar --}}
    @if (! session('ordered'))
        <div x-data="{ show: false }" x-init="window.addEventListener('scroll', () => show = window.scrollY > 420)"
             x-show="show" x-transition.opacity x-cloak
             class="fixed inset-x-0 bottom-0 z-50 border-t border-border bg-bg/95 px-4 py-3 shadow-[0_-2px_16px_rgba(0,0,0,0.08)] backdrop-blur-sm">
            <div class="mx-auto flex max-w-md items-center gap-3">
                <div class="leading-tight">
                    <p class="text-[0.7rem] text-muted">Harga</p>
                    <p class="text-lg font-extrabold text-primary tnum">RM{{ number_format($price, 2) }}</p>
                </div>
                <a href="#checkout" class="flex flex-1 items-center justify-center gap-2 rounded-full bg-primary px-5 py-3.5 text-sm font-bold text-primary-fg shadow-lg shadow-primary/25 active:scale-[0.98]">Beli Sekarang <x-lucide-arrow-right class="size-4" /></a>
            </div>
        </div>
    @endif
</x-layouts.base>
