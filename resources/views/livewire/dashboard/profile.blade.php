<div>
    @section('page_title', 'Profil Saya')

    <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-4xl border-4 border-indigo-50">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-500">{{ auth()->user()->email }}</p>
                    <p class="text-xs text-gray-400 mt-1">Bergabung sejak {{ auth()->user()->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Penggunaan</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Total Aktivitas</p>
                        <p class="text-2xl font-bold text-gray-800">{{ auth()->user()->activities()->count() }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Aktivitas Selesai</p>
                        <p class="text-2xl font-bold text-green-600">{{ auth()->user()->activities()->where('status', 'completed')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
