<div>
    <div class="flex items-center justify-between mt-5 mb-5">
        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Pengaturan Global</h2>
    </div>
    
    <div class="card p-4 sm:p-5 max-w-4xl">
        <div class="mb-5 border-b border-slate-200 pb-5 dark:border-navy-500">
            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Identitas Utama</h2>
            <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Kelola identitas utama website dan konfigurasi dasar.</p>
        </div>

        <form wire:submit="save">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-700">
                <div>
                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Nama Brand / Website</span>
                        <input wire:model="brand_name" type="text" placeholder="Contoh: Mudah Kerja" class="form-input w-full md:w-1/2 rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                    </label>
                    @error('brand_name') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
