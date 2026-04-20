@extends('layouts.admin')
@section('title', 'Sub Categories')
@section('page_title', 'Product Sub Categories')

@section('content')
<section id="subcategories-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Product Sub Categories</h4>
            <a href="{{ route('admin.product-subcategories.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Sub Category
            </a>
        </div>

        <div class="card-body py-1">
            <form id="subcategoriesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-5">
                    <select name="category_id" class="js-searchable form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" class="form-control"></div>
                <div class="col-md-3"><a href="{{ route('admin.product-subcategories.index') }}" class="btn btn-outline-secondary w-100">Clear</a></div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subcategories as $sub)
                        <tr>
                            <td>{{ $sub->category?->name ?? '—' }}</td>
                            <td class="fw-bolder">{{ $sub->name }}</td>
                            <td>
                                @if ($sub->products_count > 0)
                                    {{ $sub->products_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $sub->status ? 'success' : 'danger' }}">{{ $sub->status ? 'ACTIVE' : 'INACTIVE' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.product-subcategories.show', $sub) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.product-subcategories.edit', $sub) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                @if ($sub->products_count === 0)
                                    <form method="POST" action="{{ route('admin.product-subcategories.destroy', $sub) }}" class="d-inline js-delete-form" data-confirm="Delete '{{ $sub->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger" disabled title="Has linked products — cannot delete"><i data-feather="trash-2"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-2">No sub-categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $subcategories->links() }}</div>
    </div>
</section>

@push('scripts')<script>obAutoFilter('#subcategoriesFilter');</script>@endpush
@endsection
