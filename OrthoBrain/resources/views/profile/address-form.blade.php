@extends('layouts.app')

@php
    $mode     = $mode ?? 'create';            // 'create' | 'edit' | 'view'
    $isView   = $mode === 'view';
    $isEdit   = $mode === 'edit';
    $isCreate = $mode === 'create';

    $heading = $isView  ? 'View Address'
             : ($isEdit ? 'Edit Address'
             :            'Add ' . ucfirst($type) . ' Address');

    $practice = $activePractice?->name ?? $doctor?->practice?->name ?? '';
    $docName  = trim(($doctor?->first_name ?? '') . ' ' . ($doctor?->last_name ?? ''));

    // Pre-fill values when editing / viewing an existing address.
    $street1   = old('street_address_1', $address?->street_address_1 ?? '');
    $street2   = old('street_address_2', $address?->street_address_2 ?? '');
    $selZipId  = old('zip_id',           $address?->zip_id ?? '');
    $cityName  = $address?->city?->name ?? '';
    $stateName = $address?->state?->name ?? '';
    $countryName = $address?->country?->name ?? '';
@endphp

@section('title', $heading)

@push('styles')
<style>
    /* ── Address form — login-style premium polish ── */
    .addr-form-shell {
        padding: 1rem 0 2rem;
    }
    .addr-form-card {
        position: relative;
        max-width: 940px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.65) !important;
        border-radius: 20px !important;
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.03),
            0 12px 32px -18px rgba(15, 23, 42, 0.10) !important;
        overflow: hidden;
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
        color: #0F172A;
    }
    .addr-form-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent 0%, #60A5FA 30%, #2563EB 50%, #60A5FA 70%, transparent 100%);
        opacity: .55;
        z-index: 1;
    }
    .addr-form-card > .card-body {
        padding: 36px 40px !important;
    }

    /* Title */
    .addr-form-card .addr-form-title {
        font-size: 1.5rem;
        font-weight: 500;
        color: #0F172A;
        letter-spacing: -0.015em;
        line-height: 1.3;
        margin-bottom: 1.75rem !important;
    }

    /* Validation error alert */
    .addr-form-card .alert.alert-danger {
        background: rgba(220, 38, 38, 0.06);
        border: 1px solid rgba(220, 38, 38, 0.18);
        color: #991B1B;
        border-radius: 12px;
        padding: .85rem 1rem;
        margin-bottom: 1.5rem;
    }
    .addr-form-card .alert.alert-danger p { color: #991B1B; }

    /* Row spacing */
    .addr-form-card .row > [class*="col-"].mb-1 {
        margin-bottom: 1.25rem !important;
    }

    /* Labels */
    .addr-form-card .form-label {
        font-size: .78rem;
        font-weight: 500;
        color: #64748B;
        letter-spacing: .01em;
        margin-bottom: .45rem;
    }
    .addr-form-card .form-label .text-danger {
        color: #DC2626 !important;
        margin-left: 2px;
    }

    /* Inputs & selects */
    .addr-form-card .form-control,
    .addr-form-card .form-select {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .65rem .85rem;
        font-size: .92rem;
        color: #0F172A;
        background-color: #ffffff;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }
    .addr-form-card .form-control::placeholder {
        color: #94A3B8;
    }
    .addr-form-card .form-control:hover:not(:disabled):not([readonly]),
    .addr-form-card .form-select:hover:not(:disabled) {
        border-color: #cbd5e1;
    }
    .addr-form-card .form-control:focus,
    .addr-form-card .form-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        outline: none;
        background-color: #ffffff;
    }
    .addr-form-card .form-control.is-invalid,
    .addr-form-card .form-select.is-invalid {
        border-color: #DC2626;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);
    }
    .addr-form-card .form-control[readonly],
    .addr-form-card .form-control.bg-light-secondary,
    .addr-form-card .form-select.bg-light-secondary,
    .addr-form-card .form-select:disabled {
        background-color: #f8fafc !important;
        color: #64748B !important;
        border-color: #eef2f7;
        cursor: not-allowed;
    }
    .addr-form-card .form-control[readonly]:focus,
    .addr-form-card .form-control.bg-light-secondary:focus,
    .addr-form-card .form-select.bg-light-secondary:focus {
        border-color: #eef2f7;
        box-shadow: none;
    }

    /* Input-group icon (location/mail) */
    .addr-form-card .input-group {
        position: relative;
    }
    .addr-form-card .input-group .input-group-text {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-right: 0;
        color: #94A3B8;
        border-radius: 10px 0 0 10px;
        padding: 0 .85rem;
        transition: border-color .2s ease, color .2s ease;
    }
    .addr-form-card .input-group .input-group-text svg {
        width: 16px;
        height: 16px;
    }
    .addr-form-card .input-group .form-control {
        border-radius: 0 10px 10px 0 !important;
        border-left: 0;
    }
    .addr-form-card .input-group:focus-within .input-group-text {
        border-color: #3B82F6;
        color: #3B82F6;
    }
    .addr-form-card .input-group:focus-within .form-control {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
    }
    .addr-form-card .input-group:hover:not(:focus-within) .input-group-text {
        border-color: #cbd5e1;
    }

    /* Validation error text under fields */
    .addr-form-card small.text-danger {
        display: block;
        color: #DC2626 !important;
        font-size: .78rem;
        margin-top: .35rem;
    }

    /* Action row */
    .addr-form-card .addr-form-actions {
        gap: .75rem;
        border-top: 1px solid #f1f5f9;
        padding-top: 1.5rem;
        margin-top: 2rem !important;
    }

    /* Mobile */
    @media (max-width: 575.98px) {
        .addr-form-card { border-radius: 16px !important; }
        .addr-form-card > .card-body { padding: 24px 20px !important; }
        .addr-form-card .addr-form-title { font-size: 1.3rem; margin-bottom: 1.25rem !important; }
        .addr-form-card .addr-form-actions { flex-direction: column-reverse; align-items: stretch; }
        .addr-form-card .addr-form-actions .btn { width: 100%; margin-right: 0 !important; }
    }

    /* ── Dark theme: align .addr-form-card with Vuexy dark-layout ── */
    .dark-layout .addr-form-card {
        background: #283046 !important;
        border-color: rgba(255, 255, 255, 0.06) !important;
        box-shadow:
            0 1px 2px rgba(0, 0, 0, 0.20),
            0 12px 32px -18px rgba(0, 0, 0, 0.55) !important;
        color: #b4b7bd;
    }
    .dark-layout .addr-form-card .addr-form-title { color: #d0d2d6; }
    .dark-layout .addr-form-card .alert.alert-danger {
        background: rgba(220, 38, 38, 0.10);
        border-color: rgba(220, 38, 38, 0.30);
        color: #FCA5A5;
    }
    .dark-layout .addr-form-card .alert.alert-danger p { color: #FCA5A5; }
    .dark-layout .addr-form-card .form-label { color: rgba(255, 255, 255, 0.55); }
    .dark-layout .addr-form-card .form-control,
    .dark-layout .addr-form-card .form-select {
        background-color: #283046;
        border-color: #404656;
        color: #d0d2d6;
    }
    .dark-layout .addr-form-card .form-control::placeholder { color: rgba(255, 255, 255, 0.35); }
    .dark-layout .addr-form-card .form-control:hover:not(:disabled):not([readonly]),
    .dark-layout .addr-form-card .form-select:hover:not(:disabled) { border-color: #4d5670; }
    .dark-layout .addr-form-card .form-control:focus,
    .dark-layout .addr-form-card .form-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18);
        background-color: #283046;
    }
    .dark-layout .addr-form-card .form-control[readonly],
    .dark-layout .addr-form-card .form-control.bg-light-secondary,
    .dark-layout .addr-form-card .form-select.bg-light-secondary,
    .dark-layout .addr-form-card .form-select:disabled {
        background-color: #1f2638 !important;
        color: rgba(255, 255, 255, 0.55) !important;
        border-color: #2e3548;
    }
    .dark-layout .addr-form-card .input-group .input-group-text {
        background-color: #283046;
        border-color: #404656;
        color: rgba(255, 255, 255, 0.45);
    }
    .dark-layout .addr-form-card .input-group:hover:not(:focus-within) .input-group-text { border-color: #4d5670; }
    .dark-layout .addr-form-card .input-group:focus-within .input-group-text {
        border-color: #3B82F6;
        color: #60A5FA;
    }
    .dark-layout .addr-form-card .addr-form-actions { border-top-color: #3b4253; }

    /* Smooth theme cross-fade */
    .addr-form-card,
    .addr-form-card .form-control,
    .addr-form-card .form-select,
    .addr-form-card .input-group-text {
        transition:
            background-color .25s ease,
            border-color .25s ease,
            color .25s ease,
            box-shadow .25s ease;
    }
</style>
@endpush

@section('content')
<div class="addr-form-shell">
<div class="card addr-form-card">
    <div class="card-body">
        <h4 class="card-title addr-form-title mb-2">{{ $heading }}</h4>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="alert-body">
                    @foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
                </div>
            </div>
        @endif

        <form id="addressForm"
              method="POST"
              action="{{ $isEdit ? route('doctor.profile.address.update', $address) : route('doctor.profile.address.store') }}"
              class="ob-form-validate"
              novalidate>
            @csrf
            @if($isEdit) @method('PUT') @endif

            @if($isCreate)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <input type="hidden" name="city_id"    id="hid-city"    value="{{ old('city_id',    $address?->city_id ?? '') }}">
            <input type="hidden" name="state_id"   id="hid-state"   value="{{ old('state_id',   $address?->state_id ?? '') }}">
            <input type="hidden" name="country_id" id="hid-country" value="{{ old('country_id', $address?->country_id ?? '') }}">

            <div class="row">
                <div class="col-md-6 mb-1">
                    <label for="in-practice-name" class="form-label">Practice Name<span class="text-danger">*</span></label>
                    <input id="in-practice-name" type="text" class="form-control bg-light-secondary" value="{{ $practice }}" readonly>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="in-doctor-name" class="form-label">Doctor Name<span class="text-danger">*</span></label>
                    <input id="in-doctor-name" type="text" class="form-control bg-light-secondary" value="{{ $docName }}" readonly>
                </div>

                @if($type === 'billing')
                    <div class="col-md-6 mb-1">
                        <label for="in-billing-email" class="form-label">Billing Email Address<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i data-feather="mail"></i></span>
                            <input id="in-billing-email" name="billing_email" type="email"
                                   class="form-control {{ $isView ? 'bg-light-secondary' : '' }}"
                                   placeholder="Billing email address"
                                   value="{{ old('billing_email', $address?->billing_email ?? '') }}"
                                   {{ $isView ? 'readonly' : '' }}>
                        </div>
                        <small id="err-billing-email" class="text-danger d-none"></small>
                    </div>
                @endif

                <div class="col-md-6 mb-1">
                    <label for="in-address1" class="form-label">Street Address<span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i data-feather="map-pin"></i></span>
                        <input id="in-address1" name="street_address_1" type="text"
                               class="form-control {{ $isView ? 'bg-light-secondary' : '' }}"
                               placeholder="Street address 1"
                               value="{{ $street1 }}"
                               {{ $isView ? 'readonly' : '' }}>
                    </div>
                    <small id="err-address1" class="text-danger d-none"></small>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="in-address2" class="form-label">Street Address 2</label>
                    <div class="input-group">
                        <span class="input-group-text"><i data-feather="map-pin"></i></span>
                        <input id="in-address2" name="street_address_2" type="text"
                               class="form-control {{ $isView ? 'bg-light-secondary' : '' }}"
                               placeholder="Street address 2"
                               value="{{ $street2 }}"
                               {{ $isView ? 'readonly' : '' }}>
                    </div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="in-zip" class="form-label">Zip<span class="text-danger">*</span></label>
                    <select id="in-zip" name="zip_id" class="form-select {{ $isView ? 'bg-light-secondary' : '' }}" {{ $isView ? 'disabled' : '' }}>
                        <option value="" disabled {{ $selZipId ? '' : 'selected' }}>Select zip code</option>
                        @foreach($zipcodes as $z)
                            <option value="{{ $z->id }}"
                                    data-code="{{ $z->code }}"
                                    data-city-id="{{ $z->city?->id }}"
                                    data-city="{{ $z->city?->name }}"
                                    data-state-id="{{ $z->city?->state?->id }}"
                                    data-state="{{ $z->city?->state?->name }}"
                                    data-country-id="{{ $z->city?->state?->country?->id }}"
                                    data-country="{{ $z->city?->state?->country?->name }}"
                                    {{ (string) $selZipId === (string) $z->id ? 'selected' : '' }}>
                                {{ $z->code }} — {{ $z->city?->name }}, {{ $z->city?->state?->state_code }}
                            </option>
                        @endforeach
                    </select>
                    @if($isView)
                        {{-- Disabled selects don't submit; keep a hidden copy for any downstream logic --}}
                        <input type="hidden" name="zip_id" value="{{ $selZipId }}">
                    @endif
                    <small id="err-zip" class="text-danger d-none"></small>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="in-city" class="form-label">City<span class="text-danger">*</span></label>
                    <input id="in-city" type="text" class="form-control bg-light-secondary"
                           placeholder="Auto-filled from zip"
                           value="{{ $cityName }}" readonly>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="in-state" class="form-label">State/Province<span class="text-danger">*</span></label>
                    <input id="in-state" type="text" class="form-control bg-light-secondary"
                           placeholder="Auto-filled from zip"
                           value="{{ $stateName }}" readonly>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="in-country" class="form-label">Country<span class="text-danger">*</span></label>
                    <input id="in-country" type="text" class="form-control bg-light-secondary"
                           placeholder="Auto-filled from zip"
                           value="{{ $countryName }}" readonly>
                </div>
            </div>

            <div class="d-flex addr-form-actions mt-2">
                @if($isView)
                    <a href="{{ route('doctor.profile.index', ['tab' => $type]) }}" class="btn btn-outline-secondary">Back</a>
                @elseif($isEdit)
                    <button type="button" onclick="validateAddressForm()" class="btn btn-primary me-1">Update</button>
                    <a href="{{ route('doctor.profile.index', ['tab' => $type]) }}" class="btn btn-outline-secondary">Cancel</a>
                @else
                    <button type="button" onclick="validateAddressForm()" class="btn btn-primary me-1">Save</button>
                    <a href="{{ route('doctor.profile.index', ['tab' => $type]) }}" class="btn btn-outline-secondary">Cancel</a>
                @endif
            </div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    const ZIP_LOOKUP_URL = @json(route('doctor.profile.address.zip-lookup'));
    const FORM_MODE = @json($mode);

    function applyZipData(d) {
        document.getElementById('in-city').value     = d.city    || '';
        document.getElementById('in-state').value    = d.state   || '';
        document.getElementById('in-country').value  = d.country || '';
        document.getElementById('hid-city').value    = d.city_id    || '';
        document.getElementById('hid-state').value   = d.state_id   || '';
        document.getElementById('hid-country').value = d.country_id || '';
    }
    function clearZipData() {
        ['in-city','in-state','in-country','hid-city','hid-state','hid-country']
            .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    }

    async function onZipChange() {
        const sel = document.getElementById('in-zip');
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { clearZipData(); return; }

        if (opt.dataset.city) {
            applyZipData({
                city: opt.dataset.city,   city_id:    opt.dataset.cityId,
                state: opt.dataset.state, state_id:   opt.dataset.stateId,
                country: opt.dataset.country, country_id: opt.dataset.countryId,
            });
            return;
        }

        try {
            const res = await fetch(ZIP_LOOKUP_URL + '?zip_id=' + encodeURIComponent(opt.value), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('lookup failed');
            const data = await res.json();
            if (data.ok) applyZipData(data);
        } catch (e) {
            console.error('zip lookup error', e);
        }
    }

    // ── Live validation (create/edit only) ──
    const addrEmailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const addrValidators = {
        'address1': v => {
            if (!v) return 'Street address is required.';
            if (v.length < 5) return 'Please enter a complete street address (min 5 characters).';
            return '';
        },
        'zip': v => !v ? 'Please select a zip code.' : '',
        'billing-email': v => {
            if (!v) return 'Billing email is required.';
            if (!addrEmailRe.test(v)) return 'Enter a valid email address.';
            return '';
        },
    };
    const addrTouched = new Set();

    function _addrSetError(id, msg) {
        const input = document.getElementById('in-' + id);
        const err   = document.getElementById('err-' + id);
        if (msg) {
            if (err)   { err.textContent = msg; err.classList.remove('d-none'); }
            if (input) input.classList.add('is-invalid');
        } else {
            if (err)   { err.textContent = ''; err.classList.add('d-none'); }
            if (input) input.classList.remove('is-invalid');
        }
    }

    function addrValidateField(id) {
        const fn = addrValidators[id];
        const el = document.getElementById('in-' + id);
        if (!fn || !el) return true;
        const msg = fn((el.value || '').trim());
        _addrSetError(id, msg);
        return !msg;
    }

    function validateAddressForm() {
        let ok = true;
        Object.keys(addrValidators).forEach(id => {
            addrTouched.add(id);
            if (!addrValidateField(id)) ok = false;
        });
        if (ok && document.getElementById('in-zip').value && !document.getElementById('hid-city').value) {
            _addrSetError('zip', 'Zip lookup failed. Please reselect the zip code.');
            ok = false;
        }
        if (ok) document.getElementById('addressForm').submit();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (FORM_MODE === 'view') return;

        // Text inputs: validate on blur, re-validate on input after first blur
        ['address1', 'billing-email'].forEach(id => {
            const el = document.getElementById('in-' + id);
            if (!el) return;
            el.addEventListener('blur', () => { addrTouched.add(id); addrValidateField(id); });
            el.addEventListener('input', () => { if (addrTouched.has(id)) addrValidateField(id); });
        });

        // Zip has extra auto-fill behavior
        const zipEl = document.getElementById('in-zip');
        if (zipEl) {
            zipEl.addEventListener('change', () => {
                addrTouched.add('zip');
                addrValidateField('zip');
                onZipChange();
            });
        }
    });
</script>
@endpush
