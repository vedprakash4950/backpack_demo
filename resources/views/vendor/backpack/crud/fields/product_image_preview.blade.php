@php
    $product = $entry ?? $crud->getCurrentEntry();
    $currentImageUrl = null;

    if ($product && $product->image) {
        $imagePath = ltrim($product->image, '/');
        if (!str_starts_with($imagePath, 'products/')) {
            $imagePath = 'products/' . $imagePath;
        }
        $currentImageUrl = '/storage/' . $imagePath;
    }
@endphp

<div id="product-image-preview" class="mb-3" @if (!$currentImageUrl) hidden @endif>
    <label class="form-label">Image preview</label>
    <div>
        <img
            id="product-image-preview-img"
            @if ($currentImageUrl) src="{{ $currentImageUrl }}" @endif
            alt="{{ $product->name ?? 'Selected product image' }}"
            style="max-width: 220px; max-height: 220px; object-fit: contain;"
        >
    </div>
</div>

@push('crud_fields_scripts')
<script>
    (function () {
        const preview = document.getElementById('product-image-preview');
        const image = document.getElementById('product-image-preview-img');
        if (!preview || !image) return;

        const savedImageUrl = image.getAttribute('src') || '';
        let selectedImageUrl = null;

        document.addEventListener('change', function (event) {
            const input = event.target;
            if (!input.matches('input[type="file"][name="image"]')) return;

            if (selectedImageUrl) {
                URL.revokeObjectURL(selectedImageUrl);
                selectedImageUrl = null;
            }

            const file = input.files && input.files[0];
            if (file && file.type.startsWith('image/')) {
                selectedImageUrl = URL.createObjectURL(file);
                image.src = selectedImageUrl;
                preview.hidden = false;
            } else if (savedImageUrl) {
                image.src = savedImageUrl;
                preview.hidden = false;
            } else {
                image.removeAttribute('src');
                preview.hidden = true;
            }
        });
    })();
</script>
@endpush
