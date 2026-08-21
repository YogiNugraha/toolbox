@if ($paginator->hasPages())
    <ol class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500 opacity-50 cursor-not-allowed">
                <button type="button" disabled class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </li>
        @else
            <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="bg-slate-150 dark:bg-navy-500">
                    <span class="flex h-8 min-w-[2rem] items-center justify-center px-2 text-slate-400 dark:text-navy-300 leading-tight text-xs">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="bg-slate-150 dark:bg-navy-500">
                            <button type="button" class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg bg-primary px-3 text-xs font-semibold text-white transition-colors dark:bg-accent leading-tight shadow-sm">
                                {{ $page }}
                            </button>
                        </li>
                    @else
                        <li class="bg-slate-150 dark:bg-navy-500">
                            <button type="button" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-3 text-xs text-slate-700 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90 leading-tight">
                                {{ $page }}
                            </button>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500">
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="flex size-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </li>
        @else
            <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500 opacity-50 cursor-not-allowed">
                <button type="button" disabled class="flex size-8 items-center justify-center rounded-lg text-slate-400 dark:text-navy-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </li>
        @endif
    </ol>
@endif
