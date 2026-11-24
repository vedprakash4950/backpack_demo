<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Exports\ProductsExport;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('product', 'products');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::button('export_products')
            ->stack('top')
            ->meta(['categories' => Category::query()->orderBy('name')->get()])
            ->view('vendor.backpack.crud.buttons.export_products');
        CRUD::setFromDb();

        // Remove auto-generated columns
        $this->crud->removeColumn('status');
        $this->crud->removeColumn('category_id');
        $this->crud->removeColumn('image');

        // Status
        CRUD::column([
            'name'  => 'status',
            'label' => 'Status',
            'type'  => 'boolean',
            'options' => [
                0 => 'Inactive',
                1 => 'Active',
            ],
        ]);

        // Category column
        CRUD::column([
            'name'      => 'category_id',
            'label'     => 'Category',
            'type'      => 'select',
            'entity'    => 'category',
            'model'     => \App\Models\Category::class,
            'attribute' => 'name',
        ]);

        // Product image thumbnail
        CRUD::column([
            'name'   => 'image',
            'label'  => 'Product Image',
            'type'   => 'image',
            'disk'   => 'public',
            'height' => '60px',
            'width'  => '60px',
        ]);

        $this->crud->query = $this->applyProductFilters($this->crud->query);
    }

    private function applyProductFilters(Builder $query): Builder
    {
        $status = request()->query('status');
        if (in_array($status, ['0', '1', 0, 1], true)) {
            $query->where('products.status', (int) $status);
        }

        $categoryId = request()->query('category_id');
        if (is_scalar($categoryId) && ctype_digit((string) $categoryId)) {
            $query->where('products.category_id', (int) $categoryId);
        }

        foreach (['stock_min' => '>=', 'stock_max' => '<='] as $parameter => $operator) {
            $value = request()->query($parameter);
            if (is_scalar($value) && ctype_digit((string) $value)) {
                $query->where('products.stock', $operator, (int) $value);
            }
        }

        return $query;
    }

    public function exportCsv()
    {
        $this->crud->hasAccessOrFail('list');

        return Excel::download(
            new ProductsExport($this->filteredProductQuery()),
            'products.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportExcel()
    {
        $this->crud->hasAccessOrFail('list');

        return Excel::download(
            new ProductsExport($this->filteredProductQuery()),
            'products.xlsx'
        );
    }

    private function filteredProductQuery()
    {
        $search = request()->input('search');
        $searchTerm = is_array($search)
            ? ($search['value'] ?? null)
            : ($search ?? request()->query('q'));

        $query = $this->applyProductFilters(Product::query())
            ->with('category');

        if (is_string($searchTerm) && trim($searchTerm) !== '') {
            $term = '%' . trim($searchTerm) . '%';

            $query->where(function ($query) use ($term) {
                $query->where('products.name', 'like', $term)
                    ->orWhere('products.slug', 'like', $term)
                    ->orWhere('products.description', 'like', $term)
                    ->orWhere('products.price', 'like', $term)
                    ->orWhere('products.stock', 'like', $term)
                    ->orWhere('products.status', 'like', $term)
                    ->orWhere('products.image', 'like', $term)
                    ->orWhere('products.created_at', 'like', $term)
                    ->orWhere('products.updated_at', 'like', $term)
                    ->orWhereHas('category', function ($categoryQuery) use ($term) {
                        $categoryQuery->where('name', 'like', $term);
                    });
            });
        }

        return $query->orderBy('products.id');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        CRUD::field([
            'name'      => 'category_id',
            'label'     => 'Category',
            'type'      => 'select',
            'entity'    => 'category',
            'model'     => 'App\Models\Category',
            'attribute' => 'name',
            'allows_null' => false,
            'validation' => ['required'],
        ]);

        CRUD::field('image')
            ->type('upload')
            ->withFiles([
                'disk' => 'public',
                'path' => 'products',
            ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        CRUD::column('name');

        CRUD::column([
            'name'      => 'category_id',
            'label'     => 'Category',
            'type'      => 'select',
            'entity'    => 'category',
            'model'     => \App\Models\Category::class,
            'attribute' => 'name',
        ]);

        CRUD::column('slug');

        CRUD::column('description');

        CRUD::column('price');

        CRUD::column('stock');

        CRUD::column([
            'name'   => 'image',
            'label'  => 'Product Image',
            'type'   => 'image',
            'disk'   => 'public',
            'height' => '150px',
            'width'  => '150px',
        ]);

        // Status
        CRUD::column([
            'name'  => 'status',
            'label' => 'Status',
            'type'  => 'boolean',
            'options' => [
                0 => 'Inactive',
                1 => 'Active',
            ],
        ]);

        CRUD::column('created_at');
    }
}
