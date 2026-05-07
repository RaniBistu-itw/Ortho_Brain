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

        {{-- Hidden file input — clicked programmatically by the Add button; synced via DataTransfer. --}}
        <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png"
               class="@error('images') is-invalid @enderror" hidden>

        {{-- Add-image trigger --}}
        <div class="mt-1">
            <button type="button" id="ob-add-image-btn" class="btn btn-outline-primary">
                <i data-feather="plus"></i> Add image
            </button>
            <small class="text-muted d-block mt-25">
                Up to {{ $maxImages }} images. JPG or PNG, max 2 MB each. The first image is used as the cover.
            </small>
        </div>

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
    const MAX_IMAGES  = {{ $maxImages }};
    const MAX_FILE    = 2 * 1024 * 1024;
    const MIME_MAP    = { jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png' };

    const fileInput    = document.getElementById('images');
    const gallery      = document.getElementById('ob-image-gallery');
    const hiddenInputs = document.getElementById('ob-image-hidden-inputs');
    const countEl      = document.getElementById('ob-image-count');
    const addBtn       = document.getElementById('ob-add-image-btn');
    const emptyMsg     = document.getElementById('ob-image-gallery-empty');

    // uid → { file: File, objectUrl: string }
    let pendingMap = new Map();
    let removedIds = [];
    let uidCounter = 0;

    function nextUid() { return 'p' + (++uidCounter); }

    function existingCount() {
        return gallery.querySelectorAll('.ob-image-gallery-item[data-image-id]').length;
    }

    function totalCount() {
        return existingCount() + pendingMap.size;
    }

    function syncInputFiles() {
        try {
            const dt = new DataTransfer();
            pendingMap.forEach(({ file }) => dt.items.add(file));
            fileInput.files = dt.files;
        } catch (_) {}
    }

    function renderHiddenInputs() {
        const parts = [];
        removedIds.forEach(id =>
            parts.push(`<input type="hidden" name="remove_image_ids[]" value="${id}">`)
        );
        gallery.querySelectorAll('.ob-image-gallery-item[data-image-id]').forEach(el =>
            parts.push(`<input type="hidden" name="image_order[]" value="${el.dataset.imageId}">`)
        );
        hiddenInputs.innerHTML = parts.join('');
    }

    function refreshCoverBadge() {
        gallery.querySelectorAll('.ob-image-gallery-item').forEach((el, i) => {
            const badge = el.querySelector('.ob-image-cover-badge');
            if (badge) badge.style.display = i === 0 ? '' : 'none';
        });
    }

    function refreshCount() {
        const n = totalCount();
        countEl.textContent = n + ' / ' + MAX_IMAGES;
        countEl.classList.toggle('is-full', n >= MAX_IMAGES);
        countEl.classList.toggle('is-over', n > MAX_IMAGES);
        const isEmpty = gallery.querySelectorAll('.ob-image-gallery-item').length === 0;
        if (emptyMsg) emptyMsg.style.display = isEmpty ? '' : 'none';
        addBtn.disabled = n >= MAX_IMAGES;
        addBtn.title = n >= MAX_IMAGES ? `Maximum of ${MAX_IMAGES} images reached` : '';
    }

    function warn(title, text) {
        Swal.fire({
            title, text, icon: 'warning',
            confirmButtonText: 'Got it',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
    }

    function safeText(str) {
        return document.createTextNode(str).nodeValue
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function addPendingTile(uid, objectUrl) {
        const div = document.createElement('div');
        div.className = 'ob-image-gallery-item ob-image-gallery-item--pending';
        div.dataset.pendingUid = uid;
        div.innerHTML = `
            <img src="${safeText(objectUrl)}" alt="" class="ob-image-gallery-img">
            <span class="ob-image-cover-badge" title="Cover image" style="display:none;">Cover</span>
            <span class="ob-image-pending-badge">New</span>
            <button type="button" class="ob-image-gallery-remove js-remove-pending"
                    aria-label="Remove image" title="Remove image"><i data-feather="x"></i></button>
            <span class="ob-image-drag-hint" title="Drag to reorder"><i data-feather="move"></i></span>
        `;
        gallery.appendChild(div);
        if (window.feather) feather.replace();
    }

    // Click the native hidden file input directly in the same user-gesture tick.
    addBtn.addEventListener('click', function () {
        if (addBtn.disabled) return;
        fileInput.value = '';   // reset so the same file can be re-picked
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        const files = Array.from(fileInput.files || []);
        if (!files.length) return;

        let added = 0;
        files.forEach(function (rawFile) {
            if (totalCount() + added >= MAX_IMAGES) {
                if (added === 0) {
                    warn('Too many images',
                        `You can have at most ${MAX_IMAGES} images per product. Remove one before adding another.`);
                }
                return;
            }
            if (rawFile.size > MAX_FILE) {
                warn('Image is too large', `${rawFile.name} exceeds the 2 MB limit.`);
                return;
            }
            const ext      = (rawFile.name.split('.').pop() || 'jpg').toLowerCase();
            const mimeType = rawFile.type || MIME_MAP[ext] || 'image/jpeg';
            const uid      = nextUid();
            const stamped  = new File(
                [rawFile],
                `product-image-${Date.now()}-${uidCounter}.${ext}`,
                { type: mimeType }
            );
            const objectUrl = URL.createObjectURL(stamped);
            pendingMap.set(uid, { file: stamped, objectUrl });
            addPendingTile(uid, objectUrl);
            added++;
        });

        if (added > 0) {
            syncInputFiles();
            refreshCoverBadge();
            refreshCount();
        }
    });

    // Remove a pending tile.
    gallery.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-remove-pending');
        if (!btn) return;
        const tile = btn.closest('[data-pending-uid]');
        if (!tile) return;
        const uid = tile.dataset.pendingUid;
        const entry = pendingMap.get(uid);
        if (entry) URL.revokeObjectURL(entry.objectUrl);
        pendingMap.delete(uid);
        tile.remove();
        syncInputFiles();
        refreshCoverBadge();
        refreshCount();
    });

    // Remove an existing (already-saved) image: queue its id for deletion.
    gallery.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-remove-existing-image');
        if (!btn) return;
        const tile = btn.closest('[data-image-id]');
        if (!tile) return;
        const id = tile.dataset.imageId;
        if (id) removedIds.push(id);
        tile.remove();
        refreshCoverBadge();
        refreshCount();
        renderHiddenInputs();
    });

    // Dragula: reorder all tiles (existing + pending).
    if (window.dragula && gallery) {
        const drake = dragula([gallery], {
            moves: el => el.classList.contains('ob-image-gallery-item')
        });
        drake.on('drag',    el => el.classList.add('is-dragging'));
        drake.on('dragend', el => el.classList.remove('is-dragging'));
        drake.on('drop',    ()  => { refreshCoverBadge(); renderHiddenInputs(); });
    }

    // Fallback: inject pending files into FormData at submit time (older Safari / mobile).
    const form = fileInput.closest('form');
    if (form && typeof FormDataEvent !== 'undefined') {
        form.addEventListener('formdata', function (e) {
            if (!pendingMap.size) return;
            e.formData.delete('images[]');
            pendingMap.forEach(({ file }) => e.formData.append('images[]', file));
        });
    }

    // Initial paint.
    refreshCoverBadge();
    refreshCount();
    renderHiddenInputs();
})();
</script>
@endpush
