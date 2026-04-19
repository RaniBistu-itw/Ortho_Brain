@csrf
@php $statusStr = old('status', $subcategory->status ? 'ACTIVE' : 'INACTIVE'); @endphp
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="category_id" class="form-label">Category<span class="text-danger">*</span></label>
        <select id="category_id" name="category_id" required class="js-searchable form-select">
            <option value="">Select category</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $subcategory->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">Sub Category Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $subcategory->name) }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 mb-1">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $subcategory->description) }}</textarea>
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected($statusStr === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected($statusStr === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.product-subcategories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
