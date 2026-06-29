<x-layouts.base title="Akaun digantung">
    <div class="flex min-h-screen flex-col items-center justify-center bg-bg px-6 text-center">
        <x-logo />
        <div class="mt-8 flex size-14 items-center justify-center rounded-full bg-danger-soft text-danger"><x-lucide-shield-alert class="size-7" /></div>
        <h1 class="mt-5 text-2xl font-semibold tracking-tight text-ink">Akaun anda digantung</h1>
        <p class="mt-2 max-w-md text-sm text-muted">Akses ke dashboard telah dihentikan sementara oleh pentadbir platform. Sila hubungi sokongan Lonjak untuk maklumat lanjut.</p>
        <div class="mt-6 flex items-center gap-3">
            <x-ui.button href="mailto:sokongan@lonjak.my" variant="outline">Hubungi sokongan</x-ui.button>
            <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="ghost">Log keluar</x-ui.button></form>
        </div>
    </div>
</x-layouts.base>
