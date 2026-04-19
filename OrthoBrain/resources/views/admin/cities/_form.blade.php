@csrf
@php
    $selCountry = old('country_id', $city->state?->country_id);
    $selState   = old('state_id',   $city->state_id);
@endphp
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="country_id" class="form-label">Country<span class="text-danger">*</span></label>
        <select id="country_id" name="country_id" required class="js-searchable form-select">
            <option value="">Select country</option>
            @foreach ($countries as $c)
                <option value="{{ $c->id }}" @selected($selCountry == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('country_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="state_id" class="form-label">State<span class="text-danger">*</span></label>
        <select id="state_id" name="state_id" required class="js-searchable form-select">
            <option value="">Select state</option>
        </select>
        @error('state_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="name" class="form-label">City Name<span class="text-danger">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $city->name) }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $city->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $city->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script>
obCascade({ parent:'#country_id', child:'#state_id', url:'{{ route('admin.ajax.states') }}', paramName:'country_id', placeholder:'Select state', preselectId: @json($selState) });
</script>
@endpush
