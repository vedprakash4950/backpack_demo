<form method="GET" action="{{ route('product.index') }}" class="d-flex flex-wrap align-items-end gap-2 mb-2" aria-label="Filter products">
    <div>
        <label for="product-filter-status" class="form-label mb-1">Status</label>
        <select id="product-filter-status" name="status" class="form-select form-select-sm">
            <option value="">All statuses</option>
            <option value="1" @selected((string) request('status') === '1')>Active</option>
            <option value="0" @selected((string) request('status') === '0')>Inactive</option>
        </select>
    </div>

    <div>
        <label for="product-filter-category" class="form-label mb-1">Category</label>
        <select id="product-filter-category" name="category_id" class="form-select form-select-sm">
            <option value="">All categories</option>
            @foreach ($button->meta['categories'] as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="product-filter-stock-min" class="form-label mb-1">Stock from</label>
        <input id="product-filter-stock-min" type="number" min="0" name="stock_min" value="{{ request('stock_min') }}" class="form-control form-control-sm" placeholder="Min">
    </div>

    <div>
        <label for="product-filter-stock-max" class="form-label mb-1">Stock to</label>
        <input id="product-filter-stock-max" type="number" min="0" name="stock_max" value="{{ request('stock_max') }}" class="form-control form-control-sm" placeholder="Max">
    </div>

    <button type="submit" class="btn btn-sm btn-primary">Apply filters</button>

    <a href="{{ route('backpack.product.export.csv') }}" data-export-url="{{ route('backpack.product.export.csv') }}" class="btn btn-sm btn-success js-product-export">
        <i class="la la-file-csv" aria-hidden="true"></i> Export CSV
    </a>
    <a href="{{ route('backpack.product.export.excel') }}" data-export-url="{{ route('backpack.product.export.excel') }}" class="btn btn-sm btn-success js-product-export">
        <i class="la la-file-excel" aria-hidden="true"></i> Export Excel
    </a>
</form>

@push('after_scripts')
<script>
    document.addEventListener('click', function (event) {
        const link = event.target.closest('.js-product-export');
        if (!link) return;

        event.preventDefault();

        const table = window.crud?.tables?.crudTable ?? window.crud?.table;
        const url = new URL(link.dataset.exportUrl, window.location.origin);
        const listUrl = table?.ajax?.url();

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
