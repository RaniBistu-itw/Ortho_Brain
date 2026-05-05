@php
  $adminMode = $adminMode ?? false;
  $caseRow = $caseRow ?? null;
  $caseDoctor = $caseDoctor ?? null;
  $scanners = $scanners ?? collect();
  $statusOptions = $statusOptions ?? ['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED'];
  $statusLabels = $statusLabels ?? [
      'DRAFT'     => 'Draft',
      'SUBMITTED' => 'Submitted',
      'IN_REVIEW' => 'In Review',
      'APPROVED'  => 'Approved',
      'REJECTED'  => 'Unapproved',
  ];
  $apiBase = $adminMode ? '/admin/cases' : '/dev/cases';
  $backUrl = $adminMode ? route('admin.cases.index') : route('doctor.cases.index');
@endphp

@extends($adminMode ? 'layouts.admin' : 'layouts.app')

@section('title', isset($id) ? 'Edit Case' : 'Add Case')
@section('page_title', isset($id) ? ($adminMode ? 'Case #' . $id : 'Edit Case') : 'Add Case')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/base/pages/add-case.css') }}?v={{ @filemtime(public_path('css/base/pages/add-case.css')) ?: time() }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

@php
  $caseId = $id ?? null;
  $sections = [
    ['id' => 'patient-information',    'label' => 'Patient Information',    'subtitle' => 'Personal Details',         'icon' => 'user',       'placeholder' => false],
    ['id' => 'prescription',           'label' => 'Prescription',           'subtitle' => 'Prescription Information', 'icon' => 'file-text',  'placeholder' => false],
    ['id' => 'additional-information', 'label' => 'Additional Information', 'subtitle' => 'Additional Information',   'icon' => 'plus-circle','placeholder' => false],
    ['id' => 'perfect-smile-plan',     'label' => 'Perfect Smile Plan',     'subtitle' => 'AI-assisted narrative',     'icon' => 'smile',      'placeholder' => false, 'adminOnly' => true],
    ['id' => 'impressions',            'label' => 'Impressions',            'subtitle' => 'Impression Method',        'icon' => 'check-square','placeholder' => false],
    ['id' => 'photographs',            'label' => 'Photographs',            'subtitle' => 'Upload Photographs',       'icon' => 'image',      'placeholder' => false],
    ['id' => 'xrays',                  'label' => 'X-Rays',                 'subtitle' => 'Upload X-Ray photos',      'icon' => 'radio',      'placeholder' => false],
    ['id' => 'additional-records',     'label' => 'Additional Records',     'subtitle' => 'Additional Records',       'icon' => 'list',       'placeholder' => true],
    ['id' => 'shipping-address',       'label' => 'Shipping Address',       'subtitle' => 'Shipping address',         'icon' => 'map-pin',    'placeholder' => false],
    ['id' => 'submit-order',           'label' => 'Submit Order',           'subtitle' => 'Submit Order',             'icon' => 'send',       'placeholder' => false],
  ];
  $sections = array_values(array_filter($sections, fn ($s) => $adminMode || empty($s['adminOnly'])));
@endphp

