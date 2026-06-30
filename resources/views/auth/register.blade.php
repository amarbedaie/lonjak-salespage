<x-auth-shell title="Mula dengan Mendap" subtitle="Bina salespage pertama anda hari ini — Plan Pro RM89/bulan.">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">@csrf
        @if ($errors->any())
            <p class="rounded-[var(--radius-md)] border border-danger/30 bg-danger-soft px-3 py-2 text-sm text-danger">{{ $errors->first() }}</p>
        @endif
        <x-ui.field label="Nama perniagaan"><x-ui.input name="business_name" value="{{ old('business_name') }}" placeholder="cth. Glow Empire" required /></x-ui.field>
        <x-ui.field label="Emel"><x-ui.input name="email" type="email" value="{{ old('email') }}" placeholder="anda@email.com" autocomplete="email" required /></x-ui.field>
        <x-ui.field label="No. telefon"><x-ui.input name="phone" type="tel" value="{{ old('phone') }}" placeholder="012-3456789" /></x-ui.field>
        <x-ui.field label="Kata laluan"><x-ui.input name="password" type="password" placeholder="Minimum 8 aksara" autocomplete="new-password" required /></x-ui.field>
        <x-ui.button type="submit" size="lg" class="w-full">Cipta akaun</x-ui.button>
        <p class="flex items-center justify-center gap-1.5 text-xs text-muted"><x-lucide-check class="size-3.5 text-success" /> Batal bila-bila · Tiada yuran transaksi</p>
    </form>
    <x-slot:footer>
        <p class="text-center">Dah ada akaun? <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Log masuk</a></p>
    </x-slot:footer>
</x-auth-shell>
