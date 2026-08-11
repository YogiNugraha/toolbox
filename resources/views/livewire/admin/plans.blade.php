<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="font-mono text-2xl font-bold text-ink">Kelola Paket</h1>
        <button wire:click="create" class="px-4 py-2 bg-amber text-paper font-bold font-mono rounded hover:bg-amber-600 transition-colors">
            + Tambah Paket
        </button>
    </div>

    <div class="bg-paper border border-hairline rounded-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-paper-light border-b border-hairline font-mono text-sm text-ink-muted">
                    <th class="p-4 font-normal">Nama / Slug</th>
                    <th class="p-4 font-normal">Harga</th>
                    <th class="p-4 font-normal">Durasi</th>
                    <th class="p-4 font-normal">Urutan</th>
                    <th class="p-4 font-normal">Status</th>
                    <th class="p-4 font-normal text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr class="border-b border-hairline last:border-0 hover:bg-paper-light transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-ink">{{ $plan->name }}</div>
                            <div class="text-xs text-ink-muted font-mono">{{ $plan->slug }}</div>
                            @if($plan->is_default)
                                <span class="inline-block mt-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-sm font-mono">Default (Free)</span>
                            @endif
                        </td>
                        <td class="p-4 font-mono">
                            Rp {{ number_format($plan->price, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-ink-muted">
                            {{ $plan->duration_days ? $plan->duration_days . ' hari' : 'Selamanya' }}
                        </td>
                        <td class="p-4 text-ink-muted text-sm">
                            {{ $plan->sort_order }}
                        </td>
                        <td class="p-4">
                            <button wire:click="toggleActive({{ $plan->id }})" class="relative inline-flex items-center h-5 w-9 rounded-full transition-colors {{ $plan->is_active ? 'bg-amber' : 'bg-gray-300' }}">
                                <span class="inline-block w-3 h-3 transform rounded-full bg-white transition-transform {{ $plan->is_active ? 'translate-x-5' : 'translate-x-1' }}"></span>
                            </button>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button wire:click="edit({{ $plan->id }})" class="text-sm font-mono text-amber hover:underline">Edit</button>
                            <button wire:click="confirmDelete({{ $plan->id }})" class="text-sm font-mono text-red-500 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-ink-muted">Belum ada paket.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-paper w-full max-w-3xl rounded-sm shadow-xl border border-hairline max-h-[90vh] flex flex-col">
            
            <div class="p-6 border-b border-hairline flex justify-between items-center bg-paper-light">
                <h2 class="font-mono text-xl font-bold text-ink">{{ $planId ? 'Edit Paket' : 'Tambah Paket Baru' }}</h2>
                <button wire:click="$set('isModalOpen', false)" class="text-ink-muted hover:text-ink">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-ink mb-1">Nama Paket</label>
                        <input type="text" wire:model="name" class="w-full border-hairline bg-paper-light rounded-sm px-3 py-2 text-ink focus:border-amber focus:ring-0">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-ink mb-1">Slug (Identifier)</label>
                        <input type="text" wire:model="slug" {{ $planId ? 'readonly' : '' }} class="w-full border-hairline {{ $planId ? 'bg-gray-100 text-gray-500' : 'bg-paper-light text-ink' }} rounded-sm px-3 py-2 focus:border-amber focus:ring-0">
                        @error('slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-ink mb-1">Harga (Rp)</label>
                        <input type="number" wire:model="price" class="w-full border-hairline bg-paper-light rounded-sm px-3 py-2 text-ink focus:border-amber focus:ring-0">
                        @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-ink mb-1">Durasi (Hari)</label>
                        <input type="number" wire:model="duration_days" placeholder="Kosong = Selamanya" class="w-full border-hairline bg-paper-light rounded-sm px-3 py-2 text-ink focus:border-amber focus:ring-0">
                        @error('duration_days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-ink mb-1">Deskripsi Singkat</label>
                    <textarea wire:model="description" rows="2" class="w-full border-hairline bg-paper-light rounded-sm px-3 py-2 text-ink focus:border-amber focus:ring-0"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-bold text-ink mb-1">Urutan Tampil</label>
                        <input type="number" wire:model="sort_order" class="w-full border-hairline bg-paper-light rounded-sm px-3 py-2 text-ink focus:border-amber focus:ring-0">
                    </div>
                    <div class="flex items-center mt-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_default" class="rounded-sm border-hairline text-amber focus:ring-amber">
                            <span class="ml-2 text-sm text-ink font-bold">Jadikan Default (Free)</span>
                        </label>
                    </div>
                    <div class="flex items-center mt-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded-sm border-hairline text-amber focus:ring-amber">
                            <span class="ml-2 text-sm text-ink font-bold">Paket Aktif</span>
                        </label>
                    </div>
                </div>

                <hr class="border-hairline mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-mono text-lg font-bold text-ink">Fitur Tambahan</h3>
                    <button wire:click.prevent="addFeature" type="button" class="text-sm px-3 py-1 bg-ink text-white font-mono rounded hover:bg-ink/80 transition-colors">
                        + Tambah Fitur
                    </button>
                </div>
                
                <div class="space-y-3 mb-8">
                    @forelse($features as $index => $feature)
                        <div class="flex gap-2 items-center">
                            <span class="text-amber font-bold">✓</span>
                            <input type="text" wire:model="features.{{ $index }}" placeholder="Contoh: Priority Support" class="flex-1 border-hairline bg-paper-light rounded-sm px-3 py-2 text-ink focus:border-amber focus:ring-0 text-sm">
                            <button wire:click.prevent="removeFeature({{ $index }})" type="button" class="text-red-500 hover:text-red-700 font-bold px-2">&times;</button>
                        </div>
                    @empty
                        <div class="text-sm text-ink-muted italic p-3 border border-dashed border-hairline rounded-sm bg-paper-light">
                            Belum ada fitur tambahan. Klik tombol di atas untuk menambahkan.
                        </div>
                    @endforelse
                </div>

                <hr class="border-hairline mb-6">
                <h3 class="font-mono text-lg font-bold text-ink mb-4">Batasan Tools (Limits)</h3>

                @foreach($toolsConfig as $tool)
                    <div class="mb-6 p-4 border border-hairline rounded-sm bg-paper-light">
                        <h4 class="font-bold text-amber mb-3">{{ $tool['name'] }}</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-ink-muted mb-1">Kuota Harian (Kosong = Unlimited)</label>
                                <input type="number" wire:model="limits.{{ $tool['slug'] }}.daily_quota" class="w-full border-hairline bg-paper rounded-sm px-3 py-1 text-sm text-ink focus:border-amber focus:ring-0">
                            </div>
                            
                            @if($tool['slug'] === 'pdf-to-word')
                            <div>
                                <label class="block text-xs font-bold text-ink-muted mb-1">Max Ukuran File (MB)</label>
                                <input type="number" wire:model="limits.{{ $tool['slug'] }}.max_file_size_mb" class="w-full border-hairline bg-paper rounded-sm px-3 py-1 text-sm text-ink focus:border-amber focus:ring-0">
                            </div>
                            @endif
                        </div>

                        @if($tool['slug'] === 'compress-image')
                        <div class="mt-3">
                            <label class="block text-xs font-bold text-ink-muted mb-1">Fitur Terkunci (Check untuk mengunci)</label>
                            <label class="inline-flex items-center mt-1">
                                <input type="checkbox" wire:model="limits.{{ $tool['slug'] }}.locked_features" value="preset_custom" class="rounded-sm border-hairline text-amber focus:ring-amber">
                                <span class="ml-2 text-sm text-ink">Preset Custom</span>
                            </label>
                        </div>
                        @endif
                    </div>
                @endforeach

            </div>
            
            <div class="p-6 border-t border-hairline flex justify-end gap-3 bg-paper-light rounded-b-sm">
                <button wire:click="$set('isModalOpen', false)" class="px-4 py-2 text-ink-muted font-mono hover:text-ink transition-colors">Batal</button>
                <button wire:click="save" class="px-6 py-2 bg-amber text-paper font-bold font-mono rounded hover:bg-amber-600 transition-colors">Simpan Paket</button>
            </div>
            
        </div>
    </div>
    @endif
</div>
