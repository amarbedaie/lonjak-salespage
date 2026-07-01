<x-layouts.app title="Penghantaran">
    @php $states = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Sarawak', 'Sabah', 'Labuan']; @endphp
    <div class="space-y-6">
        <x-ui.page-header title="Penghantaran" description="Sambung EasyParcel untuk jana AWB, tempah kurier & cetak label terus dari order." />

        @if (session('ok'))
            <div class="flex items-center gap-2.5 rounded-[var(--radius-md)] border border-success/30 bg-success-soft/50 px-4 py-3 text-sm text-success"><x-lucide-circle-check class="size-5 shrink-0" /> {{ session('ok') }}</div>
        @endif

        <form method="POST" action="{{ route('shipping.update') }}" class="space-y-6">@csrf @method('PUT')
            <x-ui.card>
                <x-ui.card-header title="EasyParcel">
                    <x-slot:action>
                        @if ($user->easyparcel_api_key)
                            <x-ui.badge tone="success">Disambung</x-ui.badge>
                        @else
                            <x-ui.badge tone="muted">Belum sambung</x-ui.badge>
                        @endif
                    </x-slot:action>
                </x-ui.card-header>
                <x-ui.card-body class="space-y-5">
                    <div class="rounded-[var(--radius-md)] bg-info-soft/40 px-4 py-3 text-sm text-ink-soft">
                        📦 Agregator — satu akaun, banyak kurier (Pos Laju, J&T, DHL, Skynet…). Dapatkan <strong class="text-ink">API key</strong> di
                        <a href="https://www.easyparcel.com/my/en/" target="_blank" class="text-primary hover:underline">easyparcel.com</a> (Integration → API). Kredit ditolak dari akaun EasyParcel <strong class="text-ink">anda sendiri</strong>.
                    </div>
                    <x-ui.field label="EasyParcel API Key" hint="{{ $user->easyparcel_api_key ? 'sudah disimpan — biar kosong untuk kekal' : 'wajib untuk aktifkan' }}">
                        <x-ui.input name="easyparcel_api_key" type="password" placeholder="{{ $user->easyparcel_api_key ? '••••••••••••  (tersimpan)' : 'EP-xxxxxxxxxx' }}" autocomplete="off" />
                    </x-ui.field>
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="easyparcel_sandbox" value="1" @checked($user->easyparcel_sandbox) class="size-5 accent-primary">
                        <span class="text-sm text-ink">Mod ujian (sandbox/demo) — matikan bila dah sedia untuk transaksi sebenar</span>
                    </label>
                </x-ui.card-body>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card-header title="Alamat pickup (penghantar)" subtitle="Alamat kurier ambil barang — wajib untuk tempah penghantaran" />
                <x-ui.card-body class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Nama penghantar"><x-ui.input name="ship_name" value="{{ $user->ship_name ?: $user->business_name }}" placeholder="cth. Kedai BeDaie" /></x-ui.field>
                    <x-ui.field label="No. telefon"><x-ui.input name="ship_phone" value="{{ $user->ship_phone ?: $user->phone }}" placeholder="0123456789" /></x-ui.field>
                    <div class="sm:col-span-2"><x-ui.field label="Alamat baris 1"><x-ui.input name="ship_addr1" value="{{ $user->ship_addr1 }}" placeholder="No, jalan" /></x-ui.field></div>
                    <div class="sm:col-span-2"><x-ui.field label="Alamat baris 2" hint="pilihan"><x-ui.input name="ship_addr2" value="{{ $user->ship_addr2 }}" placeholder="taman / kawasan" /></x-ui.field></div>
                    <x-ui.field label="Bandar"><x-ui.input name="ship_city" value="{{ $user->ship_city }}" placeholder="cth. Kajang" /></x-ui.field>
                    <x-ui.field label="Poskod"><x-ui.input name="ship_postcode" value="{{ $user->ship_postcode }}" placeholder="43000" /></x-ui.field>
                    <x-ui.field label="Negeri">
                        <x-ui.select name="ship_state">
                            <option value="" disabled @selected(! $user->ship_state)>Pilih negeri</option>
                            @foreach ($states as $s)<option @selected($user->ship_state === $s)>{{ $s }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <div class="sm:col-span-2"><x-ui.button type="submit">Simpan tetapan penghantaran</x-ui.button></div>
                </x-ui.card-body>
            </x-ui.card>
        </form>

        <x-ui.card>
            <x-ui.card-header title="Kurier lain" subtitle="Integrasi terus (tanpa EasyParcel) — akan datang" />
            <x-ui.card-body class="divide-y divide-border p-0">
                @foreach ([['JT', 'J&T Express'], ['PL', 'Pos Laju'], ['NV', 'NinjaVan']] as [$logo, $name])
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-border bg-bg text-sm font-bold tracking-tight text-ink">{{ $logo }}</div>
                        <div class="min-w-0 flex-1"><h3 class="font-medium text-ink">{{ $name }}</h3><p class="mt-0.5 text-sm text-muted">Boleh guna melalui EasyParcel sekarang</p></div>
                        <x-ui.badge tone="muted">Segera</x-ui.badge>
                    </div>
                @endforeach
            </x-ui.card-body>
        </x-ui.card>
    </div>
</x-layouts.app>
