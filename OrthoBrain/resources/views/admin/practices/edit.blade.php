@extends('layouts.admin')
@section('title', 'Edit Practice · ' . $practice->name)
@section('page_title', 'Edit ' . $practice->name)

@push('styles')
<style>
    #practice-edit {
        --ob-primary: #5bc0de;
        --ob-primary-softer: rgba(91, 192, 222, 0.07);
        --ob-muted: #6e6b7b;
        --ob-border: #ebe9f1;
        --ob-surface: #ffffff;
        --ob-surface-alt: #f8f8fb;
        --ob-text: #1f1f1f;
    }
    #practice-edit .ob-card {
        background: var(--ob-surface);
        border: 1px solid var(--ob-border);
        border-radius: 0.85rem;
        box-shadow: 0 1px 2px rgba(24, 28, 40, 0.04);
        overflow: hidden;
    }
    #practice-edit .ob-card + .ob-card { margin-top: 1rem; }
    #practice-edit .ob-card-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--ob-border);
        gap: 0.75rem; flex-wrap: wrap;
    }
    #practice-edit .ob-card-title { font-weight: 700; color: #111; margin: 0; font-size: 1rem; }
    #practice-edit .ob-card-body { padding: 1.25rem; }

    #practice-edit .ob-back {
        display: inline-flex; align-items: center; gap: 0.35rem;
        color: var(--ob-muted);
        font-weight: 600;
        text-decoration: none;
        margin-bottom: 0.75rem;
    }
    #practice-edit .ob-back:hover { color: var(--ob-primary); }
    #practice-edit .ob-back svg { width: 14px; height: 14px; }

    #practice-edit .ob-location-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .25rem; }
    #practice-edit .ob-location-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .35rem .6rem;
        border: 1px solid var(--ob-border);
        border-radius: 999px;
        background: var(--ob-surface-alt);
        font-size: .8rem;
        color: var(--ob-text);
    }
    #practice-edit .ob-location-chip[data-empty="1"] {
        color: var(--ob-muted);
        font-style: italic;
        border-style: dashed;
    }
    #practice-edit .ob-location-chip-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--ob-muted);
    }

    #practice-edit .ob-form-actions {
        display: flex; gap: .5rem; justify-content: flex-end;
        padding: 1rem 1.25rem;
        background: var(--ob-surface-alt);
        border-top: 1px solid var(--ob-border);
    }
</style>
@endpush

