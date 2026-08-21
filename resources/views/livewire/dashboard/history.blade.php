<div>
    @section('title', 'Riwayat Aktivitas - ' . config('app.name'))
    @section('page_title', 'Riwayat Aktivitas')
    @section('page_breadcrumb', 'Riwayat')

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:gap-6">
        <!-- Activities Table (Table Advanced from Lineone) -->
        <div>
            {{-- Top Toolbar Header --}}
            <div class="flex items-center justify-between">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Semua Riwayat Pemrosesan
                </h2>
                <div class="flex items-center space-x-2">
                    {{-- Expandable Search Input --}}
                    <div class="flex items-center" x-data="{ isInputActive: {{ !empty($search) ? 'true' : 'false' }} }">
                        <label class="block">
                            <input
                                x-effect="isInputActive === true && $nextTick(() => { $el.focus() });"
                                :class="isInputActive ? 'w-36 lg:w-56' : 'w-0'"
                                class="form-input bg-transparent px-1 text-right transition-all duration-100 placeholder:text-slate-500 dark:placeholder:text-navy-200 text-sm"
                                placeholder="Cari file, tool..."
                                type="text"
                                wire:model.live.debounce.300ms="search"
                            />
                        </label>
                        <button
                            @click="isInputActive = !isInputActive"
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                            title="Pencarian"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="size-4.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </button>
                    </div>

                    {{-- Popper Filter Dropdown --}}
                    <div
                        x-data="usePopper({ placement: 'bottom-end', offset: 4 })"
                        @click.outside="if(isShowPopper) isShowPopper = false"
                        class="inline-flex"
                    >
                        <button
                            x-ref="popperRef"
                            @click="isShowPopper = !isShowPopper"
                            class="btn size-8 rounded-full p-0 {{ ($statusFilter || $toolFilter) ? 'bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light' : 'text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-200 dark:hover:bg-navy-300/20' }}"
                            title="Filter Data"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                            </svg>
                        </button>
                        <div
                            x-ref="popperRoot"
                            class="popper-root"
                            :class="isShowPopper && 'show'"
                        >
                            <div class="popper-box rounded-md border border-slate-150 bg-white p-4 font-inter dark:border-navy-500 dark:bg-navy-700 w-64 shadow-xl text-left">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-150 dark:border-navy-500 mb-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Filter Data</p>
                                    @if($statusFilter || $toolFilter || $search)
                                        <button wire:click="resetFilters" class="text-xs text-primary dark:text-accent-light hover:underline">Reset</button>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-medium text-slate-600 dark:text-navy-200">Status</label>
                                        <select wire:model.live="statusFilter" class="form-select mt-1 h-8 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-800 dark:hover:border-navy-400 dark:focus:border-accent">
                                            <option value="">Semua Status</option>
                                            <option value="completed">Selesai</option>
                                            <option value="processing">Diproses</option>
                                            <option value="failed">Gagal</option>
                                            <option value="expired">Kedaluwarsa</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-slate-600 dark:text-navy-200">Tool</label>
                                        <select wire:model.live="toolFilter" class="form-select mt-1 h-8 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-800 dark:hover:border-navy-400 dark:focus:border-accent">
                                            <option value="">Semua Tool</option>
                                            @foreach($tools as $tool)
                                                <option value="{{ $tool['slug'] }}">{{ $tool['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Export Excel Button --}}
                    <button
                        wire:click="export"
                        class="btn h-8 space-x-1.5 rounded-full border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span>Export</span>
                    </button>
                </div>
            </div>

            {{-- Table Advanced Card Container --}}
            <div class="card mt-3">
                @if ($activities->count() > 0)
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <table class="is-hoverable w-full text-left">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Tool
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Nama File
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Ukuran Asli &rarr; Hasil
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Efisiensi
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Status
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Tanggal
                                    </th>
                                    <th class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                                @foreach ($activities as $activity)
                                    <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                                        {{-- Tool Icon & Name --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <div class="flex items-center space-x-3">
                                                <div class="mask is-squircle flex size-9 shrink-0 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-semibold">
                                                    @if($activity->tool_slug === 'compress-image')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    @elseif($activity->tool_slug === 'convert-image')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <span class="font-medium text-slate-700 dark:text-navy-100">
                                                    {{ Str::headline($activity->tool_slug) }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- File Name --}}
                                        <td class="px-4 py-3 sm:px-5 max-w-[220px]">
                                            <p class="truncate font-medium text-slate-700 dark:text-navy-100 text-sm" title="{{ $activity->original_filename }}">
                                                {{ $activity->original_filename }}
                                            </p>
                                        </td>

                                        {{-- Original -> Result Size --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs text-slate-600 dark:text-navy-200">
                                            @if ($activity->original_size && $activity->result_size)
                                                <span>{{ \Illuminate\Support\Number::fileSize($activity->original_size) }}</span>
                                                <span class="text-slate-400 mx-1">&rarr;</span>
                                                <span class="font-bold text-slate-800 dark:text-navy-100">{{ \Illuminate\Support\Number::fileSize($activity->result_size) }}</span>
                                            @elseif ($activity->original_size)
                                                <span>{{ \Illuminate\Support\Number::fileSize($activity->original_size) }}</span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>

                                        {{-- Efficiency / Saved Size --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @if ($activity->original_size && $activity->result_size && $activity->original_size > $activity->result_size)
                                                <span class="badge rounded-full bg-success/10 text-success dark:bg-success/15 px-2 py-0.5 text-xs font-semibold">
                                                    -{{ round((($activity->original_size - $activity->result_size) / $activity->original_size) * 100) }}%
                                                </span>
                                            @else
                                                <span class="text-slate-400 dark:text-navy-300 text-xs">-</span>
                                            @endif
                                        </td>

                                        {{-- Status Badge --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @if($activity->status === 'completed')
                                                <div class="badge rounded-full bg-success/10 text-success dark:bg-success/15 px-2.5 py-0.5 text-xs font-medium">
                                                    Selesai
                                                </div>
                                            @elseif($activity->status === 'processing')
                                                <div class="badge rounded-full bg-warning/10 text-warning dark:bg-warning/15 px-2.5 py-0.5 text-xs font-medium animate-pulse">
                                                    Diproses
                                                </div>
                                            @elseif($activity->status === 'expired')
                                                <div class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-100 px-2.5 py-0.5 text-xs font-medium">
                                                    Kedaluwarsa
                                                </div>
                                            @else
                                                <div class="badge rounded-full bg-error/10 text-error dark:bg-error/15 px-2.5 py-0.5 text-xs font-medium">
                                                    Gagal
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Timestamp --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs text-slate-400 dark:text-navy-300">
                                            {{ $activity->created_at->format('d M Y, H:i') }}
                                        </td>

                                        {{-- Action Menu --}}
                                        <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                            <div
                                                x-data="usePopper({ placement: 'bottom-end', offset: 4 })"
                                                @click.outside="if(isShowPopper) isShowPopper = false"
                                                class="inline-flex"
                                            >
                                                <button
                                                    x-ref="popperRef"
                                                    @click="isShowPopper = !isShowPopper"
                                                    class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                                    </svg>
                                                </button>

                                                <div
                                                    x-ref="popperRoot"
                                                    class="popper-root"
                                                    :class="isShowPopper && 'show'"
                                                >
                                                    <div class="popper-box rounded-md border border-slate-150 bg-white py-1.5 font-inter dark:border-navy-500 dark:bg-navy-700 shadow-lg text-left">
                                                        <ul>
                                                            @if($activity->status === 'completed' && $activity->result_path)
                                                                <li>
                                                                    <a
                                                                        href="{{ route('activity.download', $activity->id) }}"
                                                                        class="flex h-8 items-center space-x-2 px-3 pr-8 font-medium tracking-wide outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 focus:bg-slate-100 focus:text-slate-800 dark:hover:bg-navy-600 dark:hover:text-navy-100 dark:focus:bg-navy-600 dark:focus:text-navy-100 text-xs"
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                        </svg>
                                                                        <span>Unduh Hasil</span>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <button
                                                                    type="button"
                                                                    wire:click="deleteActivity({{ $activity->id }})"
                                                                    wire:confirm="Yakin ingin menghapus riwayat ini?"
                                                                    class="flex h-8 w-full items-center space-x-2 px-3 pr-8 font-medium tracking-wide text-error outline-hidden transition-all hover:bg-error/10 hover:text-error focus:bg-error/10 focus:text-error text-xs"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                    <span>Hapus Riwayat</span>
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Advanced Footer (Show entries + Pagination) --}}
                    <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5 border-t border-slate-150 dark:border-navy-500">
                        {{-- Per Page Select --}}
                        <div class="flex items-center space-x-2 text-xs">
                            <span>Tampilkan</span>
                            <label class="block">
                                <select
                                    wire:model.live="perPage"
                                    class="form-select rounded-full border border-slate-300 bg-white px-2 py-1 pr-6 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent text-xs"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </label>
                            <span>data per halaman</span>
                        </div>

                        {{-- Pagination Links --}}
                        <div>
                            {{ $activities->links('components.lineone-pagination') }}
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                        <div class="mask is-squircle size-16 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-700 dark:text-navy-100">Tidak Ada Riwayat</h4>
                        <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-sm">
                            @if($search || $statusFilter || $toolFilter)
                                Tidak ditemukan data aktivitas yang cocok dengan filter yang Anda tentukan.
                            @else
                                Anda belum melakukan aktivitas pemrosesan file apapun.
                            @endif
                        </p>
                        @if($search || $statusFilter || $toolFilter)
                            <button wire:click="resetFilters" class="btn mt-4 bg-primary text-white dark:bg-accent h-8 px-4 rounded-lg text-xs font-semibold">
                                Reset Filter
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
