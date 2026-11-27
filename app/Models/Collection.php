<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'short_description',
        'description',
        'thumbnail',
        'banner_image',
        'mobile_banner',
        'banner_alt',
        'status',
        'is_featured',
        'is_new',
        'sort_order',
        'start_at',
        'end_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
    ];


    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
