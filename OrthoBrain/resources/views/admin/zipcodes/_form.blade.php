@csrf
@php
    $selCountry = old('country_id', $zipcode->city?->state?->country_id);
    $selState   = old('state_id',   $zipcode->city?->state_id);
    $selCity    = old('city_id',    $zipcode->city_id);
@endphp
<div class="row">
    <div class="col-md-6 mb-1">
        <label for="country_id" class="form-label">Country Name<span class="text-danger">*</span></label>
        <select id="country_id" name="country_id" required class="js-searchable form-select">
            <option value="">Select country</option>
            @foreach ($countries as $c)<option value="{{ $c->id }}" @selected($selCountry == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('country_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="state_id" class="form-label">State Name<span class="text-danger">*</span></label>
        <select id="state_id" name="state_id" required class="js-searchable form-select">
            <option value="">Select state</option>
        </select>
        @error('state_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="city_id" class="form-label">City Name<span class="text-danger">*</span></label>
        <select id="city_id" name="city_id" required class="js-searchable form-select">
            <option value="">Select city</option>
        </select>
        @error('city_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="code" class="form-label">Zip Code<span class="text-danger">*</span></label>
        <input id="code" name="code" type="text" maxlength="20" required value="{{ old('code', $zipcode->code) }}" placeholder="Enter zip code"
               class="form-control @error('code') is-invalid @enderror">
        @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-1">
        <label for="details" class="form-label">Zip Details</label>
        <textarea id="details" name="details" rows="3" placeholder="Description goes here" class="form-control">{{ old('details', $zipcode->details) }}</textarea>
    </div>
    <div class="col-md-6 mb-1">
        <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
        <select id="status" name="status" required class="form-select">
            <option value="ACTIVE"   @selected(old('status', $zipcode->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $zipcode->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="d-flex mt-2">
    <button type="submit" class="btn btn-success me-1">{{ $submitLabel ?? 'Submit' }}</button>
    <a href="{{ route('admin.zipcodes.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script>
obCascade({ parent:'#country_id', child:'#state_id', url:'{{ route('admin.ajax.states') }}', paramName:'country_id', placeholder:'Select state', preselectId: @json($selState) });
obCascade({ parent:'#state_id',   child:'#city_id',  url:'{{ route('admin.ajax.cities') }}', paramName:'state_id',   placeholder:'Select city',  preselectId: @json($selCity) });
</script>
@endpush
