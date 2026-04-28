@extends('layouts.admin')
@section('title', 'Cities')
@section('page_title', 'Cities')

@push('styles')
<style>
    /* ── KPI strip ─────────────────────────────────────────────────── */
    .ci-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
    @media (max-width: 767.98px) { .ci-kpis { grid-template-columns: 1fr; } }
    .ci-kpi { display: flex; align-items: center; gap: .9rem; padding: 1rem 1.1rem; border-radius: .6rem;
              background: #fff; box-shadow: 0 2px 8px rgba(34, 41, 47, .05); border: 1px solid rgba(34, 41, 47, .05); }
    .ci-kpi__icon { width: 42px; height: 42px; border-radius: 10px; display: grid; place-items: center; }
    .ci-kpi__icon svg { width: 20px; height: 20px; }
    .ci-kpi__icon--total    { background: rgba(var(--bs-primary-rgb), .12); color: var(--bs-primary); }
    .ci-kpi__icon--active   { background: rgba(var(--bs-success-rgb), .12); color: var(--bs-success); }
    .ci-kpi__icon--inactive { background: rgba(var(--bs-danger-rgb), .12);  color: var(--bs-danger); }
    .ci-kpi__label { font-size: .78rem; color: #6e6b7b; text-transform: uppercase; letter-spacing: .04em; }
    .ci-kpi__value { font-size: 1.5rem; font-weight: 600; line-height: 1.2; color: #5e5873; }

    /* ── Cities — drawer + table polish ────────────────────────────── */
    .ci-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .ci-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .ci-toolbar__country,
    .ci-toolbar__state { flex: 0 0 180px; min-width: 160px; }
    .ci-toolbar__search { flex: 1 1 200px; max-width: 260px; }
    .ci-toolbar__status { flex: 0 0 150px; }
    .ci-toolbar__spacer { flex: 1; }
    .ci-toolbar__actions { display: flex; gap: .5rem; }

    .ci-table { margin-bottom: 0; }
    .ci-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .ci-table tbody tr { transition: background-color .15s ease; }
    .ci-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .ci-table .ci-row-new { animation: ci-row-flash 1.4s ease-out; }
    @keyframes ci-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .ci-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .ci-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .ci-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .ci-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .ci-action-group { display: inline-flex; gap: .25rem; }
    .ci-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .ci-action-group .btn svg { width: 15px; height: 15px; }

    .ci-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .ci-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .ci-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .ci-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .ci-drawer .offcanvas-body { overflow-y: auto; }
    .ci-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .ci-drawer .form-label { font-weight: 500; }
    .ci-drawer .select2-container--default .select2-selection--single { height: calc(2.4rem + 2px); padding: .3rem .4rem; }

    /* Bulk-add drawer rows */
    .ci-bulk-row { display: grid; grid-template-columns: 1fr 140px 36px; gap: .5rem; align-items: start; margin-bottom: .5rem; }
    .ci-bulk-row .ci-bulk-remove { width: 36px; height: 38px; padding: 0; display: grid; place-items: center; }
    .ci-bulk-row__err { grid-column: 1 / -1; font-size: .78rem; color: #ea5455; margin-top: -.25rem; }
</style>
@endpush

@section('content')
<section id="cities-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    <div class="ci-kpis">
        <div class="ci-kpi">
            <div class="ci-kpi__icon ci-kpi__icon--total"><i data-feather="layers"></i></div>
            <div>
                <div class="ci-kpi__label">Total</div>
                <div class="ci-kpi__value" data-stat="total">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="ci-kpi">
            <div class="ci-kpi__icon ci-kpi__icon--active"><i data-feather="check-circle"></i></div>
            <div>
                <div class="ci-kpi__label">Active</div>
                <div class="ci-kpi__value" data-stat="active">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="ci-kpi">
            <div class="ci-kpi__icon ci-kpi__icon--inactive"><i data-feather="slash"></i></div>
            <div>
                <div class="ci-kpi__label">Inactive</div>
                <div class="ci-kpi__value" data-stat="inactive">{{ $stats['inactive'] }}</div>
            </div>
        </div>
    </div>

    <div class="card ci-card">

        @php
            $selectedCountry = $countries->firstWhere('id', request('country_id'));
            $selectedState   = $states->firstWhere('id', request('state_id'));
            $selectedStatus  = request('status');
            $statusLabel     = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;

            if ($selectedState) {
                $headerTitle = 'All ' . ($statusLabel ? $statusLabel . ' ' : '') . $selectedState->name . ' Cities';
            } elseif ($selectedCountry) {
                $headerTitle = 'All ' . ($statusLabel ? $statusLabel . ' ' : '') . $selectedCountry->name . ' Cities';
            } elseif ($statusLabel) {
                $headerTitle = 'All ' . $statusLabel . ' Cities';
            } else {
                $headerTitle = 'All Cities';
            }
        @endphp

        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-outline-primary" id="ciBulkOpen">
                    <i data-feather="layers" class="me-25"></i> Bulk add
                </button>
                <button type="button" class="btn btn-primary" id="ciDrawerOpen">
                    <i data-feather="plus" class="me-25"></i> Add City
                </button>
            </div>
        </div>

        <div class="card-body py-1">
            <form id="citiesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-3">
                    <select id="filter_country_id" name="country_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}" @selected(request('country_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter_state_id" name="state_id" class="js-searchable form-select">
                        <option value="">All states</option>
                        @foreach ($states as $s)
                            <option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" @selected(request('state_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" placeholder="Search city…" value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.cities.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Table ─────────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover ci-table" id="ciTable">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Country', 'key' => 'country', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'State', 'key' => 'state', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'City', 'key' => 'name', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Status', 'key' => 'status', 'default' => 'name'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Zip Codes', 'key' => 'zipcodes_count', 'default' => 'name'])</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="ciTbody">
                    @forelse ($cities as $city)
                        @php $hasZips = $city->zipcodes_count > 0; @endphp
                        <tr data-id="{{ $city->id }}">
                            <td class="ci-cell-country">{{ $city->state?->country?->name ?? '—' }}</td>
                            <td class="ci-cell-state">{{ $city->state?->name ?? '—' }}</td>
                            <td class="fw-bolder ci-cell-name">{{ $city->name }}</td>
                            <td>
                                <span class="ci-status ci-status--{{ $city->status === 'ACTIVE' ? 'active' : 'inactive' }}" data-status="{{ $city->status }}">{{ $city->status }}</span>
                            </td>
                            <td class="ci-cell-zips">
                                @if ($hasZips)
                                    {{ $city->zipcodes_count }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="ci-action-group">
                                    <a href="{{ route('admin.cities.show', $city) }}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="btn btn-outline-primary ci-edit" title="Edit"
                                            data-id="{{ $city->id }}"
                                            data-country-id="{{ $city->state?->country_id }}"
                                            data-state-id="{{ $city->state_id }}"
                                            data-state-name="{{ $city->state?->name }}"
                                            data-name="{{ $city->name }}"
                                            data-status="{{ $city->status }}"
                                            data-url="{{ route('admin.cities.ajax.update', $city) }}">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    @if (! $hasZips)
                                        <form method="POST" action="{{ route('admin.cities.destroy', $city) }}" class="d-inline js-delete-form" data-confirm="Delete city '{{ $city->name }}'?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '{{ $city->name }}' — it has linked zip codes. Remove them first.">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="ciEmptyRow">
                            <td colspan="6">
                                <div class="ci-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No cities yet</div>
                                    <div class="small">Click <em>Add City</em> to create one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($cities->hasPages())
            <div class="card-body border-top">{{ $cities->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Slide-over drawer (create / edit) ─────────────────────── --}}
<div class="offcanvas offcanvas-end ci-drawer" tabindex="-1" id="ciDrawer" aria-labelledby="ciDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="ciDrawerTitle">Add City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="ciDrawerForm" novalidate autocomplete="off">
            <input type="hidden" id="ciDrawerId" value="">

            <div class="mb-1">
                <label class="form-label" for="ciDrawerCountry">Country<span class="text-danger">*</span></label>
                <select id="ciDrawerCountry" class="form-select">
                    <option value="">Select country</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback d-block" id="ciDrawerCountryErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ciDrawerState">State<span class="text-danger">*</span></label>
                <select id="ciDrawerState" class="form-select" disabled>
                    <option value="">Select state</option>
                </select>
                <div class="invalid-feedback d-block" id="ciDrawerStateErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ciDrawerName">City Name<span class="text-danger">*</span></label>
                <input type="text" id="ciDrawerName" class="form-control" maxlength="100" required>
                <div class="invalid-feedback d-block" id="ciDrawerNameErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ciDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="ciDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <div class="invalid-feedback d-block" id="ciDrawerStatusErr"></div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="ciDrawerSubmit">
            <span class="ci-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

{{-- ── Bulk-add drawer ────────────────────────────────────────── --}}
<div class="offcanvas offcanvas-end ci-drawer" tabindex="-1" id="ciBulkDrawer" aria-labelledby="ciBulkTitle" style="width: min(640px, 100vw);">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title mb-0" id="ciBulkTitle">Bulk add cities</h5>
            <small class="text-muted">Pick a country and state, then add up to 50 cities in one save.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="row g-2 mb-2">
            <div class="col-sm-6">
                <label class="form-label" for="ciBulkCountry">Country<span class="text-danger">*</span></label>
                <select id="ciBulkCountry" class="form-select">
                    <option value="">Select country</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback d-block" id="ciBulkCountryErr"></div>
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="ciBulkState">State<span class="text-danger">*</span></label>
                <select id="ciBulkState" class="form-select" disabled>
                    <option value="">Select state</option>
                </select>
                <div class="invalid-feedback d-block" id="ciBulkStateErr"></div>
            </div>
        </div>
        <div id="ciBulkRows"></div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="ciBulkAddRow">
            <i data-feather="plus" style="width:14px;height:14px;"></i> Add another row
        </button>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="ciBulkSubmit">
            <span class="ci-bulk-label">Save all</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    obPreloadedCascade({
        parent: '#filter_country_id',
        child: '#filter_state_id',
        parentAttr: 'data-country-id',
    });
    obAutoFilter('#citiesFilter');

    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store:  @json(route('admin.cities.ajax.store')),
        bulk:   @json(route('admin.cities.ajax.bulk')),
        states: @json(route('admin.ajax.states')),
    };

    const $tbody = $('#ciTbody');

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

    function removeEmptyPlaceholder() { $('#ciEmptyRow').remove(); }

    function buildRowHtml(ci) {
        const statusClass = ci.status === 'ACTIVE' ? 'ci-status--active' : 'ci-status--inactive';
        const hasZips = ci.zipcodes_count > 0;
        const zipsCell = hasZips ? String(ci.zipcodes_count) : '<span class="text-muted">—</span>';
        const deleteBtn = hasZips
            ? `<button type="button" class="btn btn-outline-danger js-delete-blocked" aria-disabled="true" title="Cannot delete" data-reason="Cannot delete '${escapeHtml(ci.name)}' — it has linked zip codes. Remove them first."><i data-feather="trash-2"></i></button>`
            : `<form method="POST" action="${ci.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete city '${escapeHtml(ci.name)}'?">
                   <input type="hidden" name="_token" value="${CSRF}">
                   <input type="hidden" name="_method" value="DELETE">
                   <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
               </form>`;

        return `
            <tr class="ci-row-new" data-id="${ci.id}">
                <td class="ci-cell-country">${escapeHtml(ci.country_name)}</td>
                <td class="ci-cell-state">${escapeHtml(ci.state_name)}</td>
                <td class="fw-bolder ci-cell-name">${escapeHtml(ci.name)}</td>
                <td><span class="ci-status ${statusClass}" data-status="${ci.status}">${ci.status}</span></td>
                <td class="ci-cell-zips">${zipsCell}</td>
                <td class="text-end">
                    <div class="ci-action-group">
                        <a href="${ci.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary ci-edit" title="Edit"
                                data-id="${ci.id}"
                                data-country-id="${ci.country_id ?? ''}"
                                data-state-id="${ci.state_id}"
                                data-state-name="${escapeHtml(ci.state_name)}"
                                data-name="${escapeHtml(ci.name)}"
                                data-status="${ci.status}"
                                data-url="${ci.update_url}"><i data-feather="edit-2"></i></button>
                        ${deleteBtn}
                    </div>
                </td>
            </tr>`;
    }

    function insertRow(ci) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(ci));
        if (window.feather) window.feather.replace();
    }

    function updateRow(ci) {
        const $row = $(`#ciTbody tr[data-id="${ci.id}"]`);
        if (!$row.length) return;
        $row.find('.ci-cell-country').text(ci.country_name);
        $row.find('.ci-cell-state').text(ci.state_name);
        $row.find('.ci-cell-name').text(ci.name);

        const $status = $row.find('.ci-status');
        $status.text(ci.status)
            .removeClass('ci-status--active ci-status--inactive')
            .addClass(ci.status === 'ACTIVE' ? 'ci-status--active' : 'ci-status--inactive')
            .attr('data-status', ci.status);

        const $edit = $row.find('.ci-edit');
        $edit.attr('data-country-id', ci.country_id ?? '')
             .attr('data-state-id', ci.state_id)
             .attr('data-state-name', ci.state_name)
             .attr('data-name', ci.name)
             .attr('data-status', ci.status)
             .attr('data-url', ci.update_url);

        $row.removeClass('ci-row-new'); void $row[0].offsetWidth; $row.addClass('ci-row-new');
    }

    // ── Drawer setup ──────────────────────────────────────────
    const drawerEl = document.getElementById('ciDrawer');
    const drawer   = new bootstrap.Offcanvas(drawerEl);
    let select2Ready = false;

    function ensureSelect2() {
        if (select2Ready) return;
        window.obSearchable('#ciDrawerCountry', { dropdownParent: $(drawerEl) });
        window.obSearchable('#ciDrawerState',   { dropdownParent: $(drawerEl) });
        select2Ready = true;
    }

    const FIELDS = {
        country_id: { input: '#ciDrawerCountry', err: '#ciDrawerCountryErr' },
        state_id:   { input: '#ciDrawerState',   err: '#ciDrawerStateErr' },
        name:       { input: '#ciDrawerName',    err: '#ciDrawerNameErr' },
        status:     { input: '#ciDrawerStatus',  err: '#ciDrawerStatusErr' },
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

    // Load states for the drawer's Country → State cascade.
    // Returns a Promise that resolves after options are populated and optional preselect applied.
    function loadStates(countryId, preselect) {
        const $state = $('#ciDrawerState');
        if (!countryId) {
            $state.prop('disabled', true)
                  .empty()
                  .append(new Option('Select state', ''))
                  .val('')
                  .trigger('change.select2');
            return $.Deferred().resolve().promise();
        }
        $state.prop('disabled', true)
              .empty()
              .append(new Option('Loading…', ''))
              .trigger('change.select2');
        return $.getJSON(ROUTES.states, { country_id: countryId })
            .done((items) => {
                $state.empty().append(new Option('Select state', ''));
                items.forEach((it) => $state.append(new Option(it.name, it.id)));
                if (preselect) $state.val(String(preselect));
                $state.prop('disabled', false).trigger('change.select2');
            })
            .fail(() => {
                $state.empty().append(new Option('Failed to load', ''));
            });
    }

    $('#ciDrawerCountry').on('change', function () {
        loadStates($(this).val(), null);
    });

    function openDrawerCreate() {
        ensureSelect2();
        $('#ciDrawerTitle').text('Add City');
        $('.ci-drawer-label', drawerEl).text('Save');
        $('#ciDrawerForm').removeAttr('data-url');
        $('#ciDrawerId').val('');
        const preCountry = $('#filter_country_id').val() || '';
        const preState   = $('#filter_state_id').val() || '';
        $('#ciDrawerCountry').val(preCountry).trigger('change.select2');
        $('#ciDrawerName').val('');
        $('#ciDrawerStatus').val('ACTIVE');
        clearErrors();
        loadStates(preCountry, preState);
        drawer.show();
    }

    function openDrawerEdit(data) {
        ensureSelect2();
        $('#ciDrawerTitle').text('Edit City');
        $('.ci-drawer-label', drawerEl).text('Update');
        $('#ciDrawerForm').attr('data-url', data.url);
        $('#ciDrawerId').val(data.id);
        $('#ciDrawerCountry').val(String(data.countryId || '')).trigger('change.select2');
        $('#ciDrawerName').val(data.name || '');
        $('#ciDrawerStatus').val(data.status || 'ACTIVE');
        clearErrors();
        loadStates(data.countryId, data.stateId);
        drawer.show();
    }

    drawerEl.addEventListener('shown.bs.offcanvas', () => {
        setTimeout(() => $('#ciDrawerName').trigger('focus'), 50);
    });

    $('#ciDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.ci-edit', function () {
        openDrawerEdit({
            id:        $(this).data('id'),
            countryId: $(this).data('country-id'),
            stateId:   $(this).data('state-id'),
            name:      $(this).attr('data-name') || '',
            status:    $(this).data('status'),
            url:       $(this).data('url'),
        });
    });

    function validateLocal(payload) {
        clearErrors();
        let firstInvalid = null;
        if (!payload.country_id) { setFieldError('country_id', 'Country is required.'); firstInvalid = firstInvalid || FIELDS.country_id.input; }
        if (!payload.state_id) { setFieldError('state_id', 'State is required.'); firstInvalid = firstInvalid || FIELDS.state_id.input; }
        if (!payload.name) { setFieldError('name', 'Name is required.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        else if (payload.name.length > 100) { setFieldError('name', 'Name may not be longer than 100 characters.'); firstInvalid = firstInvalid || FIELDS.name.input; }
        if (!payload.status) { setFieldError('status', 'Status is required.'); firstInvalid = firstInvalid || FIELDS.status.input; }
        if (firstInvalid) $(firstInvalid).trigger('focus');
        return !firstInvalid;
    }

    $('#ciDrawerForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#ciDrawerSubmit').trigger('click'); }
    });

    $('#ciDrawerSubmit').on('click', function () {
        const id = $('#ciDrawerId').val();
        const payload = {
            country_id: $('#ciDrawerCountry').val(),
            state_id:   $('#ciDrawerState').val(),
            name:       $('#ciDrawerName').val().trim(),
            status:     $('#ciDrawerStatus').val(),
        };

        if (!validateLocal(payload)) return;

        const isEdit = !!id;
        const url    = isEdit ? $('#ciDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? Object.assign({ _token: CSRF, _method: 'PUT' }, payload)
            : Object.assign({ _token: CSRF }, payload);

        const $btn = $('#ciDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    const $prevStatus = $(`#ciTbody tr[data-id="${res.city.id}"] .ci-status`);
                    const prev = $prevStatus.data('status');
                    if (prev && prev !== res.city.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.city.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.city);
                } else {
                    insertRow(res.city);
                    incrementStatsFor(res.city.status);
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
    const bulkEl = document.getElementById('ciBulkDrawer');
    const bulk   = new bootstrap.Offcanvas(bulkEl);
    let bulkSelect2Ready = false;

    function ensureBulkSelect2() {
        if (bulkSelect2Ready) return;
        window.obSearchable('#ciBulkCountry', { dropdownParent: $(bulkEl) });
        window.obSearchable('#ciBulkState',   { dropdownParent: $(bulkEl) });
        bulkSelect2Ready = true;
    }

    function loadBulkStates(countryId, preselect) {
        const $state = $('#ciBulkState');
        if (!countryId) {
            $state.prop('disabled', true)
                  .empty()
                  .append(new Option('Select state', ''))
                  .val('')
                  .trigger('change.select2');
            return $.Deferred().resolve().promise();
        }
        $state.prop('disabled', true)
              .empty()
              .append(new Option('Loading…', ''))
              .trigger('change.select2');
        return $.getJSON(ROUTES.states, { country_id: countryId })
            .done((items) => {
                $state.empty().append(new Option('Select state', ''));
                items.forEach((it) => $state.append(new Option(it.name, it.id)));
                if (preselect) $state.val(String(preselect));
                $state.prop('disabled', false).trigger('change.select2');
            })
            .fail(() => {
                $state.empty().append(new Option('Failed to load', ''));
            });
    }

    $('#ciBulkCountry').on('change', function () {
        loadBulkStates($(this).val(), null);
    });

    function buildBulkRow(name = '', status = 'ACTIVE') {
        const $row = $(`
            <div class="ci-bulk-row">
                <input type="text" class="form-control ci-bulk-name" placeholder="City name" maxlength="100">
                <select class="form-select ci-bulk-status">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <button type="button" class="btn btn-outline-danger ci-bulk-remove" title="Remove row">
                    <i data-feather="x" style="width:14px;height:14px;"></i>
                </button>
                <div class="ci-bulk-row__err"></div>
            </div>
        `);
        $row.find('.ci-bulk-name').val(name);
        $row.find('.ci-bulk-status').val(status);
        return $row;
    }

    function resetBulk() {
        ensureBulkSelect2();
        const preCountry = $('#filter_country_id').val() || '';
        const preState   = $('#filter_state_id').val() || '';
        $('#ciBulkCountry').val(preCountry).trigger('change.select2');
        $('#ciBulkCountry, #ciBulkState').removeClass('is-invalid');
        $('#ciBulkCountryErr, #ciBulkStateErr').text('');
        loadBulkStates(preCountry, preState);
        $('#ciBulkRows').empty()
            .append(buildBulkRow())
            .append(buildBulkRow());
        if (window.feather) window.feather.replace();
    }

    $('#ciBulkOpen').on('click', () => { resetBulk(); bulk.show(); });

    $('#ciBulkAddRow').on('click', () => {
        const $r = buildBulkRow();
        $('#ciBulkRows').append($r);
        if (window.feather) window.feather.replace();
        $r.find('.ci-bulk-name').trigger('focus');
    });

    $(document).on('click', '.ci-bulk-remove', function () {
        const $rows = $('#ciBulkRows .ci-bulk-row');
        if ($rows.length <= 1) {
            $(this).closest('.ci-bulk-row').find('.ci-bulk-name').val('');
            return;
        }
        $(this).closest('.ci-bulk-row').remove();
    });

    $('#ciBulkSubmit').on('click', function () {
        const countryId = $('#ciBulkCountry').val();
        const stateId   = $('#ciBulkState').val();

        $('#ciBulkCountry, #ciBulkState').removeClass('is-invalid');
        $('#ciBulkCountryErr, #ciBulkStateErr').text('');

        if (!countryId) {
            $('#ciBulkCountry').addClass('is-invalid').trigger('focus');
            $('#ciBulkCountryErr').text('Country is required.');
            return;
        }
        if (!stateId) {
            $('#ciBulkState').addClass('is-invalid').trigger('focus');
            $('#ciBulkStateErr').text('State is required.');
            return;
        }

        const rows = [];
        const $rowEls = $('#ciBulkRows .ci-bulk-row');
        $rowEls.find('.ci-bulk-row__err').text('');
        $rowEls.find('.ci-bulk-name').removeClass('is-invalid');

        let firstInvalid = null;
        $rowEls.each(function (i) {
            const name   = $(this).find('.ci-bulk-name').val().trim();
            const status = $(this).find('.ci-bulk-status').val();
            if (name) {
                rows.push({ state_id: stateId, name, status });
            } else if (i === 0) {
                if (!firstInvalid) firstInvalid = $(this);
                $(this).find('.ci-bulk-name').addClass('is-invalid');
                $(this).find('.ci-bulk-row__err').text('Name is required.');
            }
        });

        if (firstInvalid) { firstInvalid.find('.ci-bulk-name').trigger('focus'); return; }
        if (rows.length === 0) { toast('info', 'Add at least one city.'); return; }

        const $btn = $('#ciBulkSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({
            url: ROUTES.bulk,
            method: 'POST',
            data: { _token: CSRF, cities: rows },
            dataType: 'json'
        })
        .done((res) => {
            if (!res || !res.ok) return;
            res.cities.forEach((c) => { insertRow(c); incrementStatsFor(c.status); });
            toast('success', res.message);
            bulk.hide();
        })
        .fail((xhr) => {
            const errs = xhr.responseJSON?.errors || {};
            Object.keys(errs).forEach((key) => {
                const m = key.match(/^cities\.(\d+)\.(state_id|name|status)$/);
                if (!m) return;
                if (m[2] === 'state_id') {
                    $('#ciBulkState').addClass('is-invalid');
                    $('#ciBulkStateErr').text(errs[key][0]);
                    return;
                }
                const $row = $('#ciBulkRows .ci-bulk-row').eq(parseInt(m[1], 10));
                if (!$row.length) return;
                $row.find('.ci-bulk-name').addClass('is-invalid');
                $row.find('.ci-bulk-row__err').text(errs[key][0]);
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
            const $btn = $(`#ciTbody tr[data-id="{{ (int) $editId }}"] .ci-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
