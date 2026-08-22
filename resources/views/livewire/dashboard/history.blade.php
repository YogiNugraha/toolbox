<div>
    @section('title', 'Riwayat Aktivitas - ' . config('app.name'))
    @section('page_title', 'Riwayat Aktivitas')
    @section('page_breadcrumb', 'Riwayat')

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session()->has('info'))
            <div class="alert flex rounded-lg border border-info px-4 py-3.5 text-info sm:px-5">
                {{ session('info') }}
            </div>
        @endif

        @if (session()->has('message'))
            <div class="alert flex rounded-lg border border-success px-4 py-3.5 text-success sm:px-5">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert flex rounded-lg border border-error px-4 py-3.5 text-error sm:px-5">
                {{ session('error') }}
            </div>
        @endif

        {{-- Activities Card (Aligned with Billing & Admin Transactions Table Style) --}}
        <div class="card">
            {{-- Filters Bar --}}
            <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-150 dark:border-navy-600">
                {{-- Search Bar --}}
                <div class="relative flex w-full sm:w-80">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama file, tool, status..."
                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                    <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 dark:text-navy-300">
                        <x-lucide-search class="size-4" />
                    </span>
                </div>

                {{-- Multi Filters & Actions --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Tool Filter --}}
                    <select wire:model.live="toolFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">Semua Tool</option>
                        @foreach($tools as $tool)
                            <option value="{{ is_array($tool) ? $tool['slug'] : $tool->slug }}">{{ is_array($tool) ? $tool['name'] : $tool->name }}</option>
                        @endforeach
                    </select>

                    {{-- Status Filter --}}
                    <select wire:model.live="statusFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">Semua Status</option>
                        <option value="completed">Selesai</option>
                        <option value="processing">Diproses</option>
                        <option value="failed">Gagal</option>
                        <option value="expired">Kedaluwarsa</option>
                    </select>

                    @if($search || $toolFilter || $statusFilter)
                        <button wire:click="resetFilters" class="btn h-8 rounded-lg bg-slate-150 px-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450">
                            Reset
                        </button>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr>
                            <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                TOOL & NAMA FILE
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                UKURAN (ASLI &rarr; HASIL)
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                                EFISIENSI
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                                STATUS
                            </th>
                            <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                TANGGAL
                            </th>
                            <th class="rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                AKSI
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                        @forelse ($activities as $activity)
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                                {{-- Tool & File Name --}}
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <div class="flex items-center space-x-3">
                                        <div class="mask is-squircle flex size-9 shrink-0 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                                            @if(str_contains($activity->tool_slug, 'compress') || str_contains($activity->tool_slug, 'image') || str_contains($activity->tool_slug, 'convert'))
                                                <x-lucide-image class="size-4.5" />
                                            @elseif(str_contains($activity->tool_slug, 'pdf') || str_contains($activity->tool_slug, 'word') || str_contains($activity->tool_slug, 'doc'))
                                                <x-lucide-file-text class="size-4.5" />
                                            @else
                                                <x-lucide-wrench class="size-4.5" />
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                                    {{ Str::headline($activity->tool_slug) }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-slate-400 dark:text-navy-300 font-mono mt-0.5 max-w-[240px] truncate" title="{{ $activity->original_filename }}">
                                                {{ $activity->original_filename }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Size Comparison --}}
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs text-slate-600 dark:text-navy-200">
                                    @if ($activity->original_size && $activity->result_size)
                                        <span>{{ \Illuminate\Support\Number::fileSize($activity->original_size) }}</span>
                                        <span class="text-slate-400 mx-1.5">&rarr;</span>
                                        <span class="font-bold text-slate-800 dark:text-navy-100">{{ \Illuminate\Support\Number::fileSize($activity->result_size) }}</span>
                                    @elseif ($activity->original_size)
                                        <span>{{ \Illuminate\Support\Number::fileSize($activity->original_size) }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                {{-- Efficiency --}}
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center">
                                    @if ($activity->original_size && $activity->result_size && $activity->original_size > $activity->result_size)
                                        <span class="badge rounded-full bg-success/15 text-success dark:bg-success/20 px-2 py-0.5 text-[10px] font-bold">
                                            -{{ round((($activity->original_size - $activity->result_size) / $activity->original_size) * 100) }}%
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-navy-300 text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap px-4 py-3 text-center sm:px-5">
                                    @if($activity->status === 'completed')
                                        <span class="badge space-x-1.5 rounded-full bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                            <span>Selesai</span>
                                        </span>
                                    @elseif($activity->status === 'processing')
                                        <span class="badge space-x-1.5 rounded-full bg-warning/10 text-warning text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current animate-ping"></span>
                                            <span>Diproses</span>
                                        </span>
                                    @elseif($activity->status === 'expired')
                                        <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-200 text-[10px] font-bold px-2.5 py-0.5">
                                            Kedaluwarsa
                                        </span>
                                    @else
                                        <span class="badge space-x-1.5 rounded-full bg-error/10 text-error text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                            <span>Gagal</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Timestamp --}}
                                <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5 text-xs text-slate-400 dark:text-navy-300">
                                    {{ $activity->created_at->format('d M Y, H:i') }}
                                </td>

                                {{-- Actions --}}
                                <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        @if($activity->status === 'completed' && $activity->result_path)
                                            <a href="{{ route('activity.download', $activity->id) }}" title="Unduh Hasil"
                                               class="btn size-7 rounded-md bg-primary/10 text-primary hover:bg-primary/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 p-0 text-xs font-semibold shadow-xs">
                                                <x-lucide-download class="size-3.5" />
                                            </a>
                                        @endif
                                        <button wire:click="deleteActivity({{ $activity->id }})" wire:confirm="Yakin ingin menghapus riwayat ini?" title="Hapus Riwayat"
                                                class="btn size-7 rounded-md bg-error/10 text-error hover:bg-error/20 p-0 text-xs font-semibold shadow-xs">
                                            <x-lucide-trash-2 class="size-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                    <x-lucide-file-text class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" />
                                    <p class="font-medium">
                                        @if($search || $statusFilter || $toolFilter)
                                            Tidak ditemukan riwayat yang cocok dengan kriteria filter Anda.
                                        @else
                                            Belum ada riwayat aktivitas pemrosesan file.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($activities->hasPages())
                <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center sm:px-5 border-t border-slate-150 dark:border-navy-600">
                    <div class="text-xs text-slate-400 dark:text-navy-300">
                        Menampilkan <strong>{{ $activities->firstItem() }}</strong> sampai <strong>{{ $activities->lastItem() }}</strong> dari <strong>{{ $activities->total() }}</strong> aktivitas
                    </div>
                    <div>
                        {{ $activities->links('components.lineone-pagination') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
