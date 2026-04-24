@extends('layouts.admin')
@section('title', 'Countries')
@section('page_title', 'Countries')

@push('styles')
<style>
    /* ── KPI strip ─────────────────────────────────────────────────── */
    .co-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .co-kpis { grid-template-columns: 1fr; } }
    .co-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .co-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .co-kpi__icon svg { width: 20px; height: 20px; }
    .co-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .co-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .co-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .co-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .co-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }

    /* ── Countries — drawer + table polish ─────────────────────────── */
    .co-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .co-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .co-toolbar__search { flex: 1 1 260px; max-width: 380px; }
    .co-toolbar__status { flex: 0 0 180px; }
    .co-toolbar__spacer { flex: 1; }
    .co-toolbar__actions { display: flex; gap: .5rem; }

    .co-table { margin-bottom: 0; }
    .co-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .co-table tbody tr { transition: background-color .15s ease; }
    .co-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .co-table .co-row-new { animation: co-row-flash 1.4s ease-out; }
    @keyframes co-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .co-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .co-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .co-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .co-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .co-action-group { display: inline-flex; gap: .25rem; }
    .co-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .co-action-group .btn svg { width: 15px; height: 15px; }

    .co-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .co-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .co-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .co-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .co-drawer .offcanvas-body { overflow-y: auto; }
    .co-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .co-drawer .form-label { font-weight: 500; }

    /* Bulk-add drawer rows */
    .co-bulk-row { display: grid; grid-template-columns: 1fr 90px 90px 120px 36px; gap: .5rem; align-items: start; margin-bottom: .5rem; }
    .co-bulk-row .co-bulk-remove { width: 36px; height: 38px; padding: 0; display: grid; place-items: center; }
    .co-bulk-row__err { grid-column: 1 / -1; font-size: .78rem; color: #ea5455; margin-top: -.25rem; }
</style>
@endpush

@section('content')
<section id="countries-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    <div class="co-kpis">
        <div class="co-kpi">
            <div class="co-kpi__icon co-kpi__icon--total"><i data-feather="layers"></i></div>
            <div>
                <div class="co-kpi__label">Total</div>
                <div class="co-kpi__value" data-stat="total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="co-kpi">
            <div class="co-kpi__icon co-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="co-kpi__label">Active</div>
                <div class="co-kpi__value" data-stat="active">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="co-kpi">
            <div class="co-kpi__icon co-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="co-kpi__label">Inactive</div>
                <div class="co-kpi__value" data-stat="inactive">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

    <div class="card co-card">

        {{-- ── Toolbar ──────────────────────────────────────────── --}}
        <div class="co-toolbar">
            <form id="countriesFilter" method="GET" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <div class="co-toolbar__search">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i data-feather="search" style="width:16px;height:16px;"></i></span>
                        <input type="text" name="search" placeholder="Search name or code…" value="{{ request('search') }}" class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="co-toolbar__status">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                @if (request('search') || request('status'))
                    <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                @endif
            </form>
            <div class="co-toolbar__spacer"></div>
            <div class="co-toolbar__actions">
                <button type="button" class="btn btn-outline-primary" id="coBulkOpen">
                    <i data-feather="layers" class="me-25"></i> Bulk add
                </button>
                <button type="button" class="btn btn-primary" id="coDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add Country
                </button>
            </div>
        </div>

        {{-- ── Table ─────────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover co-table" id="coTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>States</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="coTbody">
                    @forelse ($countries as $c)
                        @php $hasStates = $c->states_count > 0; @endphp
                        <tr data-id="{{ $c->id }}">
                            <td class="fw-bolder co-cell-name">{{ $c->name }}</td>
                            <td class="co-cell-code">{{ $c->country_code }}</td>
                            <td class="co-cell-phone">{{ $c->phone_code }}</td>
                            <td>
                                <span class="co-status co-status--{{ $c->status === 'ACTIVE' ? 'active' : 'inactive' }}" data-status="{{ $c->status }}">{{ $c->status }}</span>
                            </td>
                            <td class="co-cell-states">
                                @if ($hasStates)
                                    {{ $c->states_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="co-action-group">
                                    <a href="{{ route('admin.countries.show', $c) }}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="btn btn-outline-primary co-edit" title="Edit"
                                            data-id="{{ $c->id }}"
                                            data-name="{{ $c->name }}"
                                            data-country-code="{{ $c->country_code }}"
                                            data-phone-code="{{ $c->phone_code }}"
                                            data-status="{{ $c->status }}"
                                            data-url="{{ route('admin.countries.ajax.update', $c) }}">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    @if (! $hasStates)
                                        <form method="POST" action="{{ route('admin.countries.destroy', $c) }}" class="d-inline js-delete-form" data-confirm="Delete country '{{ $c->name }}'?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $c->name }}' — it has linked states. Remove them first.">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="coEmptyRow">
                            <td colspan="6">
                                <div class="co-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No countries yet</div>
                                    <div class="small">Click <em>Add Country</em> to create one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($countries->hasPages())
            <div class="card-body border-top">{{ $countries->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Slide-over drawer (create / edit) ─────────────────────── --}}
<div class="offcanvas offcanvas-end co-drawer" tabindex="-1" id="coDrawer" aria-labelledby="coDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="coDrawerTitle">Add Country</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="coDrawerForm" novalidate autocomplete="off">
            <input type="hidden" id="coDrawerId" value="">

            <div class="mb-1">
                <label class="form-label" for="coDrawerName">Country Name<span class="text-danger">*</span></label>
                <input type="text" id="coDrawerName" class="form-control" maxlength="100" required>
                <div class="invalid-feedback d-block" id="coDrawerNameErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="coDrawerCode">Country Code<span class="text-danger">*</span></label>
                <input type="text" id="coDrawerCode" class="form-control" maxlength="10" placeholder="e.g. US" required>
                <div class="invalid-feedback d-block" id="coDrawerCodeErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="coDrawerPhone">Phone Code<span class="text-danger">*</span></label>
                <input type="text" id="coDrawerPhone" class="form-control" maxlength="10" placeholder="e.g. +1" required>
                <div class="invalid-feedback d-block" id="coDrawerPhoneErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="coDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="coDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <div class="invalid-feedback d-block" id="coDrawerStatusErr"></div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="coDrawerSubmit">
            <span class="co-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

{{-- ── Bulk-add drawer ────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end co-drawer" tabindex="-1" id="coBulkDrawer" aria-labelledby="coBulkTitle" style="width: min(720px, 100vw);">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-0" id="coBulkTitle">Bulk add countries</h5>
            <small class="text-muted">Add up to 50 countries in a single save.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="coBulkRows"></div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="coBulkAddRow">
            <i data-feather="plus" style="width:14px;height:14px;"></i> Add another row
        </button>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="coBulkSubmit">
            <span class="co-bulk-label">Save all</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    obAutoFilter('#countriesFilter');

    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store: @json(route('admin.countries.ajax.store')),
        bulk:  @json(route('admin.countries.ajax.bulk')),
    };

    const $tbody = $('#coTbody');

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

    function removeEmptyPlaceholder() { $('#coEmptyRow').remove(); }

    function buildRowHtml(co) {
        const statusClass = co.status === 'ACTIVE' ? 'co-status--active' : 'co-status--inactive';
        const hasStates = co.states_count > 0;
        const statesCell = hasStates ? String(co.states_count) : '<span class="text-muted">—</span>';
        const deleteBtn = hasStates
            ? `<button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(co.name)}' — it has linked states. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${co.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete country '${escapeHtml(co.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="co-row-new" data-id="${co.id}">
                <td class="fw-bolder co-cell-name">${escapeHtml(co.name)}</td>
                <td class="co-cell-code">${escapeHtml(co.country_code)}</td>
                <td class="co-cell-phone">${escapeHtml(co.phone_code)}</td>
                <td><span class="co-status ${statusClass}" data-status="${co.status}">${co.status}</span></td>
                <td class="co-cell-states">${statesCell}</td>
                <td class="text-end">
                    <div class="co-action-group">
                        <a href="${co.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary co-edit" title="Edit"
                                data-id="${co.id}"
                                data-name="${escapeHtml(co.name)}"
                                data-country-code="${escapeHtml(co.country_code)}"
                                data-phone-code="${escapeHtml(co.phone_code)}"
                                data-status="${co.status}"
                                data-url="${co.update_url}"><i data-feather="edit-2"></i></button>
                        ${deleteBtn}
                    </div>
                </td>
            </tr>`;
    }

    function insertRow(co) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(co));
        if (window.feather) window.feather.replace();
    }

    function updateRow(co) {
        const $row = $(`#coTbody tr[data-id="${co.id}"]`);
        if (!$row.length) return;
        $row.find('.co-cell-name').text(co.name);
        $row.find('.co-cell-code').text(co.country_code);
        $row.find('.co-cell-phone').text(co.phone_code);

        const $status = $row.find('.co-status');
        $status.text(co.status)
            .removeClass('co-status--active co-status--inactive')
            .addClass(co.status === 'ACTIVE' ? 'co-status--active' : 'co-status--inactive')
            .attr('data-status', co.status);

        const $edit = $row.find('.co-edit');
        $edit.attr('data-name', co.name)
             .attr('data-country-code', co.country_code)
             .attr('data-phone-code', co.phone_code)
             .attr('data-status', co.status)
             .attr('data-url', co.update_url);

        $row.removeClass('co-row-new'); void $row[0].offsetWidth; $row.addClass('co-row-new');
    }

    // ── Drawer setup ──────────────────────────────────────────
    const drawerEl = document.getElementById('coDrawer');
    const drawer   = new bootstrap.Offcanvas(drawerEl);

    const FIELDS = {
        name:         { input: '#coDrawerName',   err: '#coDrawerNameErr' },
        country_code: { input: '#coDrawerCode',   err: '#coDrawerCodeErr' },
        phone_code:   { input: '#coDrawerPhone',  err: '#coDrawerPhoneErr' },
        status:       { input: '#coDrawerStatus', err: '#coDrawerStatusErr' },
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

    function openDrawerCreate() {
        $('#coDrawerTitle').text('Add Country');
        $('.co-drawer-label', drawerEl).text('Save');
        $('#coDrawerForm').removeAttr('data-url');
        $('#coDrawerId').val('');
        $('#coDrawerName').val('');
        $('#coDrawerCode').val('');
        $('#coDrawerPhone').val('');
        $('#coDrawerStatus').val('ACTIVE');
        clearErrors();
        drawer.show();
    }

    function openDrawerEdit(data) {
        $('#coDrawerTitle').text('Edit Country');
        $('.co-drawer-label', drawerEl).text('Update');
        $('#coDrawerForm').attr('data-url', data.url);
        $('#coDrawerId').val(data.id);
        $('#coDrawerName').val(data.name || '');
        $('#coDrawerCode').val(data.countryCode || '');
        $('#coDrawerPhone').val(data.phoneCode || '');
        $('#coDrawerStatus').val(data.status || 'ACTIVE');
        clearErrors();
        drawer.show();
    }

    drawerEl.addEventListener('shown.bs.offcanvas', () => {
        setTimeout(() => $('#coDrawerName').trigger('focus'), 50);
    });

    $('#coDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.co-edit', function () {
        openDrawerEdit({
            id:          $(this).data('id'),
            name:        $(this).attr('data-name') || '',
            countryCode: $(this).attr('data-country-code') || '',
            phoneCode:   $(this).attr('data-phone-code') || '',
            status:      $(this).data('status'),
            url:         $(this).data('url'),
        });
    });

    function validateLocal(payload) {
        clearErrors();
        let firstInvalid = null;
        if (!payload.name) { setFieldError('name', 'Name is required.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        else if (payload.name.length > 100) { setFieldError('name', 'Name may not be longer than 100 characters.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        if (!payload.country_code) { setFieldError('country_code', 'Country code is required.'); firstInvalid = firstInvalid || FIELDS.country_code.input; }
        else if (payload.country_code.length > 10) { setFieldError('country_code', 'Country code may not be longer than 10 characters.'); firstInvalid = firstInvalid || FIELDS.country_code.input; }
        if (!payload.phone_code) { setFieldError('phone_code', 'Phone code is required.'); firstInvalid = firstInvalid || FIELDS.phone_code.input; }
        else if (payload.phone_code.length > 10) { setFieldError('phone_code', 'Phone code may not be longer than 10 characters.'); firstInvalid = firstInvalid || FIELDS.phone_code.input; }
        if (!payload.status) { setFieldError('status', 'Status is required.'); firstInvalid = firstInvalid || FIELDS.status.input; }
        if (firstInvalid) $(firstInvalid).trigger('focus');
        return !firstInvalid;
    }

    $('#coDrawerForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#coDrawerSubmit').trigger('click'); }
    });

    $('#coDrawerSubmit').on('click', function () {
        const id = $('#coDrawerId').val();
        const payload = {
            name:         $('#coDrawerName').val().trim(),
            country_code: $('#coDrawerCode').val().trim(),
            phone_code:   $('#coDrawerPhone').val().trim(),
            status:       $('#coDrawerStatus').val(),
        };

        if (!validateLocal(payload)) return;

        const isEdit = !!id;
        const url    = isEdit ? $('#coDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? Object.assign({ _token: CSRF, _method: 'PUT' }, payload)
            : Object.assign({ _token: CSRF }, payload);

        const $btn = $('#coDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    const $prevStatus = $(`#coTbody tr[data-id="${res.country.id}"] .co-status`);
                    const prev = $prevStatus.data('status');
                    if (prev && prev !== res.country.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.country.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.country);
                } else {
                    insertRow(res.country);
                    incrementStatsFor(res.country.status);
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

    // ── Bulk-add drawer ────────────────────────────────────────
    const bulkEl = document.getElementById('coBulkDrawer');
    const bulk   = new bootstrap.Offcanvas(bulkEl);

    function buildBulkRow(name = '', code = '', phone = '', status = 'ACTIVE') {
        const $row = $(`
            <div class="co-bulk-row">
                <input type="text" class="form-control co-bulk-name" placeholder="Country name" maxlength="100">
                <input type="text" class="form-control co-bulk-code" placeholder="Code" maxlength="10">
                <input type="text" class="form-control co-bulk-phone" placeholder="+Phone" maxlength="10">
                <select class="form-select co-bulk-status">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <button type="button" class="btn btn-outline-danger co-bulk-remove" title="Remove row">
                    <i data-feather="x" style="width:14px;height:14px;"></i>
                </button>
                <div class="co-bulk-row__err"></div>
            </div>
        `);
        $row.find('.co-bulk-name').val(name);
        $row.find('.co-bulk-code').val(code);
        $row.find('.co-bulk-phone').val(phone);
        $row.find('.co-bulk-status').val(status);
        return $row;
    }

    function resetBulk() {
        $('#coBulkRows').empty()
            .append(buildBulkRow())
            .append(buildBulkRow());
        if (window.feather) window.feather.replace();
    }

    $('#coBulkOpen').on('click', () => { resetBulk(); bulk.show(); });

    $('#coBulkAddRow').on('click', () => {
        const $r = buildBulkRow();
        $('#coBulkRows').append($r);
        if (window.feather) window.feather.replace();
        $r.find('.co-bulk-name').trigger('focus');
    });

    $(document).on('click', '.co-bulk-remove', function () {
        const $rows = $('#coBulkRows .co-bulk-row');
        if ($rows.length <= 1) {
            $(this).closest('.co-bulk-row').find('input').val('');
            return;
        }
        $(this).closest('.co-bulk-row').remove();
    });

    $('#coBulkSubmit').on('click', function () {
        const rows = [];
        const $rowEls = $('#coBulkRows .co-bulk-row');
        $rowEls.find('.co-bulk-row__err').text('');
        $rowEls.find('input').removeClass('is-invalid');

        let firstInvalid = null;
        const markBad = ($row, $input, msg) => {
            $input.addClass('is-invalid');
            const $err = $row.find('.co-bulk-row__err');
            $err.text($err.text() ? ($err.text() + ' ' + msg) : msg);
            if (!firstInvalid) firstInvalid = $input;
        };

        $rowEls.each(function (i) {
            const $row = $(this);
            const name  = $row.find('.co-bulk-name').val().trim();
            const code  = $row.find('.co-bulk-code').val().trim();
            const phone = $row.find('.co-bulk-phone').val().trim();
            const status = $row.find('.co-bulk-status').val();

            // Only validate rows the user has started filling, except the first which must be complete.
            const hasAny = name || code || phone;
            if (!hasAny && i !== 0) return;

            if (!name)  markBad($row, $row.find('.co-bulk-name'),  'Name is required.');
            if (!code)  markBad($row, $row.find('.co-bulk-code'),  'Code is required.');
            if (!phone) markBad($row, $row.find('.co-bulk-phone'), 'Phone is required.');
            if (name && code && phone) rows.push({ name, country_code: code, phone_code: phone, status });
        });

        if (firstInvalid) { firstInvalid.trigger('focus'); return; }
        if (rows.length === 0) { toast('info', 'Add at least one country.'); return; }

        const $btn = $('#coBulkSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: ROUTES.bulk,
            method: 'POST',
            data: { _token: CSRF, countries: rows },
            dataType: 'json'
        })
        .done((res) => {
            if (!res || !res.ok) return;
            res.countries.forEach((c) => { insertRow(c); incrementStatsFor(c.status); });
            toast('success', res.message);
            bulk.hide();
        })
        .fail((xhr) => {
            const errs = xhr.responseJSON?.errors || {};
            const inputClass = { name: '.co-bulk-name', country_code: '.co-bulk-code', phone_code: '.co-bulk-phone', status: '.co-bulk-status' };
            Object.keys(errs).forEach((key) => {
                const m = key.match(/^countries\.(\d+)\.(name|country_code|phone_code|status)$/);
                if (!m) return;
                const $row = $('#coBulkRows .co-bulk-row').eq(parseInt(m[1], 10));
                if (!$row.length) return;
                $row.find(inputClass[m[2]]).addClass('is-invalid');
                const $err = $row.find('.co-bulk-row__err');
                $err.text($err.text() ? ($err.text() + ' ' + errs[key][0]) : errs[key][0]);
            });
            toast('error', 'Please fix the highlighted rows.');
        })
        .always(() => {
            $btn.prop('disabled', false);
            $btn.find('.spinner-border').addClass('d-none');
        });
    });

    if (window.feather) window.feather.replace();

    @if ($editId = request('edit'))
        (function () {
            const $btn = $(`#coTbody tr[data-id="{{ (int) $editId }}"] .co-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
