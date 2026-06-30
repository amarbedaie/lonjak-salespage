<x-layouts.app title="Bayaran">
    <div class="space-y-6">
        <x-ui.page-header title="Bayaran" description="Sambung BayarCash anda sendiri — duit jualan masuk terus ke akaun bank anda." />

        @if (session('ok'))<x-ui.card class="bg-success-soft/40"><x-ui.card-body class="text-sm text-success">✅ {{ session('ok') }}</x-ui.card-body></x-ui.card>@endif
        @error('bayarcash')<x-ui.card class="bg-danger-soft/40"><x-ui.card-body class="text-sm text-danger">{{ $message }}</x-ui.card-body></x-ui.card>@enderror

        <x-ui.card class="{{ $user->hasBayarcash() ? 'bg-success-soft/30' : 'bg-info-soft/40' }}">
            <x-ui.card-body class="flex items-center gap-3 text-sm text-ink-soft">
                @if ($user->hasBayarcash())
                    <x-lucide-check-circle class="size-5 shrink-0 text-success" />
                    <span><strong class="text-ink">Bayaran online aktif</strong> ({{ $user->bayarcash_sandbox ? 'mod sandbox' : 'mod LIVE' }}). Pelanggan boleh bayar FPX/DuitNow di salespage anda.</span>
                @else
                    <x-lucide-info class="size-5 shrink-0 text-info" />
                    <span><strong class="text-ink">COD / manual aktif secara lalai.</strong> Sambung BayarCash di bawah untuk terima bayaran online automatik.</span>
                @endif
            </x-ui.card-body>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card-header title="BayarCash (FPX / DuitNow)" subtitle="Kunci ini milik anda — dapatkan di console.bayarcash.com" />
            <x-ui.card-body>
                <form method="POST" action="{{ route('payments.update') }}" class="max-w-xl space-y-5">@csrf
                    <x-ui.field label="Personal Access Token (PAT)" hint="{{ $user->bayarcash_pat ? 'tersimpan — biar kosong untuk kekalkan' : 'Settings → API di console BayarCash' }}">
                        <x-ui.textarea name="bayarcash_pat" rows="2" placeholder="{{ $user->bayarcash_pat ? '•••••••••• (tersimpan)' : 'eyJ0eXAiOiJKV1Qi...' }}" />
                    </x-ui.field>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Portal Key">
                            <x-ui.input name="bayarcash_portal_key" value="{{ $user->bayarcash_portal_key }}" placeholder="cth. 7f139a12..." />
                        </x-ui.field>
                        <x-ui.field label="API Secret Key" hint="{{ $user->bayarcash_api_secret ? 'tersimpan' : '' }}">
                            <x-ui.input name="bayarcash_api_secret" placeholder="{{ $user->bayarcash_api_secret ? '•••••• (tersimpan)' : 'API Secret' }}" />
                        </x-ui.field>
                    </div>

                    <label class="flex items-center justify-between gap-4 rounded-[var(--radius-md)] border border-border px-4 py-3">
                        <span><span class="block text-sm font-medium text-ink">Mod sandbox</span><span class="text-xs text-muted">Hidupkan untuk uji tanpa duit betul. Matikan untuk LIVE.</span></span>
                        <input type="hidden" name="bayarcash_sandbox" value="0">
                        <input type="checkbox" name="bayarcash_sandbox" value="1" @checked($user->bayarcash_sandbox) class="size-5 accent-[oklch(0.55_0.224_350)]">
                    </label>
                    <label class="flex items-center justify-between gap-4 rounded-[var(--radius-md)] border border-primary/30 bg-primary-soft/40 px-4 py-3">
                        <span><span class="block text-sm font-medium text-ink">Aktifkan bayaran online</span><span class="text-xs text-muted">Salespage dengan gateway "BayarCash" akan terima bayaran FPX.</span></span>
                        <input type="hidden" name="bayarcash_active" value="0">
                        <input type="checkbox" name="bayarcash_active" value="1" @checked($user->bayarcash_active) class="size-5 accent-[oklch(0.55_0.224_350)]">
                    </label>

                    <x-ui.button type="submit">Simpan & sahkan</x-ui.button>
                </form>
            </x-ui.card-body>
        </x-ui.card>

        <p class="px-1 text-xs text-muted">Mendap tidak kenakan yuran transaksi. Caj pemprosesan BayarCash dikenakan oleh BayarCash. Settlement terus ke akaun bank anda.</p>
    </div>
</x-layouts.app>
