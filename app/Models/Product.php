<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use CrudTrait;
    
    protected $fillable = ['name', 'slug', 'description', 'category_id', 'price', 'stock', 'image', 'status'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
