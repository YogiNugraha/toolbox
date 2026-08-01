<div>
    @section('page_title', 'Riwayat Aktivitas')

    <div class="bg-white rounded-sm border border-hairline overflow-hidden">
        @if ($activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-hairline">
                    <thead class="bg-paper border-b border-hairline">
                        <tr>
                            <th class="px-6 py-3 text-left text-[11px] font-mono font-medium text-ink-muted uppercase tracking-wider">
                                Tool</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono font-medium text-ink-muted uppercase tracking-wider">
                                Detail File</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono font-medium text-ink-muted uppercase tracking-wider">
                                Efisiensi</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono font-medium text-ink-muted uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-[11px] font-mono font-medium text-ink-muted uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-6 py-3 text-right text-[11px] font-mono font-medium text-ink-muted uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-hairline">
                        @foreach ($activities as $activity)
                            <tr class="hover:bg-paper/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-ink">
                                        {{ Str::headline($activity->tool_slug) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-ink font-mono truncate max-w-50"
                                        title="{{ $activity->original_filename }}">
                                        {{ Str::limit($activity->original_filename, 30) }}
                                    </div>
                                    <div class="text-[11px] font-mono text-ink-muted mt-1">
                                        Asli:
                                        {{ $activity->original_size ? \Illuminate\Support\Number::fileSize($activity->original_size) : '-' }}
                                        @if ($activity->result_size)
                                            <br>Hasil:
                                            {{ \Illuminate\Support\Number::fileSize($activity->result_size) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink-muted font-mono">
                                    @if ($activity->original_size && $activity->result_size && $activity->original_size > $activity->result_size)
                                        <span class="text-amber font-medium">
                                            -{{ round((($activity->original_size - $activity->result_size) / $activity->original_size) * 100) }}%
                                        </span>
                                    @else
                                        -
                                    @endif
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
                                    {{ $activity->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($activity->status === 'completed' && $activity->result_path)
                                        <a href="{{ route('activity.download', $activity->id) }}"
                                            class="text-amber hover:text-amber bg-amber/10 border border-amber/20 px-3 py-1 rounded-sm hover:bg-amber/20 transition-colors">Download</a>
                                    @elseif($activity->status === 'expired')
                                        <span class="text-ink-muted">Tidak tersedia</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-hairline">
                {{ $activities->links() }}
            </div>
        @else
            <div class="p-8 text-center text-ink-muted">
                Belum ada aktivitas.
            </div>
        @endif
    </div>
</div>
