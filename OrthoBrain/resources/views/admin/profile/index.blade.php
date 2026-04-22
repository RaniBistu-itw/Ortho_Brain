@extends('layouts.admin')
@section('title', 'My Profile')
@section('page_title', 'My Profile')

@section('content')
@php $avatarUrl = $admin->avatarUrl(); @endphp

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
                    <label for="phone" class="form-label">Phone Number</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i data-feather="phone"></i></span>
                        <input id="phone" type="text" name="phone" maxlength="20"
                               value="{{ old('phone', $admin->phone) }}"
                               placeholder="XXX-XXX-XXXX"
                               class="form-control @error('phone') is-invalid @enderror">
                    </div>
                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 mb-1">
                    <label class="form-label">Profile Image</label>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:96px; height:96px; border-radius:50%; overflow:hidden; border:1px solid #ebe9f1; background:#f8f8f8; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <i class="bi bi-person" style="font-size:2.75rem; color:#b9b9c3;"></i>
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
                <button type="submit" class="btn btn-success me-1">Save</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>

        {{-- Hidden delete-avatar form (kept outside the main form) --}}
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
