<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-display font-bold text-ink">Pengaturan Global</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola identitas utama website dan konfigurasi dasar.</p>
    </div>

    <div class="bg-white rounded-sm border border-hairline p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="p-4 border border-hairline rounded-sm bg-paper-light">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-ink mb-2">Nama Brand / Website</label>
                    <input type="text" wire:model="brand_name" class="w-full md:w-1/2 rounded-sm border-hairline focus:border-amber focus:ring-amber/20 text-sm" placeholder="Contoh: Mudah Kerja">
                    @error('brand_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-hairline">
                <button type="submit" class="bg-amber hover:bg-amber/90 text-ink font-medium py-2 px-6 rounded-sm text-sm inline-flex items-center gap-2">
                    <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
