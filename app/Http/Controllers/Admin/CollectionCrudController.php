<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CollectionRequest;
use App\Models\Product;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CollectionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CollectionCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Collection::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/collection');
        CRUD::setEntityNameStrings('collection', 'collections');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('id')
            ->type('number');

        CRUD::column('name')
            ->type('text')
            ->label('Collection Name');

        CRUD::column('slug')
            ->type('text');

        CRUD::column([
            'name'   => 'thumbnail',
            'label'  => 'Thumbnail',
            'type'   => 'image',
            'disk'   => 'public',
            'prefix' => 'collections/thumbnails/',
            'value'  => static function ($entry) {
                $thumbnail = str_replace('\\', '/', (string) $entry->thumbnail);

                return $thumbnail !== '' ? basename($thumbnail) : null;
            },
            'height' => '60px',
            'width'  => '80px',
        ]);

        CRUD::column('status')
            ->type('text');

        CRUD::column('is_featured')
            ->type('boolean')
            ->label('Featured');

        CRUD::column('is_new')
            ->type('boolean')
            ->label('New');

        CRUD::column('sort_order')
            ->type('number');

        CRUD::column('created_at')
            ->type('datetime');
    }

    protected function setupShowOperation()
    {
        CRUD::setFromDb();

        foreach (['thumbnail', 'banner_image', 'mobile_banner'] as $imageField) {
            $this->crud->removeColumn($imageField);
        }

        foreach ([
            'thumbnail' => ['Thumbnail', 'collections/thumbnails/'],
            'banner_image' => ['Desktop Banner', 'collections/banners/'],
            'mobile_banner' => ['Mobile Banner', 'collections/mobile-banners/'],
        ] as $field => [$label, $prefix]) {
            CRUD::column([
                'name' => $field,
                'label' => $label,
                'type' => 'image',
                'disk' => 'public',
                'prefix' => $prefix,
                'value' => static function ($entry) use ($field) {
                    $path = str_replace('\\', '/', (string) $entry->{$field});

                    return $path !== '' ? basename($path) : null;
                },
                'height' => '180px',
                'width' => 'auto',
            ]);
        }
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CollectionRequest::class);
        CRUD::field('name')
            ->type('text')
            ->label('Collection Name');

        CRUD::field('slug')
            ->type('text');

        CRUD::field('subtitle')
            ->type('text');

        CRUD::field('short_description')
            ->type('textarea');

        CRUD::field('description')
            ->type('textarea');

        CRUD::field('banner_alt')
            ->type('text');

        CRUD::field('status')
            ->type('select_from_array')
            ->options([
                'draft' => 'Draft',
                'active' => 'Active',
                'inactive' => 'Inactive',
                'scheduled' => 'Scheduled',
                'archived' => 'Archived',
            ])
            ->allowsNull(false);

        CRUD::field('is_featured')
            ->type('checkbox');

        CRUD::field('is_new')
            ->type('checkbox');

        CRUD::field('sort_order')
            ->type('number');

        CRUD::field('start_at')
            ->type('datetime');

        CRUD::field('end_at')
            ->type('datetime');

        CRUD::field('meta_title')
            ->type('text');

        CRUD::field('meta_description')
            ->type('textarea');

        CRUD::field('meta_keywords')
            ->type('text');

        CRUD::field('canonical_url')
            ->type('url');
        // Collection Thumbnail
        CRUD::field('thumbnail')
            ->type('upload')
            ->withFiles([
                'disk' => 'public',
                'path' => 'collections/thumbnails',
            ]);
        $this->addImagePreview('thumbnail', 'Thumbnail preview', 'collections/thumbnails');

        // Desktop Banner
        CRUD::field('banner_image')
            ->type('upload')
            ->withFiles([
                'disk' => 'public',
                'path' => 'collections/banners',
            ]);
        $this->addImagePreview('banner_image', 'Desktop banner preview', 'collections/banners');

        // Mobile Banner
        CRUD::field('mobile_banner')
            ->type('upload')
            ->withFiles([
                'disk' => 'public',
                'path' => 'collections/mobile-banners',
            ]);
        $this->addImagePreview('mobile_banner', 'Mobile banner preview', 'collections/mobile-banners');

        $productImages = Product::query()->select(['id', 'image'])->get()
            ->mapWithKeys(function (Product $product) {
                if (!$product->image) {
                    return [$product->id => null];
                }

                $path = str_replace('\\', '/', $product->image);
                if (preg_match('/^(https?:)?\/\//i', $path)) {
                    return [$product->id => $path];
                }

                $path = preg_replace('#^(public/|storage/)#', '', ltrim($path, '/'));
                $path = preg_replace('#^(products/)+#', '', $path);

                return [$product->id => url('/storage/products/' . $path)];
            })->all();

        CRUD::field([
            'name'      => 'products',
            'label'     => 'Select Products',
            'type'      => 'select_multiple',
            'entity'    => 'products',
            'model'     => \App\Models\Product::class,
            'attribute' => 'name',
            'pivot'     => true,
            'attributes' => ['class' => 'collection-product-select'],
        ]);

        CRUD::field([
            'name' => 'collection_product_select2_init',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.fields.collection_product_select2',
            'product_images' => $productImages,
        ]);
    }

    private function addImagePreview(string $field, string $label, string $pathPrefix): void
    {
        CRUD::field([
            'name' => $field . '_preview',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.fields.image_preview',
            'field_name' => $field,
            'path_prefix' => $pathPrefix,
            'label' => $label,
            'wrapper' => ['class' => 'form-group col-sm-12'],
        ]);
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
}
