@extends('layouts.admin')
@section('title', 'Zip Codes')
@section('page_title', 'Zip Codes')

@push('styles')
<style>
    /* ── Zip Codes — drawer + table polish ─────────────────────────── */
    .zi-card { border: 1px solid rgba(34, 41, 47, .05); box-shadow: 0 2px 10px rgba(34, 41, 47, .05); }
    .zi-toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(34, 41, 47, .06); }
    .zi-toolbar__geo { flex: 0 0 160px; min-width: 140px; }
    .zi-toolbar__search { flex: 1 1 180px; max-width: 220px; }
    .zi-toolbar__status { flex: 0 0 140px; }
    .zi-toolbar__spacer { flex: 1; }
    .zi-toolbar__actions { display: flex; gap: .5rem; }

    .zi-table { margin-bottom: 0; }
    .zi-table thead th { background: #f8f8f8; text-transform: uppercase; font-size: .74rem; letter-spacing: .06em; color: #6e6b7b; font-weight: 600; border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .zi-table tbody tr { transition: background-color .15s ease; }
    .zi-table tbody tr:hover { background: rgba(var(--bs-primary-rgb), .04); }
    .zi-table .zi-row-new { animation: zi-row-flash 1.4s ease-out; }
    @keyframes zi-row-flash { 0% { background: rgba(var(--bs-success-rgb), .2); } 100% { background: transparent; } }

    .zi-status { display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .04em; }
    .zi-status::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; }
    .zi-status--active   { background: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .zi-status--inactive { background: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }

    .zi-action-group { display: inline-flex; gap: .25rem; }
    .zi-action-group .btn { width: 32px; height: 32px; padding: 0; display: inline-grid; place-items: center; }
    .zi-action-group .btn svg { width: 15px; height: 15px; }

    .zi-empty { text-align: center; padding: 3rem 1rem; color: #6e6b7b; }
    .zi-empty svg { width: 56px; height: 56px; opacity: .35; margin-bottom: .75rem; }

    /* Slide-over drawer */
    .zi-drawer { width: min(560px, 100vw); display: flex; flex-direction: column; }
    .zi-drawer .offcanvas-header { border-bottom: 1px solid rgba(34, 41, 47, .08); }
    .zi-drawer .offcanvas-body { overflow-y: auto; }
    .zi-drawer .offcanvas-footer { border-top: 1px solid rgba(34, 41, 47, .08); padding: 1rem 1.25rem; display: flex; gap: .5rem; justify-content: flex-end; background: #fafafa; }
    .zi-drawer .form-label { font-weight: 500; }
    .zi-drawer .select2-container--default .select2-selection--single { height: calc(2.4rem + 2px); padding: .3rem .4rem; }
</style>
@endpush

@section('content')
<section id="zipcodes-list">

    {{-- ── KPI strip ───────────────────────────────────────────── --}}
    @include('admin._partials.stat_cards', [
        'cards' => [
            ['label' => 'Total',    'value' => $stats['total'],    'icon' => 'mail',         'tone' => 'primary', 'stat_key' => 'total'],
            ['label' => 'Active',   'value' => $stats['active'],   'icon' => 'check-circle', 'tone' => 'success', 'stat_key' => 'active'],
            ['label' => 'Inactive', 'value' => $stats['inactive'], 'icon' => 'slash',        'tone' => 'danger',  'stat_key' => 'inactive'],
        ],
    ])

    <div class="card zi-card">

        @php
            $selectedCountry = $countries->firstWhere('id', request('country_id'));
            $selectedState   = $states->firstWhere('id', request('state_id'));
            $selectedCity    = $cities->firstWhere('id', request('city_id'));
            $selectedStatus  = request('status');
            $statusLabel     = $selectedStatus ? ucfirst(strtolower($selectedStatus)) : null;
            $statusPrefix    = $statusLabel ? $statusLabel . ' ' : '';

            if ($selectedCity) {
                $headerTitle = 'All ' . $statusPrefix . $selectedCity->name . ' Zip Codes';
            } elseif ($selectedState) {
                $headerTitle = 'All ' . $statusPrefix . $selectedState->name . ' Zip Codes';
            } elseif ($selectedCountry) {
                $headerTitle = 'All ' . $statusPrefix . $selectedCountry->name . ' Zip Codes';
            } elseif ($statusLabel) {
                $headerTitle = 'All ' . $statusLabel . ' Zip Codes';
            } else {
                $headerTitle = 'All Zip Codes';
            }
        @endphp

        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">{{ $headerTitle }}</h4>
            <button type="button" class="btn btn-primary" id="ziDrawerOpen">
                <i data-feather="plus" class="me-25"></i> Add Zip Code
            </button>
        </div>

        <div class="card-body py-1">
            <form id="zipcodesFilter" method="GET" class="row g-1 py-1">
                <div class="col-md-2">
                    <select id="zip_country_id" name="country_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All countries</option>
                        @foreach ($countries as $c)<option value="{{ $c->id }}" @selected(request('country_id') == $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="zip_state_id" name="state_id" data-ob-cascade-parent class="js-searchable form-select">
                        <option value="">All states</option>
                        @foreach ($states as $s)<option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" @selected(request('state_id') == $s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="zip_city_id" name="city_id" class="js-searchable form-select">
                        <option value="">All cities</option>
                        @foreach ($cities as $cityOpt)<option value="{{ $cityOpt->id }}" data-state-id="{{ $cityOpt->state_id }}" @selected(request('city_id') == $cityOpt->id)>{{ $cityOpt->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" placeholder="Search zip…" value="{{ request('search') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="ACTIVE"   @selected(request('status') === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.zipcodes.index') }}" class="ob-btn-clear w-100">
                        <i data-feather="x"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Table ─────────────────────────────────────────────── --}}
        <div class="table-responsive">
            <table class="table table-hover zi-table" id="ziTable">
                <thead>
                    <tr>
                        <th>@include('admin._partials.sort_th', ['label' => 'Zip Code', 'key' => 'code', 'default' => 'code'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'City', 'key' => 'city', 'default' => 'code'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'State', 'key' => 'state', 'default' => 'code'])</th>
                        <th>@include('admin._partials.sort_th', ['label' => 'Country', 'key' => 'country', 'default' => 'code'])</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="ziTbody">
                    @forelse ($zipcodes as $z)
                        <tr data-id="{{ $z->id }}">
                            <td class="fw-bolder zi-cell-code">{{ $z->code }}</td>
                            <td class="zi-cell-city">{{ $z->city?->name ?? '—' }}</td>
                            <td class="zi-cell-state">{{ $z->city?->state?->name ?? '—' }}</td>
                            <td class="zi-cell-country">{{ $z->city?->state?->country?->name ?? '—' }}</td>
                            <td>
                                <span class="zi-status zi-status--{{ $z->status === 'ACTIVE' ? 'active' : 'inactive' }}" data-status="{{ $z->status }}">{{ $z->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="zi-action-group">
                                    <a href="{{ route('admin.zipcodes.show', $z) }}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                                    <button type="button" class="btn btn-outline-primary zi-edit" title="Edit"
                                            data-id="{{ $z->id }}"
                                            data-country-id="{{ $z->city?->state?->country_id }}"
                                            data-state-id="{{ $z->city?->state_id }}"
                                            data-city-id="{{ $z->city_id }}"
                                            data-code="{{ $z->code }}"
                                            data-details="{{ $z->details }}"
                                            data-status="{{ $z->status }}"
                                            data-url="{{ route('admin.zipcodes.ajax.update', $z) }}">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.zipcodes.destroy', $z) }}" class="d-inline js-delete-form" data-confirm="Delete zip '{{ $z->code }}'?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="ziEmptyRow">
                            <td colspan="6">
                                <div class="zi-empty">
                                    <i data-feather="inbox"></i>
                                    <div class="fw-bolder text-body">No zip codes yet</div>
                                    <div class="small">Click <em>Add Zip Code</em> to create one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($zipcodes->hasPages())
            <div class="card-body border-top">{{ $zipcodes->links() }}</div>
        @endif
    </div>
</section>

{{-- ── Slide-over drawer (create / edit) ─────────────────────── --}}
<div class="offcanvas offcanvas-end zi-drawer" tabindex="-1" id="ziDrawer" aria-labelledby="ziDrawerTitle">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="ziDrawerTitle">Add Zip Code</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="ziDrawerForm" novalidate autocomplete="off">
            <input type="hidden" id="ziDrawerId" value="">

            <div class="mb-1">
                <label class="form-label" for="ziDrawerCountry">Country<span class="text-danger">*</span></label>
                <select id="ziDrawerCountry" class="form-select">
                    <option value="">Select country</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback d-block" id="ziDrawerCountryErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ziDrawerState">State<span class="text-danger">*</span></label>
                <select id="ziDrawerState" class="form-select" disabled>
                    <option value="">Select state</option>
                </select>
                <div class="invalid-feedback d-block" id="ziDrawerStateErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ziDrawerCity">City<span class="text-danger">*</span></label>
                <select id="ziDrawerCity" class="form-select" disabled>
                    <option value="">Select city</option>
                </select>
                <div class="invalid-feedback d-block" id="ziDrawerCityErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ziDrawerCode">Zip Code<span class="text-danger">*</span></label>
                <input type="text" id="ziDrawerCode" class="form-control" maxlength="20" placeholder="Enter zip code" required>
                <div class="invalid-feedback d-block" id="ziDrawerCodeErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ziDrawerDetails">Zip Details</label>
                <textarea id="ziDrawerDetails" class="form-control" rows="3" placeholder="Description goes here"></textarea>
                <div class="invalid-feedback d-block" id="ziDrawerDetailsErr"></div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="ziDrawerStatus">Status<span class="text-danger">*</span></label>
                <select id="ziDrawerStatus" class="form-select">
                    <option value="ACTIVE">Active</option>
                    <option value="INACTIVE">Inactive</option>
                </select>
                <div class="invalid-feedback d-block" id="ziDrawerStatusErr"></div>
            </div>
        </form>
    </div>
    <div class="offcanvas-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="button" class="btn btn-primary" id="ziDrawerSubmit">
            <span class="zi-drawer-label">Save</span>
            <span class="spinner-border spinner-border-sm d-none ms-25" role="status"></span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    obPreloadedCascade({ parent: '#zip_country_id', child: '#zip_state_id', parentAttr: 'data-country-id' });
    obPreloadedCascade({ parent: '#zip_state_id',   child: '#zip_city_id',  parentAttr: 'data-state-id' });
    obAutoFilter('#zipcodesFilter');

    const toast = (icon, title) => Swal.fire({
        toast: true, position: 'top-end', icon, title,
        showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const ROUTES = {
        store:  @json(route('admin.zipcodes.ajax.store')),
        states: @json(route('admin.ajax.states')),
        cities: @json(route('admin.ajax.cities')),
    };

    const $tbody = $('#ziTbody');

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

    function removeEmptyPlaceholder() { $('#ziEmptyRow').remove(); }

    function buildRowHtml(zi) {
        const statusClass = zi.status === 'ACTIVE' ? 'zi-status--active' : 'zi-status--inactive';
        return `
            <tr class="zi-row-new" data-id="${zi.id}">
                <td class="fw-bolder zi-cell-code">${escapeHtml(zi.code)}</td>
                <td class="zi-cell-city">${escapeHtml(zi.city_name)}</td>
                <td class="zi-cell-state">${escapeHtml(zi.state_name)}</td>
                <td class="zi-cell-country">${escapeHtml(zi.country_name)}</td>
                <td><span class="zi-status ${statusClass}" data-status="${zi.status}">${zi.status}</span></td>
                <td class="text-end">
                    <div class="zi-action-group">
                        <a href="${zi.show_url}" class="btn btn-outline-success" title="View"><i data-feather="eye"></i></a>
                        <button type="button" class="btn btn-outline-primary zi-edit" title="Edit"
                                data-id="${zi.id}"
                                data-country-id="${zi.country_id ?? ''}"
                                data-state-id="${zi.state_id ?? ''}"
                                data-city-id="${zi.city_id}"
                                data-code="${escapeHtml(zi.code)}"
                                data-details="${escapeHtml(zi.details)}"
                                data-status="${zi.status}"
                                data-url="${zi.update_url}"><i data-feather="edit-2"></i></button>
                        <form method="POST" action="${zi.destroy_url}" class="d-inline js-delete-form" data-confirm="Delete zip '${escapeHtml(zi.code)}'?">
                            <input type="hidden" name="_token" value="${CSRF}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-outline-danger" title="Delete"><i data-feather="trash-2"></i></button>
                        </form>
                    </div>
                </td>
            </tr>`;
    }

    function insertRow(zi) {
        removeEmptyPlaceholder();
        $tbody.prepend(buildRowHtml(zi));
        if (window.feather) window.feather.replace();
    }

    function updateRow(zi) {
        const $row = $(`#ziTbody tr[data-id="${zi.id}"]`);
        if (!$row.length) return;
        $row.find('.zi-cell-code').text(zi.code);
        $row.find('.zi-cell-city').text(zi.city_name);
        $row.find('.zi-cell-state').text(zi.state_name);
        $row.find('.zi-cell-country').text(zi.country_name);

        const $status = $row.find('.zi-status');
        $status.text(zi.status)
            .removeClass('zi-status--active zi-status--inactive')
            .addClass(zi.status === 'ACTIVE' ? 'zi-status--active' : 'zi-status--inactive')
            .attr('data-status', zi.status);

        const $edit = $row.find('.zi-edit');
        $edit.attr('data-country-id', zi.country_id ?? '')
             .attr('data-state-id', zi.state_id ?? '')
             .attr('data-city-id', zi.city_id)
             .attr('data-code', zi.code)
             .attr('data-details', zi.details)
             .attr('data-status', zi.status)
             .attr('data-url', zi.update_url);

        $row.find('.js-delete-form').attr('data-confirm', `Delete zip '${zi.code}'?`);

        $row.removeClass('zi-row-new'); void $row[0].offsetWidth; $row.addClass('zi-row-new');
    }

    // ── Drawer setup ──────────────────────────────────────────
    const drawerEl = document.getElementById('ziDrawer');
    const drawer   = new bootstrap.Offcanvas(drawerEl);
    let select2Ready = false;

    function ensureSelect2() {
        if (select2Ready) return;
        window.obSearchable('#ziDrawerCountry', { dropdownParent: $(drawerEl) });
        window.obSearchable('#ziDrawerState',   { dropdownParent: $(drawerEl) });
        window.obSearchable('#ziDrawerCity',    { dropdownParent: $(drawerEl) });
        select2Ready = true;
    }

    const FIELDS = {
        country_id: { input: '#ziDrawerCountry', err: '#ziDrawerCountryErr' },
        state_id:   { input: '#ziDrawerState',   err: '#ziDrawerStateErr' },
        city_id:    { input: '#ziDrawerCity',    err: '#ziDrawerCityErr' },
        code:       { input: '#ziDrawerCode',    err: '#ziDrawerCodeErr' },
        details:    { input: '#ziDrawerDetails', err: '#ziDrawerDetailsErr' },
        status:     { input: '#ziDrawerStatus',  err: '#ziDrawerStatusErr' },
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

    function populateSelect($select, items, placeholder, preselect) {
        $select.empty().append(new Option(placeholder, ''));
        items.forEach((it) => $select.append(new Option(it.name, it.id)));
        if (preselect) $select.val(String(preselect));
        $select.trigger('change.select2');
    }

    function loadStates(countryId, preselect) {
        const $state = $('#ziDrawerState');
        if (!countryId) {
            populateSelect($state, [], 'Select state', null);
            $state.prop('disabled', true);
            return $.Deferred().resolve().promise();
        }
        $state.prop('disabled', true);
        populateSelect($state, [], 'Loading…', null);
        return $.getJSON(ROUTES.states, { country_id: countryId })
            .done((items) => {
                populateSelect($state, items, 'Select state', preselect);
                $state.prop('disabled', false);
            })
            .fail(() => populateSelect($state, [], 'Failed to load', null));
    }

    function loadCities(stateId, preselect) {
        const $city = $('#ziDrawerCity');
        if (!stateId) {
            populateSelect($city, [], 'Select city', null);
            $city.prop('disabled', true);
            return $.Deferred().resolve().promise();
        }
        $city.prop('disabled', true);
        populateSelect($city, [], 'Loading…', null);
        return $.getJSON(ROUTES.cities, { state_id: stateId })
            .done((items) => {
                populateSelect($city, items, 'Select city', preselect);
                $city.prop('disabled', false);
            })
            .fail(() => populateSelect($city, [], 'Failed to load', null));
    }

    $('#ziDrawerCountry').on('change', function () {
        loadStates($(this).val(), null);
        populateSelect($('#ziDrawerCity'), [], 'Select city', null);
        $('#ziDrawerCity').prop('disabled', true);
    });

    $('#ziDrawerState').on('change', function () {
        loadCities($(this).val(), null);
    });

    function openDrawerCreate() {
        ensureSelect2();
        $('#ziDrawerTitle').text('Add Zip Code');
        $('.zi-drawer-label', drawerEl).text('Save');
        $('#ziDrawerForm').removeAttr('data-url');
        $('#ziDrawerId').val('');

        const preCountry = $('#zip_country_id').val() || '';
        const preState   = $('#zip_state_id').val()   || '';
        const preCity    = $('#zip_city_id').val()    || '';

        $('#ziDrawerCountry').val(preCountry).trigger('change.select2');
        $('#ziDrawerCode').val('');
        $('#ziDrawerDetails').val('');
        $('#ziDrawerStatus').val('ACTIVE');
        clearErrors();

        // Chain: load states (with preState), then load cities (with preCity)
        loadStates(preCountry, preState).always(() => {
            loadCities(preState, preCity);
        });

        drawer.show();
    }

    function openDrawerEdit(data) {
        ensureSelect2();
        $('#ziDrawerTitle').text('Edit Zip Code');
        $('.zi-drawer-label', drawerEl).text('Update');
        $('#ziDrawerForm').attr('data-url', data.url);
        $('#ziDrawerId').val(data.id);

        $('#ziDrawerCountry').val(String(data.countryId || '')).trigger('change.select2');
        $('#ziDrawerCode').val(data.code || '');
        $('#ziDrawerDetails').val(data.details || '');
        $('#ziDrawerStatus').val(data.status || 'ACTIVE');
        clearErrors();

        loadStates(data.countryId, data.stateId).always(() => {
            loadCities(data.stateId, data.cityId);
        });

        drawer.show();
    }

    drawerEl.addEventListener('shown.bs.offcanvas', () => {
        setTimeout(() => $('#ziDrawerCode').trigger('focus'), 50);
    });

    $('#ziDrawerOpen').on('click', openDrawerCreate);

    $(document).on('click', '.zi-edit', function () {
        openDrawerEdit({
            id:        $(this).data('id'),
            countryId: $(this).data('country-id'),
            stateId:   $(this).data('state-id'),
            cityId:    $(this).data('city-id'),
            code:      $(this).attr('data-code') || '',
            details:   $(this).attr('data-details') || '',
            status:    $(this).data('status'),
            url:       $(this).data('url'),
        });
    });

    function validateLocal(payload) {
        clearErrors();
        let firstInvalid = null;
        if (!payload.country_id) { setFieldError('country_id', 'Country is required.'); firstInvalid = firstInvalid || FIELDS.country_id.input; }
        if (!payload.state_id) { setFieldError('state_id', 'State is required.'); firstInvalid = firstInvalid || FIELDS.state_id.input; }
        if (!payload.city_id) { setFieldError('city_id', 'City is required.'); firstInvalid = firstInvalid || FIELDS.city_id.input; }
        if (!payload.code) { setFieldError('code', 'Zip code is required.'); firstInvalid = firstInvalid || FIELDS.code.input; }
        else if (payload.code.length > 20) { setFieldError('code', 'Zip code may not be longer than 20 characters.'); firstInvalid = firstInvalid || FIELDS.code.input; }
        if (!payload.status) { setFieldError('status', 'Status is required.'); firstInvalid = firstInvalid || FIELDS.status.input; }
        if (firstInvalid) $(firstInvalid).trigger('focus');
        return !firstInvalid;
    }

    $('#ziDrawerForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#ziDrawerSubmit').trigger('click'); }
    });

    $('#ziDrawerSubmit').on('click', function () {
        const id = $('#ziDrawerId').val();
        const payload = {
            country_id: $('#ziDrawerCountry').val(),
            state_id:   $('#ziDrawerState').val(),
            city_id:    $('#ziDrawerCity').val(),
            code:       $('#ziDrawerCode').val().trim(),
            details:    $('#ziDrawerDetails').val(),
            status:     $('#ziDrawerStatus').val(),
        };

        if (!validateLocal(payload)) return;

        const isEdit = !!id;
        const url    = isEdit ? $('#ziDrawerForm').attr('data-url') : ROUTES.store;
        const data   = isEdit
            ? Object.assign({ _token: CSRF, _method: 'PUT' }, payload)
            : Object.assign({ _token: CSRF }, payload);

        const $btn = $('#ziDrawerSubmit').prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');

        $.ajax({ url, method: 'POST', data, dataType: 'json' })
            .done((res) => {
                if (!res || !res.ok) return;
                if (isEdit) {
                    const $prevStatus = $(`#ziTbody tr[data-id="${res.zipcode.id}"] .zi-status`);
                    const prev = $prevStatus.data('status');
                    if (prev && prev !== res.zipcode.status) {
                        bumpStat(prev === 'ACTIVE' ? 'active' : 'inactive', -1);
                        bumpStat(res.zipcode.status === 'ACTIVE' ? 'active' : 'inactive', 1);
                    }
                    updateRow(res.zipcode);
                } else {
                    insertRow(res.zipcode);
                    incrementStatsFor(res.zipcode.status);
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
            const $btn = $(`#ziTbody tr[data-id="{{ (int) $editId }}"] .zi-edit`);
            if ($btn.length) $btn.trigger('click');
        })();
    @endif
})();
</script>
@endpush
@endsection