@section('content')
<div class="add-case-page" data-case-id="{{ $caseId ?? 'new' }}">

  {{-- Sticky Top Bar --}}
  <div class="add-case-topbar card mb-0">
    <div class="card-body py-75 px-1 d-flex align-items-center gap-75">
      <a href="{{ $backUrl }}" class="btn btn-danger btn-sm add-case-topbar__back" title="Back to Cases">
        <i data-feather="arrow-left"></i>
      </a>

      <span id="case-id-badge" class="badge bg-light-primary ms-50" @if(!$caseId) style="display:none" @endif>
        Case #<span id="case-id-badge-num">{{ $caseId ?? '' }}</span>
      </span>

      @if($adminMode && $caseRow && $caseRow->doctor)
        <span class="text-muted font-small-2 ms-1">
          {{ trim($caseRow->doctor->first_name . ' ' . $caseRow->doctor->last_name) }}
          @if($caseRow->doctor->practice) · {{ $caseRow->doctor->practice->name }} @endif
        </span>
      @endif

      <div class="flex-fill"></div>

      <span class="add-case-topbar__autosave text-muted font-small-2" id="autosave-indicator"></span>

      @if($adminMode && $caseRow)
        @php
          // Show only the current status + the legal next-states the
          // controller's transition machine accepts. Current status is
          // prepended so the dropdown reflects where the case is now;
          // a "transition to self" submit is a no-op handled server-side.
          $dropdownStatuses = array_values(array_unique(array_merge(
              [$caseRow->status],
              $allowedTransitions ?? []
          )));
        @endphp
        <div class="d-flex align-items-center gap-50 add-case-topbar__group">
          <select class="form-select form-select-sm" id="admin-status-select" style="width:auto;" aria-label="Case status">
            @foreach($dropdownStatuses as $s)
              <option value="{{ $s }}" @selected($caseRow->status === $s)>{{ $statusLabels[$s] ?? $s }}</option>
            @endforeach
          </select>
          <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-25" id="btn-admin-save-status" title="Save status change">
            <i data-feather="check"></i> Save Status
          </button>
        </div>
        <span class="add-case-topbar__divider"></span>
      @endif

      <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-25" id="btn-export-pdf"
              title="@if($caseId) Export this case as PDF @else Save the case first to enable PDF export @endif"
              @unless($caseId) disabled @endunless
              onclick="window.AddCaseExport && window.AddCaseExport.exportPdf()">
        <i data-feather="file-text"></i> Export PDF
      </button>

      <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-25" id="btn-save-draft" title="Save draft now">
        <i data-feather="save"></i> Save Draft
      </button>

      @unless($adminMode)
        <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-25" id="btn-submit" title="Submit case for review"
                onclick="window.AddCaseSubmit.submit()">
          <i data-feather="check"></i> Submit
        </button>
      @endunless
    </div>
  </div>

  {{-- Three-column layout --}}
  <div class="add-case-layout d-flex">

    {{-- Middle Rail (Sticky Section Nav) --}}
    <aside class="add-case-rail d-none d-xl-block" id="case-section-rail">
      <ul class="add-case-rail__list list-unstyled mb-0">
        @foreach($sections as $section)
          <li class="add-case-rail__item{{ $section['placeholder'] ? ' add-case-rail__item--placeholder' : '' }}"
              data-section="{{ $section['id'] }}"
              role="button"
              onclick="caseScrollSpy.scrollTo('{{ $section['id'] }}')">
            <div class="add-case-rail__icon-tile">
              <i data-feather="{{ $section['icon'] }}"></i>
            </div>
            <div class="add-case-rail__text">
              <span class="add-case-rail__label">{{ $section['label'] }}</span>
              <span class="add-case-rail__subtitle">{{ $section['subtitle'] }}</span>
            </div>
          </li>
        @endforeach
      </ul>
    </aside>

    {{-- Tablet dropdown nav --}}
    <div class="add-case-tab-nav d-xl-none w-100 mb-1" id="case-tab-nav">
      <select class="form-select" id="case-section-select" onchange="caseScrollSpy.scrollTo(this.value)">
        @foreach($sections as $section)
          <option value="{{ $section['id'] }}">{{ $section['label'] }}</option>
        @endforeach
      </select>
    </div>

    {{-- Form Pane --}}
    <main class="add-case-form-pane flex-fill" id="case-form-pane">
      @include('content.cases.sections.patient-information')
      @include('content.cases.sections.prescription')
      @include('content.cases.sections.additional-information')
      @if($adminMode)
        @include('content.cases.sections.perfect-smile-plan')
      @endif
      @include('content.cases.sections.impressions')
      @include('content.cases.sections.photographs')
      @include('content.cases.sections.xrays')
      @include('content.cases.sections.additional-records')
      @include('content.cases.sections.shipping-address')
      @include('content.cases.sections.submit-order')

      {{-- Shared crop modal — rendered once, outside Alpine scope, managed by CropModalController --}}
      @include('content.cases.components.crop-modal')

      {{-- Submit confirmation modal — rendered once, managed by AddCaseSubmit --}}
      @include('content.cases.components.submit-confirm-modal')
    </main>

  </div>

  {{-- Shared undo-toast for media-tile removal --}}
  <div id="media-undo-toast" class="media-undo-toast" role="status" aria-live="polite" aria-hidden="true">
    <span class="media-undo-toast__label"></span>
    <button type="button" class="media-undo-toast__undo">Undo</button>
  </div>
