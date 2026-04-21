@extends('layouts.admin')
@section('title', 'Doctor — Dr. ' . $doctor->first_name . ' ' . $doctor->last_name)
@section('page_title', 'Doctor Details')

@push('styles')
<style>
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
    // SweetAlert confirmation for simple approve/reactivate POST forms.
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
            if (result.value) {
                $form.data('confirmed', true).trigger('submit');
            }
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
</script>
@endpush
@endsection
