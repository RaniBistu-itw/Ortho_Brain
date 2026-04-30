@extends('layouts.admin')
@section('title', 'Sub Categories')
@section('page_title', 'Product Sub Categories')

@push('styles')
<style>
    /* ── Product Sub Categories — drawer + table polish ───────────────── */
    .psc-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .psc-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .psc-toolbar__filter { flex: 0 0 240px; min-width: 200px; }
    .psc-toolbar__search { flex: 1 1 260px; max-width: 380px; }
    .psc-toolbar__spacer { flex: 1; }
    .psc-toolbar__actions { display: flex; gap: .5rem; }

    .psc-table { margin-bottom: 0; }
    .psc-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .psc-table tbody tr { transition: background-color .15s ease; }
    .psc-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .psc-table .psc-row-new { animation: psc-row-flash 1.4s ease-out; }
    @keyframes psc-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .psc-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .psc-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .psc-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .psc-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    /* Body hugs its content so the action bar sits right below the form, not pinned at the panel's bottom edge */
    .psc-drawer .offcanvas-body { flex: 0 1 auto; overflow-y: auto; }
    .psc-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .psc-drawer .form-label { font-weight: 500; }
    /* Keep Select2 inside the drawer visually consistent */
    .psc-drawer .select2-container--default .select2-selection--single { height: calc(2.4rem + 2px); padding: .3rem .4rem; }

    /* Bulk-add drawer rows */
    .psc-bulk-row { display: grid; grid-template-columns: 1fr 140px 36px; gap: .5rem; align-items: start; margin-bottom: .5rem; }
    .psc-bulk-row .psc-bulk-remove { width: 36px; height: 38px; padding: 0; display: grid; place-items: center; }
    .psc-bulk-row__err { grid-column: 1 / -1; font-size: .78rem; color: #ea5455; margin-top: -.25rem; }
</style>
@endpush

@section('content')
@include('admin._partials.inline_status_dropdown')
<section id="subcategories-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    @include('admin._partials.stat_cards', [
        'cards' => [
            ['label' => 'Total',    'value' => $stats['total'],    'icon' => 'layers',       'tone' => 'primary', 'stat_key' => 'total'],
            ['label' => 'Active',   'value' => $stats['active'],   'icon' => 'check-circle', 'tone' => 'success', 'stat_key' => 'active'],
            ['label' => 'Inactive', 'value' => $stats['inactive'], 'icon' => 'slash',        'tone' => 'danger',  'stat_key' => 'inactive'],
        ],
    ])

    <div class="card psc-card">

        @php
            $selectedCategory = $categories->firstWhere('id', request('category_id'));
            $selectedStatus   = request('status');
            $statusLabel      = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;

            if ($selectedCategory) {
                $headerTitle = 'All ' . ($statusLabel ? $statusLabel . ' ' : '') . $selectedCategory->name . ' Sub Categories';
            } elseif ($statusLabel) {
                $headerTitle = 'All ' . $statusLabel . ' Sub Categories';
            } else {
                $headerTitle = 'All Sub Categories';
            }
        @endphp

        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-outline-primary" id="pscBulkOpen">
                    <i data-feather="layers" class="me-25"></i> Bulk add
                </button>
                <button type="button" class="btn btn-primary" id="pscDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add Sub Category
                </button>
            </div>
        </div>

        <div class="card-body py-1">
            <form id="subcategoriesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-3">
                    <select name="category_id" class="js-searchable form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" placeholder="Search sub-categories…" value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="order" class="form-select" aria-label="Sort order">
                        <option value="newest" @selected(request('order', 'newest') === 'newest')>Newest first</option>
                        <option value="oldest" @selected(request('order') === 'oldest')>Oldest first</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.product-subcategories.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Table ───────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover psc-table" id="pscTable">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Category', 'key' => 'category', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Sub-category', 'key' => 'name', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Products', 'key' => 'products_count', 'default' => 'name'])</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="pscTbody">
                    @forelse ($subcategories as $sub)
                        @php
                            $statusStr = $sub->status ? 'ACTIVE' : 'INACTIVE';
                            $hasProducts = $sub->products_count > 0;
                        @endphp
                        <tr data-id="{{ $sub->id }}" data-row-href="{{ route('admin.product-subcategories.show', $sub) }}">
                            <td class="psc-cell-category">{{ $sub->category?->name ?? '—' }}</td>
                            <td class="fw-bolder psc-cell-name">{{ $sub->name }}</td>
                            <td class="psc-cell-products">
                                @if ($hasProducts)
                                    {{ $sub->products_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <select class="ob-status-select" data-inline-status
                                        data-url="{{ route('admin.product-subcategories.status', $sub) }}"
                                        data-status="{{ $statusStr }}"
                                        aria-label="Update status for {{ $sub->name }}">
                                    <option value="ACTIVE"   @selected($statusStr === 'ACTIVE')>Active</option>
                                    <option value="INACTIVE" @selected($statusStr === 'INACTIVE')>Inactive</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.product-subcategories.show', $sub) }}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="ob-icon-btn ob-icon-btn--edit psc-edit" title="Edit"
                                            data-id="{{ $sub->id }}"
                                            data-category-id="{{ $sub->category_id }}"
                                            data-name="{{ $sub->name }}"
                                            data-description="{{ $sub->description }}"
                                            data-status="{{ $statusStr }}"
                                            data-url="{{ route('admin.product-subcategories.ajax.update', $sub) }}">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    @if (! $hasProducts)
                                        <form method="POST" action="{{ route('admin.product-subcategories.destroy', $sub) }}" class="d-inline js-delete-form" data-confirm="Delete '{{ $sub->name }}'?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $sub->name }}' — it has linked products. Remove them first.">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="pscEmptyRow">
                            <td colspan="5">
                                <div class="psc-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No sub-categories yet</div>
                                    <div class="small">Click <em>Add Sub Category</em> to create one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subcategories->hasPages())
            <div class="card-body border-top">{{ $subcategories->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Slide-over drawer (create / edit) ─────────────────────── --}}
<div class="offcanvas offcanvas-end psc-drawer" tabindex="-1" id="pscDrawer" aria-labelledby="pscDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="pscDrawerTitle">Add Sub Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="pscDrawerForm" novalidate autocomplete="off">
            <input type="hidden" id="pscDrawerId" value="">

            <div class="mb-1">
                <label class="form-label" for="pscDrawerCategory">Category<span class="text-danger">*</span></label>
                <select id="pscDrawerCategory" class="form-select">
                    <option value="">Select category</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback d-block" id="pscDrawerCategoryErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="pscDrawerName">Sub Category Name<span class="text-danger">*</span></label>
                <input type="text" id="pscDrawerName" class="form-control" maxlength="100" required>
                <div class="invalid-feedback d-block" id="pscDrawerNameErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="pscDrawerDescription">Description</label>
                <textarea id="pscDrawerDescription" class="form-control" rows="4"></textarea>
                <div class="invalid-feedback d-block" id="pscDrawerDescriptionErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="pscDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="pscDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <div class="invalid-feedback d-block" id="pscDrawerStatusErr"></div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="pscDrawerSubmit">
            <span class="psc-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

{{-- ── Bulk-add drawer ────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end psc-drawer" tabindex="-1" id="pscBulkDrawer" aria-labelledby="pscBulkTitle">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-0" id="pscBulkTitle">Bulk add sub-categories</h5>
            <small class="text-muted">Pick a category, then add up to 50 sub-categories in one save.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-2">
            <label class="form-label" for="pscBulkCategory">Category<span class="text-danger">*</span></label>
            <select id="pscBulkCategory" class="form-select">
                <option value="">Select category</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback d-block" id="pscBulkCategoryErr"></div>
        </div>
        <div id="pscBulkRows"></div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="pscBulkAddRow">
            <i data-feather="plus" style="width:14px;height:14px;"></i> Add another row
        </button>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="pscBulkSubmit">
            <span class="psc-bulk-label">Save all</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    // Keep existing auto-filter behavior for the list toolbar
    obAutoFilter('#subcategoriesFilter');

    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store:        @json(route('admin.product-subcategories.ajax.store')),
        bulk:         @json(route('admin.product-subcategories.ajax.bulk')),
        checkUnique:  @json(route('admin.product-subcategories.ajax.check-unique')),
    };

    function debounce(fn, ms) {
        let t;
        return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), ms); };
    }

    const $tbody = $('#pscTbody');

    function bumpStat(key, delta) {
        const el = document.querySelector(`[data-stat="${key}"]`);
        if (!el) return;
        el.textContent = (parseInt(el.textContent, 10) || 0) + delta;
    }
    function incrementStatsFor(status) {
        bumpStat('total', 1);
        bumpStat(status === 'ACTIVE' ? 'active' : 'inactive', 1);
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    function removeEmptyPlaceholder() { $('#pscEmptyRow').remove(); }

    function buildRowHtml(sub) {
        const hasProducts = sub.products_count > 0;
        const productsCell = hasProducts
            ? String(sub.products_count)
            : '<span class="text-muted">—</span>';
        const deleteBtn = hasProducts
            ? `<button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(sub.name)}' — it has linked products. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${sub.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete '${escapeHtml(sub.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="psc-row-new" data-id="${sub.id}" data-row-href="${sub.show_url}">
                <td class="psc-cell-category">${escapeHtml(sub.category_name)}</td>
                <td class="fw-bolder psc-cell-name">${escapeHtml(sub.name)}</td>
                <td class="psc-cell-products">${productsCell}</td>
                <td>
                    <select class="ob-status-select" data-inline-status data-url="${sub.status_url}" data-status="${sub.status}" aria-label="Update status for ${escapeHtml(sub.name)}">
                        <option value="ACTIVE"${sub.status === 'ACTIVE' ? ' selected' : ''}>Active</option>
                        <option value="INACTIVE"${sub.status === 'INACTIVE' ? ' selected' : ''}>Inactive</option>
                    </select>
                </td>
                <td class="text-end">
                    <div class="ob-row-actions">
                        <a href="${sub.show_url}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="ob-icon-btn ob-icon-btn--edit psc-edit" title="Edit"
                                data-id="${sub.id}"
                                data-category-id="${sub.category_id}"
                                data-name="${escapeHtml(sub.name)}"
                                data-description="${escapeHtml(sub.description)}"
                                data-status="${sub.status}"
                                data-url="${sub.update_url}"><i data-feather="edit-2"></i></button>
                        ${deleteBtn}
                    </div>
                </td>
            </tr>`;
    }

    function insertRow(sub) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(sub));
        if (window.feather) window.feather.replace();
    }

    function updateRow(sub) {
        const $row = $(`#pscTbody tr[data-id="${sub.id}"]`);
        if (!$row.length) return;
        $row.find('.psc-cell-category').text(sub.category_name);
        $row.find('.psc-cell-name').text(sub.name);
        $row.find('.psc-cell-products').html(
            sub.products_count > 0 ? String(sub.products_count) : '<span class="text-muted">—</span>'
        );
        const $status = $row.find('.ob-status-select');
        $status.attr('data-status', sub.status).val(sub.status);

        const $edit = $row.find('.psc-edit');
        $edit.attr('data-category-id', sub.category_id)
             .attr('data-name', sub.name)
             .attr('data-description', sub.description)
             .attr('data-status', sub.status)
             .attr('data-url', sub.update_url);

        // flash highlight
        $row.removeClass('psc-row-new'); void $row[0].offsetWidth; $row.addClass('psc-row-new');
    }

    // ── Drawer setup ──────────────────────────────────────────
    const drawerEl = document.getElementById('pscDrawer');
    const drawer   = new bootstrap.Offcanvas(drawerEl);
    let select2Ready = false;

    function ensureCategorySelect2() {
        if (select2Ready) return;
        // Render the dropdown inside the offcanvas so focus + z-index work correctly.
        window.obSearchable('#pscDrawerCategory', { dropdownParent: $(drawerEl) });
        select2Ready = true;
    }

    function clearErrors() {
        $('#pscDrawerCategory, #pscDrawerName, #pscDrawerDescription, #pscDrawerStatus').removeClass('is-invalid');
        $('#pscDrawerCategoryErr, #pscDrawerNameErr, #pscDrawerDescriptionErr, #pscDrawerStatusErr').text('');
    }

    function setFieldError(field, message) {
        const map = {
            category_id: { input: '#pscDrawerCategory', err: '#pscDrawerCategoryErr' },
            name:        { input: '#pscDrawerName',     err: '#pscDrawerNameErr' },
            description: { input: '#pscDrawerDescription', err: '#pscDrawerDescriptionErr' },
            status:      { input: '#pscDrawerStatus',   err: '#pscDrawerStatusErr' },
        };
        const target = map[field];
        if (!target) return;
        $(target.input).addClass('is-invalid');
        $(target.err).text(message);
    }

    function openDrawerCreate() {
        ensureCategorySelect2();
        $('#pscDrawerTitle').text('Add Sub Category');
        $('.psc-drawer-label', drawerEl).text('Save');
        $('#pscDrawerForm').removeAttr('data-url');
        $('#pscDrawerId').val('');
        // Pre-select the currently filtered category, if any
        const preCategory = $('#subcategoriesFilter select[name="category_id"]').val() || '';
        $('#pscDrawerCategory').val(preCategory).trigger('change.select2');
        $('#pscDrawerName').val('');
        $('#pscDrawerDescription').val('');
        $('#pscDrawerStatus').val('ACTIVE');
        clearErrors();
        drawer.show();
    }

    function openDrawerEdit(data) {
        ensureCategorySelect2();
        $('#pscDrawerTitle').text('Edit Sub Category');
        $('.psc-drawer-label', drawerEl).text('Update');
        $('#pscDrawerForm').attr('data-url', data.url);
        $('#pscDrawerId').val(data.id);
        $('#pscDrawerCategory').val(String(data.categoryId || '')).trigger('change.select2');
        $('#pscDrawerName').val(data.name || '');
        $('#pscDrawerDescription').val(data.description || '');
        $('#pscDrawerStatus').val(data.status || 'ACTIVE');
        clearErrors();
        drawer.show();
    }

    // Auto-focus name field once the drawer has finished sliding in
    drawerEl.addEventListener('shown.bs.offcanvas', () => {
        setTimeout(() => $('#pscDrawerName').trigger('focus'), 50);
    });

    // ── Live duplicate check on the single drawer (name + category combo) ─
    const liveCheckDrawer = debounce(function () {
        const name        = $('#pscDrawerName').val().trim();
        const category_id = $('#pscDrawerCategory').val();
        const ignore_id   = $('#pscDrawerId').val() || null;
        if (!name || !category_id) {
            $('#pscDrawerName').removeClass('is-invalid');
            $('#pscDrawerNameErr').text('');
            return;
        }
        $.post(ROUTES.checkUnique, { _token: CSRF, name, category_id, ignore_id })
            .done((res) => {
                if ($('#pscDrawerName').val().trim() !== name) return;
                if (res.available) {
                    $('#pscDrawerName').removeClass('is-invalid');
                    $('#pscDrawerNameErr').text('');
                } else {
                    $('#pscDrawerName').addClass('is-invalid');
                    $('#pscDrawerNameErr').text(res.message || 'Already exists.');
                }
            });
    }, 350);
    $(document).on('input', '#pscDrawerName', liveCheckDrawer);
    // Re-run when the user changes the parent category — uniqueness is scoped per category
    $(document).on('change', '#pscDrawerCategory', liveCheckDrawer);

    // ── Live duplicate check on each bulk row ────────────────────────────
    function bulkRowLiveCheck($input) {
        const $row       = $input.closest('.psc-bulk-row');
        const $err       = $row.find('.psc-bulk-row__err');
        const name       = $input.val().trim();
        const categoryId = $('#pscBulkCategory').val();

        // Within-batch duplicates first (case-insensitive)
        let dupInBatch = false;
        if (name) {
            $('#pscBulkRows .psc-bulk-row').each(function () {
                if (this === $row[0]) return;
                const other = $(this).find('.psc-bulk-name').val().trim().toLowerCase();
                if (other && other === name.toLowerCase()) dupInBatch = true;
            });
        }
        if (dupInBatch) {
            $input.addClass('is-invalid');
            $err.text('Duplicate name in this batch.');
            return;
        }

        if (!name || !categoryId) {
            $input.removeClass('is-invalid');
            $err.text('');
            return;
        }
        $.post(ROUTES.checkUnique, { _token: CSRF, name, category_id: categoryId })
            .done((res) => {
                if ($input.val().trim() !== name) return;
                if (res.available) {
                    $input.removeClass('is-invalid');
                    $err.text('');
                } else {
                    $input.addClass('is-invalid');
                    $err.text(res.message || 'Already exists.');
                }
            });
    }
    const bulkRowLiveCheckDebounced = debounce(function (el) { bulkRowLiveCheck($(el)); }, 350);
    $(document).on('input', '#pscBulkRows .psc-bulk-name', function () {
        bulkRowLiveCheckDebounced(this);
    });
    // Re-check all rows whenever the parent category changes
    $(document).on('change', '#pscBulkCategory', function () {
        $('#pscBulkRows .psc-bulk-name').each(function () { bulkRowLiveCheck($(this)); });
    });

    $('#pscDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.psc-edit', function () {
        openDrawerEdit({
            id:         $(this).data('id'),
            categoryId: $(this).data('category-id'),
            name:       $(this).attr('data-name'),
            description:$(this).attr('data-description') || '',
            status:     $(this).data('status'),
            url:        $(this).data('url'),
        });
    });

    // Client-side check before hitting the server
    function validateLocal(payload) {
        clearErrors();
        let firstInvalid = null;
        if (!payload.category_id) { setFieldError('category_id', 'Category is required.'); firstInvalid = firstInvalid || '#pscDrawerCategory'; }
        if (!payload.name) { setFieldError('name', 'Name is required.'); firstInvalid = firstInvalid || '#pscDrawerName'; }
        else if (payload.name.length > 100) { setFieldError('name', 'Name may not be longer than 100 characters.'); firstInvalid = firstInvalid || '#pscDrawerName'; }
        if (!payload.status) { setFieldError('status', 'Status is required.'); firstInvalid = firstInvalid || '#pscDrawerStatus'; }
        if (firstInvalid) $(firstInvalid).trigger('focus');
        return !firstInvalid;
    }

    // Submit on Enter inside single-line inputs (skip textarea)
    $('#pscDrawerForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#pscDrawerSubmit').trigger('click'); }
    });

    $('#pscDrawerSubmit').on('click', function () {
        const id = $('#pscDrawerId').val();
        const payload = {
            category_id: $('#pscDrawerCategory').val(),
            name:        $('#pscDrawerName').val().trim(),
            description: $('#pscDrawerDescription').val(),
            status:      $('#pscDrawerStatus').val(),
        };

        if (!validateLocal(payload)) return;

        const isEdit = !!id;
        const url    = isEdit ? $('#pscDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? Object.assign({ _token: CSRF, _method: 'PUT' }, payload)
            : Object.assign({ _token: CSRF }, payload);

        const $btn = $('#pscDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    const prev = $(`#pscTbody tr[data-id="${res.subcategory.id}"] .ob-status-select`).attr('data-status');
                    if (prev && prev !== res.subcategory.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.subcategory.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.subcategory);
                } else {
                    insertRow(res.subcategory);
                    incrementStatsFor(res.subcategory.status);
                }
                toast('success', res.message);
                drawer.hide();
            })
            .fail((xhr) => {
                const errs = xhr.responseJSON?.errors;
                if (errs && typeof errs === 'object') {
                    Object.keys(errs).forEach((field) => setFieldError(field, errs[field][0]));
                    const firstKey = Object.keys(errs)[0];
                    const focusMap = {
                        category_id: '#pscDrawerCategory',
                        name:        '#pscDrawerName',
                        description: '#pscDrawerDescription',
                        status:      '#pscDrawerStatus',
                    };
                    if (focusMap[firstKey]) $(focusMap[firstKey]).trigger('focus');
                } else {
                    toast('error', xhr.responseJSON?.message || 'Could not save.');
                }
            })
            .always(() => {
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
            });
    });

    // ── Bulk-add drawer ────────────────────────────────────────
    const bulkEl = document.getElementById('pscBulkDrawer');
    const bulk   = new bootstrap.Offcanvas(bulkEl);
    let bulkSelect2Ready = false;

    function ensureBulkSelect2() {
        if (bulkSelect2Ready) return;
        window.obSearchable('#pscBulkCategory', { dropdownParent: $(bulkEl) });
        bulkSelect2Ready = true;
    }

    function buildBulkRow(name = '', status = 'ACTIVE') {
        const $row = $(`
            <div class="psc-bulk-row">
                <input type="text" class="form-control psc-bulk-name" placeholder="Sub-category name" maxlength="100">
                <select class="form-select psc-bulk-status">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <button type="button" class="btn btn-outline-danger psc-bulk-remove" title="Remove row">
                    <i data-feather="x" style="width:14px;height:14px;"></i>
                </button>
                <div class="psc-bulk-row__err"></div>
            </div>
        `);
        $row.find('.psc-bulk-name').val(name);
        $row.find('.psc-bulk-status').val(status);
        return $row;
    }

    function resetBulk() {
        ensureBulkSelect2();
        const preCategory = $('#subcategoriesFilter select[name="category_id"]').val() || '';
        $('#pscBulkCategory').val(preCategory).trigger('change.select2');
        $('#pscBulkCategory').removeClass('is-invalid');
        $('#pscBulkCategoryErr').text('');
        $('#pscBulkRows').empty()
            .append(buildBulkRow())
            .append(buildBulkRow());
        if (window.feather) window.feather.replace();
    }

    $('#pscBulkOpen').on('click', () => { resetBulk(); bulk.show(); });

    $('#pscBulkAddRow').on('click', () => {
        const $r = buildBulkRow();
        $('#pscBulkRows').append($r);
        if (window.feather) window.feather.replace();
        $r.find('.psc-bulk-name').trigger('focus');
    });

    $(document).on('click', '.psc-bulk-remove', function () {
        const $rows = $('#pscBulkRows .psc-bulk-row');
        if ($rows.length <= 1) {
            $(this).closest('.psc-bulk-row').find('.psc-bulk-name').val('');
            return;
        }
        $(this).closest('.psc-bulk-row').remove();
    });

    $('#pscBulkSubmit').on('click', function () {
        const categoryId = $('#pscBulkCategory').val();
        $('#pscBulkCategory').removeClass('is-invalid');
        $('#pscBulkCategoryErr').text('');
        if (!categoryId) {
            $('#pscBulkCategory').addClass('is-invalid').trigger('focus');
            $('#pscBulkCategoryErr').text('Category is required.');
            return;
        }

        const rows = [];
        const $rowEls = $('#pscBulkRows .psc-bulk-row');
        $rowEls.find('.psc-bulk-row__err').text('');
        $rowEls.find('.psc-bulk-name').removeClass('is-invalid');

        let firstInvalid = null;
        $rowEls.each(function (i) {
            const name   = $(this).find('.psc-bulk-name').val().trim();
            const status = $(this).find('.psc-bulk-status').val();
            if (name) {
                rows.push({ category_id: categoryId, name, status });
            } else if (i === 0) {
                if (!firstInvalid) firstInvalid = $(this);
                $(this).find('.psc-bulk-name').addClass('is-invalid');
                $(this).find('.psc-bulk-row__err').text('Name is required.');
            }
        });

        if (firstInvalid) { firstInvalid.find('.psc-bulk-name').trigger('focus'); return; }
        if (rows.length === 0) { toast('info', 'Add at least one sub-category.'); return; }

        const $btn = $('#pscBulkSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: ROUTES.bulk,
            method: 'POST',
            data: { _token: CSRF, subcategories: rows },
            dataType: 'json'
        })
        .done((res) => {
            if (!res || !res.ok) return;
            res.subcategories.forEach((s) => { insertRow(s); incrementStatsFor(s.status); });
            toast('success', res.message);
            bulk.hide();
        })
        .fail((xhr) => {
            const errs = xhr.responseJSON?.errors || {};
            Object.keys(errs).forEach((key) => {
                const m = key.match(/^subcategories\.(\d+)\.(category_id|name|status)$/);
                if (!m) return;
                const $row = $('#pscBulkRows .psc-bulk-row').eq(parseInt(m[1], 10));
                if (!$row.length) return;
                if (m[2] === 'category_id') {
                    $('#pscBulkCategory').addClass('is-invalid');
                    $('#pscBulkCategoryErr').text(errs[key][0]);
                } else {
                    $row.find('.psc-bulk-name').addClass('is-invalid');
                    $row.find('.psc-bulk-row__err').text(errs[key][0]);
                }
            });
            toast('error', 'Please fix the highlighted rows.');
        })
        .always(() => {
            $btn.prop('disabled', false);
            $btn.find('.spinner-border').addClass('d-none');
        });
    });

    if (window.feather) window.feather.replace();

    // Auto-open the edit drawer when navigated from the show page with ?edit=<id>
    @if ($editId = request('edit'))
        (function () {
            const $btn = $(`#pscTbody tr[data-id="{{ (int) $editId }}"] .psc-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
