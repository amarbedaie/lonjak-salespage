<x-layouts.app :title="$product->exists ? 'Edit produk' : 'Produk baru'">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border text-ink-soft hover:bg-muted-surface"><x-lucide-arrow-left class="size-4" /></a>
            <x-ui.page-header :title="$product->exists ? 'Edit produk' : 'Produk baru'" description="Simpan sekali, guna berulang untuk salespage & order." />
        </div>

        @if ($errors->any())<x-ui.card class="bg-danger-soft/40"><x-ui.card-body class="text-sm text-danger">{{ $errors->first() }}</x-ui.card-body></x-ui.card>@endif

        <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if ($product->exists) @method('PUT') @endif

            <x-ui.card>
                <x-ui.card-header title="Maklumat asas" />
                <x-ui.card-body class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Nama produk" class="sm:col-span-2"><x-ui.input name="name" value="{{ old('name', $product->name) }}" placeholder="cth. Buku Panduan Qadha Solat" required /></x-ui.field>
                        <x-ui.field label="Harga jualan (RM)"><x-ui.input name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" placeholder="39" required /></x-ui.field>
                        <x-ui.field label="Harga coret (RM)" hint="pilihan"><x-ui.input name="compare_price" type="number" step="0.01" value="{{ old('compare_price', $product->compare_price) }}" placeholder="59" /></x-ui.field>
                        <x-ui.field label="Kos (RM)" hint="pilihan"><x-ui.input name="cost" type="number" step="0.01" value="{{ old('cost', $product->cost) }}" placeholder="12" /></x-ui.field>
                        <x-ui.field label="Stok" hint="pilihan"><x-ui.input name="stock" type="number" value="{{ old('stock', $product->stock) }}" placeholder="100" /></x-ui.field>
                        <x-ui.field label="Kategori"><x-ui.input name="category" value="{{ old('category', $product->category) }}" placeholder="cth. Buku / Ilmu" /></x-ui.field>
                        <x-ui.field label="SKU" hint="pilihan"><x-ui.input name="sku" value="{{ old('sku', $product->sku) }}" placeholder="BK-001" /></x-ui.field>
                    </div>
                </x-ui.card-body>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card-header title="Untuk AI salespage" subtitle="Diisi auto ke builder bila tekan ‘Jana Salespage’." />
                <x-ui.card-body class="space-y-4">
                    <x-ui.field label="Target audiens"><x-ui.input name="audience" value="{{ old('audience', $product->audience) }}" placeholder="cth. Muslim 25–45 yang nak belajar qadha solat" /></x-ui.field>
                    <x-ui.field label="Masalah yang diselesaikan"><x-ui.textarea name="problem" rows="2" placeholder="cth. Keliru cara kira & niat solat yang tertinggal">{{ old('problem', $product->problem) }}</x-ui.textarea></x-ui.field>
                    <x-ui.field label="Kelebihan / selling point"><x-ui.textarea name="benefits" rows="2" placeholder="cth. Panduan langkah demi langkah, mudah faham, rujukan ustaz">{{ old('benefits', $product->benefits) }}</x-ui.textarea></x-ui.field>
                    <x-ui.field label="Penerangan penuh" hint="pilihan"><x-ui.textarea name="description" rows="3" placeholder="Penerangan produk...">{{ old('description', $product->description) }}</x-ui.textarea></x-ui.field>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Tona">
                            <x-ui.select name="tone">
                                @foreach (['santai' => 'Santai', 'profesional' => 'Profesional', 'mesra' => 'Mesra', 'formal' => 'Formal'] as $v => $l)
                                    <option value="{{ $v }}" @selected(old('tone', $product->tone) === $v)>{{ $l }}</option>
                                @endforeach
                            </x-ui.select>
                        </x-ui.field>
                        <x-ui.field label="Pautan video" hint="YouTube / Vimeo (pilihan)"><x-ui.input name="video_url" type="url" value="{{ old('video_url', $product->video_url) }}" placeholder="https://youtu.be/..." /></x-ui.field>
                    </div>
                </x-ui.card-body>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card-header title="Gambar produk" subtitle="Simpan gambar/cover. Yang pertama jadi thumbnail." />
                <x-ui.card-body class="space-y-4">
                    @if ($product->exists && $product->images)
                        <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                            @foreach ($product->images as $img)
                                <label class="group relative aspect-square cursor-pointer overflow-hidden rounded-[var(--radius-md)] border border-border">
                                    <img src="{{ asset('storage/'.$img) }}" class="size-full object-cover" alt="">
                                    <input type="checkbox" name="keep_images[]" value="{{ $img }}" checked class="absolute right-1.5 top-1.5 size-4 accent-[oklch(0.55_0.224_350)]">
                                    <span class="absolute inset-x-0 bottom-0 bg-black/50 py-0.5 text-center text-[10px] text-white">simpan</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-muted">Nyahtanda untuk buang gambar.</p>
                    @endif
                    <div x-data class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-[var(--radius-lg)] border-2 border-dashed border-border bg-muted-surface/40 px-6 py-8 text-center hover:bg-muted-surface" @click="$refs.fi.click()" role="button" tabindex="0" @keydown.enter="$refs.fi.click()">
                        <input type="file" name="images[]" x-ref="fi" accept="image/*" multiple class="hidden">
                        <x-lucide-image-plus class="size-7 text-muted" />
                        <span class="text-sm font-medium text-ink">Klik untuk pilih gambar</span>
                        <span class="text-xs text-muted">JPG/PNG, sehingga 5MB setiap satu. Boleh pilih banyak.</span>
                    </div>
                </x-ui.card-body>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="rounded-[var(--radius-md)] border border-border px-4 py-2.5 text-sm font-medium text-ink-soft hover:bg-muted-surface">Batal</a>
                <x-ui.button type="submit"><x-lucide-check class="size-4" /> {{ $product->exists ? 'Simpan perubahan' : 'Simpan produk' }}</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
