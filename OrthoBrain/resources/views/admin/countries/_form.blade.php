@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="name" class="block text-sm font-medium text-[#5e5873] mb-1.5">Country Name<span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required
               value="{{ old('name', $country->name) }}"
               class="w-full rounded-md border @error('name') border-red-500 @else border-[#d8d6de] @enderror px-3 py-2 text-sm focus:border-[#5bc0de] focus:ring-2 focus:ring-[#5bc0de]/20 outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="country_code" class="block text-sm font-medium text-[#5e5873] mb-1.5">Country Code<span class="text-red-500">*</span></label>
        <input id="country_code" name="country_code" type="text" maxlength="10" required
               value="{{ old('country_code', $country->country_code) }}" placeholder="e.g. US"
               class="w-full rounded-md border @error('country_code') border-red-500 @else border-[#d8d6de] @enderror px-3 py-2 text-sm focus:border-[#5bc0de] focus:ring-2 focus:ring-[#5bc0de]/20 outline-none">
        @error('country_code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone_code" class="block text-sm font-medium text-[#5e5873] mb-1.5">Phone Code<span class="text-red-500">*</span></label>
        <input id="phone_code" name="phone_code" type="text" maxlength="10" required
               value="{{ old('phone_code', $country->phone_code) }}" placeholder="e.g. +1"
               class="w-full rounded-md border @error('phone_code') border-red-500 @else border-[#d8d6de] @enderror px-3 py-2 text-sm focus:border-[#5bc0de] focus:ring-2 focus:ring-[#5bc0de]/20 outline-none">
        @error('phone_code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-[#5e5873] mb-1.5">Status<span class="text-red-500">*</span></label>
        <select id="status" name="status" required
                class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="ACTIVE"   @selected(old('status', $country->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $country->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="px-5 py-2 bg-[#8cc63f] hover:bg-[#7ab332] text-white text-sm font-medium rounded-md shadow-sm">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.countries.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Cancel</a>
</div>
