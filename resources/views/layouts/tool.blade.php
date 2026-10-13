@extends('layouts.dashboard')

@section('page_title', $tool['name'])
@section('page_breadcrumb', $tool['name'])

@section('content')
<div class="w-full space-y-10 sm:space-y-12">
    {{-- Main Tool Execution Area --}}
    <div class="w-full">
        @yield('tool_content')
    </div>

    {{-- Similar & Related Category Tools Section --}}
    @php
        $currentSlug = $tool['slug'] ?? '';
        $currentCategory = $tool['category'] ?? 'Document';
        $user = auth()->user();
        $isProUser = $user && $user->isSubscribed();
        $isAdmin = $user && $user->is_admin;

        // 1. Get tools in the same category first (excluding current tool)
        $relatedTools = \App\Models\Tool::where('is_active', true)
            ->where('slug', '!=', $currentSlug)
            ->where('category', $currentCategory)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        // 2. If fewer than 4 tools in the same category, pad with other popular tools
        if ($relatedTools->count() < 4) {
            $excludeIds = $relatedTools->pluck('id')->push($tool['id'] ?? 0)->filter();
            $additionalTools = \App\Models\Tool::where('is_active', true)
                ->whereNotIn('id', $excludeIds)
                ->orderBy('total_usage_count', 'desc')
                ->orderBy('sort_order')
                ->take(4 - $relatedTools->count())
                ->get();
            $relatedTools = $relatedTools->concat($additionalTools);
        }

        // Category Display Name
        $catDisplayName = $currentCategory;
        if (strtolower($currentCategory) === 'image') $catDisplayName = 'Gambar & Foto';
        if (strtolower($currentCategory) === 'document') $catDisplayName = 'Dokumen & PDF';
    @endphp

    @if($relatedTools->isNotEmpty())
        <div class="pt-6 sm:pt-8 border-t border-slate-200 dark:border-navy-600">
            {{-- Section Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="mask is-squircle flex size-8 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            @if(stripos($currentCategory, 'image') !== false || stripos($currentCategory, 'foto') !== false)
                                <x-lucide-image class="size-4.5" />
                            @elseif(stripos($currentCategory, 'pdf') !== false || stripos($currentCategory, 'doc') !== false)
                                <x-lucide-file-text class="size-4.5" />
                            @else
                                <x-lucide-boxes class="size-4.5" />
                            @endif
                        </div>
                        <h3 class="text-base sm:text-lg font-medium tracking-wide text-slate-800 dark:text-navy-50">
                            Tools Serupa & Kategori {{ $catDisplayName }}
                        </h3>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-navy-300">
                        Pilihan alat bantu produktivitas lainnya yang mungkin Anda butuhkan untuk mengolah file secara instan.
                    </p>
                </div>

                <a 
                    href="{{ route('dashboard') }}" 
                    wire:navigate
                    class="self-start sm:self-auto inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent group transition-colors"
                >
                    <span>Lihat Semua Tools</span>
                    <x-lucide-arrow-right class="size-3.5 transition-transform group-hover:translate-x-1" />
                </a>
            </div>

            {{-- 4-Columns Card Grid --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5">
                @foreach($relatedTools as $relTool)
                    @php
                        $isLocked = $relTool->is_pro_only && !$isProUser && !$isAdmin;
                    @endphp
                    <div class="card flex flex-col justify-between h-full group">
                        {{-- Top Image / Thumbnail --}}
                        <div class="flex h-36 items-center justify-center p-4 bg-slate-50 dark:bg-navy-800 rounded-t-lg relative overflow-hidden">
                            <img
                                class="max-h-28 max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                src="{{ $relTool->image_url ?: asset('images/illustrations/creativedesign.svg') }}"
                                alt="{{ $relTool->name }}"
                            />

                            {{-- Badges Top Right --}}
                            <div class="absolute top-2.5 right-2.5 flex items-center gap-1">
                                @if($relTool->is_maintenance)
                                    <span class="badge rounded-full bg-warning/10 text-warning dark:bg-warning/15 text-xs font-medium px-2 py-0.5">
                                        Maintenance
                                    </span>
                                @elseif($relTool->is_pro_only)
                                    <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light text-xs font-medium px-2 py-0.5 uppercase">
                                        PRO
                                    </span>
                                @elseif($relTool->badge === 'HOT')
                                    <span class="badge rounded-full bg-error/10 text-error dark:bg-error/15 text-xs font-medium px-2 py-0.5 uppercase">
                                        HOT
                                    </span>
                                @elseif($relTool->badge === 'NEW')
                                    <span class="badge rounded-full bg-success/10 text-success dark:bg-success/15 text-xs font-medium px-2 py-0.5 uppercase">
                                        NEW
                                    </span>
                                @elseif($relTool->badge === 'PRO')
                                    <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light text-xs font-medium px-2 py-0.5 uppercase">
                                        PRO
                                    </span>
                                @elseif($relTool->badge)
                                    <span class="badge rounded-full bg-info/10 text-info dark:bg-info/15 text-xs font-medium px-2 py-0.5 uppercase">
                                        {{ $relTool->badge }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="flex flex-1 flex-col justify-between p-4 text-center">
                            <div>
                                <h4 class="text-sm font-medium tracking-wide text-slate-700 dark:text-navy-100 line-clamp-1">
                                    {{ $relTool->name }}
                                </h4>
                                <p class="pt-1.5 text-xs text-slate-500 dark:text-navy-300 line-clamp-2 leading-relaxed">
                                    {{ $relTool->description }}
                                </p>
                            </div>
                            <div class="pt-4">
                                <a
                                    href="{{ route('tool', $relTool->slug) }}" 
                                    wire:navigate
                                    class="btn h-9 w-full {{ $isLocked ? 'bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90' : 'bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90' }} rounded-lg text-xs flex items-center justify-center gap-1.5"
                                >
                                    @if($isLocked)
                                        <x-lucide-lock class="size-3.5" />
                                        <span>Buka Tool (PRO)</span>
                                    @else
                                        <span>Buka Tool</span>
                                        <x-lucide-arrow-up-right class="size-3.5" />
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