@section('content')
<section id="practice-edit">
    <a href="{{ route('admin.practices.show', $practice) }}" class="ob-back">
        <i data-feather="arrow-left"></i> Back to Practice
    </a>

    <form method="POST" action="{{ route('admin.practices.update', $practice) }}" novalidate>
        @csrf
        @method('PUT')

        {{-- Identity & contact --}}
        <div class="ob-card">
            <div class="ob-card-head">
                <h5 class="ob-card-title">Practice details</h5>
            </div>
            <div class="ob-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Practice Name<span class="text-danger">*</span></label>
                        <input id="name" name="name" type="text"
                               value="{{ old('name', $practice->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="website" class="form-label">Website</label>
                        <input id="website" name="website" type="text"
                               value="{{ old('website', $practice->website) }}"
                               placeholder="example.com"
                               class="form-control @error('website') is-invalid @enderror">
                        @error('website')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">Phone<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select id="phone_country_code" name="phone_country_code" class="form-select" style="max-width:110px;">
                                @php $currentCode = old('phone_country_code', $practice->phone_country_code ?? '+1'); @endphp
                                @forelse ($phoneCodes as $code)
                                    <option value="{{ $code }}" @selected($currentCode === $code)>{{ $code }}</option>
                                @empty
                                    <option value="+1" @selected($currentCode === '+1')>+1</option>
                                @endforelse
                            </select>
                            <input id="phone_number" name="phone_number" type="tel"
                                   value="{{ old('phone_number', $practice->phone_number) }}"
                                   inputmode="numeric" maxlength="10"
                                   placeholder="10 digits"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   required>
                        </div>
                        @error('phone_country_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('phone_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="ob-card">
            <div class="ob-card-head">
                <h5 class="ob-card-title">Address</h5>
            </div>
            <div class="ob-card-body">
                <input type="hidden" name="city_id"    id="city_id"    value="{{ old('city_id', $practice->city_id) }}">
                <input type="hidden" name="state_id"   id="state_id"   value="{{ old('state_id', $practice->state_id) }}">
                <input type="hidden" name="country_id" id="country_id" value="{{ old('country_id', $practice->country_id) }}">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="street_address_1" class="form-label">Street Address<span class="text-danger">*</span></label>
                        <input id="street_address_1" name="street_address_1" type="text"
                               value="{{ old('street_address_1', $practice->street_address_1) }}"
                               placeholder="123 Main Street"
                               class="form-control @error('street_address_1') is-invalid @enderror"
                               required>
                        @error('street_address_1')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="street_address_2" class="form-label">Suite / Apt <span class="text-muted small">(optional)</span></label>
                        <input id="street_address_2" name="street_address_2" type="text"
                               value="{{ old('street_address_2', $practice->street_address_2) }}"
                               placeholder="Suite 400"
                               class="form-control">
                    </div>

                    <div class="col-md-5">
                        <label for="zip_id" class="form-label">Zip / Postal Code<span class="text-danger">*</span></label>
                        @php $currentZip = old('zip_id', $practice->zip_id); @endphp
                        <select id="zip_id" name="zip_id" required
                                class="form-select js-searchable @error('zip_id') is-invalid @enderror"
                                data-placeholder="Select a zip code">
                            <option value="">Select a zip code</option>
                            @foreach ($zipcodes as $z)
                                <option value="{{ $z->id }}"
                                        @selected($currentZip == $z->id)
                                        data-city-id="{{ $z->city?->id }}"
                                        data-city="{{ $z->city?->name }}"
                                        data-state-id="{{ $z->city?->state?->id }}"
                                        data-state="{{ $z->city?->state?->name }}"
                                        data-country-id="{{ $z->city?->state?->country?->id }}"
                                        data-country="{{ $z->city?->state?->country?->name }}">
                                    {{ $z->code }} — {{ $z->city?->name }}, {{ $z->city?->state?->state_code }}
                                </option>
                            @endforeach
                        </select>
                        @error('zip_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-7">
                        <label class="form-label">Auto-filled location</label>
                        <div class="ob-location-chips">
                            <span class="ob-location-chip" data-chip="city" data-empty="{{ $practice->city?->name ? '0' : '1' }}">
                                <span class="ob-location-chip-label">City</span>
                                <span class="ob-location-chip-val">{{ $practice->city?->name ?? '—' }}</span>
                            </span>
                            <span class="ob-location-chip" data-chip="state" data-empty="{{ $practice->state?->name ? '0' : '1' }}">
                                <span class="ob-location-chip-label">State</span>
                                <span class="ob-location-chip-val">{{ $practice->state?->name ?? '—' }}</span>
                            </span>
                            <span class="ob-location-chip" data-chip="country" data-empty="{{ $practice->country?->name ? '0' : '1' }}">
                                <span class="ob-location-chip-label">Country</span>
                                <span class="ob-location-chip-val">{{ $practice->country?->name ?? '—' }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ob-form-actions">
                <a href="{{ route('admin.practices.show', $practice) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
(function () {
    function syncZipChips() {
        var sel = document.getElementById('zip_id');
        var opt = sel ? sel.options[sel.selectedIndex] : null;
        var cityId = '', stateId = '', countryId = '';
        var values = { city: '', state: '', country: '' };

        if (opt && opt.value) {
            cityId    = opt.dataset.cityId    || '';
            stateId   = opt.dataset.stateId   || '';
            countryId = opt.dataset.countryId || '';
            values.city    = opt.dataset.city    || '';
            values.state   = opt.dataset.state   || '';
            values.country = opt.dataset.country || '';
        }

        document.getElementById('city_id').value    = cityId;
        document.getElementById('state_id').value   = stateId;
        document.getElementById('country_id').value = countryId;

        Object.keys(values).forEach(function (k) {
            var chip = document.querySelector('.ob-location-chip[data-chip="' + k + '"]');
            if (!chip) return;
            chip.dataset.empty = values[k] ? '0' : '1';
            chip.querySelector('.ob-location-chip-val').textContent = values[k] || '—';
        });
    }

    var zipEl = document.getElementById('zip_id');
    if (zipEl) {
        // jQuery change for select2/obSearchable compatibility, plain change as fallback.
        if (window.jQuery) {
            window.jQuery('#zip_id').on('change', syncZipChips);
        } else {
            zipEl.addEventListener('change', syncZipChips);
        }
    }

    if (window.obSearchable) { window.obSearchable('#zip_id'); }
})();
</script>
@endpush
