<div class="space-y-6">
    <x-ui.page-header title="Pustaka Media" description="Upload media produk anda SEKALI — selepas ini, setiap kali jana salespage, taip nama produk sahaja: mockup & video dipasang sendiri secara automatik." />

    @if (session('ok'))
        <div class="flex items-center gap-2.5 rounded-[var(--radius-md)] border border-success/30 bg-success-soft/50 px-4 py-3 text-sm text-success"><x-lucide-circle-check class="size-5 shrink-0" /> {{ session('ok') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Mockup produk --}}
        <x-ui.card>
            <x-ui.card-header title="Mockup / gambar produk" subtitle="Namakan ikut produk — sistem padankan sendiri masa jana salespage" />
            <x-ui.card-body class="space-y-4">
                <x-ui.field label="Nama produk" hint="cth. Panduan Smart Tajwid">
                    <x-ui.input wire:model="mockupTitle" placeholder="Nama produk untuk gambar-gambar ini" />
                </x-ui.field>
                <div>
                    <label for="mockup-files" class="flex cursor-pointer flex-col items-center gap-2 rounded-[var(--radius-lg)] border-2 border-dashed border-border px-4 py-6 text-center transition-colors hover:border-primary/60 hover:bg-primary/5">
                        <x-lucide-image-plus class="size-7 text-muted" />
                        <span class="text-sm font-medium text-ink">Klik untuk pilih gambar <span class="font-normal text-muted">(boleh banyak sekaligus, max 8MB/gambar)</span></span>
                        <input id="mockup-files" type="file" wire:model="newMockups" multiple accept="image/*" class="sr-only" />
                    </label>
                    <div wire:loading wire:target="newMockups" class="mt-2 flex items-center gap-2 text-xs font-medium text-primary"><span class="size-3 animate-spin rounded-full border-2 border-primary border-t-transparent"></span> Memuat naik…</div>
                    @error('newMockups.*')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                    @error('newMockups')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                    @error('mockupTitle')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                    @if (count((array) $newMockups))
                        <p class="mt-2 text-xs text-ink-soft">{{ count((array) $newMockups) }} gambar sedia untuk disimpan.</p>
                    @endif
                </div>
                <x-ui.button wire:click="saveMockups" wire:loading.attr="disabled" wire:target="saveMockups,newMockups"><x-lucide-save class="size-4" /> Simpan mockup</x-ui.button>

                @if ($mockups->isEmpty())
                    <p class="rounded-[var(--radius-md)] bg-muted-surface/60 px-4 py-3 text-sm text-muted">Belum ada mockup. @if (auth()->user()->isAdmin()) Koleksi kongsi BeDaie (59 cover) tetap aktif sebagai fallback auto-detect. @endif</p>
                @else
                    <div class="space-y-4 border-t border-border pt-4">
                        @foreach ($mockups as $title => $files)
                            <div>
                                <p class="mb-2 text-sm font-semibold text-ink">{{ \Illuminate\Support\Str::of($title)->replace('-', ' ')->title() }} <span class="font-normal text-muted">· {{ count($files) }} gambar</span></p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($files as $f)
                                        <div class="group relative">
                                            <img src="{{ asset('storage/'.$f) }}" class="size-20 rounded-[var(--radius-md)] border border-border object-cover" alt="">
                                            <button type="button" wire:click="deleteFile('{{ $f }}')" wire:confirm="Padam gambar ini?" class="absolute -right-1.5 -top-1.5 hidden size-5 items-center justify-center rounded-full bg-danger text-white shadow group-hover:flex"><x-lucide-x class="size-3" /></button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card-body>
        </x-ui.card>

        {{-- Video --}}
        <div class="space-y-6">
            <x-ui.card>
                <x-ui.card-header title="Video klip pendek" subtitle="Promo 15-90 saat — dimainkan terus dalam salespage (max 100MB)" />
                <x-ui.card-body class="space-y-4">
                    <x-ui.field label="Nama produk" hint="untuk auto-detect">
                        <x-ui.input wire:model="videoTitle" placeholder="cth. Panduan Smart Tajwid" />
                    </x-ui.field>
                    <div>
                        <label for="video-file" class="flex cursor-pointer flex-col items-center gap-2 rounded-[var(--radius-lg)] border-2 border-dashed border-border px-4 py-5 text-center transition-colors hover:border-primary/60 hover:bg-primary/5">
                            <x-lucide-clapperboard class="size-6 text-muted" />
                            <span class="text-sm font-medium text-ink">Pilih video <span class="font-normal text-muted">(mp4/mov, max 100MB)</span></span>
                            <input id="video-file" type="file" wire:model="newVideo" accept="video/mp4,video/quicktime,video/webm" class="sr-only" />
                        </label>
                        <div wire:loading wire:target="newVideo" class="mt-2 flex items-center gap-2 text-xs font-medium text-primary"><span class="size-3 animate-spin rounded-full border-2 border-primary border-t-transparent"></span> Memuat naik video…</div>
                        @error('newVideo')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                        @error('videoTitle')<p class="mt-1.5 text-xs text-danger">{{ $message }}</p>@enderror
                        @if ($newVideo)<p class="mt-2 text-xs text-ink-soft">Video sedia untuk disimpan.</p>@endif
                    </div>
                    <x-ui.button wire:click="saveVideo" wire:loading.attr="disabled" wire:target="saveVideo,newVideo"><x-lucide-save class="size-4" /> Simpan video</x-ui.button>

                    @if ($videos->isNotEmpty())
                        <ul class="space-y-2 border-t border-border pt-4">
                            @foreach ($videos as $v)
                                <li class="flex items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2 text-sm">
                                    <x-lucide-play class="size-4 shrink-0 text-primary" />
                                    <a href="{{ asset('storage/'.$v) }}" target="_blank" class="min-w-0 flex-1 truncate font-medium text-ink hover:text-primary">{{ basename($v) }}</a>
                                    <button type="button" wire:click="deleteFile('{{ $v }}')" wire:confirm="Padam video ini?" class="text-muted hover:text-danger"><x-lucide-trash-2 class="size-4" /></button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card-body>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card-header title="Video panjang (YouTube)" subtitle="Ceramah / preview panjang — embed dari YouTube, laju & percuma" />
                <x-ui.card-body class="space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.field label="Nama produk"><x-ui.input wire:model="linkTitle" placeholder="cth. Rahsia Ramadan" /></x-ui.field>
                        <x-ui.field label="Link YouTube"><x-ui.input wire:model="linkUrl" placeholder="https://youtu.be/..." /></x-ui.field>
                    </div>
                    @error('linkTitle')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    @error('linkUrl')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    <x-ui.button wire:click="addLink" variant="outline"><x-lucide-link class="size-4" /> Simpan link</x-ui.button>

                    @if (count($links))
                        <ul class="space-y-2 border-t border-border pt-4">
                            @foreach ($links as $title => $url)
                                <li class="flex items-center gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2 text-sm">
                                    <x-lucide-youtube class="size-4 shrink-0 text-danger" />
                                    <span class="min-w-0 flex-1 truncate"><span class="font-medium text-ink">{{ $title }}</span> <a href="{{ $url }}" target="_blank" class="text-xs text-muted hover:text-primary">{{ \Illuminate\Support\Str::limit($url, 36) }}</a></span>
                                    <button type="button" wire:click="deleteLink('{{ addslashes($title) }}')" wire:confirm="Padam link ini?" class="text-muted hover:text-danger"><x-lucide-trash-2 class="size-4" /></button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card-body>
            </x-ui.card>
        </div>
    </div>

    <x-ui.card class="bg-info-soft/40">
        <x-ui.card-body class="flex items-start gap-3 text-sm text-ink-soft">
            <x-lucide-sparkles class="mt-0.5 size-5 shrink-0 text-primary" />
            <div><strong class="text-ink">Semua auto selepas ini:</strong> bila anda jana salespage dan taip nama produk yang sama, sistem akan pasang mockup + video ini sendiri, jana gambar AI orang <em>memegang</em> & <em>membaca</em> produk anda, pilih tema warna yang sepadan, dan susun semuanya di seksyen yang betul. Tiada upload berulang.</div>
        </x-ui.card-body>
    </x-ui.card>
</div>
