<x-auth-shell title="Selamat kembali" subtitle="Log masuk untuk urus kedai anda.">
    <form method="POST" action="{{ route('login') }}" class="space-y-4">@csrf
        @if ($errors->any())
            <p class="rounded-[var(--radius-md)] border border-danger/30 bg-danger-soft px-3 py-2 text-sm text-danger">{{ $errors->first() }}</p>
        @endif
        <x-ui.field label="Emel"><x-ui.input name="email" type="email" value="{{ old('email') }}" placeholder="anda@email.com" autocomplete="email" required /></x-ui.field>
        <x-ui.field label="Kata laluan" hint="Lupa kata laluan?"><x-ui.input name="password" type="password" placeholder="••••••••" autocomplete="current-password" required /></x-ui.field>
        <x-ui.button type="submit" size="lg" class="w-full">Log masuk</x-ui.button>
    </form>
    <x-slot:footer>
        <p class="text-center">Belum ada akaun? <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">Langgan sekarang</a></p>
    </x-slot:footer>
</x-auth-shell>
