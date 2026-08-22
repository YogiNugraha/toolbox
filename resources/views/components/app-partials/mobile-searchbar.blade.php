@php
    $headerTools = \App\Models\Tool::getActiveTools()->map(function($t) {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'slug' => $t->slug,
            'description' => $t->description,
            'category' => $t->category,
            'badge' => $t->badge,
            'is_highlighted' => (bool) $t->is_highlighted,
            'is_maintenance' => (bool) $t->is_maintenance,
            'image_url' => $t->image_url,
        ];
    });
    $headerCategories = $headerTools->pluck('category')->unique()->values();
@endphp

<div x-show="$store.breakpoints.isXs && $store.global.isSearchbarActive" 
     x-transition:enter="easy-out transition-all"
     x-transition:enter-start="opacity-0 scale-105" 
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="easy-in transition-all" 
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     x-data="{
         searchQuery: '',
         selectedCat: 'all',
         tools: {{ json_encode($headerTools) }},
         categories: {{ json_encode($headerCategories) }},
         get filteredTools() {
             const q = this.searchQuery.trim().toLowerCase();
             return this.tools.filter(t => {
                 const matchCat = this.selectedCat === 'all' || t.category === this.selectedCat;
                 const matchQuery = !q || 
                     t.name.toLowerCase().includes(q) || 
                     t.slug.toLowerCase().includes(q) || 
                     (t.description && t.description.toLowerCase().includes(q)) ||
                     (t.category && t.category.toLowerCase().includes(q));
                 return matchCat && matchQuery;
             });
         }
     }"
     class="fixed inset-0 z-100 flex flex-col bg-white dark:bg-navy-700 sm:hidden">
    
    {{-- Top Search Header --}}
    <div class="flex items-center space-x-2 bg-slate-100 px-3 py-2.5 dark:bg-navy-800 border-b border-slate-200 dark:border-navy-600">
        <button
            class="btn -ml-1.5 size-8 shrink-0 rounded-full p-0 text-slate-600 hover:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-100 dark:hover:bg-navy-300/20 dark:active:bg-navy-300/25"
            @click="$store.global.isSearchbarActive = false">
            <x-lucide-arrow-left class="size-5" />
        </button>
        <div class="relative flex-1">
            <input 
                x-model="searchQuery"
                x-effect="$store.global.isSearchbarActive && $nextTick(() => $el.focus() );"
                class="form-input h-9 w-full rounded-lg bg-white px-3 pr-8 text-xs text-slate-800 placeholder-slate-400 dark:bg-navy-900 dark:text-navy-100 dark:placeholder-navy-300 border border-slate-200 dark:border-navy-600"
                type="text"
                placeholder="Cari tool di sini..." 
            />
            <template x-if="searchQuery">
                <button @click="searchQuery = ''" type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-navy-100">
                    <x-lucide-x class="size-4" />
                </button>
            </template>
        </div>
    </div>

    {{-- Category Pills --}}
    <div class="is-scrollbar-hidden flex shrink-0 overflow-x-auto bg-slate-50 px-3 py-2 text-slate-600 dark:bg-navy-800 dark:text-navy-200 gap-1.5 border-b border-slate-150 dark:border-navy-600">
        <button 
            @click="selectedCat = 'all'"
            :class="selectedCat === 'all' ? 'bg-primary text-white dark:bg-accent font-bold shadow-xs' : 'bg-white text-slate-700 dark:bg-navy-700 dark:text-navy-100 border border-slate-200 dark:border-navy-600'"
            class="btn h-7 rounded-lg px-2.5 text-xs shrink-0">
            Semua (<span x-text="tools.length"></span>)
        </button>
        <template x-for="cat in categories" :key="cat">
            <button 
                @click="selectedCat = cat"
                :class="selectedCat === cat ? 'bg-primary text-white dark:bg-accent font-bold shadow-xs' : 'bg-white text-slate-700 dark:bg-navy-700 dark:text-navy-100 border border-slate-200 dark:border-navy-600'"
                class="btn h-7 rounded-lg px-2.5 text-xs shrink-0"
                x-text="cat">
            </button>
        </template>
    </div>

    {{-- Tool Results List --}}
    <div class="is-scrollbar-hidden overflow-y-auto overscroll-contain flex-1 p-3 space-y-1.5">
        <template x-for="tool in filteredTools" :key="tool.id">
            <a :href="'/tool/' + tool.slug" 
               @click="$store.global.isSearchbarActive = false"
               class="flex items-center justify-between p-2.5 rounded-xl border border-slate-150 dark:border-navy-600 bg-slate-50/50 dark:bg-navy-800/50 hover:bg-slate-100 dark:hover:bg-navy-600 transition-colors">
                <div class="flex items-center space-x-3 min-w-0">
                    {{-- Tool Thumbnail / Icon --}}
                    <div class="size-10 rounded-lg overflow-hidden bg-white dark:bg-navy-700 p-1 border border-slate-200 dark:border-navy-600 shrink-0 flex items-center justify-center shadow-xs">
                        <template x-if="tool.image_url">
                            <img :src="tool.image_url" :alt="tool.name" class="size-full object-contain" />
                        </template>
                        <template x-if="!tool.image_url">
                            <x-lucide-wrench class="size-5 text-primary dark:text-accent-light" />
                        </template>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold text-slate-800 dark:text-navy-100 text-xs truncate" x-text="tool.name"></span>
                            <span class="badge rounded-full bg-slate-200/80 text-slate-700 dark:bg-navy-700 dark:text-navy-200 text-[9px] font-semibold px-1.5 py-0.2" x-text="tool.category"></span>
                            <template x-if="tool.badge">
                                <span class="badge rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-[9px] font-black px-1.5 py-0.2" x-text="tool.badge"></span>
                            </template>
                            <template x-if="tool.is_maintenance">
                                <span class="badge rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-[9px] font-bold px-1.5 py-0.2">Maintenance</span>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-navy-300 truncate mt-0.5" x-text="tool.description || ('Buka alat ' + tool.name)"></p>
                    </div>
                </div>

                <div class="shrink-0 pl-2 text-slate-300 dark:text-navy-400">
                    <x-lucide-chevron-right class="size-4" />
                </div>
            </a>
        </template>

        {{-- Empty Search Result --}}
        <div x-show="filteredTools.length === 0" class="py-12 text-center text-xs text-slate-400 dark:text-navy-300">
            <x-lucide-search class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2 opacity-60" />
            <p class="font-bold text-slate-700 dark:text-navy-100 text-sm">Tool Tidak Ditemukan</p>
            <p class="text-xs mt-1">Tidak ada tool yang cocok dengan kriteria pencarian Anda.</p>
        </div>
    </div>
</div>
