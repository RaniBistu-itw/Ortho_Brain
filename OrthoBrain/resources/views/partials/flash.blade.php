@if (session('success'))
    <div class="ob-flash alert alert-success" role="alert">
        <div class="alert-body">
            <i data-feather="check-circle" class="me-50"></i> {{ session('success') }}
        </div>
    </div>
@endif
@if (session('error'))
    <div class="ob-flash alert alert-danger" role="alert">
        <div class="alert-body">
            <i data-feather="alert-circle" class="me-50"></i> {{ session('error') }}
        </div>
    </div>
@endif
@if ($errors->any() && ! $errors->has('email'))
    <div class="ob-flash alert alert-danger" role="alert">
        <div class="alert-body">
            <strong>Please fix the errors below.</strong>
        </div>
    </div>
@endif
