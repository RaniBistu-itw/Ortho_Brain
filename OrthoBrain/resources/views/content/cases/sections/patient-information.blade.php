<section id="patient-information" class="case-section">
  <div class="case-section__header">
    <h4 class="case-section__title">Patient Information</h4>
    <p class="case-section__subtitle text-muted">Personal Details</p>
  </div>

  <div class="case-section__body">

    {{-- Row 1: Practice | Doctor Name (read-only, sourced from auth doctor) --}}
    @php
      $piPractice = currentPractice()?->name ?? $caseDoctor?->practice?->name ?? '—';
      $piDoctorName = $caseDoctor ? trim($caseDoctor->first_name . ' ' . $caseDoctor->last_name) : '—';
    @endphp
    <div class="row mb-1">
      <div class="col-md-6 mb-1 mb-md-0">
        <p class="form-label text-muted mb-25">Practice</p>
        <p class="mb-0 fw-semibold" id="pi-practice-display">{{ $piPractice }}</p>
      </div>
      <div class="col-md-6">
        <p class="form-label text-muted mb-25">Doctor Name</p>
        <p class="mb-0 fw-semibold" id="pi-doctor-name-display">{{ $piDoctorName }}</p>
      </div>
    </div>

    <hr class="my-1">

    {{-- Row 2: Search Patient (full width) --}}
    <div class="row mb-1">
      <div class="col-12">
        <label class="form-label" for="pi-search-patient">Search Patient</label>
        <div class="pi-search-wrapper position-relative">
          <input
            type="text"
            class="form-control"
            id="pi-search-patient"
            placeholder="Search patient"
            autocomplete="off"
            aria-autocomplete="list"
            aria-haspopup="listbox"
          />
          <ul
            class="list-group shadow-sm pi-search-dropdown"
            id="pi-search-dropdown"
            role="listbox"
          ></ul>
        </div>
      </div>
    </div>

    {{-- Row 3: First Name | Last Name --}}
    <div class="row mb-1">
      <div class="col-md-6 mb-1 mb-md-0">
        <label class="form-label" for="pi-first-name">
          First Name <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i data-feather="user"></i></span>
          <input type="text" class="form-control" id="pi-first-name" name="firstName" autocomplete="given-name" />
        </div>
        <div class="pi-field-error small text-danger mt-25" id="pi-first-name-error"></div>
      </div>
      <div class="col-md-6">
        <label class="form-label" for="pi-last-name">
          Last Name <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <span class="input-group-text"><i data-feather="user"></i></span>
          <input type="text" class="form-control" id="pi-last-name" name="lastName" autocomplete="family-name" />
        </div>
        <div class="pi-field-error small text-danger mt-25" id="pi-last-name-error"></div>
      </div>
    </div>

    {{-- Row 4: Date of Birth | Biological Gender --}}
    <div class="row mb-1">
      <div class="col-md-6 mb-1 mb-md-0">
        <label class="form-label" for="pi-dob">
          Date of Birth <span class="text-danger">*</span>
        </label>
        <div class="input-group case-date-group">
          <span class="input-group-text case-date-trigger"><i data-feather="calendar"></i></span>
          <input
            type="date"
            class="form-control case-date-input"
            id="pi-dob"
            name="dateOfBirth"
            autocomplete="bday"
          />
        </div>
        <div class="pi-field-error small text-danger mt-25" id="pi-dob-error"></div>

        {{-- Existence check prompt (shown when a match is found) --}}
        <div class="alert alert-info py-75 px-1 mt-1 mb-0 pi-existence-alert" id="pi-existence-alert" style="display:none;" role="alert">
          Patient found —
          <a href="#" class="alert-link fw-semibold" id="pi-existence-load">click to load</a>,
          or enter a different record.
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label" for="pi-gender">
          Biological Gender <span class="text-danger">*</span>
        </label>
        <select class="form-select" id="pi-gender" name="biologicalGender">
          <option value="">-- Select --</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Non-binary">Non-binary</option>
          <option value="Prefer not to say">Prefer not to say</option>
          <option value="Self-describe/Other">Self-describe/Other</option>
        </select>
        <div class="pi-field-error small text-danger mt-25" id="pi-gender-error"></div>

        {{-- Self-describe text input (shown only when Self-describe/Other is selected) --}}
        <div class="mt-1" id="pi-gender-other-wrapper" style="display:none;">
          <label class="form-label" for="pi-gender-other">
            Please specify <span class="text-danger">*</span>
          </label>
          <input type="text" class="form-control" id="pi-gender-other" name="biologicalGenderOther" />
          <div class="pi-field-error small text-danger mt-25" id="pi-gender-other-error"></div>
        </div>
      </div>
    </div>

    {{-- Row 5: Patient Chart ID (left column only) --}}
    <div class="row mb-1">
      <div class="col-md-6">
        <label class="form-label" for="pi-chart-id">Patient Chart ID</label>
        <div class="input-group">
          <span class="input-group-text"><i data-feather="credit-card"></i></span>
          <input type="text" class="form-control" id="pi-chart-id" name="patientChartId" autocomplete="off" />
        </div>
      </div>
    </div>

    {{-- Row 6: Chief Complaint (full width) --}}
    <div class="row">
      <div class="col-12">
        <label class="form-label" for="pi-chief-complaint">
          Chief Complaint <span class="text-danger">*</span>
        </label>
        <textarea
          class="form-control"
          id="pi-chief-complaint"
          name="chiefComplaint"
          rows="4"
          maxlength="5000"
        ></textarea>
        <div class="pi-field-error small text-danger mt-25" id="pi-chief-complaint-error"></div>
        <div class="text-muted small mt-25">
          <span id="pi-complaint-count">0</span> / 5000 characters
        </div>
      </div>
    </div>

  </div>{{-- /.case-section__body --}}
</section>
