@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="name" class="block text-sm font-medium text-[#5e5873] mb-1.5">Scanner Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" maxlength="255" required value="{{ old('name', $scanner->name) }}" placeholder="Enter scanner name"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="portal_link" class="block text-sm font-medium text-[#5e5873] mb-1.5">Scanner Portal Link</label>
        <input id="portal_link" name="portal_link" type="url" maxlength="255" value="{{ old('portal_link', $scanner->portal_link) }}" placeholder="https://www.example.com"
               class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        @error('portal_link')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="portal_password" class="block text-sm font-medium text-[#5e5873] mb-1.5">Portal Password</label>
        <div class="flex">
            <input id="portal_password" name="portal_password" type="password" maxlength="255" value="{{ old('portal_password', $scanner->portal_password) }}" placeholder="••••••••"
                   class="flex-1 rounded-l-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <button type="button" onclick="const i=document.getElementById('portal_password'); i.type = i.type==='password' ? 'text' : 'password';"
                    class="px-3 rounded-r-md border border-l-0 border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50"><i class="bi bi-eye"></i></button>
        </div>
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-[#5e5873] mb-1.5">Status <span class="text-red-500">*</span></label>
        <select id="status" name="status" required class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="ACTIVE"   @selected(old('status', $scanner->status) === 'ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(old('status', $scanner->status) === 'INACTIVE')>Inactive</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-[#5e5873] mb-1.5">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="255" placeholder="Description goes here"
                  class="w-full rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">{{ old('description', $scanner->description) }}</textarea>
    </div>
</div>
<div class="mt-6 flex gap-3">
    <button type="submit" class="px-5 py-2 bg-[#8cc63f] hover:bg-[#7ab332] text-white text-sm font-medium rounded-md shadow-sm">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ route('admin.scanners.index') }}" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md">Cancel</a>
</div>
