<div class="space-y-6" x-data="{
    genPct: 0, genStep: 0,
    genSteps: ['Menganalisa produk & audiens', 'Membina hook & headline', 'Menyusun offer & bukti sosial', 'Menyiapkan jaminan & urgency', 'Menyiapkan FAQ & CTA'],
    genTimer: null,
    genBegin() {
        this.genPct = 0; this.genStep = 0;
        clearInterval(this.genTimer);
        this.genTimer = setInterval(() => {
            if (this.genPct < 93) this.genPct += Math.max(1, Math.round((93 - this.genPct) / 26));
            this.genStep = Math.min(this.genSteps.length - 1, Math.floor(this.genPct / (94 / this.genSteps.length)));
        }, 240);
    },
}">
    <div class="flex items-center gap-3">
        <a href="{{ route('salespages.index') }}" class="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-muted-surface">
            <x-lucide-arrow-left class="size-4.5" />
        </a>
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-ink">Salespage baru</h1>
            <p class="text-sm text-muted">Brief produk anda. Salespage keluar.</p>
        </div>
    </div>

    {{-- Generating overlay — step-by-step progress + percent --}}
    <div wire:loading.flex wire:target="generate" class="fixed inset-0 z-[1400] flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <x-ui.card class="mx-auto w-full max-w-md">
            <x-ui.card-body class="py-10 text-center">
                <div class="relative mx-auto flex size-20 items-center justify-center">
                    <svg class="size-20 -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-primary/15" stroke-width="3" />
                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-primary" stroke-width="3" stroke-linecap="round"
                                stroke-dasharray="100.5" :stroke-dashoffset="100.5 * (1 - genPct / 100)" style="transition: stroke-dashoffset .25s ease" />
                    </svg>
                    <span class="absolute text-base font-bold text-primary tnum" x-text="genPct + '%'"></span>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-ink">AI sedang menulis salespage anda…</h2>
                <p class="mt-1 text-sm text-muted">Biasanya ambil 10–20 saat.</p>
                <ul class="mx-auto mt-6 max-w-sm space-y-2.5 text-left text-sm">
                    <template x-for="(s, i) in genSteps" :key="i">
                        <li class="flex items-center gap-3 transition-colors" :class="i < genStep ? 'text-success font-medium' : (i === genStep ? 'text-ink' : 'text-muted/50')">
                            <span class="flex size-5 shrink-0 items-center justify-center">
                                <svg x-show="i < genStep" class="size-4 text-success" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.42 0L4.3 10.7a1 1 0 011.4-1.4l2.79 2.79 6.8-6.79a1 1 0 011.41 0z" clip-rule="evenodd" /></svg>
                                <span x-show="i === genStep" class="size-3.5 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
                                <span x-show="i > genStep" class="size-1.5 rounded-full bg-current opacity-40"></span>
                            </span>
                            <span x-text="s + '…'"></span>
                        </li>
                    </template>
                </ul>
            </x-ui.card-body>
        </x-ui.card>
    </div>

    @if ($stage === 'brief')
        <div class="grid gap-6 lg:grid-cols-5">
            <x-ui.card class="lg:col-span-3">
                <x-ui.card-header title="Brief produk" subtitle="Lagi lengkap brief, lagi tajam copy yang AI jana." />
                <x-ui.card-body class="space-y-5">
                    <x-ui.field label="Nama produk">
                        <x-ui.input wire:model="name" placeholder="cth. Serum Glow Booster" />
                        @error('name')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    </x-ui.field>
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field label="Harga jualan (RM)">
                            <x-ui.input wire:model="price" type="number" placeholder="89" />
                            @error('price')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                        </x-ui.field>
                        <x-ui.field label="Harga coret (RM)" hint="pilihan">
                            <x-ui.input wire:model="comparePrice" type="number" placeholder="159" />
                        </x-ui.field>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field label="Kategori">
                            <x-ui.select wire:model="category">
                                @foreach (['Kecantikan', 'Kesihatan', 'Fesyen', 'Makanan', 'Gadget', 'Rumah', 'Lain-lain'] as $c)<option>{{ $c }}</option>@endforeach
                            </x-ui.select>
                        </x-ui.field>
                        <x-ui.field label="Tona penulisan">
                            <x-ui.select wire:model="tone">
                                <option value="santai">Santai & mesra</option>
                                <option value="profesional">Profesional</option>
                                <option value="agresif">Agresif (hard-sell)</option>
                            </x-ui.select>
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Untuk siapa? (target audiens)">
                        <x-ui.input wire:model="audience" placeholder="cth. wanita 25–40 yang ada masalah kulit kusam" />
                        @error('audience')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    </x-ui.field>
                    <x-ui.field label="Masalah utama yang diselesaikan">
                        <x-ui.textarea wire:model="problem" placeholder="cth. kulit kusam, jeragat & tak sekata walaupun dah cuba macam-macam" />
                    </x-ui.field>
                    <x-ui.field label="Kelebihan / selling point (pisah dengan koma)">
                        <x-ui.textarea wire:model="benefits" placeholder="cth. nampak hasil 7 hari, bahan semula jadi, sesuai semua kulit" />
                    </x-ui.field>

                    <x-ui.field label="Gambar produk" hint="pilihan — akan dipapar pada salespage">
                        <div class="relative flex items-center gap-3 rounded-[var(--radius-md)] border border-dashed border-border bg-muted-surface/40 px-4 py-3 text-sm text-ink-soft hover:bg-muted-surface">
                            <input type="file" wire:model="newImages" multiple accept="image/*" class="absolute inset-0 z-10 cursor-pointer opacity-0" aria-label="Muat naik gambar">
                            <x-lucide-image-plus class="size-5 shrink-0 text-muted" />
                            <span wire:loading.remove wire:target="newImages">Klik untuk muat naik gambar (boleh banyak)</span>
                            <span wire:loading wire:target="newImages" class="text-primary">Memuat naik…</span>
                        </div>
                        @error('newImages.*')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        @if ($images)
                            <div class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-5">
                                @foreach ($images as $i => $img)
                                    <div class="group relative aspect-square overflow-hidden rounded-[var(--radius-md)] border border-border">
                                        <img src="{{ asset('storage/'.$img) }}" class="size-full object-cover" alt="">
                                        <button type="button" wire:click="removeImage({{ $i }})" class="absolute right-1 top-1 flex size-5 items-center justify-center rounded-full bg-black/60 text-xs text-white opacity-0 transition group-hover:opacity-100">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-ui.field>
                    <x-ui.field label="Video produk" hint="link YouTube / Vimeo (pilihan)">
                        <x-ui.input wire:model="videoUrl" placeholder="https://youtu.be/..." />
                    </x-ui.field>
                </x-ui.card-body>
            </x-ui.card>

            <div class="space-y-4 lg:col-span-2">
                <x-ui.card class="bg-gradient-to-b from-primary-soft/60 to-surface">
                    <x-ui.card-body>
                        <div class="flex items-center gap-2 text-primary">
                            <x-lucide-sparkles class="size-5" /><span class="font-semibold">Jana dengan AI</span>
                        </div>
                        <p class="mt-2 text-sm text-ink-soft">AI Mendap guna framework direct-response terbukti — hook, masalah, offer stack, bukti sosial, jaminan, urgency & FAQ — diadaptasi untuk pasaran Malaysia.</p>
                        <div class="mt-4 flex items-center justify-between rounded-[var(--radius-md)] border border-border bg-bg px-3 py-2 text-sm">
                            <span class="text-muted">Kredit AI</span>
                            <span class="font-medium text-ink">{{ auth()->user()->ai_credits }} / 3 tinggal</span>
                        </div>
                        <x-ui.button wire:click="generate" x-on:click="genBegin()" size="lg" class="mt-4 w-full">
                            <x-lucide-sparkles class="size-4" /> Jana salespage
                        </x-ui.button>
                    </x-ui.card-body>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card-body>
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Struktur yang akan dijana</p>
                        <ul class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-sm text-ink-soft">
                            @foreach (\App\Services\SalespageGenerator::BLOCK_LABELS as $l)
                                <li class="flex items-center gap-1.5"><x-lucide-check class="size-3.5 text-success" /> {{ $l }}</li>
                            @endforeach
                        </ul>
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-5">
            <div class="space-y-4 lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-header title="Salespage anda dah siap 🎉" subtitle="Semak preview, kemudian simpan.">
                        <x-slot:action>
                            <x-ui.badge :tone="$source === 'mock' ? 'muted' : 'primary'">{{ $source === 'mock' ? 'Contoh' : '✦ Dijana AI' }}</x-ui.badge>
                        </x-slot:action>
                    </x-ui.card-header>
                    <x-ui.card-body class="space-y-1.5">
                        @foreach ($page['blocks'] ?? [] as $b)
                            <div class="flex items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2 text-sm">
                                <span class="flex-1 font-medium text-ink">{{ $b['label'] ?? $b['type'] }}</span>
                                <x-lucide-check class="size-4 text-success" />
                            </div>
                        @endforeach
                    </x-ui.card-body>
                </x-ui.card>
                <div class="flex flex-col gap-2">
                    <x-ui.button wire:click="publish" size="lg">Simpan salespage</x-ui.button>
                    <div class="grid grid-cols-2 gap-2">
                        <x-ui.button wire:click="back" variant="outline"><x-lucide-rotate-cw class="size-4" /> Edit brief</x-ui.button>
                        <x-ui.button wire:click="generate" x-on:click="genBegin()" variant="outline"><x-lucide-sparkles class="size-4" /> Jana semula</x-ui.button>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-3">
                <x-ui.card class="overflow-hidden">
                    <x-ui.card-header title="Pratonton langsung">
                        <x-slot:action><x-ui.badge tone="muted"><x-lucide-smartphone class="size-3.5" /> Mobile</x-ui.badge></x-slot:action>
                    </x-ui.card-header>
                    <x-ui.card-body class="bg-muted-surface/60 p-5">
                        <div class="mx-auto max-w-[380px] overflow-hidden rounded-[24px] border-[6px] border-ink/90 bg-bg shadow-2xl">
                            <div class="max-h-[640px] overflow-y-auto scroll-thin">
                                @include('partials.salespage', ['page' => array_merge($page, ['images' => collect($images)->map(fn ($p) => asset('storage/'.$p))->all(), 'video' => $videoUrl])])
                            </div>
                        </div>
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        </div>
    @endif
</div>
