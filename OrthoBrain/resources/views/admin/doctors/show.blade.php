@extends('layouts.admin')
@section('title', 'Doctor — Dr. ' . $doctor->first_name . ' ' . $doctor->last_name)
@section('page_title', 'Doctor Details')

@push('styles')
<style>
    .ob-loading-overlay {
        position: absolute;
        inset: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.6);
        border-radius: inherit;
        pointer-events: none;
    }
    .dark-layout .ob-loading-overlay {
        background: rgba(40, 48, 70, 0.6);
    }

    .ob-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #ece9fb;
        color: #5e50ee;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        flex: 0 0 auto;
    }
    .ob-avatar.ob-avatar-lg {
        width: 80px; height: 80px;
        font-size: 1.6rem;
    }
    .ob-avatar img {
        width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
    }
    .ob-doctor-header .ob-doctor-actions .btn {
        white-space: nowrap;
    }
    .card.border-warning   { border-top-color: #ff9f43 !important; }
    .card.border-success   { border-top-color: #28c76f !important; }
    .card.border-danger    { border-top-color: #ea5455 !important; }
    .card.border-secondary { border-top-color: #82868b !important; }
</style>
@endpush

@php
    $editSection = request()->query('edit');
    $allowedSections = ['contact', 'preferences', 'clinical', 'ortho'];
    $editSection = in_array($editSection, $allowedSections, true) ? $editSection : null;

    // If validation errors exist, auto-open the relevant edit section.
    if (! $editSection && $errors->any()) {
        foreach ($allowedSections as $s) {
            foreach ($errors->keys() as $k) {
                if ($k === 'section' || str_contains($k, '.')) continue;
            }
        }
        // Fallback: use 'section' from old() if it was posted.
        if (old('section') && in_array(old('section'), $allowedSections, true)) {
            $editSection = old('section');
        }
    }
@endphp

@section('content')
<section id="doctor-detail">

    @include('admin.doctors._partials.header')

    <div class="row">
        <div class="col-lg-8">
            @include('admin.doctors._partials.card-contact',     ['editSection' => $editSection])
            @include('admin.doctors._partials.card-preferences', ['editSection' => $editSection])
            @include('admin.doctors._partials.card-clinical',    ['editSection' => $editSection])
            @include('admin.doctors._partials.card-ortho',       ['editSection' => $editSection])
            @include('admin.doctors._partials.card-practice-approvals', ['doctor' => $doctor])
        </div>
        <div class="col-lg-4">
            @include('admin.doctors._partials.card-approval')
            @include('admin.doctors._partials.card-practice')
            @include('admin.doctors._partials.card-addresses')

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-danger mb-50">
                        <i data-feather="alert-triangle" class="me-50"></i> Danger Zone
                    </h5>
                    <p class="text-muted mb-1" style="font-size: 0.85rem;">
                        Removing a doctor is a soft delete — records are recoverable by an engineer.
                    </p>
                    <form method="POST" action="{{ route('admin.doctors.destroy', $doctor) }}"
                          class="js-delete-form"
                          data-confirm="Remove Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}? This can be restored later.">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i data-feather="trash-2" class="me-25"></i> Remove doctor
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('admin.doctors._partials.reject-modal')
    @include('admin.doctors._partials.suspend-modal')
</section>

@push('scripts')
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function jsonPost(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data || {})
        }).then(function (r) {
            if (r.ok) return r.json();
            return r.json().catch(function () { return {}; }).then(function (d) {
                return Promise.reject(d.message || d.errors ? JSON.stringify(d.errors) : 'Request failed.');
            });
        });
    }

    // Show/hide a spinner overlay on any positioned container.
    function setCardLoading(el, on) {
        if (!el) return;
        if (on) {
            if (getComputedStyle(el).position === 'static') el.style.position = 'relative';
            var ov = document.createElement('div');
            ov.className = 'ob-loading-overlay';
            ov.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>';
            el.appendChild(ov);
            el.style.pointerEvents = 'none';
        } else {
            el.style.pointerEvents = '';
            var ov = el.querySelector('.ob-loading-overlay');
            if (ov) ov.remove();
        }
    }

    // Swap a button's content with a spinner; restore on done.
    function setBtnLoading(btn, on) {
        if (!btn) return;
        if (on) {
            btn.dataset.origHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-25" role="status" aria-hidden="true"></span>Working…';
            btn.disabled = true;
        } else {
            if (btn.dataset.origHtml !== undefined) btn.innerHTML = btn.dataset.origHtml;
            btn.disabled = false;
            delete btn.dataset.origHtml;
        }
    }

    // Fetch the page and swap only the doctor header card — avoids a full page reload.
    function reloadDoctorHeader() {
        var header = document.querySelector('.ob-doctor-header');
        if (!header) return;
        setCardLoading(header, true);
        fetch(window.location.href, { credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var doc       = new DOMParser().parseFromString(html, 'text/html');
                var newHeader = doc.querySelector('.ob-doctor-header');
                if (newHeader) {
                    header.replaceWith(newHeader);
                    if (window.feather) feather.replace();
                } else {
                    window.location.reload();
                }
            })
            .catch(function () { window.location.reload(); });
    }

    // Approve / Reactivate — js-confirm-form → AJAX instead of full form POST.
    $(document).on('submit', 'form.js-confirm-form', function (e) {
        var $form = $(this);
        if ($form.data('confirmed')) return;
        e.preventDefault();

        Swal.fire({
            title: $form.data('confirm-title') || 'Are you sure?',
            text:  $form.data('confirm-text')  || '',
            icon:  'question',
            showCancelButton: true,
            confirmButtonText: $form.data('confirm-btn') || 'Confirm',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn ' + ($form.data('confirm-class') || 'btn-primary'),
                cancelButton:  'btn btn-outline-secondary ms-1'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (!result.value) return;
            var btn = $form.find('[type="submit"]')[0];
            setBtnLoading(btn, true);
            jsonPost($form.attr('action'), {})
                .then(function () {
                    Swal.fire({ icon: 'success', title: 'Done!', timer: 900, showConfirmButton: false })
                        .then(function () { reloadDoctorHeader(); });
                })
                .catch(function (msg) {
                    setBtnLoading(btn, false);
                    Swal.fire({ icon: 'error', title: 'Action failed', text: String(msg) });
                });
        });
    });

    // Reject modal — AJAX submit with validation.
    var rejectForm = document.querySelector('#rejectModal form');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!rejectForm.checkValidity()) { rejectForm.reportValidity(); return; }
            var submitBtn = rejectForm.querySelector('[type="submit"]');
            var reason = (rejectForm.querySelector('[name="rejection_reason"]') || {}).value || '';
            setBtnLoading(submitBtn, true);
            jsonPost(rejectForm.getAttribute('action'), { rejection_reason: reason })
                .then(function () {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
                    if (modal) modal.hide();
                    Swal.fire({ icon: 'success', title: 'Doctor rejected.', timer: 900, showConfirmButton: false })
                        .then(function () { reloadDoctorHeader(); });
                })
                .catch(function (msg) {
                    setBtnLoading(submitBtn, false);
                    Swal.fire({ icon: 'error', title: 'Rejection failed', text: String(msg) });
                });
        });
    }

    // Suspend modal — AJAX submit.
    var suspendForm = document.querySelector('#suspendModal form');
    if (suspendForm) {
        suspendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = suspendForm.querySelector('[type="submit"]');
            setBtnLoading(submitBtn, true);
            jsonPost(suspendForm.getAttribute('action'), {})
                .then(function () {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('suspendModal'));
                    if (modal) modal.hide();
                    Swal.fire({ icon: 'success', title: 'Doctor suspended.', timer: 900, showConfirmButton: false })
                        .then(function () { reloadDoctorHeader(); });
                })
                .catch(function (msg) {
                    setBtnLoading(submitBtn, false);
                    Swal.fire({ icon: 'error', title: 'Suspension failed', text: String(msg) });
                });
        });
    }

    // Practice approvals card — partial reload after approve/reject.
    function reloadPracticeApprovalsCard() {
        var card = document.getElementById('practice-approvals-card');
        if (!card) return;
        setCardLoading(card, true);
        fetch(window.location.href, { credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                var doc     = new DOMParser().parseFromString(html, 'text/html');
                var newCard = doc.getElementById('practice-approvals-card');
                if (newCard) {
                    card.replaceWith(newCard);
                    if (window.feather) feather.replace();
                } else {
                    window.location.reload();
                }
            })
            .catch(function () { window.location.reload(); });
    }

    // Approve practice link — AJAX via updatePivotStatus endpoint.
    document.addEventListener('submit', function (e) {
        if (!e.target.matches('.js-practice-approve-form')) return;
        e.preventDefault();
        var form      = e.target;
        var statusUrl = form.getAttribute('data-status-url');
        var submitBtn = form.querySelector('[type="submit"]');
        setBtnLoading(submitBtn, true);
        jsonPost(statusUrl, { status: 'APPROVED' })
            .then(function () {
                Swal.fire({ icon: 'success', title: 'Practice approved.', timer: 900, showConfirmButton: false })
                    .then(function () { reloadPracticeApprovalsCard(); });
            })
            .catch(function (msg) {
                setBtnLoading(submitBtn, false);
                Swal.fire({ icon: 'error', title: 'Approval failed', text: String(msg) });
            });
    });

    // Reject practice link — AJAX via updatePivotStatus endpoint.
    document.addEventListener('submit', function (e) {
        if (!e.target.matches('.js-practice-reject-form')) return;
        e.preventDefault();
        var form      = e.target;
        var statusUrl = form.getAttribute('data-status-url');
        var modalId   = form.getAttribute('data-modal-id');
        var submitBtn = form.querySelector('[type="submit"]');
        var textarea  = form.querySelector('[name="rejection_reason"]');
        var reason    = textarea ? textarea.value : '';
        if (!reason.trim()) { if (textarea) textarea.reportValidity(); return; }
        setBtnLoading(submitBtn, true);
        jsonPost(statusUrl, { status: 'REJECTED', reason: reason })
            .then(function () {
                if (modalId) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modal) modal.hide();
                }
                Swal.fire({ icon: 'success', title: 'Practice rejected.', timer: 900, showConfirmButton: false })
                    .then(function () { reloadPracticeApprovalsCard(); });
            })
            .catch(function (msg) {
                setBtnLoading(submitBtn, false);
                Swal.fire({ icon: 'error', title: 'Rejection failed', text: String(msg) });
            });
    });

    // Smooth scroll to whichever card is in edit mode.
    $(function () {
        var hash = window.location.hash;
        if (hash && document.querySelector(hash)) {
            setTimeout(function () {
                document.querySelector(hash).scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 80);
        }
    });
})();
</script>
@endpush
@endsection
