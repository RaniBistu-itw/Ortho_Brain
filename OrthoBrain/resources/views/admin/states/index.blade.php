@extends('layouts.admin')
@section('title', 'States')
@section('page_title', 'States')

@push('styles')
<style>
    /* ── KPI strip ─────────────────────────────────────────────────── */
    .st-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .st-kpis { grid-template-columns: 1fr; } }
    .st-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .st-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .st-kpi__icon svg { width: 20px; height: 20px; }
    .st-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .st-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .st-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .st-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .st-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }

    /* ── States — drawer + table polish ────────────────────────────── */
    .st-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .st-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .st-toolbar__country { flex: 0 0 220px; min-width: 180px; }
    .st-toolbar__search { flex: 1 1 220px; max-width: 320px; }
    .st-toolbar__status { flex: 0 0 160px; }
    .st-toolbar__spacer { flex: 1; }
    .st-toolbar__actions { display: flex; gap: .5rem; }

    .st-table { margin-bottom: 0; }
    .st-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .st-table tbody tr { transition: background-color .15s ease; }
    .st-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .st-table .st-row-new { animation: st-row-flash 1.4s ease-out; }
    @keyframes st-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .st-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .st-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .st-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .st-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .st-action-group { display: inline-flex; gap: .25rem; }
    .st-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .st-action-group .btn svg { width: 15px; height: 15px; }

    .st-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .st-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .st-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .st-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .st-drawer .offcanvas-body { overflow-y: auto; }
    .st-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .st-drawer .form-label { font-weight: 500; }
    .st-drawer .select2-container--default .select2-selection--single { height: calc(2.4rem + 2px); padding: .3rem .4rem; }
</style>
@endpush

