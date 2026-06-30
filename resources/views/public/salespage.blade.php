<x-layouts.base :title="$salespage->title">
    @php $page = array_merge($salespage->blocks ?? ['blocks' => []], ['images' => $salespage->imageUrls(), 'video' => $salespage->video_url]); $price = (float) $salespage->price;
    $states = ['Selangor', 'Kuala Lumpur', 'Johor', 'Pulau Pinang', 'Perak', 'Kedah', 'Kelantan', 'Terengganu', 'Pahang', 'Melaka', 'Negeri Sembilan', 'Perlis', 'Sabah', 'Sarawak', 'Putrajaya', 'Labuan']; @endphp
    <div class="min-h-screen bg-muted-surface/40 py-6">
        <div class="mx-auto max-w-md overflow-hidden rounded-[var(--radius-xl)] border border-border bg-bg elev-2">
            @include('partials.salespage', ['page' => $page])

            <section id="checkout" class="border-t border-border bg-surface px-6 py-8">
                <h2 class="mb-4 text-center text-lg font-bold text-ink">Lengkapkan tempahan anda</h2>

                @if (session('ordered'))
                    <div class="rounded-[var(--radius-lg)] border border-success/30 bg-success-soft/50 p-8 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-success text-white"><x-lucide-check class="size-6" /></div>
                        <h3 class="mt-4 text-lg font-bold text-ink">Order diterima! 🎉</h3>
                        <p class="mt-1 text-sm text-ink-soft">Terima kasih. Kami akan WhatsApp anda untuk pengesahan & pembayaran.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('salespage.order', $salespage->slug) }}" class="space-y-4" x-data="{ qty: 1 }">@csrf
                        <div class="flex items-center justify-between rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3">
                            <div><p class="text-sm font-medium text-ink">{{ $salespage->product_name ?: $salespage->title }}</p><p class="text-xs text-muted">RM{{ number_format($price, 2) }} / unit</p></div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="qty = Math.max(1, qty-1)" class="size-8 rounded-md border border-border text-ink-soft hover:bg-muted-surface">−</button>
                                <span class="w-6 text-center text-sm font-semibold text-ink tnum" x-text="qty"></span>
                                <button type="button" @click="qty++" class="size-8 rounded-md border border-border text-ink-soft hover:bg-muted-surface">+</button>
                                <input type="hidden" name="qty" :value="qty">
                            </div>
                        </div>
                        @if ($errors->any())<p class="rounded-[var(--radius-md)] border border-danger/30 bg-danger-soft px-3 py-2 text-sm text-danger">{{ $errors->first() }}</p>@endif
                        <x-ui.field label="Nama penuh"><x-ui.input name="customer" placeholder="Nama anda" required /></x-ui.field>
                        <x-ui.field label="No. telefon (WhatsApp)"><x-ui.input name="phone" type="tel" placeholder="012-3456789" required /></x-ui.field>
                        <x-ui.field label="Emel" hint="pilihan — untuk resit"><x-ui.input name="email" type="email" placeholder="anda@email.com" /></x-ui.field>
                        <x-ui.field label="Alamat penghantaran"><x-ui.input name="address" placeholder="No, jalan, poskod, bandar" required /></x-ui.field>
                        <x-ui.field label="Negeri">
                            <x-ui.select name="state" required>
                                <option value="" disabled selected>Pilih negeri</option>
                                @foreach ($states as $s)<option>{{ $s }}</option>@endforeach
                            </x-ui.select>
                        </x-ui.field>
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-[var(--radius-md)] bg-primary px-6 py-3.5 text-base font-semibold text-primary-fg shadow-lg shadow-primary/20">
                            Sahkan Order — <span x-text="'RM' + ({{ $price }} * qty).toFixed(2)"></span>
                        </button>
                        <p class="flex items-center justify-center gap-1.5 text-xs text-muted"><x-lucide-shield-check class="size-3.5 text-success" /> Bayaran selamat · COD tersedia</p>
                    </form>
                @endif
            </section>
        </div>
        <div class="mx-auto mt-4 flex max-w-md items-center justify-center gap-1.5 text-xs text-muted">Dikuasakan oleh <x-logo class="scale-90" /></div>
    </div>
</x-layouts.base>
