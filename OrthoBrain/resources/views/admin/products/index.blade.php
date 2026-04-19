@extends('layouts.admin')
@section('title', 'Products')

@section('content')
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-semibold text-[#5e5873]">Products</h1>
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#5bc0de] hover:bg-[#46b8da] text-white text-sm font-medium rounded-md shadow-sm transition">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
</div>

<div class="bg-white border border-[#ebe9f1] rounded-lg p-5">
    <form id="productsFilter" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
        <select id="filter_category_id" name="category_id" data-ob-cascade-parent class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All categories</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <select id="filter_subcategory_id" name="subcategory_id" class="js-searchable rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All sub categories</option>
            @foreach ($subcategories as $sub)<option value="{{ $sub->id }}" data-category-id="{{ $sub->category_id }}" @selected(request('subcategory_id')==$sub->id)>{{ $sub->name }}</option>@endforeach
        </select>
        <input type="text" name="search" placeholder="Search product..." value="{{ request('search') }}" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
        <select name="status" class="rounded-md border border-[#d8d6de] px-3 py-2 text-sm focus:border-[#5bc0de] outline-none">
            <option value="">All statuses</option>
            <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
            <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
        </select>
        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-sm text-center border border-[#d8d6de] text-[#6e6b7b] hover:bg-gray-50 rounded-md transition">Clear</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead><tr class="border-b border-[#ebe9f1]">
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Image</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Category</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Sub</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Name</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Price</th>
                <th class="text-left text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Status</th>
                <th class="text-right text-xs font-semibold text-[#6e6b7b] uppercase px-3 py-3">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-[#ebe9f1]">
                @forelse ($products as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3">
                            @if ($p->image_s3_key)
                                <img src="{{ app(\App\Services\ImageUploadService::class)->url($p->image_s3_key) }}" alt="{{ $p->name }}" class="w-12 h-12 object-cover rounded-lg border border-[#ebe9f1] bg-white shadow-sm">
                            @else
                                <div class="w-12 h-12 flex items-center justify-center rounded-lg border border-dashed border-[#d8d6de] bg-[#f8f8f8] text-[#b9b9c3]" title="No image">
                                    <i class="bi bi-image text-lg"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm">{{ $p->category?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm">{{ $p->subcategory?->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm font-medium text-[#5e5873]">{{ $p->name }}</td>
                        <td class="px-3 py-3 text-sm">${{ number_format((float) $p->base_price, 2) }}</td>
                        <td class="px-3 py-3"><span class="px-2 py-1 text-xs font-semibold rounded {{ $p->status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $p->status }}</span></td>
                        <td class="px-3 py-3 text-right">
                            <a href="{{ route('admin.products.show', $p) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#8cc63f] text-[#8cc63f] hover:bg-[#8cc63f] hover:text-white transition" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.products.edit', $p) }}" class="inline-flex items-center justify-center w-8 h-8 rounded border border-[#5bc0de] text-[#5bc0de] hover:bg-[#5bc0de] hover:text-white transition" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="inline js-delete-form" data-confirm="Delete product '{{ $p->name }}'?">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded border border-red-400 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-6 text-center text-sm text-[#b9b9c3]">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</div>

@push('scripts')
<script>
obPreloadedCascade({
    parent: '#filter_category_id',
    child: '#filter_subcategory_id',
    parentAttr: 'data-category-id',
});
obAutoFilter('#productsFilter');
</script>
@endpush
@endsection
