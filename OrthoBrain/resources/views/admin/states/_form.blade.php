@csrf
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="country_id" class="form-label">Country<span class="text-danger">*</span></label>
        <select id="country_id" name="country_id" required class="js-searchable form-select">
            <option value="">Select country</option>
            @foreach ($countries as $c)
                <option value="{{ $c->id }}" @selected(old('country_id', $state->country_id) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('country_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">State Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $state->name) }}"
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="state_code" class="form-label">State Code<span class="text-danger">*</span></label>
        <input id="state_code" name="state_code" type="text" maxlength="100" required
               value="{{ old('state_code', $state->state_code) }}" placeholder="e.g. OH"
               class="form-control @error('state_code') is-invalid @enderror">
        @error('state_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $state->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $state->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.states.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