@section('content')
<section id="states-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    <div class="st-kpis">
        <div class="st-kpi">
            <div class="st-kpi__icon st-kpi__icon--total"><i data-feather="layers"></i></div>
            <div>
                <div class="st-kpi__label">Total</div>
                <div class="st-kpi__value" data-stat="total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="st-kpi">
            <div class="st-kpi__icon st-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="st-kpi__label">Active</div>
                <div class="st-kpi__value" data-stat="active">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="st-kpi">
            <div class="st-kpi__icon st-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="st-kpi__label">Inactive</div>
                <div class="st-kpi__value" data-stat="inactive">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

    <div class="card st-card">

        {{-- ── Toolbar ──────────────────────────────────────────── --}}
        <div class="st-toolbar">
            <form id="statesFilter" method="GET" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1">
                <div class="st-toolbar__country">
                    <select name="country_id" class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected(request('country_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="st-toolbar__search">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i data-feather="search" style="width:16px;height:16px;"></i></span>
                        <input type="text" name="search" placeholder="Search name or code…" value="{{ request('search') }}" class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="st-toolbar__status">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                @if (request('search') || request('status') || request('country_id'))
                    <a href="{{ route('admin.states.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                @endif
            </form>
            <div class="st-toolbar__spacer"></div>
            <div class="st-toolbar__actions">
                <button type="button" class="btn btn-primary" id="stDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add State
                </button>
            </div>
        </div>

        {{-- ── Table ─────────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover st-table" id="stTable">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Cities</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="stTbody">
                    @forelse ($states as $s)
                        @php $hasCities = $s->cities_count > 0; @endphp
                        <tr data-id="{{ $s->id }}">
                            <td class="st-cell-country">{{ $s->country?->name ?? '—' }}</td>
                            <td class="fw-bolder st-cell-name">{{ $s->name }}</td>
                            <td class="st-cell-code">{{ $s->state_code }}</td>
                            <td>
                                <span class="st-status st-status--{{ $s->status === 'ACTIVE' ? 'active' : 'inactive' }}" data-status="{{ $s->status }}">{{ $s->status }}</span>
                            </td>
                            <td class="st-cell-cities">
                                @if ($hasCities)
                                    {{ $s->cities_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="st-action-group">
                                    <a href="{{ route('admin.states.show', $s) }}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="btn btn-outline-primary st-edit" title="Edit"
                                            data-id="{{ $s->id }}"
                                            data-country-id="{{ $s->country_id }}"
                                            data-name="{{ $s->name }}"
                                            data-state-code="{{ $s->state_code }}"
                                            data-status="{{ $s->status }}"
                                            data-url="{{ route('admin.states.ajax.update', $s) }}">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    @if (! $hasCities)
                                        <form method="POST" action="{{ route('admin.states.destroy', $s) }}" class="d-inline js-delete-form" data-confirm="Delete state '{{ $s->name }}'?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $s->name }}' — it has linked cities. Remove them first.">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="stEmptyRow">
                            <td colspan="6">
                                <div class="st-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No states yet</div>
                                    <div class="small">Click <em>Add State</em> to create one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($states->hasPages())
            <div class="card-body border-top">{{ $states->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Slide-over drawer (create / edit) ─────────────────────── --}}
<div class="offcanvas offcanvas-end st-drawer" tabindex="-1" id="stDrawer" aria-labelledby="stDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="stDrawerTitle">Add State</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="stDrawerForm" novalidate autocomplete="off">
            <input type="hidden" id="stDrawerId" value="">

            <div class="mb-1">
                <label class="form-label" for="stDrawerCountry">Country<span class="text-danger">*</span></label>
                <select id="stDrawerCountry" class="form-select">
                    <option value="">Select country</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback d-block" id="stDrawerCountryErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="stDrawerName">State Name<span class="text-danger">*</span></label>
                <input type="text" id="stDrawerName" class="form-control" maxlength="100" required>
                <div class="invalid-feedback d-block" id="stDrawerNameErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="stDrawerCode">State Code<span class="text-danger">*</span></label>
                <input type="text" id="stDrawerCode" class="form-control" maxlength="100" placeholder="e.g. OH" required>
                <div class="invalid-feedback d-block" id="stDrawerCodeErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="stDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="stDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <div class="invalid-feedback d-block" id="stDrawerStatusErr"></div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="stDrawerSubmit">
            <span class="st-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    obAutoFilter('#statesFilter');

    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store: @json(route('admin.states.ajax.store')),
    };

    const $tbody = $('#stTbody');

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

    function removeEmptyPlaceholder() { $('#stEmptyRow').remove(); }

    function buildRowHtml(st) {
        const statusClass = st.status === 'ACTIVE' ? 'st-status--active' : 'st-status--inactive';
        const hasCities = st.cities_count > 0;
        const citiesCell = hasCities ? String(st.cities_count) : '<span class="text-muted">—</span>';
        const deleteBtn = hasCities
            ? `<button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(st.name)}' — it has linked cities. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${st.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete state '${escapeHtml(st.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="st-row-new" data-id="${st.id}">
                <td class="st-cell-country">${escapeHtml(st.country_name)}</td>
                <td class="fw-bolder st-cell-name">${escapeHtml(st.name)}</td>
                <td class="st-cell-code">${escapeHtml(st.state_code)}</td>
                <td><span class="st-status ${statusClass}" data-status="${st.status}">${st.status}</span></td>
                <td class="st-cell-cities">${citiesCell}</td>
                <td class="text-end">
                    <div class="st-action-group">
                        <a href="${st.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary st-edit" title="Edit"
                                data-id="${st.id}"
                                data-country-id="${st.country_id}"
                                data-name="${escapeHtml(st.name)}"
                                data-state-code="${escapeHtml(st.state_code)}"
                                data-status="${st.status}"
                                data-url="${st.update_url}"><i data-feather="edit-2"></i></button>
                        ${deleteBtn}
                    </div>
                </td>
            </tr>`;
    }

    function insertRow(st) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(st));
        if (window.feather) window.feather.replace();
    }

    function updateRow(st) {
        const $row = $(`#stTbody tr[data-id="${st.id}"]`);
        if (!$row.length) return;
        $row.find('.st-cell-country').text(st.country_name);
        $row.find('.st-cell-name').text(st.name);
        $row.find('.st-cell-code').text(st.state_code);

        const $status = $row.find('.st-status');
        $status.text(st.status)
            .removeClass('st-status--active st-status--inactive')
            .addClass(st.status === 'ACTIVE' ? 'st-status--active' : 'st-status--inactive')
            .attr('data-status', st.status);

        const $edit = $row.find('.st-edit');
        $edit.attr('data-country-id', st.country_id)
             .attr('data-name', st.name)
             .attr('data-state-code', st.state_code)
             .attr('data-status', st.status)
             .attr('data-url', st.update_url);

        $row.removeClass('st-row-new'); void $row[0].offsetWidth; $row.addClass('st-row-new');
    }

    // ── Drawer setup ──────────────────────────────────────────
    const drawerEl = document.getElementById('stDrawer');
    const drawer   = new bootstrap.Offcanvas(drawerEl);
    let select2Ready = false;

    function ensureCountrySelect2() {
        if (select2Ready) return;
        window.obSearchable('#stDrawerCountry', { dropdownParent: $(drawerEl) });
        select2Ready = true;
    }

    const FIELDS = {
        country_id:  { input: '#stDrawerCountry', err: '#stDrawerCountryErr' },
        name:        { input: '#stDrawerName',    err: '#stDrawerNameErr' },
        state_code:  { input: '#stDrawerCode',    err: '#stDrawerCodeErr' },
        status:      { input: '#stDrawerStatus',  err: '#stDrawerStatusErr' },
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
        ensureCountrySelect2();
        $('#stDrawerTitle').text('Add State');
        $('.st-drawer-label', drawerEl).text('Save');
        $('#stDrawerForm').removeAttr('data-url');
        $('#stDrawerId').val('');
        const preCountry = $('#statesFilter select[name="country_id"]').val() || '';
        $('#stDrawerCountry').val(preCountry).trigger('change.select2');
        $('#stDrawerName').val('');
        $('#stDrawerCode').val('');
        $('#stDrawerStatus').val('ACTIVE');
        clearErrors();
        drawer.show();
    }

    function openDrawerEdit(data) {
        ensureCountrySelect2();
        $('#stDrawerTitle').text('Edit State');
        $('.st-drawer-label', drawerEl).text('Update');
        $('#stDrawerForm').attr('data-url', data.url);
        $('#stDrawerId').val(data.id);
        $('#stDrawerCountry').val(String(data.countryId || '')).trigger('change.select2');
        $('#stDrawerName').val(data.name || '');
        $('#stDrawerCode').val(data.stateCode || '');
        $('#stDrawerStatus').val(data.status || 'ACTIVE');
        clearErrors();
        drawer.show();
    }

    drawerEl.addEventListener('shown.bs.offcanvas', () => {
        setTimeout(() => $('#stDrawerName').trigger('focus'), 50);
    });

    $('#stDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.st-edit', function () {
        openDrawerEdit({
            id:        $(this).data('id'),
            countryId: $(this).data('country-id'),
            name:      $(this).attr('data-name') || '',
            stateCode: $(this).attr('data-state-code') || '',
            status:    $(this).data('status'),
            url:       $(this).data('url'),
        });
    });

    function validateLocal(payload) {
        clearErrors();
        let firstInvalid = null;
        if (!payload.country_id) { setFieldError('country_id', 'Country is required.'); firstInvalid = firstInvalid || FIELDS.country_id.input; }
        if (!payload.name) { setFieldError('name', 'Name is required.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        else if (payload.name.length > 100) { setFieldError('name', 'Name may not be longer than 100 characters.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        if (!payload.state_code) { setFieldError('state_code', 'State code is required.'); firstInvalid = firstInvalid || FIELDS.state_code.input; }
        else if (payload.state_code.length > 100) { setFieldError('state_code', 'State code may not be longer than 100 characters.'); firstInvalid = firstInvalid || FIELDS.state_code.input; }
        if (!payload.status) { setFieldError('status', 'Status is required.'); firstInvalid = firstInvalid || FIELDS.status.input; }
        if (firstInvalid) $(firstInvalid).trigger('focus');
        return !firstInvalid;
    }

    $('#stDrawerForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#stDrawerSubmit').trigger('click'); }
    });

    $('#stDrawerSubmit').on('click', function () {
        const id = $('#stDrawerId').val();
        const payload = {
            country_id: $('#stDrawerCountry').val(),
            name:       $('#stDrawerName').val().trim(),
            state_code: $('#stDrawerCode').val().trim(),
            status:     $('#stDrawerStatus').val(),
        };

        if (!validateLocal(payload)) return;

        const isEdit = !!id;
        const url    = isEdit ? $('#stDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? Object.assign({ _token: CSRF, _method: 'PUT' }, payload)
            : Object.assign({ _token: CSRF }, payload);

        const $btn = $('#stDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    const $prevStatus = $(`#stTbody tr[data-id="${res.state.id}"] .st-status`);
                    const prev = $prevStatus.data('status');
                    if (prev && prev !== res.state.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.state.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.state);
                } else {
                    insertRow(res.state);
                    incrementStatsFor(res.state.status);
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

    @if ($editId = request('edit'))
        (function () {
            const $btn = $(`#stTbody tr[data-id="{{ (int) $editId }}"] .st-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
