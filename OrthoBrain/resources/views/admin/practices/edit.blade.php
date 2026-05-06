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
        --ob-heading: #111111;
    }

    .dark-layout #practice-edit {
        --ob-surface: #283046;
        --ob-surface-alt: #161d31;
        --ob-border: #3b4253;
        --ob-text: #b4b7bd;
        --ob-muted: #676d7d;
        --ob-heading: #d0d2d6;
        --ob-primary-softer: rgba(91, 192, 222, 0.15);
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
    #practice-edit .ob-card-title { font-weight: 700; color: var(--ob-heading); margin: 0; font-size: 1rem; }
    #practice-edit .ob-card-body { padding: 1rem 1.25rem; }

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
                <div class="row">
                    <div class="col-md-6 mb-1">
                        <label for="name" class="form-label">Practice Name<span class="text-danger">*</span></label>
                        <input id="name" name="name" type="text"
                               value="{{ old('name', $practice->name) }}"
                               class="form-control @error('name') is-invalid @enderror"
                               required>
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-1">
                        <label for="website" class="form-label">Website <span class="text-muted small">(optional)</span></label>
                        <input id="website" name="website" type="text"
                               value="{{ old('website', $practice->website) }}"
                               placeholder="example.com"
                               class="form-control @error('website') is-invalid @enderror">
                        @error('website')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2 mb-1">
                        <label for="phone_country_code" class="form-label">Code<span class="text-danger">*</span></label>
                        @php $currentCode = old('phone_country_code', $practice->phone_country_code ?? '+1'); @endphp
                        <select id="phone_country_code" name="phone_country_code"
                                class="form-select js-searchable @error('phone_country_code') is-invalid @enderror"
                                data-placeholder="Code">
                            <option value="">Code</option>
                            @forelse ($phoneCodes as $code)
                                <option value="{{ $code }}" @selected($currentCode === $code)>{{ $code }}</option>
                            @empty
                                <option value="+1" @selected($currentCode === '+1')>+1</option>
                            @endforelse
                        </select>
                        @error('phone_country_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-1">
                        <label for="phone_number" class="form-label">Phone Number<span class="text-danger">*</span></label>
                        <input id="phone_number" name="phone_number" type="tel"
                               value="{{ old('phone_number', $practice->phone_number) }}"
                               inputmode="numeric" maxlength="10"
                               placeholder="10 digits"
                               class="form-control @error('phone_number') is-invalid @enderror"
                               required>
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

                <div class="row">
                    <div class="col-md-8 mb-1">
                        <label for="street_address_1" class="form-label">Street Address<span class="text-danger">*</span></label>
                        <input id="street_address_1" name="street_address_1" type="text"
                               value="{{ old('street_address_1', $practice->street_address_1) }}"
                               placeholder="123 Main Street"
                               class="form-control @error('street_address_1') is-invalid @enderror"
                               required>
                        @error('street_address_1')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-1">
                        <label for="street_address_2" class="form-label">Suite / Apt <span class="text-muted small">(optional)</span></label>
                        <input id="street_address_2" name="street_address_2" type="text"
                               value="{{ old('street_address_2', $practice->street_address_2) }}"
                               placeholder="Suite 400"
                               class="form-control">
                    </div>

                    <div class="col-md-5 mb-1">
                        <label for="zip_id" class="form-label">Zip / Postal Code<span class="text-danger">*</span></label>
                        <select id="zip_id" name="zip_id" required
                                class="form-select @error('zip_id') is-invalid @enderror"
                                data-placeholder="Search zip or city...">
                            <option value="">Search zip or city...</option>
                            @if ($selectedZip)
                                <option value="{{ $selectedZip->id }}"
                                        selected
                                        data-city-id="{{ $selectedZip->city?->id }}"
                                        data-city="{{ $selectedZip->city?->name }}"
                                        data-state-id="{{ $selectedZip->city?->state?->id }}"
                                        data-state="{{ $selectedZip->city?->state?->name }}"
                                        data-country-id="{{ $selectedZip->city?->state?->country?->id }}"
                                        data-country="{{ $selectedZip->city?->state?->country?->name }}">
                                    {{ $selectedZip->code }} — {{ $selectedZip->city?->name }}, {{ $selectedZip->city?->state?->state_code }}
                                </option>
                            @endif
                        </select>
                        @error('zip_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-7 mb-1">
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

        // AJAX-selected option: pull location data from Select2's in-memory selection object.
        var s2raw = (window.jQuery && typeof $.fn.select2 === 'function' && $('#zip_id').data('select2'))
            ? (($('#zip_id').select2('data') || [])[0] || null)
            : null;

        if (s2raw && s2raw.id && s2raw.cityId) {
            cityId    = s2raw.cityId    || '';
            stateId   = s2raw.stateId   || '';
            countryId = s2raw.countryId || '';
            values.city    = s2raw.city    || '';
            values.state   = s2raw.state   || '';
            values.country = s2raw.country || '';
        } else if (opt && opt.value) {
            // Server-pre-rendered option (initial load or validation bounce) — data-* attrs present.
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

    if (window.jQuery && typeof $.fn.select2 === 'function') {
        // AJAX-backed Select2 — avoids loading all zipcodes upfront (would crash with massive data).
        $('#zip_id').select2({
            placeholder: 'Search zip or city...',
            allowClear: false,
            width: '100%',
            minimumInputLength: 2,
            dropdownParent: document.body,
            ajax: {
                url: '{{ route("admin.zipcodes.search") }}',
                dataType: 'json',
                delay: 300,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) {
                    return {
                        results: data.map(function (z) {
                            return {
                                id:        z.id,
                                text:      z.displayLabel,
                                city:      z.city,      cityId:      z.cityId,
                                state:     z.state,     stateId:     z.stateId,
                                country:   z.country,   countryId:   z.countryId,
                            };
                        })
                    };
                },
                cache: true,
            },
        }).on('select2:select', syncZipChips)
          .on('select2:open.obSearch', function () {
              setTimeout(function () {
                  var f = document.querySelector('.select2-container--open .select2-search__field');
                  if (f) f.focus();
              }, 0);
          });
    } else {
        var zipEl = document.getElementById('zip_id');
        if (zipEl) zipEl.addEventListener('change', syncZipChips);
    }
})();
</script>
@endpush
