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
@endphp
<div class="bg-bg">
    @if (!empty($images))
        <img src="{{ $images[0] }}" alt="" class="aspect-[4/3] w-full bg-muted-surface object-cover">
    @endif
    @if ($embed)
        <div class="aspect-video w-full bg-black"><iframe src="{{ $embed }}" class="size-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
    @elseif ($video)
        <video src="{{ $video }}" controls class="aspect-video w-full bg-black object-contain"></video>
    @endif
    @if (count($images) > 1)
        <div class="scroll-thin flex gap-2 overflow-x-auto px-4 py-3">
            @foreach (array_slice($images, 1) as $img)
                <img src="{{ $img }}" class="size-20 shrink-0 rounded-[var(--radius-md)] border border-border object-cover" alt="">
            @endforeach
        </div>
    @endif
    @foreach ($blocks as $b)
        @php $type = $b['type'] ?? ''; @endphp
        @switch($type)
            @case('hero')
                <section class="bg-gradient-to-b from-primary-soft to-transparent px-6 py-10 text-center">
                    <span class="inline-block rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">✦ Tawaran istimewa</span>
                    <h1 class="mx-auto mt-3 max-w-[20ch] text-2xl font-bold leading-tight text-ink text-balance">{{ $b['headline'] ?? '' }}</h1>
                    <p class="mx-auto mt-3 max-w-[34ch] text-sm text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    @if (!empty($b['bullets']))
                        <ul class="mx-auto mt-4 flex max-w-sm flex-col gap-1.5 text-left">
                            @foreach ($b['bullets'] as $bl)
                                <li class="flex items-center gap-2 text-sm text-ink-soft"><x-lucide-check class="size-4 shrink-0 text-success" /> {{ $bl }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
                @break

            @case('problem')
            @case('solution')
                <section class="px-6 py-8">
                    <h2 class="text-lg font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    @if (!empty($b['bullets']))
                        <ul class="mt-3 space-y-2">
                            @foreach ($b['bullets'] as $bl)
                                <li class="flex items-start gap-2 text-sm text-ink-soft"><span class="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full bg-primary/15 text-[0.6rem] text-primary">✓</span>{{ $bl }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
                @break

            @case('agitate')
                <section class="bg-danger-soft/50 px-6 py-8">
                    <h2 class="text-lg font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $b['body'] ?? '' }}</p>
                </section>
                @break

            @case('offer')
            @case('cta')
                @php $price = (float) ($b['meta']['price'] ?? 0); $compare = (float) ($b['meta']['compare'] ?? 0); @endphp
                <section class="px-6 py-8 text-center">
                    <h2 class="text-xl font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                    @if (!empty($b['body']))<p class="mx-auto mt-2 max-w-[36ch] text-sm text-ink-soft">{{ $b['body'] }}</p>@endif
                    @if (!empty($b['bullets']))
                        <ul class="mx-auto mt-4 max-w-sm space-y-2 text-left">
                            @foreach ($b['bullets'] as $bl)
                                <li class="flex items-center gap-2 text-sm text-ink-soft"><x-lucide-check class="size-4 shrink-0 text-success" /> {{ $bl }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if ($price > 0)
                        <div class="mt-5">
                            <span class="text-sm text-muted line-through tnum">{{ $rm($compare) }}</span>
                            <span class="ml-2 text-3xl font-bold text-primary tnum">{{ $rm($price) }}</span>
                        </div>
                    @endif
                    <a href="#checkout" class="mt-4 block w-full rounded-[var(--radius-md)] bg-primary px-6 py-3.5 text-base font-semibold text-primary-fg shadow-lg shadow-primary/20">Saya Nak Sekarang →</a>
                    <p class="mt-2 text-xs text-muted">🔒 Bayaran selamat · COD tersedia</p>
                </section>
                @break

            @case('bonus')
                <section class="mx-4 my-2 rounded-[var(--radius-lg)] border border-dashed border-primary/40 bg-primary-soft/40 px-5 py-6">
                    <h2 class="text-base font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($b['bullets'] ?? [] as $bl)<li class="flex items-center gap-2 text-sm text-ink-soft">🎁 {{ $bl }}</li>@endforeach
                    </ul>
                </section>
                @break

            @case('proof')
                <section class="px-6 py-8">
                    <h2 class="text-lg font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($b['items'] ?? [] as $it)
                            <figure class="rounded-[var(--radius-md)] border border-border bg-surface p-4">
                                <div class="flex gap-0.5 text-warning">@for ($i = 0; $i < 5; $i++)<x-lucide-star class="size-3.5 fill-current" />@endfor</div>
                                <blockquote class="mt-2 text-sm text-ink-soft">"{{ $it['a'] ?? '' }}"</blockquote>
                                <figcaption class="mt-1.5 text-xs font-medium text-muted">— {{ $it['q'] ?? '' }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </section>
                @break

            @case('guarantee')
                <section class="mx-4 my-2 flex items-start gap-3 rounded-[var(--radius-lg)] border border-success/30 bg-success-soft/50 px-5 py-5">
                    <x-lucide-shield-check class="size-8 shrink-0 text-success" />
                    <div>
                        <h2 class="text-base font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                        <p class="mt-1 text-sm text-ink-soft">{{ $b['body'] ?? '' }}</p>
                    </div>
                </section>
                @break

            @case('urgency')
                <section class="flex items-center justify-center gap-2 bg-ink px-6 py-4 text-center text-sm font-medium text-bg">
                    <x-lucide-clock class="size-4" /> {{ $b['headline'] ?? '' }} — {{ $b['body'] ?? '' }}
                </section>
                @break

            @case('faq')
                <section class="px-6 py-8">
                    <h2 class="text-lg font-bold text-ink">{{ $b['headline'] ?? '' }}</h2>
                    <div class="mt-3 divide-y divide-border rounded-[var(--radius-md)] border border-border">
                        @foreach ($b['items'] ?? [] as $it)
                            <details class="group px-4 py-3">
                                <summary class="cursor-pointer list-none text-sm font-medium text-ink marker:hidden">{{ $it['q'] ?? '' }}</summary>
                                <p class="mt-1.5 text-sm text-ink-soft">{{ $it['a'] ?? '' }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
                @break

            @case('ps')
                <section class="px-6 py-6">
                    <p class="rounded-[var(--radius-md)] bg-muted-surface px-4 py-3 text-sm italic text-ink-soft">{{ $b['body'] ?? '' }}</p>
                </section>
                @break
        @endswitch
    @endforeach
    <footer class="border-t border-border px-6 py-6 text-center text-xs text-muted">© {{ date('Y') }} · Dikuasakan oleh <span class="font-semibold text-ink">Mendap</span></footer>
</div>
