@extends('layouts.admin')
@section('title', 'Scanners')
@section('page_title', 'Scanners')

@push('styles')
<style>
    /* ── KPI strip ─────────────────────────────────────────────────── */
    .sc-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .sc-kpis { grid-template-columns: 1fr; } }
    .sc-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .sc-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .sc-kpi__icon svg { width: 20px; height: 20px; }
    .sc-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .sc-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .sc-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .sc-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .sc-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }

    /* ── Scanners — drawer + table polish ───────────────────────────── */
    .sc-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .sc-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .sc-toolbar__search { flex: 1 1 260px; max-width: 380px; }
    .sc-toolbar__status { flex: 0 0 180px; }
    .sc-toolbar__spacer { flex: 1; }
    .sc-toolbar__actions { display: flex; gap: .5rem; }

    .sc-table { margin-bottom: 0; }
    .sc-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .sc-table tbody tr { transition: background-color .15s ease; }
    .sc-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .sc-table .sc-row-new { animation: sc-row-flash 1.4s ease-out; }
    @keyframes sc-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }
    .sc-cell-link a { max-width: 240px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }

    .sc-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .sc-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .sc-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .sc-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .sc-action-group { display: inline-flex; gap: .25rem; }
    .sc-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .sc-action-group .btn svg { width: 15px; height: 15px; }

    .sc-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .sc-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .sc-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .sc-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .sc-drawer .offcanvas-body { overflow-y: auto; }
    .sc-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .sc-drawer .form-label { font-weight: 500; }
    .sc-drawer .sc-pw-toggle { cursor: pointer; user-select: none; }
</style>
@endpush

