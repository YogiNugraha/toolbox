@if ($paginator->hasPages())
    <ol class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500 opacity-50 cursor-not-allowed">
                <button type="button" disabled class="flex size-8 items-center justify-center rounded-l-lg text-slate-400 dark:text-navy-300">
                    <x-lucide-chevron-left class="size-4 stroke-2" />
                </button>
            </li>
        @else
            <li class="rounded-l-lg bg-slate-150 dark:bg-navy-500">
                <button type="button" wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="flex size-8 items-center justify-center rounded-l-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                    <x-lucide-chevron-left class="size-4 stroke-2" />
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
                            <button type="button" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-3 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90 leading-tight">
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
                <button type="button" wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="flex size-8 items-center justify-center rounded-r-lg text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                    <x-lucide-chevron-right class="size-4 stroke-2" />
                </button>
            </li>
        @else
            <li class="rounded-r-lg bg-slate-150 dark:bg-navy-500 opacity-50 cursor-not-allowed">
                <button type="button" disabled class="flex size-8 items-center justify-center rounded-r-lg text-slate-400 dark:text-navy-300">
                    <x-lucide-chevron-right class="size-4 stroke-2" />
                </button>
            </li>
        @endif
    </ol>
@endif
