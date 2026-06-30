@php
    $blocks = $page['blocks'] ?? [];
    $images = array_values(array_filter($page['images'] ?? []));
    $video = $page['video'] ?? null;
    $gallery = $page['gallery'] ?? null;
    $videoBlock = $page['video_block'] ?? 'hero';
    // If media wasn't AI-directed (no block carries an image), fall back to basic placement.
    if (! collect($blocks)->contains(fn ($b) => ! empty($b['image'])) && $images) {
        $map = ['hero' => 0, 'problem' => 1, 'solution' => 2];
        foreach ($blocks as $i => $b) {
            $t = $b['type'] ?? '';
            if (isset($map[$t], $images[$map[$t]])) {
                $blocks[$i]['image'] = $images[$map[$t]];
            }
        }
        $gallery = array_slice($images, 3);
    }
    $gallery = $gallery ?: [];
    $rm = fn ($n) => 'RM' . number_format((float) $n, 2);

    // Visual theme (color scheme) — picked per salespage.
    $themes = [
        'default' => ['heroBg' => 'bg-gradient-to-b from-primary-soft via-primary-soft/50 to-bg', 'heroText' => 'text-ink', 'heroSub' => 'text-ink-soft', 'badge' => 'bg-primary/12 text-primary ring-primary/20', 'accent' => 'text-primary', 'cta' => 'bg-primary text-primary-fg shadow-primary/25', 'banner' => 'bg-ink text-bg', 'offerBorder' => 'border-primary/30', 'offerHead' => 'bg-primary/10', 'price' => 'text-primary'],
        'hijau'   => ['heroBg' => 'bg-gradient-to-b from-emerald-900 to-emerald-950', 'heroText' => 'text-amber-50', 'heroSub' => 'text-emerald-100/80', 'badge' => 'bg-amber-400/15 text-amber-200 ring-amber-300/25', 'accent' => 'text-emerald-700', 'cta' => 'bg-amber-400 text-amber-950 shadow-amber-500/30', 'banner' => 'bg-emerald-950 text-amber-50', 'offerBorder' => 'border-emerald-600/40', 'offerHead' => 'bg-emerald-700/10', 'price' => 'text-emerald-700'],
        'biru'    => ['heroBg' => 'bg-gradient-to-b from-teal-800 to-teal-950', 'heroText' => 'text-white', 'heroSub' => 'text-teal-100/80', 'badge' => 'bg-white/10 text-teal-50 ring-white/20', 'accent' => 'text-teal-700', 'cta' => 'bg-rose-500 text-white shadow-rose-500/30', 'banner' => 'bg-teal-950 text-white', 'offerBorder' => 'border-teal-500/40', 'offerHead' => 'bg-teal-600/10', 'price' => 'text-teal-700'],
        'oren'    => ['heroBg' => 'bg-gradient-to-b from-orange-100 to-bg', 'heroText' => 'text-stone-900', 'heroSub' => 'text-stone-600', 'badge' => 'bg-orange-500/15 text-orange-700 ring-orange-500/25', 'accent' => 'text-orange-600', 'cta' => 'bg-orange-500 text-white shadow-orange-500/30', 'banner' => 'bg-stone-900 text-orange-50', 'offerBorder' => 'border-orange-400/50', 'offerHead' => 'bg-orange-500/10', 'price' => 'text-orange-600'],
        'gelap'   => ['heroBg' => 'bg-gradient-to-b from-zinc-900 to-zinc-950', 'heroText' => 'text-white', 'heroSub' => 'text-zinc-300', 'badge' => 'bg-primary/20 text-primary ring-primary/30', 'accent' => 'text-primary', 'cta' => 'bg-primary text-primary-fg shadow-primary/30', 'banner' => 'bg-black text-white', 'offerBorder' => 'border-primary/40', 'offerHead' => 'bg-primary/15', 'price' => 'text-primary'],
    ];
    $T = $themes[$page['theme'] ?? 'default'] ?? $themes['default'];
    $painEmoji = ['😔', '😰', '🤔', '😣', '💸', '😩'];

    $heroPrice = 0; $heroCompare = 0; $urgencyText = '';
    foreach ($blocks as $bb) {
        if (in_array($bb['type'] ?? '', ['offer', 'cta'], true) && ! empty($bb['meta']['price']) && ! $heroPrice) {
            $heroPrice = (float) $bb['meta']['price']; $heroCompare = (float) ($bb['meta']['compare'] ?? 0);
        }
        if (($bb['type'] ?? '') === 'urgency' && ! $urgencyText) {
            $urgencyText = trim(($bb['headline'] ?? '') . ' ' . ($bb['body'] ?? ''));
        }
    }
    $embed = null;
    if ($video) {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]+)~', $video, $m)) $embed = 'https://www.youtube.com/embed/' . $m[1];
        elseif (preg_match('~vimeo\.com/(\d+)~', $video, $m)) $embed = 'https://player.vimeo.com/video/' . $m[1];
    }
