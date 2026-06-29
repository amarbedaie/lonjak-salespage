<x-layouts.app title="Tetapan">
    <div class="space-y-6">
        <x-ui.page-header title="Tetapan" description="Urus profil, langganan & keutamaan." />

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.card>
                <x-ui.card-header title="Profil perniagaan" />
                <x-ui.card-body>
                    @if (session('ok'))<p class="mb-3 rounded-[var(--radius-md)] border border-success/30 bg-success-soft px-3 py-2 text-sm text-success">{{ session('ok') }}</p>@endif
                    <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">@csrf
                        <div class="flex items-center gap-4">
                            <span class="flex size-14 items-center justify-center rounded-full bg-primary text-xl font-semibold text-primary-fg">{{ strtoupper(substr($user->business_name ?: $user->email, 0, 1)) }}</span>
                        </div>
                        <x-ui.field label="Nama perniagaan"><x-ui.input name="business_name" value="{{ $user->business_name }}" /></x-ui.field>
                        <x-ui.field label="Emel"><x-ui.input type="email" value="{{ $user->email }}" disabled /></x-ui.field>
                        <x-ui.field label="No. telefon"><x-ui.input name="phone" value="{{ $user->phone }}" /></x-ui.field>
                        <x-ui.button type="submit">Simpan perubahan</x-ui.button>
                    </form>
                </x-ui.card-body>
            </x-ui.card>

            <div class="space-y-6">
                <x-ui.card>
                    <x-ui.card-header title="Langganan" subtitle="Plan & kredit AI" />
                    <x-ui.card-body class="space-y-4">
                        <div class="flex items-center justify-between rounded-[var(--radius-md)] border border-primary/30 bg-primary-soft px-4 py-3">
                            <div><p class="font-semibold capitalize text-ink">Plan {{ $user->plan }}</p><p class="text-xs text-muted">Diuruskan oleh Lonjak</p></div>
                            <p class="text-right"><span class="text-lg font-semibold text-ink tnum">RM89</span><span class="text-xs text-muted">/bulan</span></p>
                        </div>
                        <div class="flex items-center justify-between text-sm"><span class="text-ink-soft">Kredit AI</span><span class="font-medium text-ink">{{ $user->ai_credits }} / 3 generasi</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted-surface"><div class="h-full rounded-full bg-primary" style="width: {{ $user->ai_credits / 3 * 100 }}%"></div></div>
                    </x-ui.card-body>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card-header title="Rupa & tema" />
                    <x-ui.card-body>
                        <div class="grid grid-cols-2 gap-3" x-data="{ dark: document.documentElement.classList.contains('dark') }">
                            <button @click="dark=false; document.documentElement.classList.remove('dark'); localStorage.setItem('lonjak-theme','light')" :class="!dark ? 'border-primary bg-primary-soft text-primary' : 'border-border text-ink-soft hover:bg-muted-surface'" class="relative flex items-center gap-2 rounded-[var(--radius-md)] border px-4 py-3 text-sm font-medium transition-colors"><x-lucide-sun class="size-4" /> Terang</button>
                            <button @click="dark=true; document.documentElement.classList.add('dark'); localStorage.setItem('lonjak-theme','dark')" :class="dark ? 'border-primary bg-primary-soft text-primary' : 'border-border text-ink-soft hover:bg-muted-surface'" class="relative flex items-center gap-2 rounded-[var(--radius-md)] border px-4 py-3 text-sm font-medium transition-colors"><x-lucide-moon class="size-4" /> Gelap</button>
                        </div>
                    </x-ui.card-body>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
