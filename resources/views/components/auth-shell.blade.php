@props(['title', 'subtitle'])
<x-layouts.base :title="$title">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="flex flex-col px-6 py-8 sm:px-10">
            <div class="flex items-center justify-between">
                <a href="{{ route('landing') }}"><x-logo /></a>
                <x-theme-toggle />
            </div>
            <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-10">
                <h1 class="text-2xl font-semibold tracking-tight text-ink">{{ $title }}</h1>
                <p class="mt-1.5 text-sm text-muted">{{ $subtitle }}</p>
                <div class="mt-7">{{ $slot }}</div>
            </div>
            @isset($footer)<div class="mx-auto w-full max-w-sm text-sm text-muted">{{ $footer }}</div>@endisset
        </div>

        <div class="relative hidden overflow-hidden bg-primary lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="pointer-events-none absolute inset-0 opacity-30" style="background: radial-gradient(50% 50% at 80% 20%, white, transparent 60%)"></div>
            <div class="relative flex items-center gap-2 text-primary-fg"><x-lucide-sparkles class="size-5" /><span class="font-semibold">Mendap Pro</span></div>
            <div class="relative">
                <blockquote class="text-2xl font-medium leading-snug text-primary-fg text-balance">"Dulu seminggu nak siapkan satu salespage. Sekarang sebelum lunch dah live, petang dah ada sale."</blockquote>
                <p class="mt-4 text-sm text-primary-fg/80">— Faizal, usahawan skincare</p>
                <ul class="mt-8 space-y-2.5">
                    @foreach (['AI salespage dalam BM', 'Gateway & kurier Malaysia', 'Recovery WhatsApp', 'Tiada yuran transaksi'] as $f)
                        <li class="flex items-center gap-2 text-sm text-primary-fg/90"><x-lucide-check class="size-4" /> {{ $f }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="relative text-xs text-primary-fg/70">© {{ date('Y') }} Mendap · ADFusion Marketing</div>
        </div>
    </div>
</x-layouts.base>
