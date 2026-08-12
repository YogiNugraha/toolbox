<div>
    @section('page_title', 'Riwayat Aktivitas')

    <div class="card">
        {{-- Search and Export Header --}}
        <div class="flex flex-col justify-between gap-4 px-4 py-4 sm:flex-row sm:items-center sm:px-5">
            <label class="relative flex w-full sm:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari tool, file, status..."
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 dark:text-navy-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
            </label>

            <button wire:click="export"
                class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export Excel
            </button>
        </div>

        @if ($activities->count() > 0)
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Tool</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Detail File</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Efisiensi</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Tanggal</th>
                            <th class="whitespace-nowrap px-3 py-3 text-right font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                    {{ Str::headline($activity->tool_slug) }}
                                </td>
                                <td class="px-4 py-3 sm:px-5">
                                    <p class="max-w-[200px] truncate font-medium text-slate-700 dark:text-navy-100" title="{{ $activity->original_filename }}">
                                        {{ Str::limit($activity->original_filename, 30) }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                                        Asli: {{ $activity->original_size ? \Illuminate\Support\Number::fileSize($activity->original_size) : '-' }}
                                        @if ($activity->result_size)
                                            · Hasil: {{ \Illuminate\Support\Number::fileSize($activity->result_size) }}
                                        @endif
                                    </p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    @if ($activity->original_size && $activity->result_size && $activity->original_size > $activity->result_size)
                                        <span class="font-medium text-success">
                                            -{{ round((($activity->original_size - $activity->result_size) / $activity->original_size) * 100) }}%
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-navy-300">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    @if($activity->status === 'completed')
                                        <span class="badge bg-success/10 text-success dark:bg-success/15">Selesai</span>
                                    @elseif($activity->status === 'processing')
                                        <span class="badge bg-warning/10 text-warning dark:bg-warning/15">Proses</span>
                                    @elseif($activity->status === 'expired')
                                        <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">Kedaluwarsa</span>
                                    @else
                                        <span class="badge bg-error/10 text-error dark:bg-error/15">Gagal</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ $activity->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                    @if ($activity->status === 'completed' && $activity->result_path)
                                        <a href="{{ route('activity.download', $activity->id) }}"
                                            class="btn border border-primary/30 px-3 py-1 text-xs font-medium text-primary hover:bg-primary/10 dark:border-accent/30 dark:text-accent-light dark:hover:bg-accent/10">
                                            Download
                                        </a>
                                    @elseif($activity->status === 'expired')
                                        <span class="text-xs text-slate-400 dark:text-navy-300">Tidak tersedia</span>
                                    @else
                                        <span class="text-slate-400 dark:text-navy-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-4 dark:border-navy-500 sm:px-5">
                {{ $activities->links() }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12">
                <p class="text-slate-400 dark:text-navy-300">Belum ada aktivitas.</p>
            </div>
        @endif
    </div>
</div>
