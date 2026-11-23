<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        $productId = $this->route('id') ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['category_id' => 'category'];
    }

    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'The slug may contain letters, numbers, dashes, and underscores only.',
            'image.max' => 'The product image must not be larger than 2 MB.',
        ];
    }
}
