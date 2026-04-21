@php
    $formatAddress = function ($a) {
        return implode(', ', array_filter([
            $a->street_address_1,
            $a->street_address_2,
            $a->city?->name,
            $a->state?->name,
            $a->zipcode?->code,
            $a->country?->name,
        ]));
    };
    $shipping = $doctor->shippingAddresses;
    $billing  = $doctor->billingAddresses;
@endphp
<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title mb-0">
            <i data-feather="map-pin" class="me-50"></i> Addresses
        </h4>
    </div>
    <div class="card-body pt-1">
        <ul class="nav nav-pills mb-1" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab"
                        data-bs-target="#shipping-tab-{{ $doctor->id }}" type="button">
                    Shipping ({{ $shipping->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab"
                        data-bs-target="#billing-tab-{{ $doctor->id }}" type="button">
                    Billing ({{ $billing->count() }})
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="shipping-tab-{{ $doctor->id }}">
                @forelse ($shipping as $addr)
                    <div class="mb-1 p-1 rounded bg-light-secondary">
                        @if ($addr->is_default)
                            <span class="badge rounded-pill badge-light-primary mb-50">Default</span>
                        @endif
                        <div>{{ $formatAddress($addr) }}</div>
                    </div>
                @empty
                    <div class="text-muted text-center py-1">No shipping addresses on file.</div>
                @endforelse
            </div>
            <div class="tab-pane fade" id="billing-tab-{{ $doctor->id }}">
                @forelse ($billing as $addr)
                    <div class="mb-1 p-1 rounded bg-light-secondary">
                        @if ($addr->is_default)
                            <span class="badge rounded-pill badge-light-primary mb-50">Default</span>
                        @endif
                        <div>{{ $formatAddress($addr) }}</div>
                        @if ($addr->billing_email)
                            <div class="text-muted mt-25" style="font-size: 0.78rem;">
                                <i data-feather="mail" style="width: 12px; height: 12px;" class="me-25"></i>{{ $addr->billing_email }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-muted text-center py-1">No billing addresses on file.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
