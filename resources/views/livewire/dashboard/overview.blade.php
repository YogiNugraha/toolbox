<div>
    @section('title', 'Dashboard - ' . config('app.name'))
    @section('page_title', 'Dashboard Overview')
    @section('page_breadcrumb', 'Overview')

    <div class="mt-2 grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
        {{-- Left / Main Column (col-span-12 lg:col-span-8 xl:col-span-9) --}}
        <div class="col-span-12 lg:col-span-8 xl:col-span-9 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Teacher Dashboard Welcome Hero Banner --}}
            <div class="card bg-linear-to-l from-pink-300 to-indigo-400 p-5 sm:flex-row items-center relative overflow-hidden shadow-md">
                <div class="flex justify-center sm:order-last shrink-0">
                    <img class="-mt-6 sm:-mt-2 h-36 sm:h-40 object-contain drop-shadow" 
                         src="{{ asset('images/illustrations/teacher.svg') }}" 
                         alt="Teacher Illustration" />
                </div>
                <div class="mt-4 flex-1 pt-1 text-center text-white sm:mt-0 sm:text-left sm:pr-4">
                    <h3 class="text-xl sm:text-2xl font-bold">
                        Selamat Datang Kembali, <span class="font-extrabold">{{ auth()->user()->name }}</span>!
                    </h3>
                    <p class="mt-2 text-sm text-indigo-50 leading-relaxed">
                        Anda telah berhasil memproses 
                        <span class="font-bold text-navy-900 bg-white/40 px-2 py-0.5 rounded-md">{{ $totalFiles }} file</span> sejauh ini.
                    </p>
                    <p class="text-xs text-indigo-100 mt-1">
                        Total efisiensi penyimpanan: <span class="font-bold text-white">{{ \Illuminate\Support\Number::fileSize($totalSaved) }}</span> dihemat.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2.5 justify-center sm:justify-start">
                        @if(!$activeSub)
                            <a href="{{ route('pricing') }}" 
                               class="btn bg-white font-bold text-primary hover:bg-slate-100 active:bg-slate-200 text-xs px-4 py-2 rounded-lg shadow-sm">
                                Upgrade ke Pro
                            </a>
                            <a href="{{ route('home') }}#tools-section" 
                               class="btn bg-white/20 hover:bg-white/30 text-white font-semibold text-xs px-3.5 py-2 rounded-lg backdrop-blur-xs">
                                Jelajahi Tools
                            </a>
                        @else
                            <a href="{{ route('dashboard.billing') }}" 
                               class="btn bg-white font-bold text-primary hover:bg-slate-100 active:bg-slate-200 text-xs px-4 py-2 rounded-lg shadow-sm">
                                Kelola Langganan
                            </a>
                            <span class="badge rounded-lg bg-white/20 text-white text-xs px-3 py-1.5 backdrop-blur-xs font-semibold">
                                Paket Pro Aktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Featured Quick Access Tools (Classes Cards from Teacher Dashboard) --}}
            <div>
                <div class="flex h-8 items-center justify-between">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 dark:text-navy-100">
                        Pilihan Tools Cepat
                    </h2>
                    <a href="{{ route('home') }}#tools-section"
                        class="border-b border-dotted border-current pb-0.5 text-xs-plus font-medium text-primary outline-hidden transition-colors duration-300 hover:text-primary/70 dark:text-accent-light">
                        Lihat Semua Tools
                    </a>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5">
                    @foreach(array_slice($tools, 0, 3) as $tool)
                        @php
                            $gradients = [
                                0 => 'from-blue-500 to-purple-600',
                                1 => 'from-info to-info-focus',
                                2 => 'from-secondary-light to-secondary',
                            ];
                            $grad = $gradients[$loop->index % 3];
                        @endphp
                        <div class="card flex-row overflow-hidden hover:shadow-md transition-shadow">
                            <div class="h-full w-1.5 bg-linear-to-b {{ $grad }}"></div>
                            <div class="flex flex-1 flex-col justify-between p-4 sm:px-5">
                                <div>
                                    <div class="mask is-squircle flex size-12 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                                        @if($tool['slug'] === 'compress-image')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @elseif($tool['slug'] === 'convert-image')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <h3 class="font-medium text-slate-700 line-clamp-1 dark:text-navy-100 text-sm">
                                        {{ $tool['name'] }}
                                    </h3>
                                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-1 line-clamp-2">
                                        {{ $tool['description'] }}
                                    </p>
                                    <div class="mt-3 flex space-x-1.5">
                                        <span class="tag bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light py-0.5 px-2 rounded text-[11px] font-semibold">
                                            {{ ucfirst($tool['category']) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-between items-center pt-2 border-t border-slate-100 dark:border-navy-600">
                                    <span class="text-[11px] text-slate-400 dark:text-navy-300 font-medium">Buka Tool</span>
                                    <a href="{{ route('tool', $tool['slug']) }}"
                                        class="btn size-7 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg focus:bg-slate-200 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 flex items-center justify-center transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 rotate-45" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent File Activities Table (Media for lessons table style from Teacher Dashboard) --}}
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                        Aktivitas File Terakhir
                    </h2>
                    <a href="{{ route('history') }}"
                        class="border-b border-dotted border-current pb-0.5 text-xs-plus font-medium text-primary outline-hidden transition-colors duration-300 hover:text-primary/70 dark:text-accent-light">
                        Lihat Riwayat Lengkap
                    </a>
                </div>
                <div class="card mt-3">
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <table class="is-hoverable w-full text-left">
                            <thead>
                                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5 text-xs">
                                        Tool
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5 text-xs">
                                        Detail File
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5 text-xs">
                                        Status
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5 text-xs">
                                        Ukuran
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5 text-xs">
                                        Waktu
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5 text-xs text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                                @forelse ($activities as $activity)
                                    <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <div class="flex items-center space-x-3">
                                                <div class="relative flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent dark:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <span class="font-medium text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                                    {{ Str::headline($activity->tool_slug) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 sm:px-5 max-w-[200px]">
                                            <p class="truncate font-medium text-slate-700 dark:text-navy-100 text-xs sm:text-sm" title="{{ $activity->original_filename }}">
                                                {{ $activity->original_filename }}
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @if($activity->status === 'completed')
                                                <div class="badge space-x-2 text-success font-medium text-xs">
                                                    <div class="size-2 rounded-full bg-current"></div>
                                                    <span>Selesai</span>
                                                </div>
                                            @elseif($activity->status === 'processing')
                                                <div class="badge space-x-2 text-warning font-medium text-xs">
                                                    <div class="size-2 rounded-full bg-current animate-ping"></div>
                                                    <span>Diproses</span>
                                                </div>
                                            @elseif($activity->status === 'expired')
                                                <div class="badge space-x-2 text-slate-500 dark:text-navy-200 font-medium text-xs">
                                                    <div class="size-2 rounded-full bg-current"></div>
                                                    <span>Expired</span>
                                                </div>
                                            @else
                                                <div class="badge space-x-2 text-error font-medium text-xs">
                                                    <div class="size-2 rounded-full bg-current"></div>
                                                    <span>Gagal</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5 text-xs">
                                            @if($activity->result_size)
                                                {{ \Illuminate\Support\Number::fileSize($activity->result_size) }}
                                            @elseif($activity->original_size)
                                                {{ \Illuminate\Support\Number::fileSize($activity->original_size) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-400 dark:text-navy-300 sm:px-5">
                                            {{ $activity->created_at->diffForHumans() }}
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                            @if($activity->status === 'completed' && $activity->result_path)
                                                <a href="{{ route('activity.download', $activity->id) }}"
                                                    class="btn size-8 rounded-full bg-slate-150 p-0 font-medium text-slate-800 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 inline-flex items-center justify-center"
                                                    title="Unduh File">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-slate-400 dark:text-navy-300 text-xs">
                                            Belum ada aktivitas pemrosesan file.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (col-span-12 lg:col-span-4 xl:col-span-3) --}}
        <div class="col-span-12 lg:col-span-4 xl:col-span-3 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Status & Quota Card (Working Hours Card Style) --}}
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-150 dark:border-navy-600">
                    <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100 text-sm">
                        Paket & Status Kuota
                    </h2>
                    <span class="badge rounded-full {{ $activeSub ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light' }} text-xs font-semibold px-2.5 py-0.5">
                        {{ $activeSub ? 'PRO' : 'FREE' }}
                    </span>
                </div>

                <div class="py-4 space-y-3.5">
                    <div>
                        <div class="flex justify-between text-xs font-medium text-slate-600 dark:text-navy-200 mb-1">
                            <span>Aktivitas Hari Ini</span>
                            <span class="font-bold text-slate-800 dark:text-navy-100">{{ $todayFiles }} file</span>
                        </div>
                        <div class="progress h-2 rounded-full bg-slate-150 dark:bg-navy-500 overflow-hidden">
                            @php
                                $quotaLimit = $currentPlan ? $currentPlan->daily_limit : 10;
                                $quotaPercent = min(100, round(($todayFiles / max(1, $quotaLimit)) * 100));
                            @endphp
                            <div class="h-full rounded-full bg-primary dark:bg-accent transition-all" style="width: {{ $quotaPercent }}%"></div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-navy-600 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400 dark:text-navy-300">Total File Selesai</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100">{{ $totalFiles }} file</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 dark:text-navy-300">Penyimpanan Dihemat</span>
                            <span class="font-bold text-success">{{ \Illuminate\Support\Number::fileSize($totalSaved) }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    @if(!$activeSub)
                        <a href="{{ route('pricing') }}" class="btn w-full bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs py-2 rounded-lg shadow-sm">
                            Upgrade Kuota Unlimited
                        </a>
                    @else
                        <a href="{{ route('dashboard.billing') }}" class="btn w-full border border-slate-300 text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs py-2 rounded-lg">
                            Detail Langganan
                        </a>
                    @endif
                </div>
            </div>

            {{-- Interactive Calendar Widget (Exact Teacher Dashboard Calendar) --}}
            <div class="card p-4">
                <div class="space-y-1 text-center font-inter text-xs-plus">
                    <div class="flex items-center justify-between px-2 pb-3">
                        <p class="font-bold text-slate-700 dark:text-navy-100 text-sm">
                            {{ now()->translatedFormat('F Y') }}
                        </p>
                        <div class="flex space-x-1">
                            <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[10px] font-semibold px-2 py-0.5">
                                Hari Ini
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 pb-2 text-[11px] font-semibold text-primary dark:text-accent-light">
                        <div>MIN</div>
                        <div>SEN</div>
                        <div>SEL</div>
                        <div>RAB</div>
                        <div>KAM</div>
                        <div>JUM</div>
                        <div>SAB</div>
                    </div>
                    
                    @php
                        $startOfMonth = now()->startOfMonth();
                        $endOfMonth = now()->endOfMonth();
                        $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 for Sun
                        $totalDays = $endOfMonth->day;
                        $today = now()->day;
                    @endphp

                    <div class="grid grid-cols-7 gap-y-1 place-items-center text-xs">
                        {{-- Empty leading days --}}
                        @for($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="h-7 w-8"></div>
                        @endfor

                        {{-- Days of current month --}}
                        @for($day = 1; $day <= $totalDays; $day++)
                            @if($day === $today)
                                <button class="flex h-7 w-8 items-center justify-center rounded-lg bg-primary text-white font-bold dark:bg-accent shadow-xs">
                                    {{ $day }}
                                </button>
                            @else
                                <button class="flex h-7 w-8 items-center justify-center rounded-lg text-slate-700 hover:bg-primary/10 hover:text-primary dark:text-navy-100 dark:hover:bg-accent-light/10 dark:hover:text-accent-light">
                                    {{ $day }}
                                </button>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Summary Cards (Student Cards style from Teacher Dashboard) --}}
            <div class="space-y-3">
                <div class="card p-3.5 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-700 dark:text-navy-100 text-xs">
                                File Diproses Hari Ini
                            </p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">
                                {{ $todayFiles }} file sukses
                            </p>
                        </div>
                    </div>
                    <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-2 py-0.5">
                        {{ $todayFiles }}
                    </span>
                </div>

                <div class="card p-3.5 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="mask is-squircle flex size-10 items-center justify-center bg-success/10 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-700 dark:text-navy-100 text-xs">
                                Efisiensi Kompresi
                            </p>
                            <p class="text-xs text-slate-400 dark:text-navy-300">
                                {{ \Illuminate\Support\Number::fileSize($totalSaved) }}
                            </p>
                        </div>
                    </div>
                    <span class="badge rounded-full bg-success/10 text-success text-xs font-bold px-2 py-0.5">
                        Hemat
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>
