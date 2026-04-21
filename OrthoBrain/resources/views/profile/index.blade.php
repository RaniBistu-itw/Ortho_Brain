@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<style>
    .profile-nav .list-group-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .6rem .75rem;
        border: 0;
        border-radius: .358rem;
        color: #5e5873;
        background: transparent;
        transition: all .2s;
    }
    .profile-nav .list-group-item:hover { background: #f8f8f8; }
    .profile-nav .list-group-item .nav-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: .358rem;
        background: #f8f8f8;
        color: #b9b9c3;
        flex-shrink: 0;
    }
    .profile-nav .list-group-item .nav-label { font-weight: 500; line-height: 1.2; }
    .profile-nav .list-group-item .nav-sub { font-size: .8rem; color: #b9b9c3; }
    .profile-nav .list-group-item.active,
    .profile-nav .list-group-item.active:hover {
        background: rgba(91, 192, 222, 0.12);
        color: #5bc0de;
    }
    .profile-nav .list-group-item.active .nav-icon {
        background: #5bc0de;
        color: #fff;
        box-shadow: 0 2px 4px rgba(91,192,222,0.4);
    }
    .profile-nav .list-group-item.active .nav-label { color: #5bc0de; }
    .profile-dropzone {
        border: 2px dashed #d8d6de;
        border-radius: .358rem;
        background: #fcfcfc;
        min-height: 10rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #5e5873;
        cursor: pointer;
        position: relative;
        transition: background-color .15s ease;
    }
    .profile-dropzone:hover { background: #f8f8f8; }
    .profile-dropzone input[type="file"] {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        opacity: 0; cursor: pointer;
    }
    .profile-dropzone .dz-icon { color: #a5a8b6; }
    .preferences-card .pref-section { border-bottom: 1px solid #ebe9f1; padding: 1rem 1.25rem; }
    .preferences-card .pref-section:last-child { border-bottom: 0; }
    .preferences-card .pref-section p.pref-title { font-weight: 500; color: #5e5873; margin-bottom: .75rem; }

    /* Scrollable Doctor Preferences body (matches registration page) */
    .preferences-card .card-body {
        max-height: 500px;
        overflow-y: auto;
        position: relative;
    }
    .preferences-card .card-body::-webkit-scrollbar { width: 6px; }
    .preferences-card .card-body::-webkit-scrollbar-track { background: #fcfcfc; }
    .preferences-card .card-body::-webkit-scrollbar-thumb { background: #d8d6de; border-radius: 4px; }
    .preferences-card .card-body::-webkit-scrollbar-thumb:hover { background: #b9b9c3; }
    .sticky-back-top {
        position: sticky;
        bottom: 0.5rem;
        float: right;
        margin-top: -2rem;
        z-index: 5;
    }
    /* ── Mobile: render sidebar as horizontal scrollable tab strip ── */
    @media (max-width: 767.98px) {
        .profile-nav {
            flex-direction: row !important;
            overflow-x: auto;
            flex-wrap: nowrap;
            gap: .5rem;
            padding-bottom: .25rem;
        }
        .profile-nav .list-group-item {
            flex: 0 0 auto;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: .5rem .75rem;
            min-width: 5.5rem;
        }
        .profile-nav .list-group-item .nav-icon {
            width: 32px;
            height: 32px;
        }
        .profile-nav .list-group-item .nav-sub { display: none; }
        .preferences-card .pref-section { padding: .75rem 1rem; }
    }
</style>
@endpush

@section('content')
<div class="row">
    {{-- ─── Sidebar ─── --}}
    <div class="col-lg-3 col-12 mb-2">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-1 d-none d-md-block">My Profile</h4>
                <div class="list-group list-group-flush profile-nav">
                    <a href="?tab=account" class="list-group-item {{ $tab == 'account' ? 'active' : '' }}">
                        <span class="nav-icon"><i data-feather="user"></i></span>
                        <span>
                            <span class="nav-label d-block">Account</span>
                            <span class="nav-sub">Account Details</span>
                        </span>
                    </a>
                    <a href="?tab=practice" class="list-group-item {{ $tab == 'practice' ? 'active' : '' }}">
                        <span class="nav-icon"><i data-feather="home"></i></span>
                        <span>
                            <span class="nav-label d-block">Practice</span>
                            <span class="nav-sub">Practice Information</span>
                        </span>
                    </a>
                    <a href="?tab=shipping" class="list-group-item {{ $tab == 'shipping' ? 'active' : '' }}">
                        <span class="nav-icon"><i data-feather="map-pin"></i></span>
                        <span>
                            <span class="nav-label d-block">Shipping</span>
                            <span class="nav-sub">Shipping Address</span>
                        </span>
                    </a>
                    <a href="?tab=billing" class="list-group-item {{ $tab == 'billing' ? 'active' : '' }}">
                        <span class="nav-icon"><i data-feather="credit-card"></i></span>
                        <span>
                            <span class="nav-label d-block">Billing</span>
                            <span class="nav-sub">Billing Address</span>
                        </span>
                    </a>
                    <a href="?tab=additional" class="list-group-item {{ $tab == 'additional' ? 'active' : '' }}">
                        <span class="nav-icon"><i data-feather="file-text"></i></span>
                        <span>
                            <span class="nav-label d-block">Additional</span>
                            <span class="nav-sub">Doctor Information</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Content ─── --}}
    <div class="col-lg-9 col-12">
        @php
            $doctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
        @endphp

        <div class="card">
            <div class="card-body">
                {{-- ─── Tab: Account ─── --}}
                @if($tab == 'account')
                <h4 class="card-title mb-2">Account</h4>

                <form id="accountForm" method="POST" action="/dev/profile/index?tab=account" enctype="multipart/form-data" class="ob-form-validate" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label for="in-acc-first-name" class="form-label">First Name<span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                                <input id="in-acc-first-name" type="text" name="first_name"
                                       value="{{ old('first_name', $doctor?->first_name ?? '') }}"
                                       class="form-control">
                            </div>
                            <small id="err-acc-first-name" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label for="in-acc-last-name" class="form-label">Last Name<span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                                <input id="in-acc-last-name" type="text" name="last_name"
                                       value="{{ old('last_name', $doctor?->last_name ?? '') }}"
                                       class="form-control">
                            </div>
                            <small id="err-acc-last-name" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label for="in-acc-email" class="form-label">Email<span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i data-feather="mail"></i></span>
                                <input id="in-acc-email" type="email" name="email"
                                       value="{{ old('email', auth()->user()->email ?? '') }}"
                                       class="form-control">
                            </div>
                            <small id="err-acc-email" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label for="in-acc-phone" class="form-label">Phone Number</label>
                            <div class="row g-1">
                                <div class="col-5 col-sm-4">
                                    <select name="practice_phone_country_code" class="form-select">
                                        <option value="+1_US" selected>+1 (US)</option>
                                    </select>
                                </div>
                                <div class="col-7 col-sm-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i data-feather="phone"></i></span>
                                        <input id="in-acc-phone" type="text" name="practice_phone_number"
                                               value="{{ old('practice_phone_number', $doctor?->practice?->phone_number ?? '') }}"
                                               placeholder="XXX-XXX-XXXX" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <small id="err-acc-phone" class="text-danger d-none"></small>
                        </div>

                        <div class="col-12 mb-1">
                            <label class="form-label">Profile Image</label>
                            <div class="profile-dropzone">
                                <i data-feather="upload" class="dz-icon mb-50" style="width:36px;height:36px;"></i>
                                <span class="fw-bolder">Choose a File or Drag &amp; Drop Files</span>
                                <input type="file" name="profile_image" accept="image/*">
                            </div>
                        </div>

                        <div class="col-12 mt-1 mb-1">
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check form-switch me-3">
                                    <input type="checkbox" class="form-check-input" id="notif-1" checked>
                                    <label class="form-check-label" for="notif-1">Notification</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="notif-2" checked>
                                    <label class="form-check-label" for="notif-2">Notification</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex mt-2">
                        <button type="button" onclick="validateAccountForm()" class="btn btn-success me-1">Save</button>
                        <a href="/dev/cases/list" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
                @endif


                {{-- ─── Tab: Practice ─── --}}
                @if($tab == 'practice')
                <h4 class="card-title mb-2 pb-1 border-bottom">Practice</h4>

                <form id="practiceForm" method="POST" action="/dev/profile/index?tab=practice" enctype="multipart/form-data" class="ob-form-validate" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label for="in-prac-name" class="form-label">Practice Name<span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i data-feather="award"></i></span>
                                <input id="in-prac-name" type="text" name="practice_name"
                                       value="{{ old('practice_name', $doctor?->practice?->name ?? '') }}"
                                       class="form-control">
                            </div>
                            <small id="err-prac-name" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label for="in-prac-phone" class="form-label">Practice Phone Number<span class="text-danger">*</span></label>
                            <div class="row g-1">
                                <div class="col-5 col-sm-4">
                                    <select name="practice_phone_country_code" class="form-select">
                                        <option value="+1_US" selected>+1 (US)</option>
                                    </select>
                                </div>
                                <div class="col-7 col-sm-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i data-feather="phone"></i></span>
                                        <input id="in-prac-phone" type="text" name="practice_phone_number"
                                               value="{{ old('practice_phone_number', $doctor?->practice?->phone_number ?? '') }}"
                                               placeholder="XXX-XXX-XXXX" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <small id="err-prac-phone" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label for="in-prac-website" class="form-label">Website<span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i data-feather="globe"></i></span>
                                <input id="in-prac-website" type="text" name="website"
                                       value="{{ old('website', $doctor?->practice?->website ?? '') }}"
                                       placeholder="https://yoursite.com" class="form-control">
                            </div>
                            <small id="err-prac-website" class="text-danger d-none"></small>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label for="in-prac-language" class="form-label">Preferred Language</label>
                            <select id="in-prac-language" name="language" class="form-select">
                                <option value="English" selected>English</option>
                            </select>
                        </div>

                        <div class="col-12 mb-1">
                            <label class="form-label">Practice Logo</label>
                            <div class="profile-dropzone">
                                <i data-feather="upload" class="dz-icon mb-50" style="width:36px;height:36px;"></i>
                                <span class="fw-bolder">Choose a File or Drag &amp; Drop Files</span>
                                <input type="file" name="practice_logo" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex mt-2">
                        <button type="button" onclick="validatePracticeForm()" class="btn btn-success me-1">Save</button>
                        <a href="/dev/cases/list" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
                @endif


                {{-- ─── Tab: Shipping ─── --}}
                @if($tab == 'shipping')
                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 mb-2">
                    <h4 class="card-title mb-0">Shipping Addresses</h4>
                    <a href="{{ route('doctor.profile.address.create', ['type' => 'shipping']) }}" class="btn btn-info align-self-start align-self-sm-auto">
                        <i data-feather="plus" class="me-25"></i> Add Shipping Address
                    </a>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 border-top border-bottom py-1 mb-1">
                    <div class="d-flex align-items-center">
                        <span class="me-50">Show</span>
                        <select id="ship-pagesize" class="form-select form-select-sm" style="width: 5.5rem; padding-right: 2rem;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-50">entries</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="ship-search" class="me-50 mb-0">Search:</label>
                        <input id="ship-search" type="text" class="form-control form-control-sm flex-grow-1" style="max-width: 14rem;">
                    </div>
                </div>

                @php $shippingList = ($addresses ?? collect())->where('type', 'shipping'); @endphp
                <div class="table-responsive">
                    <table id="ship-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Street Address</th>
                                <th>City</th>
                                <th>State/Province</th>
                                <th>Zip Code</th>
                                <th>Is Default</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shippingList as $addr)
                                @php
                                    $searchText = strtolower(trim(implode(' ', array_filter([
                                        $addr->street_address_1,
                                        $addr->street_address_2,
                                        $addr->city?->name,
                                        $addr->state?->name,
                                        $addr->zipcode?->code,
                                        $addr->is_default ? 'yes default' : 'no',
                                    ]))));
                                @endphp
                                <tr data-searchable="{{ $searchText }}">
                                    <td>
                                        <a href="{{ route('doctor.profile.address.show', $addr) }}" class="btn btn-icon btn-sm btn-flat-success" title="View"><i data-feather="eye"></i></a>
                                        <a href="{{ route('doctor.profile.address.edit', $addr) }}" class="btn btn-icon btn-sm btn-flat-info" title="Edit"><i data-feather="edit-2"></i></a>
                                    </td>
                                    <td>{{ $addr->street_address_1 }}{{ $addr->street_address_2 ? ', ' . $addr->street_address_2 : '' }}</td>
                                    <td>{{ $addr->city?->name }}</td>
                                    <td>{{ $addr->state?->name }}</td>
                                    <td>{{ $addr->zipcode?->code }}</td>
                                    <td>
                                        @if($addr->is_default)
                                            <span class="badge bg-light-success">Yes</span>
                                        @else
                                            <span class="badge bg-light-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-state"><td colspan="6" class="text-center text-muted">No shipping addresses yet.</td></tr>
                            @endforelse
                            <tr class="no-results d-none"><td colspan="6" class="text-center text-muted">No matching results.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 mt-1">
                    <small id="ship-footer" class="text-muted"></small>
                    <ul id="ship-pager" class="pagination pagination-sm mb-0"></ul>
                </div>
                @endif


                {{-- ─── Tab: Billing ─── --}}
                @if($tab == 'billing')
                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 mb-2">
                    <h4 class="card-title mb-0">Billing Addresses</h4>
                    <a href="{{ route('doctor.profile.address.create', ['type' => 'billing']) }}" class="btn btn-info align-self-start align-self-sm-auto">
                        <i data-feather="plus" class="me-25"></i> Add Billing Address
                    </a>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 border-top border-bottom py-1 mb-1">
                    <div class="d-flex align-items-center">
                        <span class="me-50">Show</span>
                        <select id="bill-pagesize" class="form-select form-select-sm" style="width: 5.5rem; padding-right: 2rem;">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-50">entries</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="bill-search" class="me-50 mb-0">Search:</label>
                        <input id="bill-search" type="text" class="form-control form-control-sm flex-grow-1" style="max-width: 14rem;">
                    </div>
                </div>

                @php $billingList = ($addresses ?? collect())->where('type', 'billing'); @endphp
                <div class="table-responsive">
                    <table id="bill-table" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Street Address</th>
                                <th>City</th>
                                <th>State/Province</th>
                                <th>Zip Code</th>
                                <th>Billing Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($billingList as $addr)
                                @php
                                    $searchText = strtolower(trim(implode(' ', array_filter([
                                        $addr->street_address_1,
                                        $addr->street_address_2,
                                        $addr->city?->name,
                                        $addr->state?->name,
                                        $addr->zipcode?->code,
                                        $addr->billing_email,
                                    ]))));
                                @endphp
                                <tr data-searchable="{{ $searchText }}">
                                    <td>
                                        <a href="{{ route('doctor.profile.address.show', $addr) }}" class="btn btn-icon btn-sm btn-flat-success" title="View"><i data-feather="eye"></i></a>
                                        <a href="{{ route('doctor.profile.address.edit', $addr) }}" class="btn btn-icon btn-sm btn-flat-info" title="Edit"><i data-feather="edit-2"></i></a>
                                    </td>
                                    <td>{{ $addr->street_address_1 }}{{ $addr->street_address_2 ? ', ' . $addr->street_address_2 : '' }}</td>
                                    <td>{{ $addr->city?->name }}</td>
                                    <td>{{ $addr->state?->name }}</td>
                                    <td>{{ $addr->zipcode?->code }}</td>
                                    <td>{{ $addr->billing_email ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr class="empty-state"><td colspan="6" class="text-center text-muted">No billing addresses yet.</td></tr>
                            @endforelse
                            <tr class="no-results d-none"><td colspan="6" class="text-center text-muted">No matching results.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center gap-1 mt-1">
                    <small id="bill-footer" class="text-muted"></small>
                    <ul id="bill-pager" class="pagination pagination-sm mb-0"></ul>
                </div>
                @endif


                {{-- ─── Tab: Additional ─── --}}
                @if($tab == 'additional')
                <h4 class="card-title mb-2">Additional Information</h4>

                <form id="additionalForm" method="POST" action="{{ route('doctor.profile.additional.update') }}">
                    @csrf

                <div class="mb-2">
                    <p class="fw-bolder mb-50">Are you currently providing orthodontic services in your practice?</p>
                    <div class="form-check">
                        <input type="radio" name="ortho_services" id="ortho-yes" class="form-check-input" checked>
                        <label class="form-check-label" for="ortho-yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="ortho_services" id="ortho-no" class="form-check-input">
                        <label class="form-check-label" for="ortho-no">No</label>
                    </div>
                </div>

                <div class="mb-2">
                    <p class="fw-bolder mb-50">What modalities are you currently/or planning to provide?</p>
                    @foreach(($modalitiesList ?? collect()) as $opt)
                        <div class="form-check">
                            <input type="checkbox" name="modalities[]" value="{{ $opt->id }}"
                                   id="mod-{{ $opt->id }}" class="form-check-input"
                                   @checked(in_array($opt->id, $selectedModalityIds ?? [], true))>
                            <label class="form-check-label" for="mod-{{ $opt->id }}">{{ $opt->name }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="mb-2">
                    <p class="fw-bolder mb-50">Specialties:</p>
                    @php $specs = ($specialtiesList ?? collect()); $half = (int) ceil($specs->count() / 2); @endphp
                    <div class="row">
                        <div class="col-md-6">
                            @foreach($specs->take($half) as $opt)
                                <div class="form-check">
                                    <input type="checkbox" name="specialties[]" value="{{ $opt->id }}"
                                           id="spec-{{ $opt->id }}" class="form-check-input"
                                           @checked(in_array($opt->id, $selectedSpecialtyIds ?? [], true))>
                                    <label class="form-check-label" for="spec-{{ $opt->id }}">{{ $opt->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            @foreach($specs->slice($half) as $opt)
                                <div class="form-check">
                                    <input type="checkbox" name="specialties[]" value="{{ $opt->id }}"
                                           id="spec-{{ $opt->id }}" class="form-check-input"
                                           @checked(in_array($opt->id, $selectedSpecialtyIds ?? [], true))>
                                    <label class="form-check-label" for="spec-{{ $opt->id }}">{{ $opt->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <p class="fw-bolder mb-50">Preferred doctor contact information</p>
                    <div class="form-check">
                        <input type="radio" name="contact_info" id="ci-doc" value="doctor_only" class="form-check-input" checked onchange="toggleContactForms()">
                        <label class="form-check-label" for="ci-doc">Doctor Only</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="contact_info" id="ci-emp" value="employee_office" class="form-check-input" onchange="toggleContactForms()">
                        <label class="form-check-label" for="ci-emp">Employee/Office</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="contact_info" id="ci-both" value="doctor_and_employee" class="form-check-input" onchange="toggleContactForms()">
                        <label class="form-check-label" for="ci-both">Doctor and Employee/Office</label>
                    </div>
                </div>

                {{-- Doctor Contact Block --}}
                <div id="form-doctor" class="card bg-light-secondary shadow-none border mb-1">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <label for="in-doc-email" class="form-label">Doctor Email<span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="mail"></i></span>
                                    <input id="in-doc-email" type="email" class="form-control"
                                           value="{{ $doctor?->doctor_contact_email ?? '' }}">
                                </div>
                                <small id="err-doc-email" class="text-danger d-none"></small>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label for="in-doc-phone" class="form-label">Doctor Cell Phone Number</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="phone"></i></span>
                                    <input id="in-doc-phone" type="text" class="form-control" placeholder="XXX-XXX-XXXX"
                                           value="{{ $doctor?->doctor_cell_phone ?? '' }}">
                                </div>
                                <small id="err-doc-phone" class="text-danger d-none"></small>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Other Email</label>
                            <div id="doc-other-emails" class="d-flex flex-column gap-1"></div>
                        </div>
                        <button type="button" onclick="addOtherEmail('doc')" class="btn btn-info btn-sm">
                            <i data-feather="plus" class="me-25"></i> Add Other Email
                        </button>
                    </div>
                </div>

                {{-- Employee Contact Block --}}
                <div id="form-employee" class="card bg-light-secondary shadow-none border mb-1 d-none">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <label for="in-emp-name" class="form-label">Employee Name<span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="user"></i></span>
                                    <input id="in-emp-name" type="text" class="form-control" placeholder="Enter name">
                                </div>
                                <small id="err-emp-name" class="text-danger d-none"></small>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label for="in-emp-title" class="form-label">Employee Title<span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="file-text"></i></span>
                                    <input id="in-emp-title" type="text" class="form-control" placeholder="Enter title">
                                </div>
                                <small id="err-emp-title" class="text-danger d-none"></small>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label for="in-emp-email" class="form-label">Office/Employee Email<span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="mail"></i></span>
                                    <input id="in-emp-email" type="email" class="form-control" placeholder="Enter office/employee email">
                                </div>
                                <small id="err-emp-email" class="text-danger d-none"></small>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label for="in-emp-phone" class="form-label">Office/Employee Cell Phone Number<span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i data-feather="phone"></i></span>
                                    <input id="in-emp-phone" type="text" class="form-control" placeholder="XXX-XXX-XXXX">
                                </div>
                                <small id="err-emp-phone" class="text-danger d-none"></small>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Other Email</label>
                            <div id="emp-other-emails" class="d-flex flex-column gap-1"></div>
                        </div>
                        <button type="button" onclick="addOtherEmail('emp')" class="btn btn-info btn-sm">
                            <i data-feather="plus" class="me-25"></i> Add Other Email
                        </button>
                    </div>
                </div>

                {{-- Doctor Preferences --}}
                <div class="card border shadow-none preferences-card mb-2">
                    <div class="card-header border-bottom py-1">
                        <h5 class="card-title mb-0">Doctor Preferences</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="pref-section">
                            <p class="pref-title">Preferred Treatment Modality</p>
                            @foreach(($treatmentModalitiesList ?? collect()) as $opt)
                                <div class="form-check">
                                    <input type="checkbox" name="treatment_modalities[]" value="{{ $opt->id }}"
                                           id="tm-{{ $opt->id }}" class="form-check-input"
                                           @checked(in_array($opt->id, $selectedTreatmentModalityIds ?? [], true))>
                                    <label class="form-check-label" for="tm-{{ $opt->id }}">{{ $opt->name }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Preferred Tooth Numbering System</p>
                            @foreach(['Universal (1-32)','FDI (11-48)','Palmer (UR1-UR8)','International (11-48)'] as $i => $opt)
                                <div class="form-check">
                                    <input type="radio" name="tooth_numbering" id="tn-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tn-{{ $i }}">{{ $opt }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Smile Arc</p>
                            @foreach(['Defer to orthobrain®','Lateral incisors .5mm shorter than central incisors','Lateral incisors same length as central incisors'] as $i => $opt)
                                <div class="form-check">
                                    <input type="radio" name="smile_arc" id="sa-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sa-{{ $i }}">{!! $opt !!}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Treatment of Small Lateral Incisors</p>
                            @foreach([
                                'Defer to orthobrain®',
                                'Interproximal Reduction (IPR) on lower arch to camouflage',
                                'Leave spacing mesial and distal to maxillary laterals for future cosmetic correction'
                            ] as $i => $opt)
                                <div class="form-check">
                                    <input type="radio" name="lateral_incisors" id="li-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="li-{{ $i }}">{!! $opt !!}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Buccal Corridors</p>
                            @foreach(($buccalCorridorsList ?? collect()) as $opt)
                                <div class="form-check">
                                    <input type="checkbox" name="buccal_corridors[]" value="{{ $opt->id }}"
                                           id="bc-{{ $opt->id }}" class="form-check-input"
                                           @checked(in_array($opt->id, $selectedBuccalCorridorIds ?? [], true))>
                                    <label class="form-check-label" for="bc-{{ $opt->id }}">{!! $opt->name !!}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Mixed Dentition and Bite Correcting Appliances</p>
                            <div class="form-check">
                                <input type="radio" name="mixed_dentition" id="md-0" class="form-check-input" checked>
                                <label class="form-check-label" for="md-0">Defer to orthobrain®</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="mixed_dentition" id="md-1" class="form-check-input">
                                <label class="form-check-label" for="md-1">I prefer not to use any growth and adjunctive appliances (e.g., expanders, bite planes, bit correctors, herbst, etc.) and request a proposal for a best outcome without an appliance knowing and fully understanding that this may not be an ideal Perfect Smile Plan for optimal results.</label>
                            </div>
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Orthodontic Extractions</p>
                            <div class="form-check">
                                <input type="radio" name="ortho_extractions" id="oe-0" class="form-check-input" checked>
                                <label class="form-check-label" for="oe-0">Defer to orthobrain®</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="ortho_extractions" id="oe-1" class="form-check-input">
                                <label class="form-check-label" for="oe-1">I prefer not to extract teeth and request a proposal for a best outcome without extractions fully knowing and fully understanding that this may not be an ideal treatment plan for optimal results.</label>
                            </div>
                        </div>

                        <div class="pref-section">
                            <p class="pref-title">Preferences</p>

                            <div class="mb-1">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="pref-ipr" onchange="document.getElementById('ipr-options').classList.toggle('d-none')">
                                    <label class="form-check-label" for="pref-ipr">IPR Protocol</label>
                                </div>
                                <div id="ipr-options" class="d-none mt-50 p-1 rounded bg-light">
                                    @foreach(['Defer to orthobrain®','No IPR','Other'] as $i => $opt)
                                        <div class="form-check">
                                            <input type="radio" name="ipr_opt" id="ipr-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ipr-{{ $i }}">{!! $opt !!}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-1">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="pref-att" onchange="document.getElementById('attachment-options').classList.toggle('d-none')">
                                    <label class="form-check-label" for="pref-att">Attachments</label>
                                </div>
                                <div id="attachment-options" class="d-none mt-50 p-1 rounded bg-light">
                                    @foreach(['At Aligner Step 1','At Aligner Step'] as $i => $opt)
                                        <div class="form-check">
                                            <input type="radio" name="attachment_opt" id="att-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="att-{{ $i }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-1">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="pref-el" onchange="document.getElementById('elastics-options').classList.toggle('d-none')">
                                    <label class="form-check-label" for="pref-el">Elastics/Bonded Buttons</label>
                                </div>
                                <div id="elastics-options" class="d-none mt-50 p-1 rounded bg-light">
                                    @foreach(['Yes','No'] as $i => $opt)
                                        <div class="form-check">
                                            <input type="radio" name="elastics_opt" id="el-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="el-{{ $i }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="pref-ex" onchange="document.getElementById('extractions-options').classList.toggle('d-none')">
                                    <label class="form-check-label" for="pref-ex">Extractions if suggested</label>
                                </div>
                                <div id="extractions-options" class="d-none mt-50 p-1 rounded bg-light">
                                    @foreach(['Yes','No'] as $i => $opt)
                                        <div class="form-check">
                                            <input type="radio" name="extractions_opt" id="ex-{{ $i }}" class="form-check-input" {{ $i === 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ex-{{ $i }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex mt-2">
                    <button type="submit" class="btn btn-info me-1">Save changes</button>
                    <a href="/dev/cases/list" class="btn btn-outline-secondary">Cancel</a>
                </div>

                </form>{{-- /#additionalForm --}}
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /* ── Contact tab helpers ── */
    function toggleContactForms() {
        const checked = document.querySelector('input[name="contact_info"]:checked');
        if (!checked) return;
        const selected = checked.value;
        const doctorForm = document.getElementById('form-doctor');
        const employeeForm = document.getElementById('form-employee');
        if (!doctorForm || !employeeForm) return;
        doctorForm.classList.toggle('d-none', selected === 'employee_office');
        employeeForm.classList.toggle('d-none', selected === 'doctor_only');
    }
    document.addEventListener('DOMContentLoaded', toggleContactForms);

    /* ── Add / Remove Other Email rows ── */
    function addOtherEmail(prefix) {
        const container = document.getElementById(prefix + '-other-emails');
        if (!container) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex align-items-center gap-1 other-email-row';
        wrapper.innerHTML = `
            <div class="input-group input-group-merge flex-grow-1">
                <span class="input-group-text"><i data-feather="mail"></i></span>
                <input type="email" placeholder="Enter other email" class="form-control">
            </div>
            <button type="button" class="btn btn-icon btn-flat-danger" title="Remove">
                <i data-feather="trash-2"></i>
            </button>
        `;
        wrapper.querySelector('button').addEventListener('click', () => wrapper.remove());
        container.appendChild(wrapper);
        if (window.feather) feather.replace({ width: 14, height: 14 });
    }

    /* ── Validation helpers ── */
    function _showErr(prefix, id, msg) {
        const e = document.getElementById('err-' + prefix + '-' + id);
        const i = document.getElementById('in-' + prefix + '-' + id);
        if (e) { e.textContent = msg; e.classList.remove('d-none'); }
        if (i) i.classList.add('is-invalid');
    }
    function _clearErr(prefix, id) {
        const e = document.getElementById('err-' + prefix + '-' + id);
        const i = document.getElementById('in-' + prefix + '-' + id);
        if (e) { e.textContent = ''; e.classList.add('d-none'); }
        if (i) i.classList.remove('is-invalid');
    }

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /* ── Account form ── */
    function validateAccountForm() {
        let ok = true;
        ['first-name','last-name','email','phone'].forEach(id => _clearErr('acc', id));

        const fn = (document.getElementById('in-acc-first-name')?.value || '').trim();
        if (!fn) { _showErr('acc','first-name','First name is required.'); ok = false; }
        else if (fn.length < 2) { _showErr('acc','first-name','Minimum 2 characters.'); ok = false; }
        else if (!/^[A-Za-z\s\-]+$/.test(fn)) { _showErr('acc','first-name','Letters, spaces and hyphens only.'); ok = false; }

        const ln = (document.getElementById('in-acc-last-name')?.value || '').trim();
        if (!ln) { _showErr('acc','last-name','Last name is required.'); ok = false; }
        else if (ln.length < 2) { _showErr('acc','last-name','Minimum 2 characters.'); ok = false; }
        else if (!/^[A-Za-z\s\-]+$/.test(ln)) { _showErr('acc','last-name','Letters, spaces and hyphens only.'); ok = false; }

        const em = (document.getElementById('in-acc-email')?.value || '').trim();
        if (!em) { _showErr('acc','email','Email is required.'); ok = false; }
        else if (!emailRe.test(em)) { _showErr('acc','email','Enter a valid email address.'); ok = false; }

        const ph = (document.getElementById('in-acc-phone')?.value || '').trim();
        if (ph && ph.replace(/\D/g,'').length !== 10) {
            _showErr('acc','phone','Phone must be 10 digits (e.g. 5551234567).'); ok = false;
        }

        if (ok) document.getElementById('accountForm').submit();
    }

    /* ── Practice form ── */
    function validatePracticeForm() {
        let ok = true;
        ['name','phone','website'].forEach(id => _clearErr('prac', id));

        const pn = (document.getElementById('in-prac-name')?.value || '').trim();
        if (!pn) { _showErr('prac','name','Practice name is required.'); ok = false; }
        else if (pn.length < 3) { _showErr('prac','name','Minimum 3 characters.'); ok = false; }

        const ph = (document.getElementById('in-prac-phone')?.value || '').trim().replace(/\D/g,'');
        if (!ph) { _showErr('prac','phone','Practice phone is required.'); ok = false; }
        else if (ph.length !== 10) { _showErr('prac','phone','Phone must be 10 digits (e.g. 5551234567).'); ok = false; }

        const ws = (document.getElementById('in-prac-website')?.value || '').trim();
        if (!ws) { _showErr('prac','website','Website is required.'); ok = false; }
        else if (!/^(https?:\/\/)?([\w\-]+\.)+[\w]{2,}(\/\S*)?$/.test(ws)) {
            _showErr('prac','website','Enter a valid URL (e.g. https://yoursite.com).'); ok = false;
        }

        if (ok) document.getElementById('practiceForm').submit();
    }

    /* ── Contact form ── */
    function validateContactForm() {
        let ok = true;
        ['email','phone'].forEach(id => _clearErr('doc', id));
        ['name','title','email','phone'].forEach(id => _clearErr('emp', id));

        const mode = document.querySelector('input[name="contact_info"]:checked')?.value || 'doctor_only';
        const useDoc = mode === 'doctor_only' || mode === 'doctor_and_employee';
        const useEmp = mode === 'employee_office' || mode === 'doctor_and_employee';

        if (useDoc) {
            const de = (document.getElementById('in-doc-email')?.value || '').trim();
            if (!de) { _showErr('doc','email','Doctor email is required.'); ok = false; }
            else if (!emailRe.test(de)) { _showErr('doc','email','Enter a valid email address.'); ok = false; }

            const dp = (document.getElementById('in-doc-phone')?.value || '').trim();
            if (dp && dp.replace(/\D/g,'').length !== 10) { _showErr('doc','phone','Phone must be 10 digits.'); ok = false; }

            document.querySelectorAll('#doc-other-emails input[type="email"]').forEach(inp => {
                const v = inp.value.trim();
                if (v && !emailRe.test(v)) { inp.classList.add('is-invalid'); ok = false; }
                else inp.classList.remove('is-invalid');
            });
        }

        if (useEmp) {
            const en = (document.getElementById('in-emp-name')?.value || '').trim();
            if (!en) { _showErr('emp','name','Employee name is required.'); ok = false; }
            else if (en.length < 2) { _showErr('emp','name','Minimum 2 characters.'); ok = false; }

            const et = (document.getElementById('in-emp-title')?.value || '').trim();
            if (!et) { _showErr('emp','title','Employee title is required.'); ok = false; }
            else if (et.length < 2) { _showErr('emp','title','Minimum 2 characters.'); ok = false; }

            const ee = (document.getElementById('in-emp-email')?.value || '').trim();
            if (!ee) { _showErr('emp','email','Office/employee email is required.'); ok = false; }
            else if (!emailRe.test(ee)) { _showErr('emp','email','Enter a valid email address.'); ok = false; }

            const ep = (document.getElementById('in-emp-phone')?.value || '').trim();
            if (!ep) { _showErr('emp','phone','Phone number is required.'); ok = false; }
            else if (ep.replace(/\D/g,'').length !== 10) { _showErr('emp','phone','Phone must be 10 digits.'); ok = false; }

            document.querySelectorAll('#emp-other-emails input[type="email"]').forEach(inp => {
                const v = inp.value.trim();
                if (v && !emailRe.test(v)) { inp.classList.add('is-invalid'); ok = false; }
                else inp.classList.remove('is-invalid');
            });
        }

        if (useDoc && useEmp) {
            const de = (document.getElementById('in-doc-email')?.value || '').trim().toLowerCase();
            const ee = (document.getElementById('in-emp-email')?.value || '').trim().toLowerCase();
            if (de && ee && de === ee) {
                _showErr('emp','email','Office/employee email must differ from doctor email.'); ok = false;
            }
        }

        if (ok) alert('Contact information is valid! (Backend save coming soon.)');
    }

    /* ── Reusable client-side datatable: search + page size + pagination ── */
    function wireDataTable({ tableId, searchId, pageSizeId, footerId, pagerId }) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const tbody    = table.querySelector('tbody');
        const allRows  = Array.from(tbody.querySelectorAll('tr[data-searchable]'));
        const emptyRow = tbody.querySelector('tr.empty-state');
        const noResRow = tbody.querySelector('tr.no-results');
        const search   = document.getElementById(searchId);
        const sizeSel  = document.getElementById(pageSizeId);
        const footer   = document.getElementById(footerId);
        const pager    = document.getElementById(pagerId);

        let currentPage = 1;

        function render() {
            const q = (search?.value || '').trim().toLowerCase();
            const pageSize = parseInt(sizeSel?.value || '10', 10);

            const filtered = q
                ? allRows.filter(r => (r.dataset.searchable || '').includes(q))
                : allRows.slice();

            const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * pageSize;
            const pageRows = filtered.slice(start, start + pageSize);

            allRows.forEach(r => r.classList.add('d-none'));
            pageRows.forEach(r => r.classList.remove('d-none'));

            if (emptyRow) emptyRow.classList.toggle('d-none', allRows.length > 0);
            if (noResRow) noResRow.classList.toggle('d-none', !(allRows.length > 0 && filtered.length === 0));

            if (footer) {
                if (allRows.length === 0) {
                    footer.textContent = '';
                } else if (filtered.length === 0) {
                    footer.textContent = `Showing 0 of ${allRows.length} entries (filtered)`;
                } else {
                    const end = Math.min(start + pageSize, filtered.length);
                    const suffix = (q && filtered.length !== allRows.length)
                        ? ` (filtered from ${allRows.length} total)`
                        : '';
                    footer.textContent = `Showing ${start + 1} to ${end} of ${filtered.length} entries${suffix}`;
                }
            }

            if (pager) renderPager(pager, currentPage, totalPages);
        }

        function renderPager(el, cur, total) {
            el.innerHTML = '';
            if (total <= 1) return;
            const mk = (label, page, opts = {}) => {
                const li = document.createElement('li');
                li.className = 'page-item'
                    + (opts.disabled ? ' disabled' : '')
                    + (opts.active ? ' active' : '');
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = label;
                a.addEventListener('click', e => {
                    e.preventDefault();
                    if (opts.disabled || opts.active) return;
                    currentPage = page;
                    render();
                });
                li.appendChild(a);
                el.appendChild(li);
            };
            mk('Previous', cur - 1, { disabled: cur === 1 });
            for (let p = 1; p <= total; p++) mk(String(p), p, { active: p === cur });
            mk('Next', cur + 1, { disabled: cur === total });
        }

        search?.addEventListener('input',  () => { currentPage = 1; render(); });
        sizeSel?.addEventListener('change', () => { currentPage = 1; render(); });
        render();
    }

    document.addEventListener('DOMContentLoaded', () => {
        wireDataTable({ tableId: 'ship-table', searchId: 'ship-search', pageSizeId: 'ship-pagesize', footerId: 'ship-footer', pagerId: 'ship-pager' });
        wireDataTable({ tableId: 'bill-table', searchId: 'bill-search', pageSizeId: 'bill-pagesize', footerId: 'bill-footer', pagerId: 'bill-pager' });
    });
</script>
@endpush
