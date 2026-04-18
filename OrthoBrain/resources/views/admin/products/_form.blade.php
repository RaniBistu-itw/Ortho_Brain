@csrf
@php
    $selCategory    = old('category_id',    $product->category_id);
    $selSubcategory = old('subcategory_id', $product->subcategory_id);
    $imgService = app(\App\Services\ImageUploadService::class);
    $existingImageUrl = $product->image_s3_key ? $imgService->url($product->image_s3_key) : null;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="category_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">Category <span class="text-red-500">*</span></label>
        <select id="category_id" name="category_id" required class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select category</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected($selCategory == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="subcategory_id" class="block text-sm font-medium text-[#5e5873] mb-1.5">Sub Category</label>
        <select id="subcategory_id" name="subcategory_id" class="js-searchable w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">Select sub category</option>
        </select>
    </div>
    <div>
        <label for="name" class="block text-sm font-medium text-[#5e5873] mb-1.5">Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name', $product->name) }}" placeholder="Enter product name"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="base_price" class="block text-sm font-medium text-[#5e5873] mb-1.5">Base Price <span class="text-red-500">*</span></label>
        <div class="flex">
            <span class="px-3 py-2 rounded-l-md border border-r-0 border-[#d8d6de] bg-gray-50 text-[#6e6b7b] text-sm">$</span>
            <input id="base_price" name="base_price" type="number" step="0.01" min="0" required value="{{ old('base_price', $product->base_price) }}" placeholder="Enter base price"
                   class="flex-1 rounded-r-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        </div>
        @error('base_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="number_of_revisions" class="block text-sm font-medium text-[#5e5873] mb-1.5">Number of Revisions/Refinements</label>
        <input id="number_of_revisions" name="number_of_revisions" type="number" min="0" value="{{ old('number_of_revisions', $product->number_of_revisions) }}"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
    </div>
    <div>
        <label for="product_term_months" class="block text-sm font-medium text-[#5e5873] mb-1.5">Product Term (in months)</label>
        <input id="product_term_months" name="product_term_months" type="number" min="0" value="{{ old('product_term_months', $product->product_term_months) }}"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
    </div>
    <div>
        <label for="from_step" class="block text-sm font-medium text-[#5e5873] mb-1.5">From Step</label>
        <input id="from_step" name="from_step" type="number" min="0" value="{{ old('from_step', $product->from_step ?? 1) }}"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
    </div>
    <div>
        <label for="to_step" class="block text-sm font-medium text-[#5e5873] mb-1.5">To Step</label>
        <input id="to_step" name="to_step" type="number" min="0" value="{{ old('to_step', $product->to_step) }}"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('to_step')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="url" class="block text-sm font-medium text-[#5e5873] mb-1.5">URL</label>
        <input id="url" name="url" type="url" maxlength="500" value="{{ old('url', $product->url) }}" placeholder="www.example.com"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-[#5e5873] mb-1.5">Status <span class="text-red-500">*</span></label>
        <select id="status" name="status" required class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="ACTIVE"   @selected(old('status', $product->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $product->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="image" class="block text-sm font-medium text-[#5e5873] mb-1.5">Image @if (! $product->exists)<span class="text-red-500">*</span>@endif</label>
        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png" @if (! $product->exists) required @endif
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        <p class="text-xs text-[#b9b9c3] mt-1">JPG or PNG, max 10 MB.</p>
        @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        @if ($existingImageUrl)
            <div class="mt-2">
                <p class="text-xs text-[#b9b9c3]">Current image:</p>
                <img src="{{ $existingImageUrl }}" alt="" class="mt-1 max-h-24 rounded border border-[#d8d6de]">
            </div>
        @endif
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-[#5e5873] mb-1.5">Description</label>
        <textarea id="description" name="description" rows="6" class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">{{ old('description', $product->description) }}</textarea>
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button type="submit" class="px-5 py-2 bg-[#8cc63f] hover:bg-[#7ab332] text-white text-sm font-medium rounded-md shadow-sm">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.products.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Cancel</a>
</div>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
<script>
obCascade({ parent:'#category_id', child:'#subcategory_id', url:'{{ route('admin.ajax.subcategories') }}', paramName:'category_id', placeholder:'Select sub category', preselectId: @json($selSubcategory) });
ClassicEditor.create(document.querySelector('#description'), {
    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo']
}).catch(err => console.error(err));
</script>
@endpush
