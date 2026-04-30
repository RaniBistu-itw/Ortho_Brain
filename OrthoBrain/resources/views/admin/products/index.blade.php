@extends('layouts.admin')
@section('title', 'Products')
@section('page_title', 'Products')

@section('content')
@include('admin._partials.inline_status_dropdown')
<section id="products-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    @include('admin._partials.stat_cards', [
        'cards' => [
            ['label' => 'Total',    'value' => $stats['total'],    'icon' => 'box',          'tone' => 'primary', 'stat_key' => 'total'],
            ['label' => 'Active',   'value' => $stats['active'],   'icon' => 'check-circle', 'tone' => 'success', 'stat_key' => 'active'],
            ['label' => 'Inactive', 'value' => $stats['inactive'], 'icon' => 'slash',        'tone' => 'danger',  'stat_key' => 'inactive'],
        ],
    ])

    <div class="card">
        @php
            $selectedCategory    = $categories->firstWhere('id', request('category_id'));
            $selectedSubcategory = $subcategories->firstWhere('id', request('subcategory_id'));
            $selectedStatus      = request('status');
            $statusLabel         = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;

            if ($selectedSubcategory) {
                $headerTitle = 'All ' . ($statusLabel ? $statusLabel . ' ' : '') . $selectedSubcategory->name;
            } elseif ($selectedCategory) {
                $headerTitle = 'All ' . ($statusLabel ? $statusLabel . ' ' : '') . $selectedCategory->name;
            } elseif ($statusLabel) {
                $headerTitle = 'All ' . $statusLabel . ' Products';
            } else {
                $headerTitle = 'All Products';
            }
        @endphp
        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i data-feather="plus" class="me-25"></i> Add Product
            </a>
        </div>

        <div class="card-body py-1">
            <form id="productsFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-2">
                    <select id="filter_category_id" name="category_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <select name="order" class="form-select" aria-label="Sort order">
                        <option value="newest" @selected(request('order', 'newest') === 'newest')>Newest first</option>
                        <option value="oldest" @selected(request('order') === 'oldest')>Oldest first</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.products.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Category', 'key' => 'category', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Sub Category', 'key' => 'subcategory', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Product', 'key' => 'name', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Price', 'key' => 'price', 'default' => 'name'])</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr data-row-href="{{ route('admin.products.show', $p) }}">
                            <td>
                                @php
                                    $imgService = app(\App\Services\ImageUploadService::class);
                                    $imageUrls = $p->images->map(fn ($im) => $imgService->url($im->s3_key))->filter()->values();
                                    $coverUrl  = $imageUrls->first();
                                @endphp
                                @if ($coverUrl)
                                    <div class="ob-list-thumb">
                                        <img src="{{ $coverUrl }}" alt="{{ $p->name }}" class="rounded ob-list-thumb-img"
                                             data-preview-src="{{ $coverUrl }}"
                                             @if ($imageUrls->count() > 1) data-preview-gallery='@json($imageUrls->values())' @endif
                                             style="width: 48px; height: 48px; object-fit: cover;">
                                        @if ($imageUrls->count() > 1)
                                            <span class="ob-list-thumb-badge" title="{{ $imageUrls->count() }} images">+{{ $imageUrls->count() - 1 }}</span>
                                        @endif
                                    </div>
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
                                <select class="ob-status-select" data-inline-status
                                        data-url="{{ route('admin.products.status', $p) }}"
                                        data-status="{{ $p->status }}"
                                        aria-label="Update status for {{ $p->name }}">
                                    <option value="ACTIVE"   @selected($p->status === 'ACTIVE')>Active</option>
                                    <option value="INACTIVE" @selected($p->status === 'INACTIVE')>Inactive</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.products.show', $p) }}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                                    <a href="{{ route('admin.products.edit', $p) }}" class="ob-icon-btn ob-icon-btn--edit" title="Edit"><i data-feather="edit-2"></i></a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="d-inline js-delete-form" data-confirm="Delete product '{{ $p->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                </div>
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
