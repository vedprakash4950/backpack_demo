<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $collectionId = $this->route('id') ?? $this->route('collection');

        return [
            'name' => 'required|string|max:255',

            'slug' => ['required', 'string', 'max:255', 'alpha_dash', \Illuminate\Validation\Rule::unique('collections', 'slug')->ignore($collectionId)],

            'subtitle' => 'nullable|string|max:255',

            'short_description' => 'nullable|string',

            'description' => 'nullable|string',

            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',

            'mobile_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',

            'banner_alt' => 'nullable|string|max:255',

            'status' => 'required|in:draft,active,inactive,scheduled,archived',

            'is_featured' => 'nullable|boolean',

            'is_new' => 'nullable|boolean',

            'sort_order' => 'nullable|integer|min:0',

            'start_at' => 'nullable|date',

            'end_at' => 'nullable|date|after_or_equal:start_at',

            'meta_title' => 'nullable|string|max:255',

            'meta_description' => 'nullable|string',

            'meta_keywords' => 'nullable|string',

            'canonical_url' => 'nullable|url|max:255',

            'products' => 'nullable|array',

            'products.*' => 'integer|distinct|exists:products,id',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
