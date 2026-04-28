@extends('layouts.admin')
@section('title', 'Products')
@section('page_title', 'Products')

@push('styles')
<style>
    /* ── KPI strip ─────────────────────────────────────────────────── */
    .pr-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .pr-kpis { grid-template-columns: 1fr; } }
    .pr-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .pr-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .pr-kpi__icon svg { width: 20px; height: 20px; }
    .pr-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .pr-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .pr-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .pr-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .pr-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }
</style>
@endpush

@section('content')
<section id="products-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    <div class="pr-kpis">
        <div class="pr-kpi">
            <div class="pr-kpi__icon pr-kpi__icon--total"><i data-feather="layers"></i></div>
            <div>
                <div class="pr-kpi__label">Total</div>
                <div class="pr-kpi__value" data-stat="total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="pr-kpi">
            <div class="pr-kpi__icon pr-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="pr-kpi__label">Active</div>
                <div class="pr-kpi__value" data-stat="active">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="pr-kpi">
            <div class="pr-kpi__icon pr-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="pr-kpi__label">Inactive</div>
                <div class="pr-kpi__value" data-stat="inactive">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

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
                        <th>@include('admin._partials.sort_th', ['label' => 'Status', 'key' => 'status', 'default' => 'name'])</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
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
