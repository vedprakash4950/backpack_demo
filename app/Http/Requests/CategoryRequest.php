<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        $categoryId = $this->route('id') ?? $this->route('category');

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'The slug may contain letters, numbers, dashes, and underscores only.',
        ];
    }
}
