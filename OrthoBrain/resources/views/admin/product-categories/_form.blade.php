@csrf
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">Category Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $category->status ?? 'ACTIVE') === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $category->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
