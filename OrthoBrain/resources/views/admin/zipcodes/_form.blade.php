@csrf
@php
    $selCountry = old('country_id', $zipcode->city?->state?->country_id);
    $selState   = old('state_id',   $zipcode->city?->state_id);
    $selCity    = old('city_id',    $zipcode->city_id);
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="country_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">Country Name <span class="text-red-500">*</span></label>
        <select id="country_id" name="country_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select country</option>
            @foreach ($countries as $c)<option value="{{ $c->id }}" @selected($selCountry == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('country_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="state_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">State Name <span class="text-red-500">*</span></label>
        <select id="state_id" name="state_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select state</option>
        </select>
        @error('state_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="city_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">City Name <span class="text-red-500">*</span></label>
        <select id="city_id" name="city_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select city</option>
        </select>
        @error('city_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="code" class="block text-sm font-medium text-[#5e5873] mb-1.5">Zip Code <span class="text-red-500">*</span></label>
        <input id="code" name="code" type="text" maxlength="20" required value="{{ old('code', $zipcode->code) }}" placeholder="Enter zip code"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-1">
        <label for="details" class="block text-sm font-medium text-[#5e5873] mb-1.5">Zip Details</label>
        <textarea id="details" name="details" rows="3" placeholder="Description goes here"
                  class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">{{ old('details', $zipcode->details) }}</textarea>
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-[#5e5873] mb-1.5">Status <span class="text-red-500">*</span></label>
        <select id="status" name="status" required class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="ACTIVE"   @selected(old('status', $zipcode->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $zipcode->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button type="submit" class="px-5 py-2 bg-[#8cc63f] hover:bg-[#7ab332] text-white text-sm font-medium rounded-md shadow-sm">{{ $submitLabel ?? 'Submit' }}</button>
    <a href="{{ route('admin.zipcodes.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Cancel</a>
</div>

@push('scripts')
<script>
obCascade({ parent:'#country_id', child:'#state_id', url:'{{ route('admin.ajax.states') }}', paramName:'country_id', placeholder:'Select state', preselectId: @json($selState) });
obCascade({ parent:'#state_id',   child:'#city_id',  url:'{{ route('admin.ajax.cities') }}', paramName:'state_id',   placeholder:'Select city',  preselectId: @json($selCity) });
</script>
@endpush
