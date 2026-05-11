<div class="modal fade" id="rejectCaseModal" tabindex="-1" aria-labelledby="rejectCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="rejectCaseForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectCaseModalLabel">
                    <i data-feather="x-circle" class="me-50 text-danger"></i>
                    Unapprove Case
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-1">
                    The doctor will be notified. Provide a clear reason so they know what to correct or re-submit.
                </p>
                <label for="reject_case_reason" class="form-label">
                    Rejection Reason<span class="text-danger">*</span>
                </label>
                <textarea id="reject_case_reason" name="rejection_reason" rows="4"
                          minlength="10" maxlength="2000" required
                          class="form-control"
                          placeholder="Explain why this case is being unapproved..."></textarea>
                <div id="reject_case_reason_error" class="invalid-feedback" style="display:none;"></div>
                <div class="form-text mt-50">Minimum 10 characters.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i data-feather="x" class="me-25"></i> Unapprove Case
                </button>
            </div>
        </form>
    </div>
</div>
