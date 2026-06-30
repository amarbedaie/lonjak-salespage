<x-layouts.app title="Affiliate">
    <div class="space-y-6" x-data="{ copied: false }">
        <x-ui.page-header title="Affiliate" description="Kongsi Mendap, dapat komisen berulang 20% setiap bulan untuk setiap rujukan aktif." />

        <div class="grid gap-6 lg:grid-cols-3">
            <x-ui.card class="bg-gradient-to-br from-primary to-primary-active text-primary-fg">
                <x-ui.card-body>
                    <p class="text-sm opacity-80">Baki boleh dikeluarkan</p>
                    <p class="mt-1 text-3xl font-semibold tnum">RM0.00</p>
                    <div class="mt-4 flex items-center justify-between border-t border-white/20 pt-3 text-sm"><span class="opacity-80">Komisen berulang</span><span class="font-medium">20% / bulan</span></div>
                </x-ui.card-body>
            </x-ui.card>
            <div class="grid grid-cols-2 gap-3 lg:col-span-2">
                <x-ui.metric label="Klik pautan" value="0" />
                <x-ui.metric label="Pendaftaran" value="0" />
                <x-ui.metric label="Rujukan aktif" value="0" />
                <x-ui.metric label="Jumlah dibayar" value="RM0.00" />
            </div>
        </div>

        <x-ui.card>
            <x-ui.card-header title="Pautan rujukan anda" subtitle="Kongsi & dapat komisen setiap langganan baru" />
            <x-ui.card-body>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <div class="flex h-10 flex-1 items-center rounded-[var(--radius-md)] border border-border bg-muted-surface px-3 font-mono text-sm text-ink-soft"><span class="truncate">{{ $link }}</span></div>
                    <x-ui.button x-on:click="navigator.clipboard.writeText('{{ $link }}'); copied=true; setTimeout(()=>copied=false,1800)" ::variant="copied ? 'secondary' : 'primary'" class="shrink-0">
                        <span x-show="!copied" class="inline-flex items-center gap-2"><x-lucide-copy class="size-4" /> Salin pautan</span>
                        <span x-show="copied" x-cloak class="inline-flex items-center gap-2"><x-lucide-check class="size-4" /> Disalin!</span>
                    </x-ui.button>
                </div>
            </x-ui.card-body>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card-header title="Macam mana ia berfungsi" />
            <x-ui.card-body>
                <ol class="grid gap-4 sm:grid-cols-3">
                    @foreach ([['mouse-pointer-click', 'Kongsi pautan', 'Hantar pautan rujukan anda kepada usahawan lain.'], ['users', 'Mereka langgan', 'Bila mereka langgan Mendap guna pautan anda.'], ['repeat', 'Dapat 20% berulang', 'Anda dapat 20% komisen setiap bulan selagi mereka aktif.']] as $i => [$ic, $t, $d])
                        <li class="rounded-[var(--radius-lg)] border border-border bg-surface p-5">
                            <span class="flex size-9 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-fg">{{ $i + 1 }}</span>
                            <x-dynamic-component :component="'lucide-'.$ic" class="mt-3 size-5 text-primary" />
                            <h3 class="mt-2 font-semibold text-ink">{{ $t }}</h3><p class="mt-1 text-sm text-ink-soft">{{ $d }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-ui.card-body>
        </x-ui.card>
    </div>
</x-layouts.app>
