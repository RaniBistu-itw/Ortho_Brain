@php
    $rejectReasonError = $errors->first('rejection_reason');
    $openOnLoad = (bool) $rejectReasonError;
@endphp
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.doctors.reject', $doctor) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i data-feather="x-circle" class="me-50 text-danger"></i>
                    Reject Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-1">
                    The doctor will be notified. Provide a clear reason so they know what to correct or address next.
                </p>
                <label for="rejection_reason" class="form-label">
                    Rejection Reason<span class="text-danger">*</span>
                </label>
                <textarea id="rejection_reason" name="rejection_reason" rows="4"
                          minlength="10" maxlength="2000" required
                          class="form-control @if ($rejectReasonError) is-invalid @endif"
                          placeholder="Explain why this application is being rejected...">{{ old('rejection_reason') }}</textarea>
                @if ($rejectReasonError)
                    <div class="invalid-feedback d-block">{{ $rejectReasonError }}</div>
                @endif
                <div class="form-text mt-50">Minimum 10 characters.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i data-feather="x" class="me-25"></i> Reject Doctor
                </button>
            </div>
        </form>
    </div>
</div>

@if ($openOnLoad)
    @push('scripts')
    <script>
        $(function () {
            var m = document.getElementById('rejectModal');
            if (m && window.bootstrap) new bootstrap.Modal(m).show();
        });
    </script>
    @endpush
@endif
