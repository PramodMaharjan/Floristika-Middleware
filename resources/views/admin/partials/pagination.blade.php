<nav aria-label="Page navigation" class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-secondary">
            @if ($orders->isNotEmpty())
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} results
            @endif
        </div>
        <div class="d-flex">
            <a href="{{ $orders->previousPageUrl() }}" class="btn btn-outline-secondary me-2 {{ $orders->onFirstPage() ? 'disabled' : '' }}">
                &laquo; Previous
            </a>
            <a href="{{ $orders->nextPageUrl() }}" class="btn btn-outline-secondary {{ $orders->hasMorePages() ? '' : 'disabled' }}">
                Next &raquo;
            </a>
        </div>
    </div>
</nav>