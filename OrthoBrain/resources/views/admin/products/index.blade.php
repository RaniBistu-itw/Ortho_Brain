@extends('layouts.admin')
@section('title', 'Products')
@section('page_title', 'Products')

@section('content')
<section id="products-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Products</h4>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Product
            </a>
        </div>

        <div class="card-body py-1">
            <form id="productsFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-3">
                    <select id="filter_category_id" name="category_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter_subcategory_id" name="subcategory_id" class="js-searchable form-select">
                        <option value="">All sub categories</option>
                        @foreach ($subcategories as $sub)<option value="{{ $sub->id }}" data-category-id="{{ $sub->category_id }}" @selected(request('subcategory_id')==$sub->id)>{{ $sub->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="text" name="search" placeholder="Search product..." value="{{ request('search') }}" class="form-control"></div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2"><a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100">Clear</a></div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Category</th>
                        <th>Sub</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
                            <td>
                                @if ($p->image_s3_key)
                                    <img src="{{ app(\App\Services\ImageUploadService::class)->url($p->image_s3_key) }}" alt="{{ $p->name }}" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    <div class="rounded bg-light-secondary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i data-feather="image" class="text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $p->category?->name ?? '—' }}</td>
                            <td>{{ $p->subcategory?->name ?? '—' }}</td>
                            <td class="fw-bolder">{{ $p->name }}</td>
                            <td>${{ number_format((float) $p->base_price, 2) }}</td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $p->status === 'ACTIVE' ? 'success' : 'danger' }}">{{ $p->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.products.show', $p) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="d-inline js-delete-form" data-confirm="Delete product '{{ $p->name }}'?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-2">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $products->links() }}</div>
    </div>
</section>

@push('scripts')
<script>
obPreloadedCascade({ parent: '#filter_category_id', child: '#filter_subcategory_id', parentAttr: 'data-category-id' });
obAutoFilter('#productsFilter');
</script>
@endpush
@endsection
