@if (session('success'))
    <div class="ob-flash mb-4 px-4 py-3 rounded-md bg-green-50 border border-green-200 text-green-800 text-sm">
        <i class="bi bi-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="ob-flash mb-4 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm">
        <i class="bi bi-exclamation-circle mr-1"></i> {{ session('error') }}
    </div>
@endif
@if ($errors->any() && ! $errors->has('email'))
    <div class="ob-flash mb-4 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm">
        <strong>Please fix the errors below.</strong>
    </div>
@endif
