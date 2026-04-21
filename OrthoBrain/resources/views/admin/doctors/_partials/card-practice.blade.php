@php $practice = $doctor->practice; @endphp
<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title mb-0">
            <i data-feather="briefcase" class="me-50"></i> Practice
        </h4>
    </div>
    <div class="card-body pt-1">
        @if ($practice)
            <div class="mb-1">
                <label class="form-label">Practice Name</label>
                <div class="form-control bg-light-secondary" style="min-height: 38px;">{{ $practice->name }}</div>
            </div>

            @if ($practice->website)
                <div class="mb-1">
                    <label class="form-label">Website</label>
                    <div class="form-control bg-light-secondary" style="min-height: 38px;">
                        <a href="{{ $practice->website }}" target="_blank" rel="noopener" class="text-break">
                            {{ $practice->website }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($practice->phone_number)
                <div class="mb-1">
                    <label class="form-label">Phone</label>
                    <div class="form-control bg-light-secondary" style="min-height: 38px;">
                        {{ $practice->phone_country_code ? str_replace('_', ' ', $practice->phone_country_code) . ' ' : '' }}{{ $practice->phone_number }}
                    </div>
                </div>
            @endif

            @php
                $addressParts = array_filter([
                    $practice->street_address_1,
                    $practice->street_address_2,
                    $practice->city?->name,
                    $practice->state?->name,
                    $practice->zipcode?->code,
                    $practice->country?->name,
                ]);
            @endphp
            @if (!empty($addressParts))
                <div class="mb-0">
                    <label class="form-label">Address</label>
                    <div class="form-control bg-light-secondary" style="min-height: 38px;">
                        {{ implode(', ', $addressParts) }}
                    </div>
                </div>
            @endif

            @if ($doctor->ownedPractices && $doctor->ownedPractices->count())
                <div class="mt-1 pt-1 border-top">
                    <span class="badge rounded-pill badge-light-primary">
                        <i data-feather="star" style="width: 12px; height: 12px;" class="me-25"></i> Practice Owner
                    </span>
                </div>
            @endif
        @else
            <div class="text-muted text-center py-2">
                <i data-feather="alert-circle" class="me-50"></i> No practice linked.
            </div>
        @endif
    </div>
</div>
