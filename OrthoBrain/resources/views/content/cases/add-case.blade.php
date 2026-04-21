@extends('layouts.app')

@section('title', isset($id) ? 'Edit Case' : 'Add Case')
@section('page_title', isset($id) ? 'Edit Case' : 'Add Case')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/base/pages/add-case.css') }}">
@endpush

@php
  $caseId = $id ?? null;
  $sections = [
    ['id' => 'patient-information',    'label' => 'Patient Information',    'subtitle' => 'Personal Details',         'icon' => 'user',       'placeholder' => false],
    ['id' => 'prescription',           'label' => 'Prescription',           'subtitle' => 'Prescription Information', 'icon' => 'file-text',  'placeholder' => false],
    ['id' => 'additional-information', 'label' => 'Additional Information', 'subtitle' => 'Additional Information',   'icon' => 'plus-circle','placeholder' => false],
    ['id' => 'perfect-smile-plan',     'label' => 'Perfect Smile Plan',     'subtitle' => 'Perfect Smile Plan',       'icon' => 'smile',      'placeholder' => true],
    ['id' => 'impressions',            'label' => 'Impressions',            'subtitle' => 'Impression Method',        'icon' => 'check-square','placeholder' => false],
    ['id' => 'photographs',            'label' => 'Photographs',            'subtitle' => 'Upload Photographs',       'icon' => 'image',      'placeholder' => false],
    ['id' => 'xrays',                  'label' => 'X-Rays',                 'subtitle' => 'Upload X-Ray photos',      'icon' => 'radio',      'placeholder' => false],
    ['id' => 'additional-records',     'label' => 'Additional Records',     'subtitle' => 'Additional Records',       'icon' => 'list',       'placeholder' => true],
    ['id' => 'shipping-address',       'label' => 'Shipping Address',       'subtitle' => 'Shipping address',         'icon' => 'map-pin',    'placeholder' => false],
    ['id' => 'submit-order',           'label' => 'Submit Order',           'subtitle' => 'Submit Order',             'icon' => 'send',       'placeholder' => false],
  ];
@endphp

@section('content')
<div class="add-case-page" data-case-id="{{ $caseId ?? 'new' }}">

  {{-- Sticky Top Bar --}}
  <div class="add-case-topbar card mb-0">
    <div class="card-body py-75 px-1 d-flex align-items-center gap-75">
      <a href="{{ route('doctor.cases.index') }}" class="btn btn-danger btn-sm add-case-topbar__back" title="Back to Cases">
        <i data-feather="arrow-left"></i>
      </a>

      <div class="flex-fill"></div>

      <span class="add-case-topbar__autosave text-muted font-small-2" id="autosave-indicator"></span>

      <button type="button" class="btn btn-outline-primary btn-sm" id="btn-save-draft">
        Save Draft
      </button>

      <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-50" id="btn-submit"
              onclick="window.AddCaseSubmit.submit()">
        Submit <i data-feather="check" class="ms-25"></i>
      </button>
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
      @include('content.cases.sections.perfect-smile-plan')
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
</div>
@endsection

@push('scripts')
  <script>
    window.CASE_ID = '{{ $caseId ?? 'new' }}';
    window.__addCasePrefill = @json($prescriptionPrefill ?? null);
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
  <script src="{{ asset('js/scripts/cases/case-api.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-patients.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-preferences.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/tooth-layout.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/patient-information.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/prescription.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/additional-information.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-scanners.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/mock-addresses.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/impressions.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/shipping-address.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/submit-order.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/add-case.js') }}"></script>
  {{-- Phase 6: shared media helpers must load before section scripts --}}
  <script src="{{ asset('js/scripts/cases/sections/media-tile-helpers.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/crop-modal.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/photographs.js') }}"></script>
  <script src="{{ asset('js/scripts/cases/sections/xrays.js') }}"></script>
  {{-- Phase 7: submit orchestrator --}}
  <script src="{{ asset('js/scripts/cases/add-case-submit.js') }}"></script>
  <script>
    // Alpine CDN build auto-starts on DOMContentLoaded; do not call Alpine.start() manually.
    document.addEventListener('DOMContentLoaded', function () {
      if (window.PatientInformationSection) window.PatientInformationSection.init();
    });
  </script>
@endpush