@section('content')
<section id="scanners-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    <div class="sc-kpis">
        <div class="sc-kpi">
            <div class="sc-kpi__icon sc-kpi__icon--total"><i data-feather="layers"></i></div>
            <div>
                <div class="sc-kpi__label">Total</div>
                <div class="sc-kpi__value" data-stat="total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="sc-kpi">
            <div class="sc-kpi__icon sc-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="sc-kpi__label">Active</div>
                <div class="sc-kpi__value" data-stat="active">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="sc-kpi">
            <div class="sc-kpi__icon sc-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="sc-kpi__label">Inactive</div>
                <div class="sc-kpi__value" data-stat="inactive">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

    <div class="card sc-card">

        @php
            $selectedStatus = request('status');
            $statusLabel    = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;
            $headerTitle    = $statusLabel ? 'All ' . $statusLabel . ' Scanners' : 'All Scanners';
        @endphp

        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <button type="button" class="btn btn-primary" id="scDrawerOpen">
                <i data-feather="plus" class="me-25"></i> Add Scanner
            </button>
        </div>

        <div class="card-body py-1">
            <form id="scannersFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-5">
                    <input type="text" name="search" placeholder="Search scanners…" value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.scanners.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Table ───────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover sc-table" id="scTable">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Scanner', 'key' => 'name', 'default' => 'name'])</th>
                        <th>Description</th>
                        <th>Portal Link</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Status', 'key' => 'status', 'default' => 'name'])</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="scTbody">
                    @forelse ($scanners as $sc)
                        <tr data-id="{{ $sc->id }}">
                            <td class="fw-bolder sc-cell-name">{{ $sc->name }}</td>
                            <td class="sc-cell-description">
                                @if ($sc->description)
                                    {{ \Illuminate\Support\Str::limit($sc->description, 60) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="sc-cell-link">
                                @if ($sc->portal_link)
                                    <a href="{{ $sc->portal_link }}" target="_blank" rel="noopener" class="text-primary">{{ $sc->portal_link }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="sc-status sc-status--{{ $sc->status === 'ACTIVE' ? 'active' : 'inactive' }}" data-status="{{ $sc->status }}">{{ $sc->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="sc-action-group">
                                    <a href="{{ route('admin.scanners.show', $sc) }}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="btn btn-outline-primary sc-edit" title="Edit"
                                            data-id="{{ $sc->id }}"
                                            data-name="{{ $sc->name }}"
                                            data-description="{{ $sc->description }}"
                                            data-portal-link="{{ $sc->portal_link }}"
                                            data-portal-password="{{ $sc->portal_password }}"
                                            data-status="{{ $sc->status }}"
                                            data-url="{{ route('admin.scanners.ajax.update', $sc) }}">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.scanners.destroy', $sc) }}" class="d-inline js-delete-form" data-confirm="Delete scanner '{{ $sc->name }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="scEmptyRow">
                            <td colspan="5">
                                <div class="sc-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No scanners yet</div>
                                    <div class="small">Click <em>Add Scanner</em> to create one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($scanners->hasPages())
            <div class="card-body border-top">{{ $scanners->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Slide-over drawer (create / edit) ─────────────────────── --}}
<div class="offcanvas offcanvas-end sc-drawer" tabindex="-1" id="scDrawer" aria-labelledby="scDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="scDrawerTitle">Add Scanner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="scDrawerForm" novalidate autocomplete="off">
            <input type="hidden" id="scDrawerId" value="">

            <div class="mb-1">
                <label class="form-label" for="scDrawerName">Scanner Name<span class="text-danger">*</span></label>
                <input type="text" id="scDrawerName" class="form-control" maxlength="255" placeholder="Enter scanner name" required>
                <div class="invalid-feedback d-block" id="scDrawerNameErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="scDrawerPortalLink">Scanner Portal Link</label>
                <input type="url" id="scDrawerPortalLink" class="form-control" maxlength="255" placeholder="https://www.example.com">
                <div class="invalid-feedback d-block" id="scDrawerPortalLinkErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="scDrawerPortalPassword">Portal Password</label>
                <div class="input-group input-group-merge">
                    <input type="password" id="scDrawerPortalPassword" class="form-control" maxlength="255" placeholder="••••••••" autocomplete="new-password">
                    <span class="input-group-text sc-pw-toggle" id="scDrawerPwToggle" title="Show / hide password">
                        <i data-feather="eye" id="scDrawerPwIcon"></i>
                    </span>
                </div>
                <div class="invalid-feedback d-block" id="scDrawerPortalPasswordErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="scDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="scDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <div class="invalid-feedback d-block" id="scDrawerStatusErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="scDrawerDescription">Description</label>
                <textarea id="scDrawerDescription" class="form-control" rows="4" maxlength="255" placeholder="Description goes here"></textarea>
                <div class="invalid-feedback d-block" id="scDrawerDescriptionErr"></div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="scDrawerSubmit">
            <span class="sc-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    obAutoFilter('#scannersFilter');

    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store: @json(route('admin.scanners.ajax.store')),
    };

    const $tbody = $('#scTbody');

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

    function truncate(s, n) {
        s = String(s ?? '');
        return s.length > n ? s.slice(0, n) + '…' : s;
    }

    function removeEmptyPlaceholder() { $('#scEmptyRow').remove(); }

    function buildRowHtml(sc) {
        const statusClass = sc.status === 'ACTIVE' ? 'sc-status--active' : 'sc-status--inactive';

        const descCell = sc.description
            ? escapeHtml(truncate(sc.description, 60))
            : '<span class="text-muted">—</span>';

        const linkCell = sc.portal_link
            ? `<a href="${escapeHtml(sc.portal_link)}" target="_blank" rel="noopener" class="text-primary">${escapeHtml(sc.portal_link)}</a>`
            : '<span class="text-muted">—</span>';

        return `
            <tr class="sc-row-new" data-id="${sc.id}">
                <td class="fw-bolder sc-cell-name">${escapeHtml(sc.name)}</td>
                <td class="sc-cell-description">${descCell}</td>
                <td class="sc-cell-link">${linkCell}</td>
                <td><span class="sc-status ${statusClass}" data-status="${sc.status}">${sc.status}</span></td>
                <td class="text-end">
                    <div class="sc-action-group">
                        <a href="${sc.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary sc-edit" title="Edit"
                                data-id="${sc.id}"
                                data-name="${escapeHtml(sc.name)}"
                                data-description="${escapeHtml(sc.description)}"
                                data-portal-link="${escapeHtml(sc.portal_link)}"
                                data-portal-password="${escapeHtml(sc.portal_password)}"
                                data-status="${sc.status}"
                                data-url="${sc.update_url}"><i data-feather="edit-2"></i></button>
                        <form method="POST" action="${sc.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete scanner '${escapeHtml(sc.name)}'?">
                            <input type="hidden" name="_token" value="${CSRF}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                        </form>
                    </div>
                </td>
            </tr>`;
    }

    function insertRow(sc) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(sc));
        if (window.feather) window.feather.replace();
    }

    function updateRow(sc) {
        const $row = $(`#scTbody tr[data-id="${sc.id}"]`);
        if (!$row.length) return;

        $row.find('.sc-cell-name').text(sc.name);
        $row.find('.sc-cell-description').html(
            sc.description ? escapeHtml(truncate(sc.description, 60)) : '<span class="text-muted">—</span>'
        );
        $row.find('.sc-cell-link').html(
            sc.portal_link
                ? `<a href="${escapeHtml(sc.portal_link)}" target="_blank" rel="noopener" class="text-primary">${escapeHtml(sc.portal_link)}</a>`
                : '<span class="text-muted">—</span>'
        );

        const $status = $row.find('.sc-status');
        $status.text(sc.status)
            .removeClass('sc-status--active sc-status--inactive')
            .addClass(sc.status === 'ACTIVE' ? 'sc-status--active' : 'sc-status--inactive')
            .attr('data-status', sc.status);

        const $edit = $row.find('.sc-edit');
        $edit.attr('data-name', sc.name)
             .attr('data-description', sc.description)
             .attr('data-portal-link', sc.portal_link)
             .attr('data-portal-password', sc.portal_password)
             .attr('data-status', sc.status)
             .attr('data-url', sc.update_url);

        $row.find('.js-delete-form').attr('data-confirm', `Delete scanner '${sc.name}'?`);

        // flash highlight
        $row.removeClass('sc-row-new'); void $row[0].offsetWidth; $row.addClass('sc-row-new');
    }

    // ── Drawer setup ──────────────────────────────────────────
    const drawerEl = document.getElementById('scDrawer');
    const drawer   = new bootstrap.Offcanvas(drawerEl);

    const FIELDS = {
        name:            { input: '#scDrawerName',           err: '#scDrawerNameErr' },
        portal_link:     { input: '#scDrawerPortalLink',     err: '#scDrawerPortalLinkErr' },
        portal_password: { input: '#scDrawerPortalPassword', err: '#scDrawerPortalPasswordErr' },
        status:          { input: '#scDrawerStatus',         err: '#scDrawerStatusErr' },
        description:     { input: '#scDrawerDescription',    err: '#scDrawerDescriptionErr' },
    };

    function clearErrors() {
        Object.values(FIELDS).forEach(({ input, err }) => {
            $(input).removeClass('is-invalid');
            $(err).text('');
        });
    }

    function setFieldError(field, message) {
        const target = FIELDS[field];
        if (!target) return;
        $(target.input).addClass('is-invalid');
        $(target.err).text(message);
    }

    function resetPwToggle() {
        $('#scDrawerPortalPassword').attr('type', 'password');
        $('#scDrawerPwIcon').attr('data-feather', 'eye');
        if (window.feather) window.feather.replace();
    }

    function openDrawerCreate() {
        $('#scDrawerTitle').text('Add Scanner');
        $('.sc-drawer-label', drawerEl).text('Save');
        $('#scDrawerForm').removeAttr('data-url');
        $('#scDrawerId').val('');
        $('#scDrawerName').val('');
        $('#scDrawerPortalLink').val('');
        $('#scDrawerPortalPassword').val('');
        $('#scDrawerStatus').val('ACTIVE');
        $('#scDrawerDescription').val('');
        clearErrors();
        resetPwToggle();
        drawer.show();
    }

    function openDrawerEdit(data) {
        $('#scDrawerTitle').text('Edit Scanner');
        $('.sc-drawer-label', drawerEl).text('Update');
        $('#scDrawerForm').attr('data-url', data.url);
        $('#scDrawerId').val(data.id);
        $('#scDrawerName').val(data.name || '');
        $('#scDrawerPortalLink').val(data.portalLink || '');
        $('#scDrawerPortalPassword').val(data.portalPassword || '');
        $('#scDrawerStatus').val(data.status || 'ACTIVE');
        $('#scDrawerDescription').val(data.description || '');
        clearErrors();
        resetPwToggle();
        drawer.show();
    }

    drawerEl.addEventListener('shown.bs.offcanvas', () => {
        setTimeout(() => $('#scDrawerName').trigger('focus'), 50);
    });

    // Password show / hide toggle
    $('#scDrawerPwToggle').on('click', function () {
        const $i = $('#scDrawerPortalPassword');
        const showing = $i.attr('type') === 'text';
        $i.attr('type', showing ? 'password' : 'text');
        $('#scDrawerPwIcon').attr('data-feather', showing ? 'eye' : 'eye-off');
        if (window.feather) window.feather.replace();
    });

    $('#scDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.sc-edit', function () {
        openDrawerEdit({
            id:             $(this).data('id'),
            name:           $(this).attr('data-name') || '',
            description:    $(this).attr('data-description') || '',
            portalLink:     $(this).attr('data-portal-link') || '',
            portalPassword: $(this).attr('data-portal-password') || '',
            status:         $(this).data('status'),
            url:            $(this).data('url'),
        });
    });

    function validateLocal(payload) {
        clearErrors();
        let firstInvalid = null;
        if (!payload.name) { setFieldError('name', 'Name is required.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        else if (payload.name.length > 255) { setFieldError('name', 'Name may not be longer than 255 characters.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        if (payload.portal_link && payload.portal_link.length > 255) { setFieldError('portal_link', 'Link may not be longer than 255 characters.'); firstInvalid = firstInvalid || FIELDS.portal_link.input; }
        if (payload.portal_password && payload.portal_password.length > 255) { setFieldError('portal_password', 'Password may not be longer than 255 characters.'); firstInvalid = firstInvalid || FIELDS.portal_password.input; }
        if (payload.description && payload.description.length > 255) { setFieldError('description', 'Description may not be longer than 255 characters.'); firstInvalid = firstInvalid || FIELDS.description.input; }
        if (!payload.status) { setFieldError('status', 'Status is required.'); firstInvalid = firstInvalid || FIELDS.status.input; }
        if (firstInvalid) $(firstInvalid).trigger('focus');
        return !firstInvalid;
    }

    // Submit on Enter in single-line inputs (skip textarea)
    $('#scDrawerForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#scDrawerSubmit').trigger('click'); }
    });

    $('#scDrawerSubmit').on('click', function () {
        const id = $('#scDrawerId').val();
        const payload = {
            name:            $('#scDrawerName').val().trim(),
            portal_link:     $('#scDrawerPortalLink').val().trim(),
            portal_password: $('#scDrawerPortalPassword').val(),
            status:          $('#scDrawerStatus').val(),
            description:     $('#scDrawerDescription').val(),
        };

        if (!validateLocal(payload)) return;

        const isEdit = !!id;
        const url    = isEdit ? $('#scDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? Object.assign({ _token: CSRF, _method: 'PUT' }, payload)
            : Object.assign({ _token: CSRF }, payload);

        const $btn = $('#scDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    const $prevStatus = $(`#scTbody tr[data-id="${res.scanner.id}"] .sc-status`);
                    const prev = $prevStatus.data('status');
                    if (prev && prev !== res.scanner.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.scanner.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.scanner);
                } else {
                    insertRow(res.scanner);
                    incrementStatsFor(res.scanner.status);
                }
                toast('success', res.message);
                drawer.hide();
            })
            .fail((xhr) => {
                const errs = xhr.responseJSON?.errors;
                if (errs && typeof errs === 'object') {
                    Object.keys(errs).forEach((field) => setFieldError(field, errs[field][0]));
                    const firstKey = Object.keys(errs)[0];
                    if (FIELDS[firstKey]) $(FIELDS[firstKey].input).trigger('focus');
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
            const $btn = $(`#scTbody tr[data-id="{{ (int) $editId }}"] .sc-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
