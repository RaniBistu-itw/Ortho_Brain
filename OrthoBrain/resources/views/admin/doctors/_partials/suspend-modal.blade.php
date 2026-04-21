<div class="modal fade" id="suspendModal" tabindex="-1" aria-labelledby="suspendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.doctors.suspend', $doctor) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="suspendModalLabel">
                    <i data-feather="pause-circle" class="me-50 text-warning"></i>
                    Suspend Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Suspending this doctor revokes access to their account. You can reactivate them later
                    from this same page.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <i data-feather="pause" class="me-25"></i> Suspend
                </button>
            </div>
        </form>
    </div>
</div>
