@php
    $imageFieldName = $field['field_name'] ?? null;
    $imageEntry = $entry ?? $crud->getCurrentEntry();
    $storedImage = $imageFieldName && $imageEntry ? data_get($imageEntry, $imageFieldName) : null;
    $imageUrl = null;

    if (is_string($storedImage) && $storedImage !== '') {
        $imagePath = str_replace('\\', '/', $storedImage);
        $pathPrefix = trim(str_replace('\\', '/', $field['path_prefix'] ?? ''), '/');

        if (preg_match('/^https?:\\/\\//i', $imagePath)) {
            $parsedPath = parse_url($imagePath, PHP_URL_PATH) ?: '';
            if (str_starts_with($parsedPath, '/storage/')) {
                $imagePath = substr($parsedPath, strlen('/storage/'));
            } else {
                $imageUrl = $imagePath;
            }
        }

        if (!$imageUrl) {
            $imagePath = preg_replace('#^(public/|storage/)#', '', ltrim($imagePath, '/'));
            if ($pathPrefix !== '' && !str_starts_with($imagePath, $pathPrefix . '/')) {
                $imagePath = $pathPrefix . '/' . $imagePath;
            }
            $imageUrl = url('/storage/' . $imagePath);
        }
    }

    $previewId = 'image-preview-' . \Illuminate\Support\Str::slug($imageFieldName ?? uniqid());
@endphp

<div id="{{ $previewId }}" class="image-preview mb-3" data-input-name="{{ $imageFieldName }}" data-saved-url="{{ $imageUrl }}" @if (!$imageUrl) hidden @endif>
    <label class="form-label">{{ $field['label'] ?? 'Image preview' }}</label>
    <div class="image-preview__frame">
        <img @if ($imageUrl) src="{{ $imageUrl }}" @endif alt="{{ $field['label'] ?? 'Selected image' }}">
    </div>
</div>

@push('crud_fields_styles')
<style>
    .image-preview__frame {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 180px;
        min-height: 120px;
        max-width: 100%;
        padding: 8px;
        border: 1px solid var(--tblr-border-color, #dce1e7);
        border-radius: 6px;
        background: var(--tblr-bg-surface-secondary, #f8f9fa);
    }
    .image-preview__frame img {
        display: block;
        max-width: min(420px, 100%);
        max-height: 260px;
        object-fit: contain;
    }
</style>
@endpush

@push('crud_fields_scripts')
<script>
    (function () {
        const preview = document.getElementById(@json($previewId));
        if (!preview) return;
        const image = preview.querySelector('img');
        let objectUrl = null;

        // Backpack already builds the saved upload URL with its configured disk and folder.
        // Backpack marks field wrappers with bp-field-name, not data-field-name.
        const uploadWrapper = Array.from(document.querySelectorAll('[bp-field-name]'))
            .find(function (wrapper) { return wrapper.getAttribute('bp-field-name') === preview.dataset.inputName; });
        const existingFileLink = uploadWrapper && uploadWrapper.querySelector('.existing-file a:not(.file_clear_button)');
        if (existingFileLink && existingFileLink.href) {
            preview.dataset.savedUrl = existingFileLink.href;
            image.src = existingFileLink.href;
            preview.hidden = false;
        }

        document.addEventListener('change', function (event) {
            const input = event.target;
            if (!(input instanceof HTMLInputElement) || input.type !== 'file') return;
            if (input.name !== preview.dataset.inputName && input.name !== preview.dataset.inputName + '[]') return;

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
            const file = input.files && input.files[0];
            if (file && file.type.startsWith('image/')) {
                objectUrl = URL.createObjectURL(file);
                image.src = objectUrl;
                preview.hidden = false;
            } else if (preview.dataset.savedUrl) {
                image.src = preview.dataset.savedUrl;
                preview.hidden = false;
            } else {
                image.removeAttribute('src');
                preview.hidden = true;
            }
        });

        document.addEventListener('click', function (event) {
            const clearButton = event.target.closest('.file_clear_button');
            if (!clearButton) return;
            const fieldWrapper = clearButton.closest('.form-group');
            const input = fieldWrapper && fieldWrapper.querySelector('input[type="file"]');
            if (input && input.name === preview.dataset.inputName) {
                preview.dataset.savedUrl = '';
                image.removeAttribute('src');
                preview.hidden = true;
            }
        });
    })();
</script>
@endpush
