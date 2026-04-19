@csrf
@php
    $selCategory    = old('category_id',    $product->category_id);
    $selSubcategory = old('subcategory_id', $product->subcategory_id);
    $imgService = app(\App\Services\ImageUploadService::class);
    $existingImageUrl = $product->image_s3_key ? $imgService->url($product->image_s3_key) : null;
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
    <div class="col-12 mb-1">
        <label for="image" class="form-label">Image</label>
        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png" class="form-control @error('image') is-invalid @enderror">
        <small class="text-muted">JPG or PNG, max 10 MB.</small>
        @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if ($existingImageUrl)
            <div class="mt-1">
                <small class="text-muted">Current image:</small><br>
                <img src="{{ $existingImageUrl }}" alt="" class="mt-25 rounded border" style="max-height: 96px;">
            </div>
        @endif
    </div>
    <div class="col-12 mb-1">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea>
    </div>
</div>

<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
<script>
obCascade({ parent:'#category_id', child:'#subcategory_id', url:'{{ route('admin.ajax.subcategories') }}', paramName:'category_id', placeholder:'Select sub category', preselectId: @json($selSubcategory) });
ClassicEditor.create(document.querySelector('#description'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
}).catch(err => console.error(err));
</script>
@endpush
