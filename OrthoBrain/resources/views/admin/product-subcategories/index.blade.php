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

    .psc-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .psc-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .psc-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .psc-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .psc-action-group { display: inline-flex; gap: .25rem; }
    .psc-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .psc-action-group .btn svg { width: 15px; height: 15px; }

    .psc-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .psc-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .psc-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .psc-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .psc-drawer .offcanvas-body { overflow-y: auto; }
    .psc-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .psc-drawer .form-label { font-weight: 500; }
    /* Keep Select2 inside the drawer visually consistent */
    .psc-drawer .select2-container--default .select2-selection--single { height: calc(2.4rem + 2px); padding: .3rem .4rem; }
</style>
@endpush

@section('content')
<section id="subcategories-list">
    <div class="card psc-card">

        {{-- ── Toolbar: filter / search / add ───────────────────── --}}
        <div class="psc-toolbar">
            <form id="subcategoriesFilter" method="GET" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <div class="psc-toolbar__filter">
                    <select name="category_id" class="js-searchable form-select">
                        <option value="">All categories</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="psc-toolbar__search">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i data-feather="search" style="width:16px;height:16px;"></i></span>
                        <input type="text" name="search" placeholder="Search sub-categories…" value="{{ request('search') }}" class="form-control border-start-0 ps-0">
                    </div>
                </div>
                @if (request('search') || request('category_id'))
                    <a href="{{ route('admin.product-subcategories.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                @endif
            </form>
            <div class="psc-toolbar__spacer"></div>
            <div class="psc-toolbar__actions">
                <button type="button" class="btn btn-primary" id="pscDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add Sub Category
                </button>
            </div>
        </div>

        {{-- ── Table ───────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover psc-table" id="pscTable">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Products</th>
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
                        <tr data-id="{{ $sub->id }}">
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
                                <span class="psc-status psc-status--{{ $sub->status ? 'active' : 'inactive' }}" data-status="{{ $statusStr }}">{{ $statusStr }}</span>
                            </td>
                            <td class="text-end">
                                <div class="psc-action-group">
                                    <a href="{{ route('admin.product-subcategories.show', $sub) }}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="btn btn-outline-primary psc-edit" title="Edit"
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
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $sub->name }}' — it has linked products. Remove them first.">
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
        store: @json(route('admin.product-subcategories.ajax.store')),
    };

    const $tbody = $('#pscTbody');

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    function removeEmptyPlaceholder() { $('#pscEmptyRow').remove(); }

    function buildRowHtml(sub) {
        const statusClass = sub.status === 'ACTIVE' ? 'psc-status--active' : 'psc-status--inactive';
        const hasProducts = sub.products_count > 0;
        const productsCell = hasProducts
            ? String(sub.products_count)
            : '<span class="text-muted">—</span>';
        const deleteBtn = hasProducts
            ? `<button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(sub.name)}' — it has linked products. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${sub.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete '${escapeHtml(sub.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="psc-row-new" data-id="${sub.id}">
                <td class="psc-cell-category">${escapeHtml(sub.category_name)}</td>
                <td class="fw-bolder psc-cell-name">${escapeHtml(sub.name)}</td>
                <td class="psc-cell-products">${productsCell}</td>
                <td><span class="psc-status ${statusClass}" data-status="${sub.status}">${sub.status}</span></td>
                <td class="text-end">
                    <div class="psc-action-group">
                        <a href="${sub.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary psc-edit" title="Edit"
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
        const $status = $row.find('.psc-status');
        $status.text(sub.status)
            .removeClass('psc-status--active psc-status--inactive')
            .addClass(sub.status === 'ACTIVE' ? 'psc-status--active' : 'psc-status--inactive')
            .attr('data-status', sub.status);

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
                if (isEdit) updateRow(res.subcategory);
                else        insertRow(res.subcategory);
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
