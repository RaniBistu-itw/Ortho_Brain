@php
    $editing = ($editSection ?? null) === 'preferences';

    $toothSystems = [
        'UNIVERSAL'     => 'Universal',
        'FDI'           => 'FDI',
        'PALMER'        => 'Palmer',
        'INTERNATIONAL' => 'International',
    ];
    $smileArc = [
        'DEFER'                 => 'Defer to OrthoBrain',
        'LATERALS_0_5MM_SHORTER'=> 'Laterals 0.5 mm shorter than centrals',
        'LATERALS_SAME_LENGTH'  => 'Laterals same length as centrals',
    ];
    $lateralIncisors = [
        'DEFER'                       => 'Defer to OrthoBrain',
        'IPR_LOWER_CAMOUFLAGE'        => 'IPR on lower + camouflage',
        'LEAVE_SPACING_MESIAL_DISTAL' => 'Leave mesial/distal spacing',
    ];
    $mixedDentition = [
        'DEFER'         => 'Defer to OrthoBrain',
        'NO_APPLIANCES' => 'No appliances',
    ];
    $extractions = [
        'DEFER'          => 'Defer to OrthoBrain',
        'NO_EXTRACTIONS' => 'No extractions',
    ];
    $iprProtocol = [
        'DEFER'  => 'Defer to OrthoBrain',
        'NO_IPR' => 'No IPR',
        'OTHER'  => 'Other (see note)',
    ];
@endphp
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i data-feather="sliders" class="me-50"></i> Treatment Preferences
        </h4>
        @if (! $editing)
            <a href="{{ route('admin.doctors.show', ['doctor' => $doctor, 'edit' => 'preferences']) }}#card-preferences"
               class="btn btn-icon btn-sm btn-outline-primary" title="Edit">
                <i data-feather="edit-2"></i>
            </a>
        @endif
    </div>
    <div class="card-body pt-1" id="card-preferences">
        <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="preferences">
            <div class="row">
                @include('admin.doctors._partials.field', [
                    'label' => 'Tooth Numbering System', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'preferred_tooth_numbering_system',
                    'current' => old('preferred_tooth_numbering_system', $doctor->preferred_tooth_numbering_system),
                    'value'   => $toothSystems[$doctor->preferred_tooth_numbering_system] ?? $doctor->preferred_tooth_numbering_system,
                    'options' => $toothSystems,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Smile Arc', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'smile_arc_pref',
                    'current' => old('smile_arc_pref', $doctor->smile_arc_pref),
                    'value'   => $smileArc[$doctor->smile_arc_pref] ?? $doctor->smile_arc_pref,
                    'options' => $smileArc,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Small Lateral Incisors', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'small_lateral_incisors_pref',
                    'current' => old('small_lateral_incisors_pref', $doctor->small_lateral_incisors_pref),
                    'value'   => $lateralIncisors[$doctor->small_lateral_incisors_pref] ?? $doctor->small_lateral_incisors_pref,
                    'options' => $lateralIncisors,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Mixed Dentition', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'mixed_dentition_pref',
                    'current' => old('mixed_dentition_pref', $doctor->mixed_dentition_pref),
                    'value'   => $mixedDentition[$doctor->mixed_dentition_pref] ?? $doctor->mixed_dentition_pref,
                    'options' => $mixedDentition,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'Orthodontic Extractions', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'orthodontic_extractions_pref',
                    'current' => old('orthodontic_extractions_pref', $doctor->orthodontic_extractions_pref),
                    'value'   => $extractions[$doctor->orthodontic_extractions_pref] ?? $doctor->orthodontic_extractions_pref,
                    'options' => $extractions,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'IPR Protocol', 'required' => true,
                    'editing' => $editing, 'type' => 'select', 'name' => 'ipr_protocol_pref',
                    'current' => old('ipr_protocol_pref', $doctor->ipr_protocol_pref),
                    'value'   => $iprProtocol[$doctor->ipr_protocol_pref] ?? $doctor->ipr_protocol_pref,
                    'options' => $iprProtocol,
                ])
                @include('admin.doctors._partials.field', [
                    'label' => 'IPR Protocol — Other Note',
                    'editing' => $editing, 'type' => 'textarea', 'name' => 'ipr_protocol_other_note',
                    'current' => old('ipr_protocol_other_note', $doctor->ipr_protocol_other_note),
                    'value'   => $doctor->ipr_protocol_other_note,
                    'colSpan' => 2, 'rows' => 2,
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