@endphp
<div class="bg-bg text-ink">
    {{-- Urgency top banner --}}
    <div class="{{ $T['banner'] }} px-4 py-2 text-center text-xs font-bold tracking-wide">
        🔥 {{ \Illuminate\Support\Str::limit($urgencyText ?: 'Stok terhad — dapatkan sekarang sebelum harga naik!', 64) }}
    </div>

    @foreach ($blocks as $b)
        @php $type = $b['type'] ?? ''; @endphp
        @switch($type)
            @case('hero')
                <section class="{{ $T['heroBg'] }} relative overflow-hidden px-6 pb-10 pt-9 text-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-wide ring-1 {{ $T['badge'] }}">✦ Tawaran istimewa hari ini</span>
                    <h1 class="font-display mx-auto mt-5 max-w-[15ch] text-[2.15rem] font-black leading-[1.05] tracking-tight text-balance {{ $T['heroText'] }}">{{ $b['headline'] ?? '' }}</h1>
                    <p class="mx-auto mt-4 max-w-[34ch] text-[0.95rem] leading-relaxed {{ $T['heroSub'] }}">{{ $b['body'] ?? '' }}</p>
                    @if (! empty($b['image']))
                        <div class="mx-auto mt-7 max-w-[300px] overflow-hidden rounded-[20px] shadow-2xl ring-1 ring-black/10"><img src="{{ $b['image'] }}" alt="" class="aspect-square w-full bg-muted-surface object-cover"></div>
                    @endif
                    @if (! empty($b['bullets']))
                        <ul class="mx-auto mt-6 flex max-w-xs flex-col gap-2 text-left">
                            @foreach ($b['bullets'] as $bl)
                                <li class="flex items-center gap-2.5 text-sm font-semibold {{ $T['heroText'] }}"><span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-success/20 text-success"><x-lucide-check class="size-3" /></span>{{ $bl }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="#checkout" class="mt-7 flex w-full items-center justify-center gap-2 rounded-full px-6 py-4 text-base font-extrabold shadow-lg transition active:scale-[0.98] {{ $T['cta'] }}">{{ $b['cta_text'] ?? 'Saya Nak Sekarang' }} <x-lucide-arrow-right class="size-5" /></a>
                    <div class="mt-5 flex items-center justify-center gap-2">
                        <div class="flex">@for ($i = 0; $i < 5; $i++)<x-lucide-star class="size-4 fill-amber-400 text-amber-400" />@endfor</div>
                        <span class="text-xs font-medium {{ $T['heroSub'] }}">{{ $b['meta']['customers'] ?? '2,000+' }} pelanggan berpuas hati</span>
                    </div>
                </section>
                @break

            @case('problem')
                <section class="px-6 py-10">
                    <p class="text-center text-xs font-bold uppercase tracking-widest {{ $T['accent'] }}">Realiti</p>
                    <h2 class="font-display mx-auto mt-2 max-w-[18ch] text-center text-[1.6rem] font-black leading-tight tracking-tight">{{ $b['headline'] ?? '' }}</h2>
                    @if (! empty($b['body']))<p class="mx-auto mt-3 max-w-[40ch] text-center text-sm leading-relaxed text-ink-soft">{{ $b['body'] }}</p>@endif
                    @if (! empty($b['image']))<div class="mx-auto mt-6 max-w-md overflow-hidden rounded-[var(--radius-xl)] shadow-lg ring-1 ring-black/5"><img src="{{ $b['image'] }}" alt="" class="aspect-[4/3] w-full bg-muted-surface object-cover"></div>@endif
                    @if (! empty($b['bullets']))
                        <div class="mx-auto mt-6 grid max-w-md gap-3">
                            @foreach ($b['bullets'] as $i => $bl)
                                <div class="flex items-start gap-3.5 rounded-[var(--radius-lg)] border border-border bg-surface px-4 py-4 shadow-sm">
                                    <span class="text-3xl leading-none">{{ $painEmoji[$i % count($painEmoji)] }}</span>
                                    <p class="pt-1 text-sm leading-relaxed text-ink-soft">{{ $bl }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
                @break

            @case('agitate')
                <section class="bg-gradient-to-b from-danger-soft/40 to-bg px-6 py-10 text-center">
                    <span class="text-4xl">⚠️</span>
                    <h2 class="font-display mx-auto mt-3 max-w-[22ch] text-[1.5rem] font-black leading-tight tracking-tight">{{ $b['headline'] ?? '' }}</h2>
                    <p class="mx-auto mt-3 max-w-[40ch] text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                </section>
                @break

            @case('solution')
                <section class="px-6 py-10">
                    <p class="text-center text-xs font-bold uppercase tracking-widest {{ $T['accent'] }}">Penyelesaiannya</p>
                    <h2 class="font-display mx-auto mt-2 max-w-[18ch] text-center text-[1.6rem] font-black leading-tight tracking-tight">{{ $b['headline'] ?? '' }}</h2>
                    <p class="mx-auto mt-3 max-w-[40ch] text-center text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    @if (! empty($b['image']))<div class="mx-auto mt-5 max-w-[320px] overflow-hidden rounded-[var(--radius-xl)] border border-border shadow-lg"><img src="{{ $b['image'] }}" alt="" class="w-full object-cover"></div>@endif
                    @if (! empty($b['bullets']))
                        <ul class="mx-auto mt-5 grid max-w-md gap-2.5">
                            @foreach ($b['bullets'] as $bl)
                                <li class="flex items-start gap-3 text-sm"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-success/15 text-success"><x-lucide-check class="size-3" /></span><span class="text-ink-soft">{{ $bl }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </section>
                @break

            @case('offer')
                @php $price = (float) ($b['meta']['price'] ?? $heroPrice); $compare = (float) ($b['meta']['compare'] ?? $heroCompare); $save = $compare > $price ? round(($compare - $price) / $compare * 100) : 0; @endphp
                <section class="px-5 py-10">
                    <div class="mx-auto max-w-md overflow-hidden rounded-[var(--radius-xl)] border-2 bg-surface shadow-xl {{ $T['offerBorder'] }}">
                        <div class="{{ $T['offerHead'] }} px-6 py-4 text-center"><h2 class="font-display text-xl font-black">{{ $b['headline'] ?? 'Apa anda akan dapat' }}</h2></div>
                        <div class="px-6 py-6">
                            @if (! empty($b['body']))<p class="mb-4 text-center text-sm text-ink-soft">{{ $b['body'] }}</p>@endif
                            @if (! empty($b['bullets']))
                                <ul class="space-y-3">@foreach ($b['bullets'] as $bl)<li class="flex items-start gap-3 text-sm"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-success text-white"><x-lucide-check class="size-3" /></span><span class="font-medium">{{ $bl }}</span></li>@endforeach</ul>
                            @endif
                            @if ($price > 0)
                                <div class="mt-6 rounded-[var(--radius-lg)] bg-muted-surface/60 p-5 text-center">
                                    @if ($compare > 0)<p class="text-sm text-muted">Harga biasa <span class="line-through tnum">{{ $rm($compare) }}</span></p>@endif
                                    <div class="mt-1 flex items-baseline justify-center gap-2"><span class="font-display text-[2.6rem] font-black leading-none {{ $T['price'] }} tnum">{{ $rm($price) }}</span>@if ($save > 0)<span class="rounded-full bg-success/15 px-2 py-0.5 text-xs font-bold text-success">Jimat {{ $save }}%</span>@endif</div>
                                </div>
                            @endif
                            <a href="#checkout" class="mt-5 flex w-full items-center justify-center gap-2 rounded-full px-6 py-4 text-base font-extrabold shadow-lg active:scale-[0.98] {{ $T['cta'] }}">Order Sekarang <x-lucide-arrow-right class="size-5" /></a>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 text-[0.7rem] text-muted"><span class="flex items-center gap-1"><x-lucide-shield-check class="size-3.5 text-success" /> Jaminan 30 hari</span><span class="flex items-center gap-1"><x-lucide-lock class="size-3.5" /> Selamat</span><span class="flex items-center gap-1"><x-lucide-truck class="size-3.5" /> COD ada</span></div>
                        </div>
                    </div>
                </section>
                @break

            @case('bonus')
                <section class="px-5 py-3">
                    <div class="mx-auto max-w-md rounded-[var(--radius-xl)] border-2 border-dashed {{ $T['offerBorder'] }} bg-muted-surface/40 px-6 py-6">
                        <div class="flex items-center gap-2"><span class="text-2xl">🎁</span><h2 class="font-display text-lg font-black">{{ $b['headline'] ?? 'Bonus eksklusif' }}</h2></div>
                        <ul class="mt-3 space-y-2.5">@foreach ($b['bullets'] ?? [] as $bl)<li class="flex items-start gap-2.5 text-sm text-ink-soft"><x-lucide-gift class="mt-0.5 size-4 shrink-0 {{ $T['accent'] }}" />{{ $bl }}</li>@endforeach</ul>
                    </div>
                </section>
                @break

            @case('proof')
                <section class="bg-muted-surface/40 px-6 py-10">
                    <p class="text-center text-xs font-bold uppercase tracking-widest {{ $T['accent'] }}">Bukti sebenar</p>
                    <h2 class="font-display mx-auto mt-2 max-w-[18ch] text-center text-[1.5rem] font-black leading-tight tracking-tight">{{ $b['headline'] ?? 'Apa kata pelanggan' }}</h2>
                    <div class="mx-auto mt-6 grid max-w-md gap-3">
                        @foreach ($b['items'] ?? [] as $it)
                            @php $name = $it['q'] ?? 'Pelanggan'; @endphp
                            <figure class="rounded-[var(--radius-lg)] border border-border bg-bg p-4 shadow-sm">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/12 text-sm font-bold text-primary">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                                    <div><figcaption class="text-sm font-bold leading-tight">{{ $name }}</figcaption><div class="flex text-amber-400">@for ($i = 0; $i < 5; $i++)<x-lucide-star class="size-3 fill-current" />@endfor</div></div>
                                    <x-lucide-badge-check class="ml-auto size-4 text-success" />
                                </div>
                                <blockquote class="mt-2.5 text-sm leading-relaxed text-ink-soft">"{{ $it['a'] ?? '' }}"</blockquote>
                            </figure>
                        @endforeach
                    </div>
                </section>
                @break

            @case('guarantee')
                <section class="px-5 py-6">
                    <div class="mx-auto flex max-w-md items-center gap-4 rounded-[var(--radius-xl)] border border-success/30 bg-success-soft/50 px-5 py-5">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-success/15"><x-lucide-shield-check class="size-8 text-success" /></div>
                        <div><h2 class="font-display text-lg font-black">{{ $b['headline'] ?? 'Jaminan Wang Dikembalikan' }}</h2><p class="mt-1 text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p></div>
                    </div>
                </section>
                @break

            @case('urgency')
                <section class="{{ $T['banner'] }} mx-5 my-3 flex items-center justify-center gap-2.5 rounded-[var(--radius-lg)] px-5 py-3.5 text-center text-sm font-bold"><x-lucide-clock class="size-4 shrink-0 animate-pulse" /><span>{{ $b['headline'] ?? '' }}@if (! empty($b['body'])) — {{ $b['body'] }}@endif</span></section>
                @break

            @case('faq')
                <section class="px-6 py-10">
                    <h2 class="font-display text-center text-[1.5rem] font-black tracking-tight">{{ $b['headline'] ?? 'Soalan Lazim' }}</h2>
                    <div class="mx-auto mt-5 max-w-md space-y-2.5">
                        @foreach ($b['items'] ?? [] as $it)
                            <details class="group rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3.5 [&_summary::-webkit-details-marker]:hidden">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-bold">{{ $it['q'] ?? '' }}<x-lucide-chevron-down class="size-4 shrink-0 text-muted transition-transform group-open:rotate-180" /></summary>
                                <p class="mt-2.5 text-sm leading-relaxed text-ink-soft">{{ $it['a'] ?? '' }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
                @break

            @case('cta')
                @php $price = (float) ($b['meta']['price'] ?? $heroPrice); $compare = (float) ($b['meta']['compare'] ?? $heroCompare); @endphp
                <section class="{{ $T['heroBg'] }} px-6 py-11 text-center">
                    <h2 class="font-display mx-auto max-w-[18ch] text-[1.8rem] font-black leading-tight tracking-tight {{ $T['heroText'] }}">{{ $b['headline'] ?? '' }}</h2>
                    @if (! empty($b['body']))<p class="mx-auto mt-3 max-w-[34ch] text-sm {{ $T['heroSub'] }}">{{ $b['body'] }}</p>@endif
                    @if ($price > 0)<div class="mt-5 flex items-baseline justify-center gap-2">@if ($compare > 0)<span class="text-base line-through {{ $T['heroSub'] }} tnum">{{ $rm($compare) }}</span>@endif<span class="font-display text-[2.2rem] font-black {{ $T['heroText'] }} tnum">{{ $rm($price) }}</span></div>@endif
                    <a href="#checkout" class="mt-5 flex w-full items-center justify-center gap-2 rounded-full px-6 py-4 text-base font-extrabold shadow-lg active:scale-[0.98] {{ $T['cta'] }}">{{ $b['cta_text'] ?? 'Saya Nak Sekarang' }} <x-lucide-arrow-right class="size-5" /></a>
                    <p class="mt-3 text-xs {{ $T['heroSub'] }}">🔒 Bayaran selamat · COD tersedia · Jaminan 30 hari</p>
                </section>
                @break

            @case('ps')
                <section class="px-6 pb-8 pt-5"><div class="mx-auto max-w-md rounded-[var(--radius-md)] border-l-[3px] border-primary bg-muted-surface/60 px-4 py-3.5"><p class="text-sm italic leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p></div></section>
                @break
        @endswitch
        @if ($video && $type === $videoBlock)
            @if ($embed)<div class="aspect-video w-full bg-black"><iframe src="{{ $embed }}" class="size-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
            @elseif ($video)<video src="{{ $video }}" controls class="aspect-video w-full bg-black object-contain"></video>@endif
        @endif
    @endforeach

    @if (count($gallery))
        <div class="scroll-thin flex gap-2.5 overflow-x-auto px-6 py-4">@foreach ($gallery as $img)<img src="{{ $img }}" class="size-24 shrink-0 rounded-[var(--radius-lg)] border border-border object-cover" alt="">@endforeach</div>
    @endif

    <footer class="border-t border-border px-6 py-6 text-center">
        <div class="flex items-center justify-center gap-4 text-[0.7rem] text-muted"><span class="flex items-center gap-1"><x-lucide-lock class="size-3.5" /> SSL Selamat</span><span class="flex items-center gap-1"><x-lucide-credit-card class="size-3.5" /> FPX · DuitNow · COD</span></div>
        <p class="mt-3 text-xs text-muted">© {{ date('Y') }} · Dikuasakan oleh <span class="font-semibold text-ink">Mendap</span></p>
    </footer>
</div>
