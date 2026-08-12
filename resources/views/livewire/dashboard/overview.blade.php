<div class="space-y-6">
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
        <div class="card px-4 py-4 sm:px-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">
                        Total File Diproses
                    </p>
                    <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ $totalFiles }}
                    </p>
                </div>
                <div class="mask is-squircle flex size-11 items-center justify-center bg-primary/10 dark:bg-accent-light/10">
                    <svg class="size-6 text-primary dark:text-accent-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card px-4 py-4 sm:px-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">
                        Total Penyimpanan Dihemat
                    </p>
                    <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ \Illuminate\Support\Number::fileSize($totalSaved) }}
                    </p>
                </div>
                <div class="mask is-squircle flex size-11 items-center justify-center bg-success/10">
                    <svg class="size-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Akses Cepat Tools --}}
    <div>
        <h2 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100">
            Akses Cepat Tools
        </h2>
        <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5">
            @foreach($tools as $tool)
                <a href="{{ route('tool', $tool['slug']) }}"
                   class="card px-4 py-4 hover:shadow-lg transition-shadow duration-300 sm:px-5">
                    <div class="flex items-start space-x-4">
                        <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary/10 dark:bg-accent-light/10">
                            <svg class="size-5 text-primary dark:text-accent-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-slate-700 dark:text-navy-100">{{ $tool['name'] }}</h3>
                            <p class="mt-0.5 text-xs-plus text-slate-400 line-clamp-2 dark:text-navy-300">{{ $tool['description'] }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Aktivitas Terakhir --}}
    <div>
        <div class="flex items-center justify-between">
            <h2 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100">
                Aktivitas Terakhir
            </h2>
            <a href="{{ route('history') }}"
               class="border-b border-dotted border-current pb-0.5 text-xs-plus font-medium text-primary outline-hidden transition-colors duration-300 hover:text-primary/70 focus:text-primary/70 dark:text-accent-light dark:hover:text-accent-light/70">
                Lihat Semua
            </a>
        </div>

        <div class="card mt-3">
            @if($activities->count() > 0)
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <table class="is-hoverable w-full text-left">
                        <thead>
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">
                                    Tool
                                </th>
                                <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">
                                    File
                                </th>
                                <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">
                                    Status
                                </th>
                                <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">
                                    Tanggal
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                        {{ Str::headline($activity->tool_slug) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        {{ Str::limit($activity->original_filename, 30) }}
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
                                        {{ $activity->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-16 text-slate-300 dark:text-navy-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <p class="mt-2 text-slate-400 dark:text-navy-300">Belum ada aktivitas. Silakan gunakan tools yang tersedia.</p>
                </div>
            @endif
        </div>
    </div>
</div>
