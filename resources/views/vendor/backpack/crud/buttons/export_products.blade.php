<div class="btn-group" role="group" aria-label="Export products">
    <a href="{{ route('backpack.product.export.csv') }}" data-export-url="{{ route('backpack.product.export.csv') }}" class="btn btn-sm btn-outline-success js-product-export">
        <i class="la la-file-csv" aria-hidden="true"></i> Export CSV
    </a>
    <a href="{{ route('backpack.product.export.excel') }}" data-export-url="{{ route('backpack.product.export.excel') }}" class="btn btn-sm btn-outline-success js-product-export">
        <i class="la la-file-excel" aria-hidden="true"></i> Export Excel
    </a>
</div>

@push('after_scripts')
<script>
    document.addEventListener('click', function (event) {
        const link = event.target.closest('.js-product-export');
        if (!link) return;

        event.preventDefault();

        const table = window.crud?.tables?.crudTable ?? window.crud?.table;
        const url = new URL(link.dataset.exportUrl, window.location.origin);
        const listUrl = table?.ajax?.url();

        // Copy active Backpack filters from the AJAX URL.
        if (listUrl) {
            new URL(listUrl, window.location.origin).searchParams.forEach((value, key) => url.searchParams.append(key, value));
        }

        const searchInput = document.querySelector('#datatable_search_stack_crudTable input.datatable-search-input');
        const search = (typeof table?.search === 'function' && table.search()) || searchInput?.value?.trim() || '';

        url.searchParams.delete('search[value]');
        url.searchParams.delete('q');
        if (search) url.searchParams.set('search[value]', search);

        window.location.assign(url.toString());
    });
</script>
@endpush
