(function () {
  'use strict';

  // ─── Helpers ──────────────────────────────────────────────────────────────

  function debounce(fn, delay) {
    var timer;
    return function () {
      var args = arguments;
      var ctx = this;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
  }

  /** Convert YYYY-MM-DD → MM-DD-YYYY for display */
  function isoToDisplay(iso) {
    if (!iso) return '';
    var parts = iso.split('-');
    if (parts.length !== 3) return iso;
    return parts[1] + '-' + parts[2] + '-' + parts[0];
  }

  /** Validate an ISO date string (YYYY-MM-DD from native <input type="date">) */
  function validateDob(iso) {
    if (!iso) return 'Date of birth is required.';
    if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) return 'Enter a valid date.';
    var d = new Date(iso);
    if (isNaN(d.getTime())) return 'Invalid date.';
    var now = new Date();
    now.setHours(0, 0, 0, 0);
    if (d >= now) return 'Date of birth cannot be in the future.';
    var minDate = new Date();
    minDate.setFullYear(minDate.getFullYear() - 120);
    if (d < minDate) return 'Date of birth cannot be more than 120 years ago.';
    return '';
  }

  // ─── State slice ──────────────────────────────────────────────────────────

  var DEFAULT_STATE = {
    // TODO: prefill practice and doctorName from authenticated user session
    practice: 'Promo Indp Practice Admin 3',
    doctorName: 'Promo Indp Practice Admin 3 Dr 1',
    searchedPatientId: null,
    firstName: '',
    lastName: '',
    dateOfBirth: '',         // stored as YYYY-MM-DD
    biologicalGender: '',
    biologicalGenderOther: '',
    patientChartId: '',
    chiefComplaint: '',
  };

  // ─── DOM refs ─────────────────────────────────────────────────────────────

  var elSearch       = document.getElementById('pi-search-patient');
  var elDropdown     = document.getElementById('pi-search-dropdown');
  var elFirstName    = document.getElementById('pi-first-name');
  var elLastName     = document.getElementById('pi-last-name');
  var elDob          = document.getElementById('pi-dob');
  var elGender       = document.getElementById('pi-gender');
  var elGenderOtherWrap = document.getElementById('pi-gender-other-wrapper');
  var elGenderOther  = document.getElementById('pi-gender-other');
  var elChartId      = document.getElementById('pi-chart-id');
  var elComplaint    = document.getElementById('pi-chief-complaint');
  var elComplaintCount = document.getElementById('pi-complaint-count');
  var elExistenceAlert = document.getElementById('pi-existence-alert');
  var elExistenceLoad  = document.getElementById('pi-existence-load');

  // Error divs
  var errFirstName  = document.getElementById('pi-first-name-error');
  var errLastName   = document.getElementById('pi-last-name-error');
  var errDob        = document.getElementById('pi-dob-error');
  var errGender     = document.getElementById('pi-gender-error');
  var errGenderOther = document.getElementById('pi-gender-other-error');
  var errComplaint  = document.getElementById('pi-chief-complaint-error');

  // ─── Validation helpers ───────────────────────────────────────────────────

  function showError(inputEl, errEl, msg) {
    if (!inputEl || !errEl) return;
    inputEl.classList.toggle('is-invalid', !!msg);
    errEl.textContent = msg;
    errEl.style.display = msg ? '' : 'none';
  }

  function clearError(inputEl, errEl) {
    showError(inputEl, errEl, '');
  }

  function validateFirstName() {
    var v = (elFirstName.value || '').trim();
    return v ? '' : 'First name is required.';
  }

  function validateLastName() {
    var v = (elLastName.value || '').trim();
    return v ? '' : 'Last name is required.';
  }

  function validateGender() {
    return elGender.value ? '' : 'Please select a gender.';
  }

  function validateGenderOther() {
    if (elGender.value !== 'Self-describe/Other') return '';
    return (elGenderOther.value || '').trim() ? '' : 'Please specify.';
  }

  function validateComplaint() {
    var v = (elComplaint.value || '').trim();
    if (!v) return 'Chief complaint is required.';
    if (v.length > 5000) return 'Must be 5000 characters or fewer.';
    return '';
  }

  // ─── State sync ───────────────────────────────────────────────────────────

  function syncToState() {
    if (!window.AddCaseState) return;
    window.AddCaseState.patientInformation = {
      practice: DEFAULT_STATE.practice,
      doctorName: DEFAULT_STATE.doctorName,
      searchedPatientId: window.AddCaseState.patientInformation
        ? window.AddCaseState.patientInformation.searchedPatientId
        : null,
      firstName: (elFirstName.value || '').trim(),
      lastName: (elLastName.value || '').trim(),
      dateOfBirth: elDob.value || '',
      biologicalGender: elGender.value || '',
      biologicalGenderOther: elGenderOther.value || '',
      patientChartId: (elChartId.value || '').trim(),
      chiefComplaint: elComplaint.value || '',
    };
    if (window.AddCaseSave) window.AddCaseSave.scheduleAutosave();
  }

  // ─── Search Patient typeahead ─────────────────────────────────────────────

  var selectedPatientId = null;

  function formatDobDisplay(isoDate) {
    return isoToDisplay(isoDate);
  }

  // Strip non-digits — lets users search by phone with or without formatting
  function digitsOnly(s) { return (s || '').replace(/[^0-9]/g, ''); }

  // Server-backed autocomplete via PatientController::search. Matches across
  // name / email / chart_id / phone-digits.
  // Cancellation: if the user is mid-fetch when they type again, drop the
  // older request's result by checking the seq counter at resolve time.
  var _searchSeq = 0;
  function searchPatients(query) {
    var raw = (query || '').trim();
    if (raw.length < 2) return Promise.resolve([]);
    if (!window.CaseApi || !window.CaseApi.searchPatients) return Promise.resolve([]);
    var mySeq = ++_searchSeq;
    return window.CaseApi.searchPatients(raw).then(function (rows) {
      if (mySeq !== _searchSeq) return null; // a newer search has fired; ignore
      return Array.isArray(rows) ? rows : [];
    }).catch(function () { return []; });
  }

  function renderDropdown(patients) {
    elDropdown.innerHTML = '';
    if (!patients.length) {
      elDropdown.style.display = 'none';
      return;
    }
    patients.forEach(function (p) {
      var li = document.createElement('li');
      li.className = 'list-group-item list-group-item-action pi-search-option';
      li.setAttribute('role', 'option');
      li.dataset.id = p.id;

      // Construct via textContent — when this gets wired to a real endpoint,
      // patient data will be user-controlled and innerHTML would be unsafe.
      var headerRow = document.createElement('div');
      headerRow.className = 'd-flex justify-content-between align-items-baseline gap-2';
      var nameEl = document.createElement('span');
      nameEl.className = 'fw-semibold';
      nameEl.textContent = p.firstName + ' ' + p.lastName;
      headerRow.appendChild(nameEl);
      if (p.chartId) {
        var chartEl = document.createElement('span');
        chartEl.className = 'small text-muted';
        chartEl.textContent = p.chartId;
        headerRow.appendChild(chartEl);
      }
      li.appendChild(headerRow);

      var meta = [p.email, p.phone, formatDobDisplay(p.dob)].filter(Boolean).join(' · ');
      if (meta) {
        var metaEl = document.createElement('div');
        metaEl.className = 'small text-muted';
        metaEl.textContent = meta;
        li.appendChild(metaEl);
      }

      li.addEventListener('mousedown', function (e) {
        e.preventDefault(); // prevent input blur before click registers
        loadPatient(p);
      });
      elDropdown.appendChild(li);
    });
    elDropdown.style.display = '';
  }

  function loadPatient(p) {
    elFirstName.value  = p.firstName;
    elLastName.value   = p.lastName;
    elDob.value        = p.dob || '';
    elGender.value     = p.gender;
    elGenderOther.value = p.genderOther || '';
    elChartId.value    = p.chartId;

    // Show/hide Self-describe wrapper
    elGenderOtherWrap.style.display = p.gender === 'Self-describe/Other' ? '' : 'none';

    // Clear errors for prefilled fields
    clearError(elFirstName, errFirstName);
    clearError(elLastName, errLastName);
    clearError(elDob, errDob);
    clearError(elGender, errGender);

    selectedPatientId = p.id;

    // Update existence alert — selecting from typeahead is already a confirmed load
    elExistenceAlert.style.display = 'none';

    // Clear search input
    elSearch.value = '';
    elDropdown.style.display = 'none';

    // Sync to shared state
    if (window.AddCaseState) {
      window.AddCaseState.patientInformation = window.AddCaseState.patientInformation || {};
      window.AddCaseState.patientInformation.searchedPatientId = p.id;
    }
    syncToState();

    // Re-trigger existence check (should immediately confirm)
    checkExistence();
  }

  // Debounce the server fetch so we don't hammer the endpoint on every key.
  var debouncedRunSearch = debounce(function (value) {
    searchPatients(value).then(function (matches) {
      if (matches === null) return; // superseded by a newer keystroke
      renderDropdown(matches);
    });
  }, 200);

  if (elSearch) {
    elSearch.addEventListener('input', function () {
      // Hide existence alert while typing in search
      elExistenceAlert.style.display = 'none';
      debouncedRunSearch(this.value);
    });

    elSearch.addEventListener('blur', function () {
      // Delay hide so mousedown on option fires first
      setTimeout(function () { elDropdown.style.display = 'none'; }, 150);
    });

    elSearch.addEventListener('focus', function () {
      if (this.value.length >= 2) {
        searchPatients(this.value).then(function (matches) {
          if (matches !== null) renderDropdown(matches);
        });
      }
    });
  }

  // ─── Existence check ─────────────────────────────────────────────────────

  var existenceMatchedPatient = null;

  // Server-backed existence check. Sends "{firstName} {lastName}" to the
  // search endpoint and looks for an exact name+DOB match in the result
  // set. The endpoint is doctor+practice scoped, so we'll only match
  // patients in the requesting doctor's roster — same as the mock did.
  function checkExistence() {
    if (!window.CaseApi || !window.CaseApi.searchPatients) return;
    var fn  = (elFirstName.value || '').trim();
    var ln  = (elLastName.value || '').trim();
    var dob = elDob.value || '';
    if (!fn || !ln || !dob || validateDob(dob)) {
      existenceMatchedPatient = null;
      elExistenceAlert.style.display = 'none';
      return;
    }
    window.CaseApi.searchPatients(fn + ' ' + ln).then(function (rows) {
      var fnL = fn.toLowerCase();
      var lnL = ln.toLowerCase();
      var match = (rows || []).find(function (p) {
        return (p.firstName || '').toLowerCase() === fnL &&
               (p.lastName  || '').toLowerCase() === lnL &&
               (p.dob       || '') === dob;
      }) || null;
      existenceMatchedPatient = match;
      elExistenceAlert.style.display = match ? '' : 'none';
    }).catch(function () {
      existenceMatchedPatient = null;
      elExistenceAlert.style.display = 'none';
    });
  }

  var debouncedExistenceCheck = debounce(checkExistence, 500);

  // Hide alert whenever First/Last/DOB change; re-trigger debounced check
  function onExistenceFieldChange() {
    elExistenceAlert.style.display = 'none';
    debouncedExistenceCheck();
    syncToState();
  }

  if (elFirstName) {
    elFirstName.addEventListener('input', onExistenceFieldChange);
    elFirstName.addEventListener('blur', function () {
      showError(elFirstName, errFirstName, validateFirstName());
    });
    elFirstName.addEventListener('input', function () {
      if (!validateFirstName()) clearError(elFirstName, errFirstName);
    });
  }

  if (elLastName) {
    elLastName.addEventListener('input', onExistenceFieldChange);
    elLastName.addEventListener('blur', function () {
      showError(elLastName, errLastName, validateLastName());
    });
    elLastName.addEventListener('input', function () {
      if (!validateLastName()) clearError(elLastName, errLastName);
    });
  }

  if (elDob) {
    elDob.addEventListener('change', function () {
      onExistenceFieldChange();
      var err = validateDob(this.value);
      showError(elDob, errDob, err);
      if (!err) checkExistence();
    });
    elDob.addEventListener('blur', function () {
      showError(elDob, errDob, validateDob(this.value));
    });
  }

  // "click to load" in existence alert
  if (elExistenceLoad) {
    elExistenceLoad.addEventListener('click', function (e) {
      e.preventDefault();
      if (existenceMatchedPatient) loadPatient(existenceMatchedPatient);
    });
  }

  // ─── Gender — Self-describe toggle ───────────────────────────────────────

  if (elGender) {
    elGender.addEventListener('change', function () {
      var isSelfDescribe = this.value === 'Self-describe/Other';
      elGenderOtherWrap.style.display = isSelfDescribe ? '' : 'none';
      if (!isSelfDescribe) {
        elGenderOther.value = '';
        clearError(elGenderOther, errGenderOther);
      }
      clearError(elGender, errGender);
      syncToState();
    });

    elGender.addEventListener('blur', function () {
      showError(elGender, errGender, validateGender());
    });
  }

  if (elGenderOther) {
    elGenderOther.addEventListener('input', function () {
      if (!validateGenderOther()) clearError(elGenderOther, errGenderOther);
      syncToState();
    });
    elGenderOther.addEventListener('blur', function () {
      showError(elGenderOther, errGenderOther, validateGenderOther());
    });
  }

  // ─── Chart ID ─────────────────────────────────────────────────────────────

  if (elChartId) {
    elChartId.addEventListener('input', syncToState);
  }

  // ─── Chief Complaint — counter + validation ───────────────────────────────

  if (elComplaint) {
    elComplaint.addEventListener('input', function () {
      var len = this.value.length;
      if (elComplaintCount) elComplaintCount.textContent = len;
      if (!validateComplaint()) clearError(elComplaint, errComplaint);
      syncToState();
    });

    elComplaint.addEventListener('blur', function () {
      showError(elComplaint, errComplaint, validateComplaint());
    });
  }

  // ─── Hydration from draft ─────────────────────────────────────────────────

  // Server-side prefill (window.__patientPrefill) is the source of truth —
  // it survives across browsers and is what admins see. Layered priority:
  //   1. Server prefill (cases.patient_id row, fetched in CasesController::edit)
  //   2. localStorage draft (window.AddCaseState.patientInformation)
  //   3. Empty form
  // (1) overrides (2) so that opening an existing case from a different
  // browser shows real data, not stale local draft.
  function hydrate() {
    var server = window.__patientPrefill || null;
    var pi = window.AddCaseState && window.AddCaseState.patientInformation;
    var src = null;

    if (server) {
      src = {
        firstName:              server.firstName || '',
        lastName:               server.lastName || '',
        dateOfBirth:            server.dob || '',
        biologicalGender:       server.gender || '',
        biologicalGenderOther:  server.genderOther || '',
        patientChartId:         server.chartId || '',
        chiefComplaint:         server.chiefComplaint || '',
        searchedPatientId:      server.id || null,
        email:                  server.email || '',
        phone:                  server.phone || '',
      };
      // Push the server prefill into shared state so the rest of the
      // wizard (autosave, submit) sees it without further wiring.
      if (window.AddCaseState) {
        window.AddCaseState.patientInformation = Object.assign(
          {},
          window.AddCaseState.patientInformation || DEFAULT_STATE,
          src
        );
      }
    } else if (pi) {
      src = pi;
    }

    if (!src) return;

    if (elFirstName)  elFirstName.value  = src.firstName  || '';
    if (elLastName)   elLastName.value   = src.lastName   || '';
    if (elDob)        elDob.value        = src.dateOfBirth || '';
    if (elGender)     elGender.value     = src.biologicalGender || '';
    if (elGenderOther) elGenderOther.value = src.biologicalGenderOther || '';
    if (elChartId)    elChartId.value    = src.patientChartId || '';
    if (elComplaint) {
      elComplaint.value = src.chiefComplaint || '';
      if (elComplaintCount) elComplaintCount.textContent = elComplaint.value.length;
    }

    // Restore Self-describe visibility
    if (elGender && elGender.value === 'Self-describe/Other') {
      elGenderOtherWrap.style.display = '';
    }

    selectedPatientId = src.searchedPatientId || null;
  }

  // ─── Public validation (called by submit flow in later phase) ─────────────

  function validateAll() {
    var errors = [];
    var e;

    e = validateFirstName();
    showError(elFirstName, errFirstName, e);
    if (e) errors.push(e);

    e = validateLastName();
    showError(elLastName, errLastName, e);
    if (e) errors.push(e);

    e = validateDob(elDob ? elDob.value : '');
    showError(elDob, errDob, e);
    if (e) errors.push(e);

    e = validateGender();
    showError(elGender, errGender, e);
    if (e) errors.push(e);

    e = validateGenderOther();
    showError(elGenderOther, errGenderOther, e);
    if (e) errors.push(e);

    e = validateComplaint();
    showError(elComplaint, errComplaint, e);
    if (e) errors.push(e);

    return errors.length === 0;
  }

  // ─── Init ─────────────────────────────────────────────────────────────────

  function init() {
    // Initialise state slice if not yet set by hydration
    if (window.AddCaseState && !window.AddCaseState.patientInformation) {
      window.AddCaseState.patientInformation = Object.assign({}, DEFAULT_STATE);
    }
    hydrate();
    // Re-init feather icons inside this section
    if (typeof feather !== 'undefined') feather.replace({ width: 14, height: 14 });

    // B-1b: apply read-only state imperatively. This section is vanilla JS,
    // not Alpine, so :disabled bindings aren't available. When the case is
    // not editable for the doctor (any non-DRAFT status), every input in
    // this section is disabled at init time. See Docs/case-workflow.md →
    // "Role capabilities > Doctor".
    if (window.AddCaseState && window.AddCaseState.isReadOnly) {
      var fields = [
        'pi-search-patient',
        'pi-first-name',
        'pi-last-name',
        'pi-dob',
        'pi-gender',
        'pi-gender-other',
        'pi-chart-id',
        'pi-chief-complaint'
      ];
      fields.forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.disabled = true;
      });
    }
  }

  // ─── Expose module ────────────────────────────────────────────────────────

  window.PatientInformationSection = { init: init, hydrate: hydrate, validateAll: validateAll };

})();
