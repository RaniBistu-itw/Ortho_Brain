@extends('layouts.admin')
@section('title', 'Categories')
@section('page_title', 'Product Categories')

@push('styles')
<style>
    /* ── Product Categories — modernized surface ─────────────────────── */
    .pc-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .pc-kpis { grid-template-columns: 1fr; } }
    .pc-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .pc-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .pc-kpi__icon svg { width: 20px; height: 20px; }
    .pc-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .pc-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .pc-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .pc-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .pc-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }

    .pc-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .pc-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .pc-toolbar__search { flex: 1 1 260px; max-width: 380px; }
    .pc-toolbar__status { flex: 0 0 180px; }
    .pc-toolbar__spacer { flex: 1; }
    .pc-toolbar__actions { display: flex; gap: .5rem; }

    .pc-table { margin-bottom: 0; }
    .pc-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .pc-table tbody tr { transition: background-color .15s ease; }
    .pc-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .pc-table .pc-row-new { animation: pc-row-flash 1.4s ease-out; }
    @keyframes pc-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .pc-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .pc-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .pc-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .pc-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .pc-action-group { display: inline-flex; gap: .25rem; }
    .pc-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .pc-action-group .btn svg { width: 15px; height: 15px; }

    .pc-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .pc-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Bulk-add drawer */
    .pc-drawer { width: min(560px, 100vw); }
    .pc-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .pc-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .pc-bulk-row { display: grid; grid-template-columns: 1fr 160px 36px; gap: .5rem; align-items: start; margin-bottom: .5rem; }
    .pc-bulk-row .pc-bulk-remove { width: 36px; height: 38px; padding: 0; display: grid; place-items: center; }
    .pc-bulk-row__err { grid-column: 1 / -1; font-size: .78rem; color: #ea5455; margin-top: -.25rem; }
</style>
@endpush

@section('content')
<section id="categories-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    <div class="pc-kpis">
        <div class="pc-kpi">
            <div class="pc-kpi__icon pc-kpi__icon--total"><i data-feather="layers"></i></div>
            <div>
                <div class="pc-kpi__label">Total</div>
                <div class="pc-kpi__value" data-stat="total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="pc-kpi">
            <div class="pc-kpi__icon pc-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="pc-kpi__label">Active</div>
                <div class="pc-kpi__value" data-stat="active">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="pc-kpi">
            <div class="pc-kpi__icon pc-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="pc-kpi__label">Inactive</div>
                <div class="pc-kpi__value" data-stat="inactive">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

    <div class="card pc-card">
        {{-- ── Toolbar: search / filter / actions ──────────────── --}}
        <div class="pc-toolbar">
            <form id="categoriesFilter" method="GET" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <div class="pc-toolbar__search">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i data-feather="search" style="width:16px;height:16px;"></i></span>
                        <input type="text" name="search" placeholder="Search categories…" value="{{ request('search') }}" class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="pc-toolbar__status">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status')==='ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status')==='INACTIVE')>Inactive</option>
                    </select>
                </div>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                @endif
            </form>
            <div class="pc-toolbar__spacer"></div>
            <div class="pc-toolbar__actions">
                <button type="button" class="btn btn-outline-primary" id="pcBulkOpen">
                    <i data-feather="layers" class="me-25"></i> Bulk add
                </button>
                <button type="button" class="btn btn-primary" id="pcDrawerOpen" data-mode="create">
                    <i data-feather="plus" class="me-25"></i> Add Category
                </button>
            </div>
        </div>

        {{-- ── Table ───────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover pc-table" id="pcTable">
                <thead>
                    <tr>
                        <th style="width: 40%;">Name</th>
                        <th>Sub-categories</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="pcTbody">
                    @forelse ($categories as $cat)
                        @include('admin.product-categories._row', ['cat' => $cat])
                    @empty
                        <tr id="pcEmptyRow">
                            <td colspan="5">
                                <div class="pc-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No categories yet</div>
                                    <div class="small">Click <em>Add Category</em> to create one, or <em>Bulk add</em> to create several.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="card-body border-top">{{ $categories->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Single-record drawer (create / edit) ───────────────────── --}}
<div class="offcanvas offcanvas-end pc-drawer" tabindex="-1" id="pcDrawer" aria-labelledby="pcDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="pcDrawerTitle">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="pcDrawerForm" novalidate>
            <input type="hidden" id="pcDrawerId" value="">
            <div class="mb-1">
                <label class="form-label" for="pcDrawerName">Category Name<span class="text-danger">*</span></label>
                <input type="text" id="pcDrawerName" class="form-control" maxlength="255" required>
                <div class="invalid-feedback d-block" id="pcDrawerNameErr"></div>
            </div>
            <div class="mb-1">
                <label class="form-label" for="pcDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="pcDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="pcDrawerSubmit">
            <span class="pc-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

{{-- ── Bulk-add drawer ────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end pc-drawer" tabindex="-1" id="pcBulkDrawer" aria-labelledby="pcBulkTitle">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-0" id="pcBulkTitle">Bulk add categories</h5>
            <small class="text-muted">Add up to 50 categories in a single save.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="pcBulkRows"></div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="pcBulkAddRow">
            <i data-feather="plus" style="width:14px;height:14px;"></i> Add another row
        </button>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="pcBulkSubmit">
            <span class="pc-bulk-label">Save all</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Toast helper (SweetAlert2 mini-toast) ─────────────────
    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store:  @json(route('admin.product-categories.ajax.store')),
        bulk:   @json(route('admin.product-categories.ajax.bulk')),
        index:  @json(route('admin.product-categories.index')),
    };

    // ── Auto-submit the filter form (keep existing behavior) ──
    obAutoFilter('#categoriesFilter');

    // ── Helpers: DOM / stats ───────────────────────────────────
    const $tbody = $('#pcTbody');
    function removeEmptyPlaceholder() { $('#pcEmptyRow').remove(); }
    function bumpStat(key, delta) {
        const el = document.querySelector(`[data-stat="${key}"]`);
        if (!el) return;
        el.textContent = (parseInt(el.textContent, 10) || 0) + delta;
    }
    function incrementStatsFor(status) {
        bumpStat('total', 1);
        bumpStat(status === 'ACTIVE' ? 'active' : 'inactive', 1);
    }

    // Build a row matching the server-rendered partial exactly enough to behave.
    function buildRowHtml(cat) {
        const statusClass = cat.status === 'ACTIVE' ? 'pc-status--active' : 'pc-status--inactive';
        const hasDeps = cat.subcategories_count > 0 || cat.products_count > 0;
        const deleteBtn = hasDeps
            ? `<button type="button" class="btn btn-outline-danger js-delete-blocked" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(cat.name)}' — it has linked sub-categories or products. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${cat.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete category '${escapeHtml(cat.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="pc-row-new" data-id="${cat.id}">
                <td class="fw-bolder pc-cell-name">${escapeHtml(cat.name)}</td>
                <td>${cat.subcategories_count > 0 ? cat.subcategories_count : '<span class="text-muted">—</span>'}</td>
                <td>${cat.products_count > 0 ? cat.products_count : '<span class="text-muted">—</span>'}</td>
                <td><span class="pc-status ${statusClass}" data-status="${cat.status}">${cat.status}</span></td>
                <td class="text-end">
                    <div class="pc-action-group">
                        <a href="${cat.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary pc-edit" title="Edit"
                                data-id="${cat.id}" data-name="${escapeHtml(cat.name)}" data-status="${cat.status}"
                                data-url="${cat.edit_url}"><i data-feather="edit-2"></i></button>
                        ${deleteBtn}
                    </div>
                </td>
            </tr>`;
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    function insertRow(cat) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(cat));
        if (window.feather) window.feather.replace();
    }

    function updateRow(cat) {
        const $row = $(`#pcTbody tr[data-id="${cat.id}"]`);
        if (!$row.length) return;
        $row.find('.pc-cell-name').text(cat.name);
        const $status = $row.find('.pc-status');
        $status.text(cat.status)
            .removeClass('pc-status--active pc-status--inactive')
            .addClass(cat.status === 'ACTIVE' ? 'pc-status--active' : 'pc-status--inactive')
            .attr('data-status', cat.status);
        // keep data-* on the edit button fresh
        const $edit = $row.find('.pc-edit');
        $edit.attr('data-name', cat.name).attr('data-status', cat.status);
        // flash highlight
        $row.removeClass('pc-row-new'); void $row[0].offsetWidth; $row.addClass('pc-row-new');
    }

    // ── Single-record drawer (create / edit) ───────────────────
    const drawerEl  = document.getElementById('pcDrawer');
    const drawer    = new bootstrap.Offcanvas(drawerEl);

    function openDrawerCreate() {
        $('#pcDrawerTitle').text('Add Category');
        $('.pc-drawer-label', drawerEl).text('Save');
        $('#pcDrawerForm').removeAttr('data-url');
        $('#pcDrawerId').val('');
        $('#pcDrawerName').val('').removeClass('is-invalid');
        $('#pcDrawerStatus').val('ACTIVE');
        $('#pcDrawerNameErr').text('');
        drawer.show();
        setTimeout(() => $('#pcDrawerName').focus(), 250);
    }

    function openDrawerEdit(data) {
        $('#pcDrawerTitle').text('Edit Category');
        $('.pc-drawer-label', drawerEl).text('Update');
        $('#pcDrawerForm').attr('data-url', data.url);
        $('#pcDrawerId').val(data.id);
        $('#pcDrawerName').val(data.name).removeClass('is-invalid');
        $('#pcDrawerStatus').val(data.status);
        $('#pcDrawerNameErr').text('');
        drawer.show();
        setTimeout(() => $('#pcDrawerName').focus(), 250);
    }

    $('#pcDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.pc-edit', function () {
        openDrawerEdit({
            id:     $(this).data('id'),
            name:   $(this).data('name'),
            status: $(this).data('status'),
            url:    $(this).data('url'),
        });
    });

    $('#pcDrawerSubmit').on('click', function () {
        const id     = $('#pcDrawerId').val();
        const name   = $('#pcDrawerName').val().trim();
        const status = $('#pcDrawerStatus').val();

        $('#pcDrawerName').removeClass('is-invalid');
        $('#pcDrawerNameErr').text('');
        if (!name) {
            $('#pcDrawerName').addClass('is-invalid').focus();
            $('#pcDrawerNameErr').text('Name is required.');
            return;
        }

        const isEdit = !!id;
        const url    = isEdit ? $('#pcDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? { _token: CSRF, _method: 'PUT', name, status }
            : { _token: CSRF, name, status };

        const $btn = $('#pcDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    // status may have flipped — adjust KPIs
                    const $old = $(`#pcTbody tr[data-id="${res.category.id}"] .pc-status`);
                    const prev = $old.data('status');
                    if (prev && prev !== res.category.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.category.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.category);
                } else {
                    insertRow(res.category);
                    incrementStatsFor(res.category.status);
                }
                toast('success', res.message);
                drawer.hide();
            })
            .fail((xhr) => {
                const msg = xhr.responseJSON?.errors?.name?.[0] || xhr.responseJSON?.message || 'Could not save.';
                $('#pcDrawerName').addClass('is-invalid');
                $('#pcDrawerNameErr').text(msg);
            })
            .always(() => {
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
            });
    });

    // ── Bulk-add drawer ────────────────────────────────────────
    const bulkEl = document.getElementById('pcBulkDrawer');
    const bulk   = new bootstrap.Offcanvas(bulkEl);

    function buildBulkRow(name = '', status = 'ACTIVE') {
        const $row = $(`
            <div class="pc-bulk-row">
                <input type="text" class="form-control pc-bulk-name" placeholder="Category name" maxlength="255">
                <select class="form-select pc-bulk-status">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <button type="button" class="btn btn-outline-danger pc-bulk-remove" title="Remove row">
                    <i data-feather="x" style="width:14px;height:14px;"></i>
                </button>
                <div class="pc-bulk-row__err"></div>
            </div>
        `);
        $row.find('.pc-bulk-name').val(name);
        $row.find('.pc-bulk-status').val(status);
        return $row;
    }

    function resetBulk() {
        $('#pcBulkRows').empty()
            .append(buildBulkRow()).append(buildBulkRow()).append(buildBulkRow());
        if (window.feather) window.feather.replace();
    }

    $('#pcBulkOpen').on('click', () => { resetBulk(); bulk.show(); });

    $('#pcBulkAddRow').on('click', () => {
        const $r = buildBulkRow();
        $('#pcBulkRows').append($r);
        if (window.feather) window.feather.replace();
        $r.find('.pc-bulk-name').focus();
    });

    $(document).on('click', '.pc-bulk-remove', function () {
        const $rows = $('#pcBulkRows .pc-bulk-row');
        if ($rows.length <= 1) {
            $(this).closest('.pc-bulk-row').find('.pc-bulk-name').val('');
            return;
        }
        $(this).closest('.pc-bulk-row').remove();
    });

    $('#pcBulkSubmit').on('click', function () {
        const rows = [];
        const $rowEls = $('#pcBulkRows .pc-bulk-row');
        $rowEls.find('.pc-bulk-row__err').text('');
        $rowEls.find('.pc-bulk-name').removeClass('is-invalid');

        let firstInvalid = null;
        $rowEls.each(function (i) {
            const name   = $(this).find('.pc-bulk-name').val().trim();
            const status = $(this).find('.pc-bulk-status').val();
            if (name) {
                rows.push({ name, status });
            } else if (i === 0) {
                if (!firstInvalid) firstInvalid = $(this);
                $(this).find('.pc-bulk-name').addClass('is-invalid');
                $(this).find('.pc-bulk-row__err').text('Name is required.');
            }
        });

        if (firstInvalid) { firstInvalid.find('.pc-bulk-name').focus(); return; }
        if (rows.length === 0) { toast('info', 'Add at least one category.'); return; }

        const $btn = $('#pcBulkSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: ROUTES.bulk,
            method: 'POST',
            data: { _token: CSRF, categories: rows },
            dataType: 'json'
        })
        .done((res) => {
            if (!res || !res.ok) return;
            res.categories.forEach((c) => { insertRow(c); incrementStatsFor(c.status); });
            toast('success', res.message);
            bulk.hide();
        })
        .fail((xhr) => {
            const errs = xhr.responseJSON?.errors || {};
            // Map errors like `categories.0.name` back to the corresponding row.
            Object.keys(errs).forEach((key) => {
                const m = key.match(/^categories\.(\d+)\.(name|status)$/);
                if (!m) return;
                const $row = $('#pcBulkRows .pc-bulk-row').eq(parseInt(m[1], 10));
                if (!$row.length) return;
                $row.find('.pc-bulk-name').addClass('is-invalid');
                $row.find('.pc-bulk-row__err').text(errs[key][0]);
            });
            toast('error', 'Please fix the highlighted rows.');
        })
        .always(() => {
            $btn.prop('disabled', false);
            $btn.find('.spinner-border').addClass('d-none');
        });
    });

    // Replace feather icons once on load for dynamically added bits
    if (window.feather) window.feather.replace();
})();
</script>
@endpush
@endsection
