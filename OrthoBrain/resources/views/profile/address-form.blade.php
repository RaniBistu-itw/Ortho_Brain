@extends('layouts.app')

@php
    $mode     = $mode ?? 'create';            // 'create' | 'edit' | 'view'
    $isView   = $mode === 'view';
    $isEdit   = $mode === 'edit';
    $isCreate = $mode === 'create';

    $heading = $isView  ? 'View Address'
             : ($isEdit ? 'Edit Address'
             :            'Add ' . ucfirst($type) . ' Address');

    $practice = $doctor?->practice_name ?? '';
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

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-2">{{ $heading }}</h4>

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

            <div class="d-flex mt-2">
                @if($isView)
                    <a href="{{ route('doctor.profile.index', ['tab' => $type]) }}" class="btn btn-danger">Back</a>
                @elseif($isEdit)
                    <button type="button" onclick="validateAddressForm()" class="btn btn-success me-1">Update</button>
                    <a href="{{ route('doctor.profile.index', ['tab' => $type]) }}" class="btn btn-danger">Cancel</a>
                @else
                    <button type="button" onclick="validateAddressForm()" class="btn btn-success me-1">Save</button>
                    <a href="{{ route('doctor.profile.index', ['tab' => $type]) }}" class="btn btn-danger">Cancel</a>
                @endif
            </div>
        </form>
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
