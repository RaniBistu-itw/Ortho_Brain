@extends('layouts.admin')
@section('title', 'States')
@section('page_title', 'States')

@push('styles')
<style>
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


    .st-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .st-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .st-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .st-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    /* Body hugs its content so the action bar sits right below the form, not pinned at the panel's bottom edge */
    .st-drawer .offcanvas-body { flex: 0 1 auto; overflow-y: auto; }
    .st-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: transparent; }
    .st-drawer .form-label { font-weight: 500; }
    .st-drawer .select2-container--default .select2-selection--single { height: calc(2.4rem + 2px); padding: .3rem .4rem; }

    /* Bulk-add drawer rows */
    .st-bulk-row { display: grid; grid-template-columns: 1fr 120px 120px 36px; gap: .5rem; align-items: start; margin-bottom: .5rem; }
    .st-bulk-row .st-bulk-remove { width: 36px; height: 38px; padding: 0; display: grid; place-items: center; }
    .st-bulk-row__err { grid-column: 1 / -1; font-size: .78rem; color: #ea5455; margin-top: -.25rem; }
</style>
@endpush

@section('content')
@include('admin._partials.inline_status_dropdown')
<section id="states-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    @include('admin._partials.stat_cards', [
        'cards' => [
            ['label' => 'Total',    'value' => $stats['total'],    'icon' => 'map',          'tone' => 'primary', 'stat_key' => 'total'],
            ['label' => 'Active',   'value' => $stats['active'],   'icon' => 'check-circle', 'tone' => 'success', 'stat_key' => 'active'],
            ['label' => 'Inactive', 'value' => $stats['inactive'], 'icon' => 'slash',        'tone' => 'danger',  'stat_key' => 'inactive'],
        ],
    ])

    <div class="card st-card">

        @php
            $selectedCountry = $countries->firstWhere('id', request('country_id'));
            $selectedStatus  = request('status');
            $statusLabel     = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;

            if ($selectedCountry) {
                $headerTitle = 'All ' . ($statusLabel ? $statusLabel . ' ' : '') . $selectedCountry->name . ' States';
            } elseif ($statusLabel) {
                $headerTitle = 'All ' . $statusLabel . ' States';
            } else {
                $headerTitle = 'All States';
            }
        @endphp

        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-outline-primary" id="stBulkOpen">
                    <i data-feather="layers" class="me-25"></i> Bulk add
                </button>
                <button type="button" class="btn btn-primary" id="stDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add State
                </button>
            </div>
        </div>

        <div class="card-body py-1">
            <form id="statesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-3">
                    <select name="country_id" class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected(request('country_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" placeholder="Search name or code…" value="{{ request('search') }}" class="form-control">
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
                    <a href="{{ route('admin.states.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Table ─────────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover st-table" id="stTable">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Country', 'key' => 'country', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'State', 'key' => 'name', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Code', 'key' => 'state_code', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Cities', 'key' => 'cities_count', 'default' => 'name'])</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="stTbody">
                    @forelse ($states as $s)
                        @php $hasCities = $s->cities_count > 0; @endphp
                        <tr data-id="{{ $s->id }}" data-row-href="{{ route('admin.states.show', $s) }}">
                            <td class="st-cell-country">{{ $s->country?->name ?? '—' }}</td>
                            <td class="fw-bolder st-cell-name">{{ $s->name }}</td>
                            <td class="st-cell-code">{{ $s->state_code }}</td>
                            <td class="st-cell-cities">
                                @if ($hasCities)
                                    {{ $s->cities_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <select class="ob-status-select" data-inline-status
                                        data-url="{{ route('admin.states.status', $s) }}"
                                        data-status="{{ $s->status }}"
                                        aria-label="Update status for {{ $s->name }}">
                                    <option value="ACTIVE"   @selected($s->status === 'ACTIVE')>Active</option>
                                    <option value="INACTIVE" @selected($s->status === 'INACTIVE')>Inactive</option>
                                </select>
                            </td>
                            <td class="text-end">
                                <div class="ob-row-actions">
                                    <a href="{{ route('admin.states.show', $s) }}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="ob-icon-btn ob-icon-btn--edit st-edit" title="Edit"
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
                                            <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $s->name }}' — it has linked cities. Remove them first.">
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

{{-- ── Bulk-add drawer ────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end st-drawer" tabindex="-1" id="stBulkDrawer" aria-labelledby="stBulkTitle" style="width: min(640px, 100vw);">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-0" id="stBulkTitle">Bulk add states</h5>
            <small class="text-muted">Pick a country, then add up to 50 states in one save.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mb-2">
            <label class="form-label" for="stBulkCountry">Country<span class="text-danger">*</span></label>
            <select id="stBulkCountry" class="form-select">
                <option value="">Select country</option>
                @foreach ($countries as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback d-block" id="stBulkCountryErr"></div>
        </div>
        <div id="stBulkRows"></div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="stBulkAddRow">
            <i data-feather="plus" style="width:14px;height:14px;"></i> Add another row
        </button>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="stBulkSubmit">
            <span class="st-bulk-label">Save all</span>
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
        store:        @json(route('admin.states.ajax.store')),
        bulk:         @json(route('admin.states.ajax.bulk')),
        checkUnique:  @json(route('admin.states.ajax.check-unique')),
    };

    function debounce(fn, ms) {
        let t;
        return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), ms); };
    }

    const namePattern = /^[a-zA-Z\s]+$/;

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
        const hasCities = st.cities_count > 0;
        const citiesCell = hasCities ? String(st.cities_count) : '<span class="text-muted">—</span>';
        const deleteBtn = hasCities
            ? `<button type="button" class="ob-icon-btn ob-icon-btn--disabled js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(st.name)}' — it has linked cities. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${st.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete state '${escapeHtml(st.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="ob-icon-btn ob-icon-btn--delete" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="st-row-new" data-id="${st.id}" data-row-href="${st.show_url}">
                <td class="st-cell-country">${escapeHtml(st.country_name)}</td>
                <td class="fw-bolder st-cell-name">${escapeHtml(st.name)}</td>
                <td class="st-cell-code">${escapeHtml(st.state_code)}</td>
                <td class="st-cell-cities">${citiesCell}</td>
                <td>
                    <select class="ob-status-select" data-inline-status data-url="${st.status_url}" data-status="${st.status}" aria-label="Update status for ${escapeHtml(st.name)}">
                        <option value="ACTIVE"${st.status === 'ACTIVE' ? ' selected' : ''}>Active</option>
                        <option value="INACTIVE"${st.status === 'INACTIVE' ? ' selected' : ''}>Inactive</option>
                    </select>
                </td>
                <td class="text-end">
                    <div class="ob-row-actions">
                        <a href="${st.show_url}" class="ob-icon-btn ob-icon-btn--view" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="ob-icon-btn ob-icon-btn--edit st-edit" title="Edit"
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

        const $status = $row.find('.ob-status-select');
        $status.attr('data-status', st.status).val(st.status);

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

    // ── Live duplicate check on the single drawer (name + code per country) ─
    function liveCheckDrawerField(field, $input, $err) {
        const value      = $input.val().trim();
        const country_id = $('#stDrawerCountry').val();
        const ignore_id  = $('#stDrawerId').val() || null;
        if (!value || !country_id) {
            $input.removeClass('is-invalid');
            $err.text('');
            return;
        }
        $.post(ROUTES.checkUnique, { _token: CSRF, field, value, country_id, ignore_id })
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
    const liveCheckDrawerCode = debounce(() =>
        liveCheckDrawerField('state_code', $('#stDrawerCode'), $('#stDrawerCodeErr')), 350);

    $(document).on('keyup input', '#stDrawerName', function () {
        const $input = $(this);
        const $err   = $('#stDrawerNameErr');
        const val    = $input.val().trim();
        if (val && !namePattern.test(val)) {
            $input.addClass('is-invalid');
            $err.text('The name may only contain letters and spaces.');
            return;
        }
        $input.removeClass('is-invalid');
        $err.text('');
        debounce(() => liveCheckDrawerField('name', $input, $err), 350)();
    });
    $(document).on('keyup input', '#stDrawerCode', liveCheckDrawerCode);
    // Re-check when the parent country changes (uniqueness is scoped per country)
    $(document).on('change', '#stDrawerCountry', () => {
        $('#stDrawerName').trigger('input');
        liveCheckDrawerCode();
    });

    // ── Live duplicate check on each bulk row (name + code) ───────────────
    function bulkRowFieldLiveCheck($input, field, dupMsg) {
        const $row      = $input.closest('.st-bulk-row');
        const $err      = $row.find('.st-bulk-row__err');
        const value     = $input.val().trim();
        const countryId = $('#stBulkCountry').val();
        const sel       = field === 'state_code' ? '.st-bulk-code' : '.st-bulk-name';

        // Within-batch duplicates first (case-insensitive, scoped to this field)
        let dupInBatch = false;
        if (value) {
            $('#stBulkRows .st-bulk-row').each(function () {
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

        if (!value || !countryId) {
            $input.removeClass('is-invalid');
            $err.text('');
            return;
        }
        $.post(ROUTES.checkUnique, { _token: CSRF, field, value, country_id: countryId })
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
const bulkRowCodeDebounced = debounce(function (el) {
        bulkRowFieldLiveCheck($(el), 'state_code', 'Duplicate code in this batch.');
    }, 350);

    $(document).on('keyup input', '#stBulkRows .st-bulk-name', function () {
        const $input = $(this);
        const $row   = $input.closest('.st-bulk-row');
        const $err   = $row.find('.st-bulk-row__err');
        const val    = $input.val().trim();
        if (val && !namePattern.test(val)) {
            $input.addClass('is-invalid');
            $err.text('The name may only contain letters and spaces.');
            return;
        }
        $input.removeClass('is-invalid');
        $err.text('');
        debounce(function (el) {
            bulkRowFieldLiveCheck($(el), 'name', 'Duplicate name in this batch.');
        }, 350)(this);
    });
    $(document).on('keyup input', '#stBulkRows .st-bulk-code', function () { bulkRowCodeDebounced(this); });
    $(document).on('change', '#stBulkCountry', function () {
        $('#stBulkRows .st-bulk-name').each(function () { bulkRowFieldLiveCheck($(this), 'name', 'Duplicate name in this batch.'); });
        $('#stBulkRows .st-bulk-code').each(function () { bulkRowFieldLiveCheck($(this), 'state_code', 'Duplicate code in this batch.'); });
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
        else if (!namePattern.test(payload.name)) { setFieldError('name', 'The name may only contain letters and spaces.'); firstInvalid = firstInvalid || FIELDS.name.input; }
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
                    const prev = $(`#stTbody tr[data-id="${res.state.id}"] .ob-status-select`).attr('data-status');
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

    // ── Bulk-add drawer ────────────────────────────────────────
    const bulkEl = document.getElementById('stBulkDrawer');
    const bulk   = new bootstrap.Offcanvas(bulkEl);
    let bulkSelect2Ready = false;

    function ensureBulkSelect2() {
        if (bulkSelect2Ready) return;
        window.obSearchable('#stBulkCountry', { dropdownParent: $(bulkEl) });
        bulkSelect2Ready = true;
    }

    function buildBulkRow(name = '', code = '', status = 'ACTIVE') {
        const $row = $(`
            <div class="st-bulk-row">
                <input type="text" class="form-control st-bulk-name" placeholder="State name" maxlength="100">
                <input type="text" class="form-control st-bulk-code" placeholder="Code" maxlength="100">
                <select class="form-select st-bulk-status">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <button type="button" class="btn btn-outline-danger st-bulk-remove" title="Remove row">
                    <i data-feather="x" style="width:14px;height:14px;"></i>
                </button>
                <div class="st-bulk-row__err"></div>
            </div>
        `);
        $row.find('.st-bulk-name').val(name);
        $row.find('.st-bulk-code').val(code);
        $row.find('.st-bulk-status').val(status);
        return $row;
    }

    function resetBulk() {
        ensureBulkSelect2();
        const preCountry = $('#statesFilter select[name="country_id"]').val() || '';
        $('#stBulkCountry').val(preCountry).trigger('change.select2');
        $('#stBulkCountry').removeClass('is-invalid');
        $('#stBulkCountryErr').text('');
        $('#stBulkRows').empty()
            .append(buildBulkRow())
            .append(buildBulkRow());
        if (window.feather) window.feather.replace();
    }

    $('#stBulkOpen').on('click', () => { resetBulk(); bulk.show(); });

    $('#stBulkAddRow').on('click', () => {
        const $r = buildBulkRow();
        $('#stBulkRows').append($r);
        if (window.feather) window.feather.replace();
        $r.find('.st-bulk-name').trigger('focus');
    });

    $(document).on('click', '.st-bulk-remove', function () {
        const $rows = $('#stBulkRows .st-bulk-row');
        if ($rows.length <= 1) {
            $(this).closest('.st-bulk-row').find('input').val('');
            return;
        }
        $(this).closest('.st-bulk-row').remove();
    });

    $('#stBulkSubmit').on('click', function () {
        const countryId = $('#stBulkCountry').val();
        $('#stBulkCountry').removeClass('is-invalid');
        $('#stBulkCountryErr').text('');
        if (!countryId) {
            $('#stBulkCountry').addClass('is-invalid').trigger('focus');
            $('#stBulkCountryErr').text('Country is required.');
            return;
        }

        const rows = [];
        const $rowEls = $('#stBulkRows .st-bulk-row');
        $rowEls.find('.st-bulk-row__err').text('');
        $rowEls.find('input').removeClass('is-invalid');

        let firstInvalid = null;
        const markBad = ($row, $input, msg) => {
            $input.addClass('is-invalid');
            const $err = $row.find('.st-bulk-row__err');
            $err.text($err.text() ? ($err.text() + ' ' + msg) : msg);
            if (!firstInvalid) firstInvalid = $input;
        };

        $rowEls.each(function (i) {
            const $row = $(this);
            const name   = $row.find('.st-bulk-name').val().trim();
            const code   = $row.find('.st-bulk-code').val().trim();
            const status = $row.find('.st-bulk-status').val();

            const hasAny = name || code;
            if (!hasAny && i !== 0) return;

            if (!name) markBad($row, $row.find('.st-bulk-name'), 'Name is required.');
            if (!code) markBad($row, $row.find('.st-bulk-code'), 'Code is required.');
            if (name && code) rows.push({ country_id: countryId, name, state_code: code, status });
        });

        if (firstInvalid) { firstInvalid.trigger('focus'); return; }
        if (rows.length === 0) { toast('info', 'Add at least one state.'); return; }

        const $btn = $('#stBulkSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: ROUTES.bulk,
            method: 'POST',
            data: { _token: CSRF, states: rows },
            dataType: 'json'
        })
        .done((res) => {
            if (!res || !res.ok) return;
            res.states.forEach((s) => { insertRow(s); incrementStatsFor(s.status); });
            toast('success', res.message);
            bulk.hide();
        })
        .fail((xhr) => {
            const errs = xhr.responseJSON?.errors || {};
            const inputClass = { name: '.st-bulk-name', state_code: '.st-bulk-code', status: '.st-bulk-status' };
            Object.keys(errs).forEach((key) => {
                const m = key.match(/^states\.(\d+)\.(country_id|name|state_code|status)$/);
                if (!m) return;
                if (m[2] === 'country_id') {
                    $('#stBulkCountry').addClass('is-invalid');
                    $('#stBulkCountryErr').text(errs[key][0]);
                    return;
                }
                const $row = $('#stBulkRows .st-bulk-row').eq(parseInt(m[1], 10));
                if (!$row.length) return;
                $row.find(inputClass[m[2]]).addClass('is-invalid');
                const $err = $row.find('.st-bulk-row__err');
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
            const $btn = $(`#stTbody tr[data-id="{{ (int) $editId }}"] .st-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
