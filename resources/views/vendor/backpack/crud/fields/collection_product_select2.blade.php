@php($productImages = $field['product_images'] ?? [])

@push('crud_fields_styles')
    @basset('https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css')
    <style>
        .collection-product-option { display: inline-flex; align-items: center; gap: 8px; }
        .collection-product-option img { width: 36px; height: 36px; object-fit: cover; border-radius: 4px; }
        .select2-selection--multiple .collection-product-option img { width: 22px; height: 22px; }
    </style>
@endpush

@push('crud_fields_scripts')
    @basset('https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js')
    <script>
        (function () {
            const productImages = @json($productImages);

            function initializeCollectionProducts() {
                if (!window.jQuery || !jQuery.fn.select2) return;

                jQuery('.collection-product-select').each(function () {
                    const select = jQuery(this);
                    if (select.hasClass('select2-hidden-accessible')) return;

                    function renderProduct(option) {
                        if (!option.id) return jQuery('<span>').text(option.text);

                        const row = jQuery('<span class="collection-product-option"></span>');
                        const imageUrl = productImages[String(option.id)];
                        if (imageUrl) {
                            row.append(jQuery('<img>').attr({ src: imageUrl, alt: '' }));
                        }
                        row.append(jQuery('<span></span>').text(option.text));
                        return row;
                    }

                    select.select2({
                        width: '100%',
                        placeholder: 'Search and select products',
                        templateResult: renderProduct,
                        templateSelection: renderProduct,
                        escapeMarkup: function (markup) { return markup; }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeCollectionProducts);
            } else {
                initializeCollectionProducts();
            }
        })();
    </script>
@endpush
