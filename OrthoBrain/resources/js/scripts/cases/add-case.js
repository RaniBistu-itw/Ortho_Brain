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

  // Server-rendered Prescription prefill (edit mode) wins over localStorage for that slice.
  if (window.__addCasePrefill) {
    state.prescription = window.__addCasePrefill;
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
    });
  }

  function persistPrescription() {
    if (!window.CaseApi || !state.prescription || caseId === 'new') return Promise.resolve();
    return window.CaseApi.savePrescription(caseId, state.prescription);
  }

  // ─── Save Draft ────────────────────────────────────────────────────────────

  function saveDraft() {
    if (!state.isDirty) return Promise.resolve();
    state.isSaving = true;
    state.isDirty = false;
    updateAutosaveIndicator();

    return ensureShellCreated()
      .then(persistPrescription)
      .then(function () {
        // TODO: teammate-owned sections — replace with real endpoints as each lands.
        try { localStorage.setItem(draftKey, JSON.stringify(state)); } catch (e) { /* ignore quota */ }
        state.lastSavedAt = new Date().toISOString();
        state.isSaving = false;
        updateAutosaveIndicator();
      })
      .catch(function (err) {
        console.error('Autosave failed', err);
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
    currentCaseId: function () { return caseId; },
  };

  // ─── Init ─────────────────────────────────────────────────────────────────

  setActiveSection(sectionIds[0]);
  updateAutosaveIndicator();

  if (typeof feather !== 'undefined') {
    feather.replace({ width: 14, height: 14 });
  }

})();
