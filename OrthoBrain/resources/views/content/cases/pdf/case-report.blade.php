<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Report — #{{ $case->case_code ?: $case->id }}</title>
    <style>
        @page { margin: 18mm 16mm 22mm 16mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5pt;
            color: #1f2937;
            line-height: 1.45;
            margin: 0;
        }
        h1, h2, h3 { color: #111827; margin: 0; }
        h1 { font-size: 18pt; font-weight: 700; }
        h2 { font-size: 13pt; font-weight: 700; padding: 6pt 8pt; background: #f3f4f6; border-left: 3pt solid #3B82F6; margin: 16pt 0 8pt 0; }
        h3 { font-size: 11pt; font-weight: 600; margin: 8pt 0 4pt 0; color: #374151; }
        .header {
            border-bottom: 2pt solid #3B82F6;
            padding-bottom: 8pt;
            margin-bottom: 12pt;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; padding: 0; }
        .badge {
            display: inline-block;
            padding: 2pt 8pt;
            border-radius: 10pt;
            font-size: 8.5pt;
            font-weight: 600;
            background: #DBEAFE;
            color: #1E40AF;
        }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 8pt; }
        .meta-table td { padding: 4pt 6pt; vertical-align: top; }
        .meta-table .label { color: #6b7280; font-size: 9pt; width: 32%; }
        .meta-table .value { font-weight: 500; }
        table.kv {
            width: 100%;
            border-collapse: collapse;
            margin: 4pt 0 6pt 0;
        }
        table.kv td { padding: 3pt 6pt; vertical-align: top; border-bottom: 1pt solid #f3f4f6; }
        table.kv td.k { color: #6b7280; width: 38%; font-size: 9.5pt; }
        table.kv td.v { font-weight: 500; }
        .empty { color: #9ca3af; font-style: italic; font-size: 9.5pt; }
        .footer {
            position: fixed;
            bottom: -14mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1pt solid #e5e7eb;
            padding-top: 4pt;
        }
        .yes { color: #059669; font-weight: 600; }
        .no  { color: #9ca3af; }
        ul.tooth-list { margin: 2pt 0 0 14pt; padding: 0; font-size: 9.5pt; }
        ul.tooth-list li { margin: 0; padding: 1pt 0; }
        .row2 { width: 100%; border-collapse: collapse; }
        .row2 td { width: 50%; padding: 0 4pt 0 0; vertical-align: top; }
        .row2 td + td { padding: 0 0 0 4pt; }
    </style>
</head>
<body>

@php
    $patient   = $clientState['patientInformation'] ?? [];
    $additional = $clientState['additionalInformation'] ?? [];
    $smilePlan = $clientState['perfectSmilePlan'] ?? [];
    $impressions = $clientState['impressions'] ?? [];
    $shipping  = $clientState['shippingAddress'] ?? [];
    $submitOrder = $clientState['submitOrder'] ?? [];
    $rx        = $case->prescription;

    $fmtBool = fn ($v) => $v ? '<span class="yes">Yes</span>' : '<span class="no">No</span>';
    $fmtVal  = fn ($v, $fallback = 'Not yet captured') => filled($v) ? e((string) $v) : '<span class="empty">' . e($fallback) . '</span>';
@endphp

{{-- ── Header ────────────────────────────────────────────────────────────── --}}
<div class="header">
    <table><tr>
        <td style="width:65%;">
            <h1>Case Report</h1>
            <div style="margin-top:4pt; color:#6b7280; font-size:10pt;">
                Case <strong>#{{ $case->case_code ?: $case->id }}</strong> ·
                <span class="badge">{{ $case->status === 'REJECTED' ? 'Unapproved' : $case->status }}</span>
            </div>
        </td>
        <td style="width:35%; text-align:right; color:#6b7280; font-size:9pt;">
            Generated {{ $generatedAt->format('M j, Y · H:i') }}<br>
            @if($case->submitted_at)
                Submitted {{ $case->submitted_at->format('M j, Y') }}
            @else
                Not submitted yet
            @endif
        </td>
    </tr></table>
</div>

{{-- ── Doctor + Practice ─────────────────────────────────────────────────── --}}
<table class="meta-table">
    <tr>
        <td class="label">Doctor</td>
        <td class="value">
            {{ trim($case->doctor->first_name . ' ' . $case->doctor->last_name) }}
        </td>
        <td class="label">Practice</td>
        <td class="value">{{ optional($case->practice)->name ?? optional($case->doctor->practice)->name ?? '—' }}</td>
    </tr>
</table>

{{-- ── 1. Patient Information ────────────────────────────────────────────── --}}
<h2>1. Patient Information</h2>
<table class="kv">
    <tr><td class="k">First name</td><td class="v">{!! $fmtVal($patient['firstName'] ?? null) !!}</td></tr>
    <tr><td class="k">Last name</td><td class="v">{!! $fmtVal($patient['lastName'] ?? null) !!}</td></tr>
    <tr><td class="k">Date of birth</td><td class="v">{!! $fmtVal($patient['dateOfBirth'] ?? null) !!}</td></tr>
    <tr><td class="k">Biological gender</td><td class="v">{!! $fmtVal($patient['biologicalGender'] ?? null) !!}@if(!empty($patient['biologicalGenderOther'])) ({{ $patient['biologicalGenderOther'] }}) @endif</td></tr>
    <tr><td class="k">Patient chart ID</td><td class="v">{!! $fmtVal($patient['patientChartId'] ?? null) !!}</td></tr>
    <tr><td class="k">Chief complaint</td><td class="v">{!! $fmtVal($patient['chiefComplaint'] ?? null) !!}</td></tr>
</table>

{{-- ── 2. Prescription ───────────────────────────────────────────────────── --}}
<h2>2. Prescription</h2>
@if($rx)
<table class="kv">
    <tr><td class="k">Arches</td><td class="v">{{ $rx->arches ?: '—' }}</td></tr>
    <tr>
        <td class="k">IPR</td>
        <td class="v">{!! $fmtBool($rx->ipr_enabled) !!}@if($rx->ipr_enabled) — {{ $rx->ipr_value ?: 'No detail' }} @endif</td>
    </tr>
    <tr>
        <td class="k">Attachments</td>
        <td class="v">
            {!! $fmtBool($rx->attachments_enabled) !!}
            @if($rx->attachments_enabled)
                — {{ $rx->attachments_value ?: 'No detail' }}
                @if($rx->attachments_specific_step) (step {{ $rx->attachments_specific_step }}) @endif
            @endif
        </td>
    </tr>
    <tr>
        <td class="k">Elastics</td>
        <td class="v">{!! $fmtBool($rx->elastics_enabled) !!}@if($rx->elastics_enabled) — {{ $rx->elastics_value ?: 'No detail' }} @endif</td>
    </tr>
    <tr>
        <td class="k">Extractions</td>
        <td class="v">{!! $fmtBool($rx->extractions_enabled) !!}@if($rx->extractions_enabled) — {{ $rx->extractions_value ?: 'No detail' }} @endif</td>
    </tr>
    <tr>
        <td class="k">Tooth movement mode</td>
        <td class="v">{{ $rx->tooth_movement_mode ?: '—' }}</td>
    </tr>
    <tr>
        <td class="k">Attachment restrictions mode</td>
        <td class="v">{{ $rx->attachment_restrictions_mode ?: '—' }}</td>
    </tr>
    @if($rx->additional_comments)
    <tr><td class="k">Additional comments</td><td class="v">{{ $rx->additional_comments }}</td></tr>
    @endif
    <tr>
        <td class="k">Future restorative work</td>
        <td class="v">
            {!! $fmtBool($rx->future_restorative_work_has_work) !!}
            @if($rx->future_restorative_work_has_work && $rx->future_restorative_work_explanation)
                — {{ $rx->future_restorative_work_explanation }}
            @endif
        </td>
    </tr>
</table>

@php
    $movement   = $rx->toothRestrictions->where('restriction_type', 'MOVEMENT');
    $attachment = $rx->toothRestrictions->where('restriction_type', 'ATTACHMENT');
@endphp
@if($movement->isNotEmpty() || $attachment->isNotEmpty())
<table class="row2"><tr>
    <td>
        <h3>Movement restrictions</h3>
        @if($movement->isEmpty())
            <span class="empty">None</span>
        @else
            <ul class="tooth-list">
                @foreach($movement as $r)
                    <li>Tooth {{ $r->tooth_number }} — {{ $r->detail ?? '—' }}</li>
                @endforeach
            </ul>
        @endif
    </td>
    <td>
        <h3>Attachment restrictions</h3>
        @if($attachment->isEmpty())
            <span class="empty">None</span>
        @else
            <ul class="tooth-list">
                @foreach($attachment as $r)
                    <li>Tooth {{ $r->tooth_number }} — {{ $r->detail ?? '—' }}</li>
                @endforeach
            </ul>
        @endif
    </td>
</tr></table>
@endif
@else
    <p class="empty">No prescription captured yet.</p>
@endif

{{-- ── 3. Additional Information ─────────────────────────────────────────── --}}
<h2>3. Additional Information</h2>
@if(empty($additional))
    <p class="empty">Not yet captured.</p>
@else
<table class="kv">
    @foreach($additional as $k => $v)
        @if(filled($v) && !is_array($v))
            <tr><td class="k">{{ \Illuminate\Support\Str::headline($k) }}</td><td class="v">{{ $v }}</td></tr>
        @endif
    @endforeach
</table>
@endif

{{-- ── 4. Perfect Smile Plan ─────────────────────────────────────────────── --}}
<h2>4. Perfect Smile Plan</h2>
@if(empty($smilePlan) || empty($smilePlan['narrative'] ?? null))
    <p class="empty">Not yet captured.</p>
@else
    <p>{{ $smilePlan['narrative'] }}</p>
@endif

{{-- ── 5. Impressions ────────────────────────────────────────────────────── --}}
<h2>5. Impressions</h2>
@if(empty($impressions))
    <p class="empty">Not yet captured.</p>
@else
<table class="kv">
    @foreach($impressions as $k => $v)
        @if(filled($v) && !is_array($v))
            <tr><td class="k">{{ \Illuminate\Support\Str::headline($k) }}</td><td class="v">{{ $v }}</td></tr>
        @endif
    @endforeach
</table>
@endif

{{-- ── 6. Photographs / X-Rays — deferred ───────────────────────────────── --}}
<h2>6. Photographs &amp; X-Rays</h2>
<p class="empty">
    Image attachments are not embedded in v1 of this report. Server-side persistence
    for photographs and X-rays is pending — when the schema lands, this section will
    include thumbnails and AI-classification metadata.
</p>

{{-- ── 7. Shipping Address ──────────────────────────────────────────────── --}}
<h2>7. Shipping Address</h2>
@if(empty($shipping) || empty($shipping['streetAddress'] ?? null))
    <p class="empty">Not yet captured.</p>
@else
<table class="kv">
    <tr><td class="k">Street address</td><td class="v">{{ $shipping['streetAddress'] ?? '' }}</td></tr>
    @if(!empty($shipping['streetAddress2']))
        <tr><td class="k">Street address 2</td><td class="v">{{ $shipping['streetAddress2'] }}</td></tr>
    @endif
    <tr><td class="k">City</td><td class="v">{{ $shipping['city'] ?? '—' }}</td></tr>
    <tr><td class="k">State / Province</td><td class="v">{{ $shipping['state'] ?? '—' }}</td></tr>
    <tr><td class="k">Country</td><td class="v">{{ $shipping['country'] ?? '—' }}</td></tr>
    <tr><td class="k">ZIP / Postal code</td><td class="v">{{ $shipping['zipId'] ?? '—' }}</td></tr>
</table>
@endif

{{-- ── 8. Submit Order ──────────────────────────────────────────────────── --}}
<h2>8. Submit Order</h2>
<table class="kv">
    <tr><td class="k">Status</td><td class="v">{{ $case->status === 'REJECTED' ? 'Unapproved' : $case->status }}</td></tr>
    @if($case->submitted_at)
        <tr><td class="k">Submitted at</td><td class="v">{{ $case->submitted_at->format('M j, Y · H:i') }}</td></tr>
    @endif
    @if(!empty($submitOrder))
        @foreach($submitOrder as $k => $v)
            @if(filled($v) && !is_array($v))
                <tr><td class="k">{{ \Illuminate\Support\Str::headline($k) }}</td><td class="v">{{ $v }}</td></tr>
            @endif
        @endforeach
    @endif
</table>

<div class="footer">
    OrthoBrain · Case #{{ $case->case_code ?: $case->id }} · Generated {{ $generatedAt->format('M j, Y H:i') }}
</div>

</body>
</html>
