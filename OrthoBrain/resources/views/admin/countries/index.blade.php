@extends('layouts.admin')
@section('title', 'Countries')
@section('page_title', 'Countries')

@push('styles')
<style>
    /* ── Countries — drawer + table polish ─────────────────────────── */
    .co-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .co-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .co-toolbar__search { flex: 1 1 260px; max-width: 380px; }
    .co-toolbar__status { flex: 0 0 180px; }
    .co-toolbar__spacer { flex: 1; }
    .co-toolbar__actions { display: flex; gap: .5rem; }

    /* Table chrome (header background, row hover, borders) comes from the
       shared .ob-admin-table rules in orthobrain-palette.css. Only the
       new-row flash highlight stays page-local. */
    .ob-admin-table .co-row-new { animation: co-row-flash 1.4s ease-out; }
    @keyframes co-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .co-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .co-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .co-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .co-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    /* Body hugs its content so the action bar sits right below the form, not pinned at the panel's bottom edge */
    .co-drawer .offcanvas-body { flex: 0 1 auto; overflow-y: auto; }
    .co-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .co-drawer .form-label { font-weight: 500; }

    /* Bulk-add drawer rows */
    .co-bulk-row { display: grid; grid-template-columns: 1fr 90px 90px 120px 36px; gap: .5rem; align-items: start; margin-bottom: .5rem; }
    .co-bulk-row .co-bulk-remove { width: 36px; height: 38px; padding: 0; display: grid; place-items: center; }
    .co-bulk-row__err { grid-column: 1 / -1; font-size: .78rem; color: #ea5455; margin-top: -.25rem; }
</style>
@endpush

@section('content')
@include('admin._partials.inline_status_dropdown')
<section id="countries-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    @include('admin._partials.stat_cards', [
        'cards' => [
            ['label' => 'Total',    'value' => $stats['total'],    'icon' => 'globe',        'tone' => 'primary', 'stat_key' => 'total'],
            ['label' => 'Active',   'value' => $stats['active'],   'icon' => 'check-circle', 'tone' => 'success', 'stat_key' => 'active'],
            ['label' => 'Inactive', 'value' => $stats['inactive'], 'icon' => 'slash',        'tone' => 'danger',  'stat_key' => 'inactive'],
        ],
    ])

    <div class="card co-card">

        @php
            $selectedStatus = request('status');
            $statusLabel    = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;
            $headerTitle    = $statusLabel ? 'All ' . $statusLabel . ' Countries' : 'All Countries';
        @endphp

        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-outline-primary" id="coBulkOpen">
                    <i data-feather="layers" class="me-25"></i> Bulk add
                </button>
                <button type="button" class="btn btn-primary" id="coDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add Country
                </button>
            </div>
        </div>

        <div class="card-body py-1">
            <form id="countriesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-5">
                    <input type="text" name="search" placeholder="Search name or code…" value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-3">
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
                    <a href="{{ route('admin.countries.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Table ─────────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table ob-admin-table" id="coTable">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Country', 'key' => 'name', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Code', 'key' => 'country_code', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Phone', 'key' => 'phone_code', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'States', 'key' => 'states_count', 'default' => 'name'])</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="coTbody">
                    @forelse ($countries as $c)
                        @php $hasStates = $c->states_count > 0; @endphp
                        <tr data-id="{{ $c->id }}" data-row-href="{{ route('admin.countries.show', $c) }}">
                            <td class="fw-bolder co-cell-name">{{ $c->name }}</td>
                            <td class="co-cell-code">{{ $c->country_code }}</td>
                            <td class="co-cell-phone">{{ $c->phone_code }}</td>
                            <td class="co-cell-states">
                                @if ($hasStates)
                                    {{ $c->states_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <select class="ob-status-select" data-inline-status
                                        data-url="{{ route('admin.countries.status', $c) }}"
                                        data-status="{{ $c->status }}"
                                        aria-label="Update status for {{ $c->name }}">
                                    <option value="ACTIVE"   @selected($c->status === 'ACTIVE')>Active</option>
                                    <option value="INACTIVE" @selected($c->status === 'INACTIVE')>Inactive</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.countries.show', $c) }}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="ob-icon-btn ob-icon-btn--edit co-edit" title="Edit"
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
                                            <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $c->name }}' — it has linked states. Remove them first.">
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
        store:        @json(route('admin.countries.ajax.store')),
        bulk:         @json(route('admin.countries.ajax.bulk')),
        checkUnique:  @json(route('admin.countries.ajax.check-unique')),
    };

    function debounce(fn, ms) {
        let t;
        return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), ms); };
    }

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
        const hasStates = co.states_count > 0;
        const statesCell = hasStates ? String(co.states_count) : '<span class="text-muted">—</span>';
        const deleteBtn = hasStates
            ? `<button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(co.name)}' — it has linked states. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${co.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete country '${escapeHtml(co.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="co-row-new" data-id="${co.id}" data-row-href="${co.show_url}">
                <td class="fw-bolder co-cell-name">${escapeHtml(co.name)}</td>
                <td class="co-cell-code">${escapeHtml(co.country_code)}</td>
                <td class="co-cell-phone">${escapeHtml(co.phone_code)}</td>
                <td class="co-cell-states">${statesCell}</td>
                <td>
                    <select class="ob-status-select" data-inline-status data-url="${co.status_url}" data-status="${co.status}" aria-label="Update status for ${escapeHtml(co.name)}">
                        <option value="ACTIVE"${co.status === 'ACTIVE' ? ' selected' : ''}>Active</option>
                        <option value="INACTIVE"${co.status === 'INACTIVE' ? ' selected' : ''}>Inactive</option>
                    </select>
                </td>
                <td class="text-end">
                    <div class="ob-row-actions">
                        <a href="${co.show_url}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="ob-icon-btn ob-icon-btn--edit co-edit" title="Edit"
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

        const $status = $row.find('.ob-status-select');
        $status.attr('data-status', co.status).val(co.status);

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

    // ── Live duplicate check on the single drawer (name + country_code) ──
    function liveCheckDrawerField(field, $input, $err) {
        const value     = $input.val().trim();
        const ignore_id = $('#coDrawerId').val() || null;
        if (!value) {
            $input.removeClass('is-invalid');
            $err.text('');
            return;
        }
        $.post(ROUTES.checkUnique, { _token: CSRF, field, value, ignore_id })
            .done((res) => {
                if ($input.val().trim() !== value) return;
                if (res.available) {
                    $input.removeClass('is-invalid');
                    $err.text('');
                } else {
                    $input.addClass('is-invalid');
                    $err.text(res.message || 'Already exists.');
                }
            });
    }
    const liveCheckName = debounce(() => liveCheckDrawerField('name', $('#coDrawerName'), $('#coDrawerNameErr')), 350);
    const liveCheckCode = debounce(() => liveCheckDrawerField('country_code', $('#coDrawerCode'), $('#coDrawerCodeErr')), 350);
    $(document).on('input', '#coDrawerName', liveCheckName);
    $(document).on('input', '#coDrawerCode', liveCheckCode);

    // ── Live duplicate check on each bulk row (name + code) ──────────────
    function bulkRowFieldLiveCheck($input, field, sel, dupMsg) {
        const $row  = $input.closest('.co-bulk-row');
        const $err  = $row.find('.co-bulk-row__err');
        const value = $input.val().trim();

        let dupInBatch = false;
        if (value) {
            $('#coBulkRows .co-bulk-row').each(function () {
                if (this === $row[0]) return;
                const other = $(this).find(sel).val().trim().toLowerCase();
                if (other && other === value.toLowerCase()) dupInBatch = true;
            });
        }
        if (dupInBatch) {
            $input.addClass('is-invalid');
            $err.text(dupMsg);
            return;
        }

        if (!value) {
            $input.removeClass('is-invalid');
            $err.text('');
            return;
        }
        $.post(ROUTES.checkUnique, { _token: CSRF, field, value })
            .done((res) => {
                if ($input.val().trim() !== value) return;
                if (res.available) {
                    $input.removeClass('is-invalid');
                    $err.text('');
                } else {
                    $input.addClass('is-invalid');
                    $err.text(res.message || 'Already exists.');
                }
            });
    }
    const bulkNameDebounced = debounce(function (el) {
        bulkRowFieldLiveCheck($(el), 'name', '.co-bulk-name', 'Duplicate name in this batch.');
    }, 350);
    const bulkCodeDebounced = debounce(function (el) {
        bulkRowFieldLiveCheck($(el), 'country_code', '.co-bulk-code', 'Duplicate code in this batch.');
    }, 350);
    $(document).on('input', '#coBulkRows .co-bulk-name', function () { bulkNameDebounced(this); });
    $(document).on('input', '#coBulkRows .co-bulk-code', function () { bulkCodeDebounced(this); });

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
                    const prev = $(`#coTbody tr[data-id="${res.country.id}"] .ob-status-select`).attr('data-status');
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
