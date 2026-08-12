@extends('layouts.app')

@section('page_title', 'Home - Alat Web Terbaik Anda')

@section('content')
<div class="py-12" x-data="{ search: '' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Hero Section --}}
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold tracking-tight text-slate-800 dark:text-navy-50 sm:text-5xl lg:text-6xl mb-4">
                Alat Web <span class="text-primary dark:text-accent-light">Terbaik</span> Anda
            </h1>
            <p class="max-w-xl mx-auto text-lg text-slate-500 dark:text-navy-300">
                Kumpulan tools gratis untuk membantu pekerjaan sehari-hari. Mulai dari kompresi gambar hingga konversi dokumen.
            </p>
            
            <div class="max-w-lg mx-auto mt-8">
                <label class="relative flex w-full">
                    <input x-model="search" type="text" placeholder="Cari tool yang Anda butuhkan..."
                        class="form-input h-12 w-full rounded-full border border-slate-300 bg-white px-4 py-3 pl-11 shadow-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent" />
                    <span class="pointer-events-none absolute flex h-full w-12 items-center justify-center text-slate-400 dark:text-navy-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                </label>
            </div>
        </div>

        @php
            $tools = config('tools');
            $categories = collect($tools)->pluck('category')->unique()->values()->all();
        @endphp

        <div class="space-y-16">
            @foreach ($categories as $category)
                <div x-data="{ 
                    categoryTools: {{ json_encode(array_filter($tools, fn($t) => $t['category'] === $category)) }},
                    get filteredTools() {
                        return Object.values(this.categoryTools).filter(tool => {
                            return tool.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                   tool.description.toLowerCase().includes(this.search.toLowerCase());
                        });
                    }
                }" x-show="filteredTools.length > 0">
                    
                    <div class="flex items-center space-x-3 mb-8 border-b border-slate-200 pb-3 dark:border-navy-500">
                        <h2 class="text-2xl font-bold text-slate-700 dark:text-navy-100 uppercase tracking-wide">
                            {{ $category }} Tools
                        </h2>
                        <div class="h-px flex-1 bg-slate-200 dark:bg-navy-500"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="tool in filteredTools" :key="tool.slug">
                            <a :href="'/tool/' + tool.slug" class="card group flex flex-col justify-between p-6 transition-all hover:shadow-lg hover:shadow-primary/10 hover:border-primary/50 dark:hover:border-accent/50 dark:hover:shadow-accent/10 border border-slate-150 dark:border-navy-600">
                                <div>
                                    <div class="flex items-center space-x-4 mb-5">
                                        <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300 dark:bg-accent-light/10 dark:text-accent-light dark:group-hover:bg-accent dark:group-hover:text-white">
                                            {{-- Use simple heroicon svg based on tool.icon. For simplicity just showing generic icon if matched --}}
                                            <template x-if="tool.icon === 'photo'">
                                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </template>
                                            <template x-if="tool.icon === 'arrows-right-left'">
                                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                            </template>
                                            <template x-if="tool.icon === 'document-text'">
                                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </template>
                                        </div>
                                        <h3 class="text-xl font-semibold text-slate-800 dark:text-navy-50" x-text="tool.name"></h3>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-navy-300" x-text="tool.description"></p>
                                </div>
                                <div class="mt-6 flex items-center text-sm font-semibold text-primary group-hover:text-primary-focus transition-colors dark:text-accent-light dark:group-hover:text-accent">
                                    <span>Gunakan Tool</span>
                                    <svg class="size-4 ml-1.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
