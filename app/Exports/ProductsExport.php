<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $products)
    {
    }

    public function query(): Builder
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Slug', 'Description', 'Category', 'Price', 'Stock',
            'Status', 'Image Path', 'Image URL', 'Created At', 'Updated At',
        ];
    }

    public function map($product): array
    {
        /** @var Product $product */
        return [
            $product->id,
            $this->safeText($product->name),
            $this->safeText($product->slug),
            $this->safeText($product->description),
            $this->safeText($product->category?->name),
            $product->price,
            $product->stock,
            $product->status ? 'Active' : 'Inactive',
            $this->safeText($product->image),
            $product->image ? $this->safeText(Storage::disk('public')->url($product->image)) : null,
            $product->created_at?->toDateTimeString(),
            $product->updated_at?->toDateTimeString(),
        ];
    }

    private function safeText(?string $value): ?string
    {
        return $value !== null && preg_match('/^[\s]*[=+@\-]/', $value)
            ? "'{$value}"
            : $value;
    }
}
