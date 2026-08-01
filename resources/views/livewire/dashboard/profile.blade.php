<div>
    @section('page_title', 'Profil Saya')

    <div class="bg-white rounded-sm border border-hairline overflow-hidden">
        <div class="p-8">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-24 h-24 rounded-full bg-amber/15 flex items-center justify-center text-amber font-mono font-bold text-4xl border border-amber/40">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-display font-bold text-ink">{{ auth()->user()->name }}</h2>
                    <p class="text-ink-muted">{{ auth()->user()->email }}</p>
                    <p class="text-xs text-ink-muted/70 mt-1 font-mono uppercase tracking-wider">Bergabung sejak {{ auth()->user()->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="border-t border-hairline pt-6">
                <h3 class="text-lg font-display font-semibold text-ink mb-4">Statistik Penggunaan</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-paper p-4 rounded-sm border border-hairline">
                        <p class="text-sm font-medium text-ink-muted mb-1">Total Aktivitas</p>
                        <p class="text-2xl font-mono font-bold text-ink">{{ auth()->user()->activities()->count() }}</p>
                    </div>
                    <div class="bg-paper p-4 rounded-sm border border-hairline">
                        <p class="text-sm font-medium text-ink-muted mb-1">Aktivitas Selesai</p>
                        <p class="text-2xl font-mono font-bold text-ink">{{ auth()->user()->activities()->where('status', 'completed')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
