<div>
    @section('page_title', 'Riwayat Aktivitas')

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tool</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efisiensi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($activities as $activity)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ Str::headline($activity->tool_slug) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-medium truncate max-w-[200px]" title="{{ $activity->original_filename }}">
                                        {{ Str::limit($activity->original_filename, 30) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Asli: {{ $activity->original_size ? \Illuminate\Support\Number::fileSize($activity->original_size) : '-' }}
                                        @if($activity->result_size)
                                            <br>Hasil: {{ \Illuminate\Support\Number::fileSize($activity->result_size) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($activity->original_size && $activity->result_size && $activity->original_size > $activity->result_size)
                                        <span class="text-green-600 font-medium">
                                            -{{ round((($activity->original_size - $activity->result_size) / $activity->original_size) * 100) }}%
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($activity->status === 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                    @elseif($activity->status === 'processing')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Proses</span>
                                    @elseif($activity->status === 'expired')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Kedaluwarsa</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $activity->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($activity->status === 'completed' && $activity->result_path)
                                        <a href="{{ route('activity.download', $activity->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded hover:bg-indigo-100 transition-colors">Download</a>
                                    @elseif($activity->status === 'expired')
                                        <span class="text-gray-400">Tidak tersedia</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $activities->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                Belum ada aktivitas.
            </div>
        @endif
    </div>
</div>
