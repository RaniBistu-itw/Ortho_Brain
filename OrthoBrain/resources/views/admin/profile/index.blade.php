@extends('layouts.admin')
@section('title', 'My Profile')
@section('page_title', 'My Profile')

@push('styles')
<style>
    /* ── Phone: fused Select2 + text input ── */
    .ob-phone-group {
        display: flex;
        align-items: stretch;
    }
    .ob-phone-group .select2-container {
        flex: 0 0 115px;
        width: 115px !important;
    }
    .ob-phone-group .select2-container--default .select2-selection--single {
        height: 100%;
        border-right: 0;
        border-radius: 0.357rem 0 0 0.357rem;
    }
    .ob-phone-group .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
        top: 0;
    }
    .ob-phone-group .form-control {
        border-radius: 0 0.357rem 0.357rem 0 !important;
    }
    .ob-phone-group .select2-container--open .select2-selection--single,
    .ob-phone-group .select2-container--focus .select2-selection--single {
        border-color: var(--ob-primary, #7367f0);
    }
</style>
@endpush

@section('content')
@php $avatarUrl = $admin->avatarUrl(); @endphp

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-feather="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
            {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-feather="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
            {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ── Account card ── --}}
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-2">Account</h4>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="ob-form-validate" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-6 mb-1">
                    <label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i data-feather="user"></i></span>
                        <input id="first_name" type="text" name="first_name" required maxlength="100"
                               value="{{ old('first_name', $admin->first_name) }}"
                               class="form-control @error('first_name') is-invalid @enderror">
                    </div>
                    @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-1">
                    <label for="last_name" class="form-label">Last Name<span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i data-feather="user"></i></span>
                        <input id="last_name" type="text" name="last_name" required maxlength="100"
                               value="{{ old('last_name', $admin->last_name) }}"
                               class="form-control @error('last_name') is-invalid @enderror">
                    </div>
                    @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-1">
                    <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i data-feather="mail"></i></span>
                        <input id="email" type="email" name="email" required maxlength="150"
                               value="{{ old('email', auth()->user()->email) }}"
                               class="form-control @error('email') is-invalid @enderror">
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-1">
                    @php $currentPhoneCode = old('phone_country_code', $admin->phone_country_code ?? '+1'); @endphp
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <div class="ob-phone-group">
                        <select id="phone_country_code" name="phone_country_code">
                            @forelse ($phoneCodes as $code)
                                <option value="{{ $code }}" @selected($currentPhoneCode === $code)>{{ $code }}</option>
                            @empty
                                <option value="+1" @selected($currentPhoneCode === '+1')>+1</option>
                            @endforelse
                        </select>
                        <input id="phone_number" name="phone_number" type="tel"
                               value="{{ old('phone_number', $admin->phone_number) }}"
                               inputmode="numeric" maxlength="10"
                               placeholder="Phone number"
                               class="form-control @error('phone_number') is-invalid @enderror">
                    </div>
                    @error('phone_country_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('phone_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 mb-1">
                    <label class="form-label">Profile Image</label>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:96px; height:96px; border-radius:50%; overflow:hidden; border:1px solid var(--ob-border, #ebe9f1); background:var(--ob-surface-2, #f8f8f8); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="bi bi-person" style="font-size:2.75rem; color:var(--ob-text-muted, #b9b9c3);"></i>
                            @endif
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openPhotoEditor('admin-avatar-editor')">
                                <i class="bi bi-camera"></i> {{ $avatarUrl ? 'Change photo' : 'Upload photo' }}
                            </button>
                            @if($avatarUrl)
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="document.getElementById('remove-admin-avatar-form').submit()">Remove</button>
                            @endif
                            <div class="text-muted small mt-1">JPG / PNG / WEBP, max 2 MB. Crop, rotate or use webcam.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex mt-2">
                <button type="submit" class="btn btn-primary me-1">Save</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>

        {{-- Hidden delete-avatar form (outside main form — nested forms are invalid HTML) --}}
        <form id="remove-admin-avatar-form" method="POST" action="{{ route('admin.profile.photo.delete') }}" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

{{-- Cropper modal for the avatar --}}
@include('partials._photo-editor', [
    'id'        => 'admin-avatar-editor',
    'title'     => 'Edit Profile Photo',
    'action'    => route('admin.profile.photo'),
    'fieldName' => 'avatar',
    'aspect'    => 1,
    'enableCam' => true,
])
@endsection

@push('scripts')
<script>
(function () {
    if (window.jQuery && typeof $.fn.select2 === 'function') {
        $('#phone_country_code').select2({
            width: '115px',
            dropdownParent: $(document.body),
        });
    }
})();
</script>
@endpush
