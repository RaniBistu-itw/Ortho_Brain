(function () {
  'use strict';

  // TODO: load from DB config on app init; cache frontend-side
  var AUTOSAVE_DELAY_MS = 30000;
  var TOP_BAR_OFFSET = 80;
  var SCROLL_PAUSE_MS = 600; // pause observer after click to avoid mid-scroll flicker

  var caseId = window.CASE_ID || 'new';
  var draftKey = 'addCaseDraft:' + caseId;

  // ─── State ────────────────────────────────────────────────────────────────

  var state = {
    isSaving: false,
    lastSavedAt: null,
    hasAttemptedSubmit: false,
    isDirty: false,
    isReadOnly: window.__isReadOnly === true,
    caseStatus: window.__caseStatus || 'DRAFT',
    sections: {},
  };

  // Hydrate from localStorage if editing an existing draft
  (function hydrateFromLocalStorage() {
    var raw = localStorage.getItem(draftKey);
    if (!raw) return;
    try {
      var data = JSON.parse(raw);
      Object.assign(state, data);
    } catch (e) { /* ignore corrupt drafts */ }
  })();

  // Server-rendered prefill (edit mode) wins over localStorage for those slices.
  if (window.__addCasePrefill) state.prescription = window.__addCasePrefill;
  if (window.__patientPrefill) state.patientInformation = window.__patientPrefill;
  if (window.__additionalInfoPrefill) state.additionalInformation = window.__additionalInfoPrefill;
  if (window.__shippingAddressPrefill) state.shippingAddress = window.__shippingAddressPrefill;
  if (window.__impressionsPrefill) state.impressions = window.__impressionsPrefill;
  if (window.__photographsPrefill && window.__photographsPrefill.dateOfPhotos) {
    state.photographs = Object.assign({}, state.photographs, { dateOfPhotos: window.__photographsPrefill.dateOfPhotos });
  }
  if (window.__xraysPrefill && window.__xraysPrefill.dateOfXrays) {
    state.xrays = Object.assign({}, state.xrays, { dateOfXrays: window.__xraysPrefill.dateOfXrays });
  }

  // ─── Autosave indicator ───────────────────────────────────────────────────

  function updateAutosaveIndicator() {
    var el = document.getElementById('autosave-indicator');
    if (!el) return;
    if (state.isSaving) {
      el.textContent = 'Saving...';
    } else if (state.lastSavedAt) {
      var d = new Date(state.lastSavedAt);
      var hh = String(d.getHours()).padStart(2, '0');
      var mm = String(d.getMinutes()).padStart(2, '0');
      el.textContent = 'Saved at ' + hh + ':' + mm;
    } else {
      el.textContent = '';
    }
  }

  // ─── Persist helpers ──────────────────────────────────────────────────────

  function ensureShellCreated() {
    if (caseId !== 'new') return Promise.resolve();
    if (!window.CaseApi) return Promise.resolve();

    var oldCaseId = caseId;
    return window.CaseApi.createShell().then(function (res) {
      caseId = String(res.id);
      draftKey = 'addCaseDraft:' + caseId;
      window.CASE_ID = caseId;
      state.caseId = caseId;
      try { history.replaceState(null, '', '/dev/cases/' + caseId + '/edit'); } catch (e) { /* noop */ }

      var badge = document.getElementById('case-id-badge');
      var badgeNum = document.getElementById('case-id-badge-num');
      if (badge && badgeNum) {
        badgeNum.textContent = caseId;
        badge.style.display = '';
      }

      // Migrate any IDB-stored images keyed under the placeholder 'new' to the real ID.
      if (window.CaseImageStore && oldCaseId !== caseId) {
        window.CaseImageStore.rekey(oldCaseId, caseId)
          .catch(function (e) { console.warn('CaseImageStore.rekey failed', e); });
      }

      // Flush any media uploaded while caseId was 'new' — _persistTile skips
      // those, leaving blobs stranded client-side. X-rays especially: they
      // have no IDB fallback, so without this they're lost on first save.
      if (window.PhotographsSection && typeof window.PhotographsSection.flushUnsynced === 'function') {
        window.PhotographsSection.flushUnsynced();
      }
      if (window.XRaysSection && typeof window.XRaysSection.flushUnsynced === 'function') {
        window.XRaysSection.flushUnsynced();
      }
    });
  }

  function persistPrescription() {
    if (!window.CaseApi || !state.prescription || caseId === 'new') return Promise.resolve();
    return window.CaseApi.savePrescription(caseId, state.prescription);
  }

  function persistShipping() {
    if (!window.CaseApi || !state.shippingAddress || caseId === 'new') return Promise.resolve();
    return window.CaseApi.saveShipping(caseId, state.shippingAddress);
  }

  function persistImpressions() {
    if (!window.CaseApi || !state.impressions || caseId === 'new') return Promise.resolve();
    var imp = state.impressions;
    var payload = {
      impressionMethod: imp.impressionMethodId === 'pvs' ? 'physical' : 'digital',
      scannerId: imp.impressionMethodId === 'pvs' ? null : (parseInt(imp.impressionMethodId) || null)
    };
    return window.CaseApi.saveImpressions(caseId, payload);
  }

  function persistAdditionalInfo() {
    if (!window.CaseApi || !state.additionalInformation || caseId === 'new') return Promise.resolve();
    return window.CaseApi.saveAdditionalInfo(caseId, state.additionalInformation);
  }

  function persistSubmitOrder() {
    if (!window.CaseApi || caseId === 'new') return Promise.resolve();
    var so = state.submitOrder;
    if (!so) return Promise.resolve();
    return window.CaseApi.saveSubmitOrder(caseId, {
      submitterInitials: so.submitterInitials || null,
    });
  }

  function persistPhotographsDate() {
    if (!window.CaseApi || caseId === 'new') return Promise.resolve();
    var date = state.photographs && state.photographs.dateOfPhotos;
    if (!date) return Promise.resolve();
    return window.CaseApi.savePhotographsDate(caseId, date);
  }

  function persistXraysDate() {
    if (!window.CaseApi || caseId === 'new') return Promise.resolve();
    var date = state.xrays && state.xrays.dateOfXrays;
    if (!date) return Promise.resolve();
    return window.CaseApi.saveXraysDate(caseId, date);
  }

  // Persist patient identity (firstName, lastName, DOB, gender, chartId,
  // chiefComplaint, plus optional email/phone). Skips silently when the
  // form isn't yet complete enough to satisfy PatientInformationRequest —
  // so a partially-filled draft just retries on the next dirty cycle.
  function persistPatient() {
    if (!window.CaseApi || caseId === 'new') return Promise.resolve();
    var pi = state.patientInformation;
    if (!pi) return Promise.resolve();

    var firstName = (pi.firstName || '').trim();
    var lastName  = (pi.lastName  || '').trim();
    var dob       = (pi.dateOfBirth || '').trim();
    var gender    = (pi.biologicalGender || '').trim();
    var complaint = (pi.chiefComplaint || '').trim();

    // PatientInformationRequest gates these as required. Skip the round-trip
    // until the user has filled the minimum.
    if (!firstName || !lastName || !dob || !gender || !complaint) {
      return Promise.resolve();
    }
    if (gender === 'Self-describe/Other' && !(pi.biologicalGenderOther || '').trim()) {
      return Promise.resolve();
    }

    return window.CaseApi.savePatient(caseId, {
      firstName:              firstName,
      lastName:               lastName,
      dateOfBirth:            dob,
      biologicalGender:       gender,
      biologicalGenderOther:  pi.biologicalGenderOther || null,
      patientChartId:         pi.patientChartId || null,
      chiefComplaint:         complaint,
      email:                  pi.email || null,
      phone:                  pi.phone || null,
      selectedPatientId:      pi.searchedPatientId || null,
    }).then(function (res) {
      // Server tells us which patient row got created/updated; remember the
      // id so subsequent autosaves keep updating the same row instead of
      // creating duplicates.
      if (res && res.patient && res.patient.id) {
        pi.searchedPatientId = res.patient.id;
        if (window.AddCaseState && window.AddCaseState.patientInformation) {
          window.AddCaseState.patientInformation.searchedPatientId = res.patient.id;
        }
      }
    }).catch(function (err) {
      // 422 = validation failed; fields are still being typed. Silent retry.
      if (err && err.status === 422) return;
      console.warn('persistPatient failed', err);
    });
  }

  // ─── Save Draft ────────────────────────────────────────────────────────────

  function saveDraft() {
    if (!state.isDirty) return Promise.resolve();
    state.isSaving = true;
    state.isDirty = false;
    updateAutosaveIndicator();

    var failures = [];

    // Wraps a persist fn so its rejection is recorded but never propagates \u2014
    // each step runs regardless of what the previous one did.
    function shield(key, fn) {
      return function () {
        return fn().catch(function (err) {
          console.error('[saveDraft] ' + key + ' failed', err);
          failures.push(key);
        });
      };
    }

    var LABELS = {
      prescription:    'Prescription',
      patient:         'Patient',
      shipping:        'Shipping',
      impressions:     'Impressions',
      additionalInfo:  'Additional Info',
      submitOrder:     'Submit Order',
      photographsDate: 'Date of Photos',
      xraysDate:       'Date of X-Rays',
    };

    return ensureShellCreated()
      .then(shield('prescription',     persistPrescription))
      .then(shield('patient',          persistPatient))
      .then(shield('shipping',         persistShipping))
      .then(shield('impressions',      persistImpressions))
      .then(shield('additionalInfo',   persistAdditionalInfo))
      .then(shield('submitOrder',      persistSubmitOrder))
      .then(shield('photographsDate',  persistPhotographsDate))
      .then(shield('xraysDate',        persistXraysDate))
      .then(function () {
        try { localStorage.setItem(draftKey, JSON.stringify(state)); } catch (e) { /* ignore quota */ }
        state.lastSavedAt = new Date().toISOString();
        state.isSaving = false;
        updateAutosaveIndicator();
        if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
          if (failures.length > 0) {
            var saved    = Object.keys(LABELS).length - failures.length;
            var failList = failures.map(function (k) { return LABELS[k] || k; }).join(', ');
            window.MediaTileHelpers.showToast(
              'Saved ' + saved + ' of ' + Object.keys(LABELS).length +
              ' \u2014 ' + failList + ' failed. Other sections persisted.',
              4000
            );
          } else {
            window.MediaTileHelpers.showToast('Saved \u2713', 2000);
          }
        }
      })
      .catch(function (err) {
        // Only fires if ensureShellCreated() itself rejects.
        console.error('Autosave failed (shell creation)', err);
        state.isDirty = true;
        state.isSaving = false;
        updateAutosaveIndicator();
      });
  }

  // ─── Debounced autosave ────────────────────────────────────────────────────

  var autosaveTimer = null;

  function scheduleAutosave() {
    state.isDirty = true;
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(saveDraft, AUTOSAVE_DELAY_MS);
  }

  var formPane = document.getElementById('case-form-pane');
  if (formPane) {
    formPane.addEventListener('input', scheduleAutosave);
    formPane.addEventListener('change', scheduleAutosave);
  }

  window.addEventListener('beforeunload', function () {
    // Last-ditch backup: if the user navigates away with typing still in
    // memory (closed tab, browser back, deep-link click), persist to
    // localStorage so the next visit can rehydrate. The practice switcher
    // uses the awaitable `flushNow()` for a real server save.
    if (state.isDirty) {
      try { localStorage.setItem(draftKey, JSON.stringify(state)); }
      catch (e) { /* ignore quota */ }
    }
    clearTimeout(autosaveTimer);
  });

  // ─── Save Draft button ────────────────────────────────────────────────────

  var btnSaveDraft = document.getElementById('btn-save-draft');
  if (btnSaveDraft) {
    btnSaveDraft.addEventListener('click', function () {
      clearTimeout(autosaveTimer);
      state.isDirty = true;
      saveDraft();
    });
  }

  // ─── Scroll Spy (IntersectionObserver) ────────────────────────────────────

  var sectionIds = [
    'patient-information',
    'prescription',
    'additional-information',
    'perfect-smile-plan',
    'impressions',
    'photographs',
    'xrays',
    'additional-records',
    'shipping-address',
    'submit-order',
  ];

  var activeSection = sectionIds[0];
  var observerPaused = false;
  var pauseTimer = null;

  function setActiveSection(id) {
    if (activeSection === id) return;
    activeSection = id;

    document.querySelectorAll('.add-case-rail__item').forEach(function (item) {
      item.classList.toggle('add-case-rail__item--active', item.dataset.section === id);
    });

    var select = document.getElementById('case-section-select');
    if (select && select.value !== id) {
      select.value = id;
    }
  }

  function pauseObserver() {
    observerPaused = true;
    clearTimeout(pauseTimer);
    pauseTimer = setTimeout(function () {
      observerPaused = false;
    }, SCROLL_PAUSE_MS);
  }

  var observer = new IntersectionObserver(function (entries) {
    if (observerPaused) return;

    var visible = [];
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        visible.push(entry);
      }
    });

    if (visible.length > 0) {
      visible.sort(function (a, b) {
        return a.boundingClientRect.top - b.boundingClientRect.top;
      });
      var topId = visible[0].target.id;
      if (topId) setActiveSection(topId);
    }
  }, {
    rootMargin: '-' + TOP_BAR_OFFSET + 'px 0px -75% 0px',
  });

  var sectionVisibility = {};
  sectionIds.forEach(function (id) { sectionVisibility[id] = false; });

  var fullObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      sectionVisibility[entry.target.id] = entry.isIntersecting;
    });
  }, { rootMargin: '0px' });

  sectionIds.forEach(function (id) {
    var el = document.getElementById(id);
    if (el) {
      observer.observe(el);
      fullObserver.observe(el);
    }
  });

  // ─── Scroll-to helper (rail clicks + dropdown) ────────────────────────────

  window.caseScrollSpy = {
    scrollTo: function (sectionId) {
      var el = document.getElementById(sectionId);
      if (!el) return;

      setActiveSection(sectionId);
      pauseObserver();

      var top = el.getBoundingClientRect().top + window.scrollY - TOP_BAR_OFFSET;
      window.scrollTo({ top: top, behavior: 'smooth' });
    },
  };

  // ─── Public globals ───────────────────────────────────────────────────────

  window.AddCaseState = state;
  window.AddCaseSave  = {
    scheduleAutosave: scheduleAutosave,
    saveDraft: saveDraft,
    markDirty: scheduleAutosave,
    markSaved: function () {
      state.lastSavedAt = new Date().toISOString();
      updateAutosaveIndicator();
    },
    currentCaseId: function () { return caseId; },

    // Flush any pending autosave immediately. Returns a Promise that
    // resolves when every section has been acknowledged by the server (or
    // no-ops if the form is clean — saveDraft early-returns on !isDirty).
    // Called by the practice switcher to guarantee no data loss across
    // a mid-form switch.
    flushNow: function () {
      clearTimeout(autosaveTimer);
      return saveDraft();
    },

    // Immediate (non-debounced) save for the X-Rays date input. The 30s
    // autosave debounce + saveDraft's isDirty early-return + the
    // change-vs-click ordering on <input type="date"> together make it
    // possible for a typed date to never reach the DB before submit fires.
    // Saving on @change closes that race.
    saveXraysDateNow: function (date) {
      if (!window.CaseApi || caseId === 'new' || !date) return;
      return window.CaseApi.saveXraysDate(caseId, date)
        .catch(function (e) { console.warn('saveXraysDateNow failed', e); });
    },
  };

  // ─── Init ─────────────────────────────────────────────────────────────────

  setActiveSection(sectionIds[0]);
  updateAutosaveIndicator();

  if (typeof feather !== 'undefined') {
    feather.replace({ width: 14, height: 14 });
  }

  // Unified date-picker triggers. Each .case-date-group contains a feather
  // calendar in .case-date-trigger plus a native <input type="date">. We hide
  // the browser-native indicator via CSS, then make BOTH the icon span and
  // the input itself open the picker on click via showPicker(). This keeps
  // the three case dates (DOB, photo date, x-ray date) visually identical.
  document.querySelectorAll('.case-date-group').forEach(function (group) {
    var input = group.querySelector('input.case-date-input');
    var trigger = group.querySelector('.case-date-trigger');
    if (!input) return;
    var open = function () {
      if (typeof input.showPicker === 'function') {
        try { input.showPicker(); } catch (e) { input.focus(); }
      } else {
        input.focus();
      }
    };
    if (trigger) trigger.addEventListener('click', open);
    input.addEventListener('click', open);
  });

})();
