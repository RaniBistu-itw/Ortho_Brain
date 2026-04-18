@csrf
@php
    $selCountry = old('country_id', $city->state?->country_id);
    $selState   = old('state_id',   $city->state_id);
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="country_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">Country <span class="text-red-500">*</span></label>
        <select id="country_id" name="country_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select country</option>
            @foreach ($countries as $c)<option value="{{ $c->id }}" @selected($selCountry == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('country_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="state_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">State <span class="text-red-500">*</span></label>
        <select id="state_id" name="state_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select state</option>
        </select>
        @error('state_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="name" class="block text-sm font-medium text-[#5e5873] mb-1.5">City Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $city->name) }}" class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-[#5e5873] mb-1.5">Status <span class="text-red-500">*</span></label>
        <select id="status" name="status" required class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="ACTIVE"   @selected(old('status', $city->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $city->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button type="submit" class="px-5 py-2 bg-[#8cc63f] hover:bg-[#7ab332] text-white text-sm font-medium rounded-md shadow-sm">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.cities.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Cancel</a>
</div>

@push('scripts')
<script>
obCascade({ parent:'#country_id', child:'#state_id', url:'{{ route('admin.ajax.states') }}', paramName:'country_id', placeholder:'Select state', preselectId: @json($selState) });
</script>
@endpush
