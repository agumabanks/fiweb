
@if ($paginator->hasPages())
    <ul class="pagination justify-content-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">
                    <i class="fas fa-angle-left"></i> @lang('pagination.previous')
                </span>
            </li>
        @else
            <li class="page-item">
                <a href="javascript:void(0)" data-page="{{ $paginator->currentPage() - 1 }}" class="page-link">
                    <i class="fas fa-angle-left"></i> @lang('pagination.previous')
                </a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a href="javascript:void(0)" data-page="{{ $page }}" class="page-link">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a href="javascript:void(0)" data-page="{{ $paginator->currentPage() + 1 }}" class="page-link">
                    @lang('pagination.next') <i class="fas fa-angle-right"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">
                    @lang('pagination.next') <i class="fas fa-angle-right"></i>
                </span>
            </li>
        @endif
    </ul>
@endif
