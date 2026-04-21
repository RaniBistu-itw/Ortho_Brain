@php
    /**
     * Reusable read/edit field for doctor cards.
     *
     * Required:
     *   $label
     *
     * Read mode (default):
     *   $value     — display string (coerced to '—' if null/empty)
     *   $colSpan   — 1 (col-md-6) or 2 (col-md-12)
     *
     * Edit mode:
     *   $editing   — true to render the input
     *   $type      — text | email | tel | select | textarea | toggle
     *   $name      — form field name
     *   $current   — current value for input
     *   $options   — assoc array [value => label] (for select)
     *   $required  — marks the label with *
     *   $maxlength — for text/email/tel
     *   $rows      — for textarea
     */
    $colSpan     = $colSpan ?? 1;
    $editing     = $editing ?? false;
    $type        = $type ?? 'text';
    $required    = !empty($required);
    $displayVal  = (isset($value) && $value !== null && $value !== '') ? $value : '—';
    $error       = $name ?? null ? $errors->first($name) : null;
@endphp
<div @class(['col-md-12' => $colSpan === 2, 'col-md-6' => $colSpan !== 2, 'mb-1'])>
    <label class="form-label">
        {{ $label }}@if ($required)<span class="text-danger">*</span>@endif
    </label>

    @if (! $editing)
        <div class="form-control bg-light-secondary" style="min-height: 38px;">{{ $displayVal }}</div>
    @else
        @switch($type)
            @case('select')
                <select name="{{ $name }}" class="form-select @if ($error) is-invalid @endif" @if ($required) required @endif>
                    @foreach (($options ?? []) as $val => $lbl)
                        <option value="{{ $val }}" @selected((string) ($current ?? '') === (string) $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @break

            @case('textarea')
                <textarea name="{{ $name }}" rows="{{ $rows ?? 3 }}"
                          class="form-control @if ($error) is-invalid @endif"
                          @if ($required) required @endif>{{ $current ?? '' }}</textarea>
                @break

            @case('toggle')
                <div class="form-check form-switch mt-50">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" class="form-check-input" id="fld-{{ $name }}"
                           name="{{ $name }}" value="1" @checked(!empty($current))>
                    <label class="form-check-label" for="fld-{{ $name }}">
                        {{ $toggleLabel ?? ($current ? 'Yes' : 'No') }}
                    </label>
                </div>
                @break

            @default
                <input type="{{ $type }}" name="{{ $name }}"
                       value="{{ $current ?? '' }}"
                       @if (!empty($maxlength)) maxlength="{{ $maxlength }}" @endif
                       class="form-control @if ($error) is-invalid @endif"
                       @if ($required) required @endif>
        @endswitch

        @if ($error)
            <div class="invalid-feedback d-block">{{ $error }}</div>
        @endif
    @endif
</div>
