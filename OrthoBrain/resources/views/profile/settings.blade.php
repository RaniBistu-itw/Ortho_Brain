@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">Change Password</h4>
    </div>
    <div class="card-body pt-2">
        <form method="POST" action="/dev/profile/settings">
            @csrf

            <div class="row">
                <div class="col-12 mb-1">
                    <label for="old_password" class="form-label">Old Password<span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge form-password-toggle">
                        <span class="input-group-text"><i data-feather="lock"></i></span>
                        <input type="password" id="old_password" name="old_password" class="form-control" placeholder="............" required>
                        <span class="input-group-text cursor-pointer" onclick="const i=document.getElementById('old_password'); i.type=i.type==='password'?'text':'password';">
                            <i data-feather="eye"></i>
                        </span>
                    </div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="new_password" class="form-label">New Password<span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge form-password-toggle">
                        <span class="input-group-text"><i data-feather="lock"></i></span>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="............" required>
                        <span class="input-group-text cursor-pointer" onclick="const i=document.getElementById('new_password'); i.type=i.type==='password'?'text':'password';">
                            <i data-feather="eye"></i>
                        </span>
                    </div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="new_password_confirmation" class="form-label">Confirm New Password<span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge form-password-toggle">
                        <span class="input-group-text"><i data-feather="lock"></i></span>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" placeholder="............" required>
                        <span class="input-group-text cursor-pointer" onclick="const i=document.getElementById('new_password_confirmation'); i.type=i.type==='password'?'text':'password';">
                            <i data-feather="eye"></i>
                        </span>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success" role="alert"><div class="alert-body">{{ session('success') }}</div></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="alert-body">
                        @foreach ($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
                    </div>
                </div>
            @endif

            <div class="d-flex mt-2">
                <button type="submit" class="btn btn-success me-1">Update Password</button>
                <a href="/dev/cases/list" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
