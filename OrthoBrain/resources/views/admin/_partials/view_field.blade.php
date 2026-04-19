@php
    $colSpan = $span ?? 1;
    $displayValue = (isset($value) && $value !== null && $value !== '') ? $value : '—';
@endphp
<div @class(['md:col-span-2' => $colSpan === 2])>
    <label class="block text-sm font-medium text-[#5e5873] mb-1.5">
        {{ $label }}@if (!empty($required))<span class="text-red-500">*</span>@endif
    </label>
    <div class="w-full rounded-md border border-[#d8d6de] bg-[#f8f8f8] px-3 py-2 text-sm text-[#6e6b7b] min-h-[38px]">
        {{ $displayValue }}
    </div>
</div>
