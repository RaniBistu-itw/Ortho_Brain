@php
    $editing = ($editSection ?? null) === 'ortho';
    $selectedSpecialties   = old('specialties',          $doctor->specialties->pluck('id')->all());
    $selectedModalities    = old('modalities',           $doctor->modalities->pluck('id')->all());
    $selectedTreatmentMods = old('treatment_modalities', $doctor->treatmentModalities->pluck('id')->all());
    $selectedBuccals       = old('buccal_corridors',     $doctor->buccalCorridorOptions->pluck('id')->all());

    $pillGroup = function ($title, $name, $options, $selected) {
        return compact('title', 'name', 'options', 'selected');
    };

    $groups = [
        ['title' => 'Specialties',           'name' => 'specialties',          'options' => $allSpecialties,         'selected' => $selectedSpecialties,   'read' => $doctor->specialties],
        ['title' => 'Modalities',            'name' => 'modalities',           'options' => $allModalities,          'selected' => $selectedModalities,    'read' => $doctor->modalities],
        ['title' => 'Treatment Modalities',  'name' => 'treatment_modalities', 'options' => $allTreatmentModalities, 'selected' => $selectedTreatmentMods, 'read' => $doctor->treatmentModalities],
        ['title' => 'Buccal Corridor',       'name' => 'buccal_corridors',     'options' => $allBuccalCorridors,     'selected' => $selectedBuccals,       'read' => $doctor->buccalCorridorOptions],
    ];
@endphp
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <i data-feather="layers" class="me-50"></i> Ortho Services
        </h4>
        @if (! $editing)
            <a href="{{ route('admin.doctors.show', ['doctor' => $doctor, 'edit' => 'ortho']) }}#card-ortho"
               class="btn btn-icon btn-sm btn-outline-primary" title="Edit">
                <i data-feather="edit-2"></i>
            </a>
        @endif
    </div>
    <div class="card-body pt-1" id="card-ortho">
        <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="ortho">

            @foreach ($groups as $i => $group)
                <div @class(['mb-1', 'pb-1', 'border-bottom' => $i < count($groups) - 1])>
                    <label class="form-label d-block">{{ $group['title'] }}</label>

                    @if (! $editing)
                        @if ($group['read']->count())
                            @foreach ($group['read'] as $item)
                                <span class="badge rounded-pill badge-light-primary me-25 mb-25">{{ $item->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    @else
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($group['options'] as $opt)
                                @php $id = $group['name'] . '-' . $opt->id; @endphp
                                <input type="checkbox" class="btn-check"
                                       id="{{ $id }}"
                                       name="{{ $group['name'] }}[]"
                                       value="{{ $opt->id }}"
                                       @checked(in_array($opt->id, $group['selected'] ?? []))>
                                <label class="btn btn-sm btn-outline-primary" for="{{ $id }}">{{ $opt->name }}</label>
                            @endforeach
                            @if ($group['options']->isEmpty())
                                <span class="text-muted">No options available.</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

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
