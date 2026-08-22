<div>
    @section('title', 'Koleksi ' . $categoryTitle . ' - ' . config('app.name'))
    @section('page_title', $categoryTitle)
    @section('page_breadcrumb', $categoryTitle)

    {{-- Category Header & Search Toolbar --}}
    <div class="flex flex-col justify-between gap-4 py-4 sm:flex-row sm:items-center sm:py-5">
        <div>
            <div class="flex items-center space-x-2.5">
                <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                    @if(stripos($rawCategory, 'image') !== false || stripos($rawCategory, 'foto') !== false)
                        <x-lucide-image class="size-5.5" />
                    @elseif(stripos($rawCategory, 'pdf') !== false || stripos($rawCategory, 'doc') !== false)
                        <x-lucide-file-text class="size-5.5" />
                    @else
                        <x-lucide-boxes class="size-5.5" />
                    @endif
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl flex items-center gap-2">
                        <span>Tools {{ $categoryTitle }}</span>
                        <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-2.5 py-0.5">
                            {{ $tools->count() }} Tools
                        </span>
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        Pilih alat di bawah untuk mulai memproses dan mengonversi file Anda secara instan.
                    </p>
                </div>
            </div>
        </div>

        {{-- Search Input within Category --}}
        <div class="relative flex w-full sm:w-72">
            <input wire:model.live.debounce.250ms="search" type="text" placeholder="Cari di kategori ini..."
                class="form-input h-10 w-full rounded-full border border-slate-300 bg-white px-4 pl-10 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-500 dark:bg-navy-800 dark:text-navy-50 dark:placeholder:text-navy-400 dark:hover:border-navy-400 dark:focus:border-accent shadow-xs" />
            <span class="pointer-events-none absolute left-3.5 flex h-full items-center justify-center text-slate-400 dark:text-navy-300">
                <x-lucide-search class="size-4" />
            </span>
        </div>
    </div>

    {{-- Exact Lineone Onboarding-1 Grid and Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5 lg:gap-6 mt-4">
        @forelse($tools as $tool)
            @php
                $defaultIllustration = match($tool->slug) {
                    'compress-image' => asset('images/illustrations/upload-cloud.svg'),
                    'convert-image' => asset('images/illustrations/responsive.svg'),
                    'pdf-to-word' => asset('images/illustrations/writer.svg'),
                    default => asset('images/illustrations/creativedesign.svg'),
                };

                $imageSrc = $tool->image_url ?: $defaultIllustration;
            @endphp

            <div class="card flex flex-col justify-between h-full">
                <div class="flex h-48 items-center justify-center p-5">
                    <img
                        class="max-h-40 max-w-full object-contain"
                        src="{{ $imageSrc }}"
                        alt="{{ $tool->name }}"
                    />
                </div>
                <div class="flex flex-1 flex-col justify-between px-4 pb-8 text-center sm:px-5">
                    <div>
                        <h4
                            class="text-lg font-semibold text-slate-700 dark:text-navy-100"
                        >
                            {{ $tool->name }}
                        </h4>
                        <p class="pt-3 text-slate-500 dark:text-navy-300">
                            {{ $tool->description }}
                        </p>
                    </div>
                    <div class="pt-8">
                        <a
                            href="{{ route('tool', $tool->slug) }}" wire:navigate
                            class="btn bg-primary font-medium text-white shadow-lg shadow-primary/50 hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:shadow-accent/50 dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                        >
                            Buka Tool
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-10 text-center border border-slate-200 dark:border-navy-600">
                <div class="mask is-squircle size-14 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mx-auto mb-3">
                    <x-lucide-search class="size-7" />
                </div>
                <h4 class="text-base font-bold text-slate-700 dark:text-navy-100">Tool Tidak Ditemukan</h4>
                <p class="text-slate-400 dark:text-navy-300 text-xs mt-1 max-w-sm mx-auto">
                    Tidak ada tool yang cocok dengan kata kunci "{{ $search }}" pada kategori ini.
                </p>
                <button wire:click="$set('search', '')" class="btn rounded-full mt-4 bg-primary text-white dark:bg-accent h-8 px-4 text-xs font-semibold mx-auto">
                    Reset Pencarian
                </button>
            </div>
        @endforelse
    </div>
</div>
