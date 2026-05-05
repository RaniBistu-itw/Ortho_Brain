@php
    $colSpan = $span ?? 1;
    $displayValue = (isset($value) && $value !== null && $value !== '') ? $value : '—';
@endphp
<div @class(['col-md-12' => $colSpan === 2, 'col-md-6' => $colSpan !== 2])>
    <div class="mb-1">
        <label class="form-label">
            {{ $label }}@if (!empty($required))<span class="text-danger">*</span>@endif
        </label>
        <div class="form-control bg-light-secondary" style="min-height: 38px;">{{ $displayValue }}</div>
    </div>
</div>
