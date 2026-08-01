<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total File Diproses</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $totalFiles }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Penyimpanan Dihemat</p>
                <h3 class="text-3xl font-bold text-green-600">{{ \Illuminate\Support\Number::fileSize($totalSaved) }}</h3>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Akses Cepat Tools</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($tools as $tool)
                <a href="{{ route('tool', $tool['slug']) }}" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group flex items-start gap-4">
                    <div class="w-10 h-10 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors">{{ $tool['name'] }}</h3>
                        <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $tool['description'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">Aktivitas Terakhir</h2>
            <a href="{{ route('history') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Lihat Semua &rarr;</a>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if($activities->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tool</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($activities as $activity)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ Str::headline($activity->tool_slug) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ Str::limit($activity->original_filename, 30) }}
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
