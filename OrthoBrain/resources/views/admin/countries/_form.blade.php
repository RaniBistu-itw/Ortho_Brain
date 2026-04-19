@csrf
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">Country Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required
               value="{{ old('name', $country->name) }}"
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="country_code" class="form-label">Country Code<span class="text-danger">*</span></label>
        <input id="country_code" name="country_code" type="text" maxlength="10" required
               value="{{ old('country_code', $country->country_code) }}" placeholder="e.g. US"
               class="form-control @error('country_code') is-invalid @enderror">
        @error('country_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="phone_code" class="form-label">Phone Code<span class="text-danger">*</span></label>
        <input id="phone_code" name="phone_code" type="text" maxlength="10" required
               value="{{ old('phone_code', $country->phone_code) }}" placeholder="e.g. +1"
               class="form-control @error('phone_code') is-invalid @enderror">
        @error('phone_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $country->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $country->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>

<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
