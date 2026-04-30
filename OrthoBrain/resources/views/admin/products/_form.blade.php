@csrf
@php
    $selCategory    = old('category_id',    $product->category_id);
    $selSubcategory = old('subcategory_id', $product->subcategory_id);
    $imgService     = app(\App\Services\ImageUploadService::class);
    $existingImages = $product->images ?? collect();
    $maxImages      = \App\Models\Product::MAX_IMAGES;
@endphp
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="category_id" class="form-label">Category<span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" required class="js-searchable form-select">
            <option value="">Select category</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected($selCategory == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="subcategory_id" class="form-label">Sub Category</label>
        <select id="subcategory_id" name="subcategory_id" class="js-searchable form-select">
            <option value="">Select sub category</option>
        </select>
    </div>
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name', $product->name) }}" placeholder="Enter product name"
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="base_price" class="form-label">Base Price<span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input id="base_price" name="base_price" type="number" step="0.01" min="0" required value="{{ old('base_price', $product->base_price) }}" placeholder="Enter base price"
                   class="form-control @error('base_price') is-invalid @enderror">
        </div>
        @error('base_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="number_of_revisions" class="form-label">Number of Revisions/Refinements</label>
        <input id="number_of_revisions" name="number_of_revisions" type="number" min="0" value="{{ old('number_of_revisions', $product->number_of_revisions) }}" class="form-control">
    </div>
    <div class="col-md-6 mb-1">
        <label for="product_term_months" class="form-label">Product Term (in months)</label>
        <input id="product_term_months" name="product_term_months" type="number" min="0" value="{{ old('product_term_months', $product->product_term_months) }}" class="form-control">
    </div>
    <div class="col-md-6 mb-1">
        <label for="from_step" class="form-label">From Step</label>
        <input id="from_step" name="from_step" type="number" min="0" value="{{ old('from_step', $product->from_step ?? 1) }}" class="form-control">
    </div>
    <div class="col-md-6 mb-1">
        <label for="to_step" class="form-label">To Step</label>
        <input id="to_step" name="to_step" type="number" min="0" value="{{ old('to_step', $product->to_step) }}" class="form-control @error('to_step') is-invalid @enderror">
        @error('to_step')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="url" class="form-label">URL</label>
        <input id="url" name="url" type="url" maxlength="500" value="{{ old('url', $product->url) }}" placeholder="www.example.com" class="form-control">
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $product->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $product->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>

    {{-- ─── Images: gallery + multi-file picker ─────────────────── --}}
    <div class="col-12 mb-1">
        <label class="form-label d-flex align-items-center" style="gap:.5rem;">
            <span>Images</span>
            <span class="ob-image-count" id="ob-image-count">0 / {{ $maxImages }}</span>
        </label>

        {{-- Existing images (edit mode): drag to reorder, × to queue for removal. --}}
        <div id="ob-image-gallery" class="ob-image-gallery" data-has-items="{{ $existingImages->count() }}">
            @foreach ($existingImages as $img)
                @php $url = $imgService->url($img->s3_key); @endphp
                <div class="ob-image-gallery-item" data-image-id="{{ $img->id }}">
                    <img src="{{ $url }}" alt="" class="ob-image-gallery-img" data-preview-src="{{ $url }}">
                    <span class="ob-image-cover-badge" title="Cover image">Cover</span>
                    <button type="button" class="ob-image-gallery-remove js-remove-existing-image"
                            aria-label="Remove image" title="Remove image"><i data-feather="x"></i></button>
                    <span class="ob-image-drag-hint" title="Drag to reorder"><i data-feather="move"></i></span>
                </div>
            @endforeach
        </div>
        @if ($existingImages->isEmpty())
            <div class="ob-image-gallery-empty" id="ob-image-gallery-empty">No images yet. Add up to {{ $maxImages }} below.</div>
        @endif

        {{-- File input (hidden — populated from the cropper modal) --}}
        <input id="images" name="images[]" type="file" multiple accept=".jpg,.jpeg,.png"
               class="@error('images') is-invalid @enderror" hidden>

        {{-- Add-image trigger: opens the cropper modal --}}
        <div class="mt-1">
            <button type="button" id="ob-add-image-btn" class="btn btn-outline-primary"
                    onclick="openPhotoEditor('product-image-editor')">
                <i data-feather="plus"></i> Add image
            </button>
            <small class="text-muted d-block mt-25">
                Up to {{ $maxImages }} images. JPG or PNG, max 2 MB each. Crop/rotate each image before saving. The first image is used as the cover.
            </small>
        </div>

        {{-- Chip list: one chip per pending new file --}}
        <div id="ob-file-chip-list" class="ob-file-chip-list mt-1"></div>

        @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @error('images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

        {{-- Hidden state: ids to remove + final ordering of existing images --}}
        <div id="ob-image-hidden-inputs"></div>
    </div>

    <div class="col-12 mb-1">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea>
    </div>
</div>

<div class="d-flex mt-2">
    <button type="submit" class="btn btn-primary me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

{{-- Cropper modal — pushes cropped File into the pending-files buffer. --}}
@include('partials._photo-editor', [
    'id'        => 'product-image-editor',
    'title'     => 'Edit Product Image',
    'callback'  => 'onProductImageCropped',
    'aspect'    => null,
    'enableCam' => false,
])

@push('styles')
<link rel="stylesheet" href="{{ asset('vuexy/vendors/css/extensions/dragula.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vuexy/vendors/js/extensions/dragula.min.js') }}"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
<script>
obCascade({ parent:'#category_id', child:'#subcategory_id', url:'{{ route('admin.ajax.subcategories') }}', paramName:'category_id', placeholder:'Select sub category', preselectId: @json($selSubcategory) });
ClassicEditor.create(document.querySelector('#description'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
}).catch(err => console.error(err));

(function () {
    const MAX_IMAGES   = {{ $maxImages }};
    const MAX_FILE     = 2 * 1024 * 1024;

    const $input        = $('#images');
    const $chipList     = $('#ob-file-chip-list');
    const $gallery      = $('#ob-image-gallery');
    const $hiddenInputs = $('#ob-image-hidden-inputs');
    const $count        = $('#ob-image-count');
    const $addBtn       = $('#ob-add-image-btn');

    // Own buffer of pending files (FileList is readonly; we sync to input via DataTransfer).
    let pendingFiles = [];   // File[]
    let removedIds   = [];   // number[] — ids of existing images queued for deletion
    let nextSeq      = 1;    // for unique filenames from cropper

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
        return (bytes / 1024 / 1024).toFixed(1) + ' MB';
    }

    function remainingExisting() {
        return $gallery.find('.ob-image-gallery-item').length;
    }

    function totalAfterSave() {
        return remainingExisting() + pendingFiles.length;
    }

    function syncInputFiles() {
        const dt = new DataTransfer();
        pendingFiles.forEach(f => dt.items.add(f));
        $input[0].files = dt.files;
    }

    function renderHiddenInputs() {
        const parts = [];
        removedIds.forEach(id => {
            parts.push('<input type="hidden" name="remove_image_ids[]" value="' + id + '">');
        });
        $gallery.find('.ob-image-gallery-item').each(function (idx) {
            const id = $(this).data('image-id');
            parts.push('<input type="hidden" name="image_order[]" value="' + id + '">');
        });
        $hiddenInputs.html(parts.join(''));
    }

    function renderChips() {
        const html = pendingFiles.map((f, idx) => `
            <div class="ob-file-chip" data-idx="${idx}">
                <span class="ob-file-chip-icon"><i data-feather="image"></i></span>
                <span class="ob-file-chip-meta">
                    <span class="ob-file-chip-name">${$('<div>').text(f.name).html()}</span>
                    <span class="ob-file-chip-size">${formatSize(f.size)}</span>
                </span>
                <button type="button" class="ob-file-chip-remove js-remove-pending-file"
                        aria-label="Remove selected file" title="Remove">
                    <i data-feather="x"></i>
                </button>
            </div>
        `).join('');
        $chipList.html(html);
        if (window.feather) feather.replace();
    }

    function refreshCoverBadge() {
        $gallery.find('.ob-image-cover-badge').hide();
        $gallery.find('.ob-image-gallery-item').first().find('.ob-image-cover-badge').show();
    }

    function refreshAddButton() {
        const atMax = totalAfterSave() >= MAX_IMAGES;
        $addBtn.prop('disabled', atMax);
        $addBtn.attr('title', atMax ? 'Maximum of ' + MAX_IMAGES + ' images reached' : '');
    }

    function refreshCount() {
        const n = totalAfterSave();
        $count.text(n + ' / ' + MAX_IMAGES);
        $count.toggleClass('is-full', n >= MAX_IMAGES);
        $count.toggleClass('is-over', n > MAX_IMAGES);
        const empty = remainingExisting() === 0 && pendingFiles.length === 0;
        $('#ob-image-gallery-empty').toggle(empty);
        refreshAddButton();
    }

    function warn(title, text) {
        Swal.fire({
            title: title, text: text, icon: 'warning',
            confirmButtonText: 'Got it',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
    }

    // Global callback invoked by the photo-editor modal once a crop is saved.
    window.onProductImageCropped = function (file) {
        if (totalAfterSave() >= MAX_IMAGES) {
            warn('Too many images',
                 'You can have at most ' + MAX_IMAGES + ' images per product. Remove one before adding another.');
            return;
        }
        if (file.size > MAX_FILE) {
            warn('Image is too large',
                 'The cropped image is ' + formatSize(file.size) + ' — the limit is 2 MB per image.');
            return;
        }
        // Re-name so chips are distinct and uploads don't collide server-side.
        const stamped = new File([file], 'product-image-' + Date.now() + '-' + (nextSeq++) + '.jpg',
                                 { type: file.type || 'image/jpeg' });
        pendingFiles.push(stamped);
        syncInputFiles();
        renderChips();
        refreshCount();
    };

    // Remove a pending (not-yet-uploaded) file from the chip list.
    $chipList.on('click', '.js-remove-pending-file', function () {
        const idx = parseInt($(this).closest('.ob-file-chip').data('idx'), 10);
        pendingFiles.splice(idx, 1);
        syncInputFiles();
        renderChips();
        refreshCount();
    });

    // Remove an existing (already-saved) image: drop its tile, queue its id.
    $gallery.on('click', '.js-remove-existing-image', function () {
        const $tile = $(this).closest('.ob-image-gallery-item');
        const id = $tile.data('image-id');
        if (id) removedIds.push(id);
        $tile.remove();
        refreshCoverBadge();
        refreshCount();
        renderHiddenInputs();
    });

    // Dragula: reorder existing-image tiles.
    if (window.dragula && $gallery.length) {
        const drake = dragula([$gallery[0]], {
            moves: function (el, source, handle, sibling) {
                return el.classList.contains('ob-image-gallery-item');
            }
        });
        drake.on('drag',  el => el.classList.add('is-dragging'));
        drake.on('dragend', el => el.classList.remove('is-dragging'));
        drake.on('drop',  () => { refreshCoverBadge(); renderHiddenInputs(); });
    }

    // Initial paint.
    refreshCoverBadge();
    refreshCount();
    renderHiddenInputs();
})();
</script>
@endpush
