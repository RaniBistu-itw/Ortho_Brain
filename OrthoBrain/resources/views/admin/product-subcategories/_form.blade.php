@csrf
@php $statusStr = old('status', $subcategory->status ? 'ACTIVE' : 'INACTIVE'); @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="category_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">Category<span class="text-red-500">*</span></label>
        <select id="category_id" name="category_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select category</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $subcategory->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="name" class="block text-sm font-medium text-[#5e5873] mb-1.5">Sub Category Name<span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $subcategory->name) }}" class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-[#5e5873] mb-1.5">Description</label>
        <textarea id="description" name="description" rows="3" class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">{{ old('description', $subcategory->description) }}</textarea>
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-[#5e5873] mb-1.5">Status<span class="text-red-500">*</span></label>
        <select id="status" name="status" required class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="ACTIVE"   @selected($statusStr === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected($statusStr === 'INACTIVE')>Inactive</option>
        </select>
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button type="submit" class="px-5 py-2 bg-[#8cc63f] hover:bg-[#7ab332] text-white text-sm font-medium rounded-md shadow-sm">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.product-subcategories.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Cancel</a>
</div>
