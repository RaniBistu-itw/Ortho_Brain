@php
    $editing = ($editSection ?? null) === 'contact';
    $langLabel = $doctor->preferred_language ?: '—';
    $contactModeLabels = [
        'DOCTOR_ONLY'                  => 'Doctor only',
        'EMPLOYEE_OFFICE'              => 'Employee / office',
        'DOCTOR_AND_EMPLOYEE_OFFICE'   => 'Doctor + employee / office',
    ];
@endphp
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i data-feather="user" class="me-50"></i> Contact &amp; Identity
        </h4>
        @if (! $editing)
            <a href="{{ route('admin.doctors.show', ['doctor' => $doctor, 'edit' => 'contact']) }}#card-contact"
               class="btn btn-icon btn-sm btn-outline-primary" title="Edit">
                <i data-feather="edit-2"></i>
            </a>
        @endif
    </div>
    <div class="card-body pt-1" id="card-contact">
        <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="contact">
            <div class="row">
                @include('admin.doctors._partials.field', [
                    'label' => 'First Name', 'required' => true,
                    'editing' => $editing, 'name' => 'first_name',
                    'current' => old('first_name', $doctor->first_name),
                    'value'   => $doctor->first_name,
                    'maxlength' => 100,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Last Name', 'required' => true,
                    'editing' => $editing, 'name' => 'last_name',
                    'current' => old('last_name', $doctor->last_name),
                    'value'   => $doctor->last_name,
                    'maxlength' => 100,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Contact Email', 'required' => true,
                    'editing' => $editing, 'type' => 'email', 'name' => 'doctor_contact_email',
                    'current' => old('doctor_contact_email', $doctor->doctor_contact_email),
                    'value'   => $doctor->doctor_contact_email,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Cell Phone',
                    'editing' => $editing, 'type' => 'tel', 'name' => 'doctor_cell_phone',
                    'current' => old('doctor_cell_phone', $doctor->doctor_cell_phone),
                    'value'   => $doctor->doctor_cell_phone,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Other Email',
                    'editing' => $editing, 'type' => 'email', 'name' => 'other_email',
                    'current' => old('other_email', $doctor->other_email),
                    'value'   => $doctor->other_email,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Preferred Language', 'required' => true,
                    'editing' => $editing, 'name' => 'preferred_language',
                    'current' => old('preferred_language', $doctor->preferred_language),
                    'value'   => $langLabel,
                    'maxlength' => 50,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Preferred Contact Mode', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'preferred_contact_mode',
                    'current' => old('preferred_contact_mode', $doctor->preferred_contact_mode),
                    'value'   => $contactModeLabels[$doctor->preferred_contact_mode] ?? $doctor->preferred_contact_mode,
                    'options' => $contactModeLabels,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Currently Providing Ortho Services',
                    'editing' => $editing, 'type' => 'toggle', 'name' => 'currently_providing_ortho_services',
                    'current' => old('currently_providing_ortho_services', $doctor->currently_providing_ortho_services),
                    'value'   => $doctor->currently_providing_ortho_services ? 'Yes' : 'No',
                ])
            </div>

            @if ($editing)
                <div class="d-flex mt-1 pt-1 border-top">
                    <button type="submit" class="btn btn-primary me-1">
                        <i data-feather="save" class="me-50"></i> Save changes
                    </button>
                    <a href="{{ route('admin.doctors.show', $doctor) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            @endif
        </form>
    </div>
</div>
