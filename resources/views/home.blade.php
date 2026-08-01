@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ search: '' }">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl mb-4">
            Alat Web <span class="text-indigo-600">Terbaik</span> Anda
        </h1>
        <p class="max-w-xl mx-auto text-xl text-gray-500">
            Kumpulan tools gratis untuk membantu pekerjaan sehari-hari. Mulai dari kompresi gambar hingga konversi dokumen.
        </p>
        
        <div class="max-w-md mx-auto mt-8">
            <div class="relative flex items-center">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input x-model="search" type="text" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out shadow-sm" placeholder="Cari tool yang Anda butuhkan...">
            </div>
        </div>
    </div>

    @php
        $tools = config('tools');
        $categories = collect($tools)->pluck('category')->unique()->values()->all();
    @endphp

    <div class="space-y-12">
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
                
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2 border-b border-gray-200 pb-2">
                    {{ $category }} Tools
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="tool in filteredTools" :key="tool.slug">
                        <a :href="'/tool/' + tool.slug" class="group block relative rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition-all hover:shadow-md hover:ring-indigo-500">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <!-- Use simple heroicon svg based on tool.icon. For simplicity just showing generic icon if matched -->
                                    <template x-if="tool.icon === 'photo'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </template>
                                    <template x-if="tool.icon === 'arrows-right-left'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </template>
                                    <template x-if="tool.icon === 'document-text'">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </template>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900" x-text="tool.name"></h3>
                            </div>
                            <p class="text-sm text-gray-500 mb-4" x-text="tool.description"></p>
                            <div class="text-indigo-600 text-sm font-semibold flex items-center group-hover:text-indigo-700 transition-colors">
                                Gunakan Tool 
                                <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
