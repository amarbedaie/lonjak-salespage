@php
    $blocks = $page['blocks'] ?? [];
    $images = array_values(array_filter($page['images'] ?? []));
    $video = $page['video'] ?? null;
    $rm = fn ($n) => 'RM' . number_format((float) $n, 2);
    $embed = null;
    if ($video) {
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]+)~', $video, $m)) {
            $embed = 'https://www.youtube.com/embed/' . $m[1];
        } elseif (preg_match('~vimeo\.com/(\d+)~', $video, $m)) {
            $embed = 'https://player.vimeo.com/video/' . $m[1];
        }
    }
    // Pull the headline price from the first offer/cta block for hero + sticky CTA.
    $heroPrice = 0; $heroCompare = 0;
    foreach ($blocks as $bb) {
        if (in_array($bb['type'] ?? '', ['offer', 'cta'], true) && ! empty($bb['meta']['price'])) {
            $heroPrice = (float) $bb['meta']['price'];
            $heroCompare = (float) ($bb['meta']['compare'] ?? 0);
            break;
        }
    }
@endphp
<div class="bg-bg text-ink">
    @foreach ($blocks as $b)
        @php $type = $b['type'] ?? ''; @endphp
        @switch($type)
            @case('hero')
                <section class="relative overflow-hidden bg-gradient-to-b from-primary-soft via-primary-soft/40 to-bg px-6 pb-10 pt-9 text-center">
                    <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-primary/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute -left-16 top-20 size-40 rounded-full bg-primary/5 blur-3xl"></div>
                    <div class="relative">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/12 px-3.5 py-1.5 text-xs font-semibold text-primary ring-1 ring-primary/15">✦ Tawaran istimewa hari ini</span>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <div class="flex">@for ($i = 0; $i < 5; $i++)<x-lucide-star class="size-4 fill-amber-400 text-amber-400" />@endfor</div>
                            <span class="text-xs font-medium text-ink-soft">{{ $b['meta']['customers'] ?? '2,000+' }} pelanggan berpuas hati</span>
                        </div>
                        <h1 class="mx-auto mt-4 max-w-[18ch] text-[1.7rem] font-extrabold leading-[1.12] tracking-tight text-balance">{{ $b['headline'] ?? '' }}</h1>
                        <p class="mx-auto mt-3.5 max-w-[34ch] text-[0.95rem] leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                        @if (! empty($images))
                            <div class="mx-auto mt-6 max-w-[290px] overflow-hidden rounded-[var(--radius-xl)] shadow-xl shadow-primary/10 ring-1 ring-black/5">
                                <img src="{{ $images[0] }}" alt="" class="aspect-square w-full bg-muted-surface object-cover">
                            </div>
                        @endif
                        @if (! empty($b['bullets']))
                            <ul class="mx-auto mt-6 flex max-w-xs flex-col gap-2 text-left">
                                @foreach ($b['bullets'] as $bl)
                                    <li class="flex items-center gap-2.5 text-sm font-medium"><span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-success/15 text-success"><x-lucide-check class="size-3" /></span>{{ $bl }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <a href="#checkout" class="mt-7 flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-4 text-base font-bold text-primary-fg shadow-lg shadow-primary/25 transition active:scale-[0.98]">Saya Nak Sekarang <x-lucide-arrow-right class="size-4" /></a>
                        <div class="mt-3 flex items-center justify-center gap-3 text-[0.7rem] text-muted">
                            <span class="flex items-center gap-1"><x-lucide-shield-check class="size-3.5 text-success" /> Bayaran selamat</span>
                            <span class="flex items-center gap-1"><x-lucide-truck class="size-3.5" /> COD tersedia</span>
                        </div>
                    </div>
                </section>
                @if ($embed)
                    <div class="aspect-video w-full bg-black"><iframe src="{{ $embed }}" class="size-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
                @elseif ($video)
                    <video src="{{ $video }}" controls class="aspect-video w-full bg-black object-contain"></video>
                @endif
                @if (count($images) > 1)
                    <div class="scroll-thin flex gap-2.5 overflow-x-auto px-6 py-4">
                        @foreach (array_slice($images, 1) as $img)
                            <img src="{{ $img }}" class="size-24 shrink-0 rounded-[var(--radius-lg)] border border-border object-cover" alt="">
                        @endforeach
                    </div>
                @endif
                @break

            @case('problem')
                <section class="px-6 py-9">
                    <h2 class="text-center text-xl font-bold tracking-tight">{{ $b['headline'] ?? '' }}</h2>
                    <p class="mx-auto mt-2.5 max-w-[40ch] text-center text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    @if (! empty($b['bullets']))
                        <ul class="mx-auto mt-5 max-w-md space-y-2.5">
                            @foreach ($b['bullets'] as $bl)
                                <li class="flex items-start gap-3 rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3 text-sm text-ink-soft"><x-lucide-x class="mt-0.5 size-4 shrink-0 text-danger" />{{ $bl }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
                @break

            @case('agitate')
                <section class="bg-gradient-to-b from-danger-soft/40 to-bg px-6 py-9 text-center">
                    <x-lucide-triangle-alert class="mx-auto size-7 text-danger/70" />
                    <h2 class="mx-auto mt-3 max-w-[24ch] text-lg font-bold leading-snug">{{ $b['headline'] ?? '' }}</h2>
                    <p class="mx-auto mt-2.5 max-w-[40ch] text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                </section>
                @break

            @case('solution')
                <section class="px-6 py-9">
                    <div class="mx-auto max-w-md text-center">
                        <span class="inline-block rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">Penyelesaiannya</span>
                        <h2 class="mt-3 text-xl font-bold tracking-tight">{{ $b['headline'] ?? '' }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    </div>
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
                <section class="px-5 py-9">
                    <div class="mx-auto max-w-md overflow-hidden rounded-[var(--radius-xl)] border-2 border-primary/25 bg-surface shadow-xl shadow-primary/10">
                        <div class="bg-primary/10 px-6 py-4 text-center"><h2 class="text-lg font-bold">{{ $b['headline'] ?? 'Apa anda akan dapat' }}</h2></div>
                        <div class="px-6 py-6">
                            @if (! empty($b['body']))<p class="mb-4 text-center text-sm text-ink-soft">{{ $b['body'] }}</p>@endif
                            @if (! empty($b['bullets']))
                                <ul class="space-y-3">
                                    @foreach ($b['bullets'] as $bl)
                                        <li class="flex items-start gap-3 text-sm"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-success text-white"><x-lucide-check class="size-3" /></span><span class="font-medium">{{ $bl }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                            @if ($price > 0)
                                <div class="mt-6 rounded-[var(--radius-lg)] bg-muted-surface/60 p-5 text-center">
                                    @if ($compare > 0)<p class="text-sm text-muted">Harga biasa <span class="line-through tnum">{{ $rm($compare) }}</span></p>@endif
                                    <div class="mt-1 flex items-baseline justify-center gap-2">
                                        <span class="text-[2.5rem] font-extrabold leading-none text-primary tnum">{{ $rm($price) }}</span>
                                        @if ($save > 0)<span class="rounded-full bg-success/15 px-2 py-0.5 text-xs font-bold text-success">Jimat {{ $save }}%</span>@endif
                                    </div>
                                </div>
                            @endif
                            <a href="#checkout" class="mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-4 text-base font-bold text-primary-fg shadow-lg shadow-primary/25 active:scale-[0.98]">Order Sekarang <x-lucide-arrow-right class="size-4" /></a>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 text-[0.7rem] text-muted">
                                <span class="flex items-center gap-1"><x-lucide-shield-check class="size-3.5 text-success" /> Jaminan 30 hari</span>
                                <span class="flex items-center gap-1"><x-lucide-lock class="size-3.5" /> Bayaran selamat</span>
                                <span class="flex items-center gap-1"><x-lucide-truck class="size-3.5" /> COD tersedia</span>
                            </div>
                        </div>
                    </div>
                </section>
                @break

            @case('bonus')
                <section class="px-5 py-3">
                    <div class="mx-auto max-w-md rounded-[var(--radius-xl)] border-2 border-dashed border-primary/40 bg-primary-soft/40 px-6 py-6">
                        <div class="flex items-center gap-2"><span class="text-xl">🎁</span><h2 class="text-base font-bold">{{ $b['headline'] ?? 'Bonus eksklusif' }}</h2></div>
                        <ul class="mt-3 space-y-2.5">
                            @foreach ($b['bullets'] ?? [] as $bl)
                                <li class="flex items-start gap-2.5 text-sm text-ink-soft"><x-lucide-gift class="mt-0.5 size-4 shrink-0 text-primary" />{{ $bl }}</li>
                            @endforeach
                        </ul>
                    </div>
                </section>
                @break

            @case('proof')
                <section class="bg-muted-surface/40 px-6 py-9">
                    <h2 class="text-center text-xl font-bold tracking-tight">{{ $b['headline'] ?? 'Apa kata pelanggan' }}</h2>
                    <div class="mx-auto mt-5 grid max-w-md gap-3">
                        @foreach ($b['items'] ?? [] as $it)
                            @php $name = $it['q'] ?? 'Pelanggan'; @endphp
                            <figure class="rounded-[var(--radius-lg)] border border-border bg-bg p-4 shadow-sm">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/12 text-sm font-bold text-primary">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                                    <div>
                                        <figcaption class="text-sm font-semibold leading-tight">{{ $name }}</figcaption>
                                        <div class="flex text-amber-400">@for ($i = 0; $i < 5; $i++)<x-lucide-star class="size-3 fill-current" />@endfor</div>
                                    </div>
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
                        <div>
                            <h2 class="text-base font-bold">{{ $b['headline'] ?? 'Jaminan Wang Dikembalikan' }}</h2>
                            <p class="mt-1 text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                        </div>
                    </div>
                </section>
                @break

            @case('urgency')
                <section class="mx-5 my-3 flex items-center justify-center gap-2.5 rounded-[var(--radius-lg)] bg-ink px-5 py-3.5 text-center text-sm font-semibold text-bg">
                    <x-lucide-clock class="size-4 shrink-0 animate-pulse" />
                    <span>{{ $b['headline'] ?? '' }}@if (! empty($b['body'])) — {{ $b['body'] }}@endif</span>
                </section>
                @break

            @case('faq')
                <section class="px-6 py-9">
                    <h2 class="text-center text-xl font-bold tracking-tight">{{ $b['headline'] ?? 'Soalan Lazim' }}</h2>
                    <div class="mx-auto mt-5 max-w-md space-y-2.5">
                        @foreach ($b['items'] ?? [] as $it)
                            <details class="group rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3.5 [&_summary::-webkit-details-marker]:hidden">
                                <summary class="flex cursor-pointer items-center justify-between gap-3 text-sm font-semibold">{{ $it['q'] ?? '' }}<x-lucide-chevron-down class="size-4 shrink-0 text-muted transition-transform group-open:rotate-180" /></summary>
                                <p class="mt-2.5 text-sm leading-relaxed text-ink-soft">{{ $it['a'] ?? '' }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
                @break

            @case('cta')
                @php $price = (float) ($b['meta']['price'] ?? $heroPrice); $compare = (float) ($b['meta']['compare'] ?? $heroCompare); @endphp
                <section class="bg-gradient-to-b from-bg to-primary-soft/50 px-6 py-10 text-center">
                    <h2 class="mx-auto max-w-[20ch] text-2xl font-extrabold tracking-tight text-balance">{{ $b['headline'] ?? '' }}</h2>
                    @if (! empty($b['body']))<p class="mx-auto mt-3 max-w-[34ch] text-sm text-ink-soft">{{ $b['body'] }}</p>@endif
                    @if ($price > 0)
                        <div class="mt-5 flex items-baseline justify-center gap-2">
                            @if ($compare > 0)<span class="text-base text-muted line-through tnum">{{ $rm($compare) }}</span>@endif
                            <span class="text-[2rem] font-extrabold text-primary tnum">{{ $rm($price) }}</span>
                        </div>
                    @endif
                    <a href="#checkout" class="mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-4 text-base font-bold text-primary-fg shadow-lg shadow-primary/25 active:scale-[0.98]">Saya Nak Sekarang <x-lucide-arrow-right class="size-4" /></a>
                    <p class="mt-3 text-xs text-muted">🔒 Bayaran selamat · COD tersedia · Jaminan 30 hari</p>
                </section>
                @break

            @case('ps')
                <section class="px-6 pb-8 pt-4">
                    <div class="mx-auto max-w-md rounded-[var(--radius-md)] border-l-[3px] border-primary bg-muted-surface/60 px-4 py-3.5">
                        <p class="text-sm italic leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    </div>
                </section>
                @break
        @endswitch
    @endforeach
    <footer class="border-t border-border px-6 py-6 text-center">
        <div class="flex items-center justify-center gap-4 text-[0.7rem] text-muted">
            <span class="flex items-center gap-1"><x-lucide-lock class="size-3.5" /> SSL Selamat</span>
            <span class="flex items-center gap-1"><x-lucide-credit-card class="size-3.5" /> FPX · DuitNow · COD</span>
        </div>
        <p class="mt-3 text-xs text-muted">© {{ date('Y') }} · Dikuasakan oleh <span class="font-semibold text-ink">Mendap</span></p>
    </footer>
</div>