</div>
@endsection

@push('scripts')
  @php
    $activePractice = \App\Support\ActivePractice::get();
    if ($activePractice) {
        $activePractice->loadMissing('zipcode', 'city', 'state', 'country');
    }
    $activePracticeAddress = $activePractice ? [
        'practiceName'   => $activePractice->name,
        'streetAddress'  => $activePractice->street_address_1,
        'streetAddress2' => $activePractice->street_address_2,
        'zipId'          => $activePractice->zip_id,
        'zipCode'        => $activePractice->zipcode?->code,
        'cityId'         => $activePractice->city_id,
        'city'           => $activePractice->city?->name,
        'stateId'        => $activePractice->state_id,
        'state'          => $activePractice->state?->name,
        'countryId'      => $activePractice->country_id,
        'country'        => $activePractice->country?->country_code,
    ] : null;

    // Country list stays inlined — small (< 30 rows) and used by the
    // country dropdown immediately on render. The Zipcode list, which
    // used to ship here too (~600 rows / 102 KB), now lazy-loads via
    // GET /dev/zipcodes/search?q=... — see shipping-address.js.
    $countryEntries = \App\Models\Country::where('status', 'ACTIVE')
        ->orderBy('name')
        ->get(['id', 'name', 'country_code'])
        ->map(fn ($c) => [
            'id'   => $c->id,
            'code' => $c->country_code,
            'name' => $c->name,
        ])
        ->values();
  @endphp
  <script>
    window.CASE_ID = '{{ $caseId ?? 'new' }}';
    window.__addCasePrefill = @json($prescriptionPrefill ?? null);
    window.__caseMediaPrefill = @json($caseMedia ?? []);
    window.__patientPrefill = @json($patientPrefill ?? null);
    window.__additionalInfoPrefill = @json($additionalInfoPrefill ?? null);
    window.__shippingAddressPrefill = @json($shippingAddressPrefill ?? null);
    window.__impressionsPrefill = @json($impressionsPrefill ?? null);
    window.__submitOrderPrefill = @json($submitOrderPrefill ?? null);
    window.CASE_API_BASE = @json($apiBase);
    window.CASE_ADMIN_MODE = @json((bool) $adminMode);
    window.ACTIVE_PRACTICE_ADDRESS = @json($activePracticeAddress);
    window.COUNTRY_ENTRIES = @json($countryEntries);
  </script>
  {{-- Alpine.js is bundled with @livewireScripts (Livewire 3); loading the
       standalone CDN here causes "Detected multiple instances of Alpine"
       which corrupts wire:navigate's hover-prefetch cleanup walker and
       leaves the navigation pipeline dead — the back button + section rail
       silently stop working as a result. The x-data factories used by the
       sections (impressionsSection, prescriptionSection, photographsSection,
       xraysSection, shippingAddressSection, submitOrderSection) are
       registered as globals on `window` by their respective JS files, so
       Livewire's Alpine resolves them exactly the same way. --}}
  {{-- Cropper.js v1 — required by the shared crop modal (Photographs + X-Rays). --}}
  <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
  {{-- heic2any — HEIC → JPEG conversion for preview on Photograph / X-Ray uploads. --}}
  <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
  <script src="{{ asset('js/scripts/cases/case-api.js') }}?v={{ @filemtime(public_path('js/scripts/cases/case-api.js')) ?: time() }}"></script>
  {{-- IndexedDB-backed image store; must load before photographs.js so its init() can hydrate. --}}
  <script src="{{ asset('js/scripts/cases/case-image-store.js') }}?v={{ @filemtime(public_path('js/scripts/cases/case-image-store.js')) ?: time() }}"></script>
  {{-- Server-side media persistence (companion to case-image-store.js). --}}
  <script src="{{ asset('js/scripts/cases/case-media-api.js') }}?v={{ @filemtime(public_path('js/scripts/cases/case-media-api.js')) ?: time() }}"></script>
  @php
    $voiceInputVer = @filemtime(public_path('js/scripts/cases/voice-input.js')) ?: time();
    $cropModalVer  = @filemtime(public_path('js/scripts/cases/sections/crop-modal.js')) ?: time();
    $photographsVer = @filemtime(public_path('js/scripts/cases/sections/photographs.js')) ?: time();
    $xraysVer      = @filemtime(public_path('js/scripts/cases/sections/xrays.js')) ?: time();
    $smilePlanVer  = @filemtime(public_path('js/scripts/cases/sections/perfect-smile-plan.js')) ?: time();
  @endphp
  <script src="{{ asset('js/scripts/cases/voice-input.js') }}?v={{ $voiceInputVer }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-patients.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-preferences.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/tooth-layout.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/patient-information.js') }}?v={{ @filemtime(public_path('js/scripts/cases/sections/patient-information.js')) ?: time() }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/prescription.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/additional-information.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-scanners.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-addresses.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/impressions.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/shipping-address.js') }}?v={{ @filemtime(public_path('js/scripts/cases/sections/shipping-address.js')) ?: time() }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/submit-order.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/add-case.js') }}?v={{ @filemtime(public_path('js/scripts/cases/add-case.js')) ?: time() }}"></script>
  {{-- Phase 6: shared media helpers must load before section scripts --}}
  <script src="{{ asset('js/scripts/cases/sections/media-tile-helpers.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/crop-modal.js') }}?v={{ $cropModalVer }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/photographs.js') }}?v={{ $photographsVer }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/xrays.js') }}?v={{ $xraysVer }}"></script>
  @if($adminMode)
    <script src="{{ asset('js/scripts/cases/sections/perfect-smile-plan.js') }}?v={{ $smilePlanVer }}"></script>
  @endif
  {{-- Phase 7: submit orchestrator --}}
  <script src="{{ asset('js/scripts/cases/add-case-submit.js') }}"></script>
  {{-- PDF export — DOMPDF roundtrip --}}
  <script src="{{ asset('js/scripts/cases/add-case-export.js') }}?v={{ @filemtime(public_path('js/scripts/cases/add-case-export.js')) ?: time() }}"></script>
  <script>
    // Alpine CDN build auto-starts on DOMContentLoaded; do not call Alpine.start() manually.
    document.addEventListener('DOMContentLoaded', function () {
      if (window.PatientInformationSection) window.PatientInformationSection.init();

      // Admin: save-status button
      var btnAdminStatus = document.getElementById('btn-admin-save-status');
      if (btnAdminStatus) {
        btnAdminStatus.addEventListener('click', function () {
          var sel = document.getElementById('admin-status-select');
          if (!sel || !window.CASE_ID || window.CASE_ID === 'new') return;
          btnAdminStatus.disabled = true;
          window.CaseApi.updateStatus(window.CASE_ID, sel.value)
            .then(function (res) {
              alert('Status updated to ' + res.status + '.');
            })
            .catch(function (err) {
              console.error('Status update failed', err);
              var msg = (err && err.data && err.data.error) || 'Failed to update status.';
              alert(msg);
            })
            .finally(function () { btnAdminStatus.disabled = false; });
        });
      }

      // One-shot self-test: confirms vendor deps are present.
      setTimeout(function () {
        console.info('[AddCase] deps check — Alpine=' + (!!window.Alpine)
          + ' Cropper=' + (typeof window.Cropper !== 'undefined')
          + ' heic2any=' + (typeof window.heic2any !== 'undefined')
          + ' bootstrap=' + (typeof window.bootstrap !== 'undefined')
          + ' feather=' + (typeof window.feather !== 'undefined')
          + ' photographsSection=' + (typeof window.photographsSection === 'function'));
      }, 300);
    });
  </script>
@endpush
