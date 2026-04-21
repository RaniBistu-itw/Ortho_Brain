@extends('layouts.admin')
@section('title', 'Categories')
@section('page_title', 'Product Categories')

@section('content')
<section id="categories-list">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Product Categories</h4>
            <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Category
            </a>
        </div>

        <div class="card-body py-1">
            <form id="categoriesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-5"><input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" class="form-control"></div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3"><a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary w-100">Clear</a></div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Sub-categories</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $cat)
                        <tr>
                            <td class="fw-bolder">{{ $cat->name }}</td>
                            <td>
                                @if ($cat->subcategories_count > 0)
                                    {{ $cat->subcategories_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($cat->products_count > 0)
                                    {{ $cat->products_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill badge-light-{{ $cat->status === 'ACTIVE' ? 'success' : 'danger' }}">{{ $cat->status ?? '—' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.product-categories.show', $cat) }}" class="btn btn-icon btn-sm btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                <a href="{{ route('admin.product-categories.edit', $cat) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit"><i data-feather="edit-2"></i></a>
                                @if ($cat->subcategories_count === 0 && $cat->products_count === 0)
                                    <form method="POST" action="{{ route('admin.product-categories.destroy', $cat) }}" class="d-inline js-delete-form" data-confirm="Delete category '{{ $cat->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $cat->name }}' — it has linked sub-categories or products. Remove them first."><i data-feather="trash-2"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-2">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $categories->links() }}</div>
    </div>
</section>

@push('scripts')<script>obAutoFilter('#categoriesFilter');</script>@endpush
@endsection
