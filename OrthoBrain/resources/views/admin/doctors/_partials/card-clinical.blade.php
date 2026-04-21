@php
    $editing = ($editSection ?? null) === 'clinical';
    $yesNo = ['YES' => 'Yes', 'NO' => 'No'];
    $attachmentStage = [
        'AT_STEP_1'     => 'At step 1',
        'AT_STEP_OTHER' => 'At a later step',
    ];
@endphp
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i data-feather="activity" class="me-50"></i> Clinical Preferences
        </h4>
        @if (! $editing)
            <a href="{{ route('admin.doctors.show', ['doctor' => $doctor, 'edit' => 'clinical']) }}#card-clinical"
               class="btn btn-icon btn-sm btn-outline-primary" title="Edit">
                <i data-feather="edit-2"></i>
            </a>
        @endif
    </div>
    <div class="card-body pt-1" id="card-clinical">
        <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="clinical">
            <div class="row">
                @include('admin.doctors._partials.field', [
                    'label' => 'Elastics / Bonded Buttons', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'elastics_bonded_buttons_pref',
                    'current' => old('elastics_bonded_buttons_pref', $doctor->elastics_bonded_buttons_pref),
                    'value'   => $yesNo[$doctor->elastics_bonded_buttons_pref] ?? $doctor->elastics_bonded_buttons_pref,
                    'options' => $yesNo,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Extractions if Suggested', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'extractions_if_suggested_pref',
                    'current' => old('extractions_if_suggested_pref', $doctor->extractions_if_suggested_pref),
                    'value'   => $yesNo[$doctor->extractions_if_suggested_pref] ?? $doctor->extractions_if_suggested_pref,
                    'options' => $yesNo,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Attachment Stage', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'attachment_stage_pref',
                    'current' => old('attachment_stage_pref', $doctor->attachment_stage_pref),
                    'value'   => $attachmentStage[$doctor->attachment_stage_pref] ?? $doctor->attachment_stage_pref,
                    'options' => $attachmentStage,
                    'colSpan' => 2,
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
