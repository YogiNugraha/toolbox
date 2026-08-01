<div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-sm border border-hairline p-6 flex items-start justify-between hover:border-amber/50 transition-colors">
            <div>
                <p class="text-sm font-medium text-ink-muted mb-2">Total File Diproses</p>
                <h3 class="text-3xl font-mono font-bold text-ink">{{ $totalFiles }}</h3>
            </div>
            <div class="w-9 h-9 rounded-sm border border-hairline bg-paper flex items-center justify-center text-ink-muted shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>

        <div class="bg-white rounded-sm border border-hairline p-6 flex items-start justify-between hover:border-amber/50 transition-colors">
            <div>
                <p class="text-sm font-medium text-ink-muted mb-2">Total Penyimpanan Dihemat</p>
                <h3 class="text-3xl font-mono font-bold text-ink">{{ \Illuminate\Support\Number::fileSize($totalSaved) }}</h3>
            </div>
            <div class="w-9 h-9 rounded-sm border border-hairline bg-paper flex items-center justify-center text-ink-muted shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-lg font-display font-bold text-ink mb-4">Akses Cepat Tools</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($tools as $tool)
                <a href="{{ route('tool', $tool['slug']) }}" class="bg-white p-5 rounded-sm border border-hairline hover:border-amber transition-colors group flex items-start gap-4">
                    <div class="w-10 h-10 rounded-sm border border-hairline bg-paper text-ink-muted flex items-center justify-center shrink-0 group-hover:bg-amber/10 group-hover:text-amber group-hover:border-amber/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-ink group-hover:text-amber transition-colors">{{ $tool['name'] }}</h3>
                        <p class="text-sm text-ink-muted line-clamp-2 mt-1">{{ $tool['description'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-display font-bold text-ink">Aktivitas Terakhir</h2>
            <a href="{{ route('history') }}" class="text-sm font-medium text-amber hover:text-amber/80">Lihat Semua &rarr;</a>
        </div>
        
        <div class="bg-white rounded-sm border border-hairline overflow-hidden">
            @if($activities->count() > 0)
                <table class="min-w-full divide-y divide-hairline">
                    <thead class="bg-paper border-b border-hairline">
                        <tr>
                            <th class="px-6 py-3 text-left text-[11px] font-mono text-ink-muted uppercase tracking-wider">Tool</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono text-ink-muted uppercase tracking-wider">File</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono text-ink-muted uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono text-ink-muted uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-hairline">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-paper/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                    {{ Str::headline($activity->tool_slug) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-muted font-mono">
                                    {{ Str::limit($activity->original_filename, 30) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($activity->status === 'completed')
                                        <span class="px-2 py-0.5 inline-flex text-[11px] font-mono rounded-sm border border-hairline bg-paper text-ink-muted">SELESAI</span>
                                    @elseif($activity->status === 'processing')
                                        <span class="px-2 py-0.5 inline-flex text-[11px] font-mono rounded-sm border border-amber/30 bg-amber/5 text-amber">PROSES</span>
                                    @elseif($activity->status === 'expired')
                                        <span class="px-2 py-0.5 inline-flex text-[11px] font-mono rounded-sm border border-hairline bg-paper text-ink-muted">KEDALUWARSA</span>
                                    @else
                                        <span class="px-2 py-0.5 inline-flex text-[11px] font-mono rounded-sm border border-red-200 bg-red-50 text-red-600">GAGAL</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-muted font-mono">
                                    {{ $activity->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-8 text-center text-gray-500">
                    Belum ada aktivitas. Silakan gunakan tools yang tersedia.
                </div>
            @endif
        </div>
    </div>
</div>
