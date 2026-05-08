(function () {
  'use strict';

  window.impressionsSection = function () {
    return {

      // B-1b: read-only mode for non-DRAFT cases. See prescription.js comment
      // for context. Bound to :disabled on the impression-method select.
      get isReadOnly() {
        return !!(window.AddCaseState && window.AddCaseState.isReadOnly);
      },

      // ── Reactive state ──────────────────────────────────────────────────────

      impressionMethodId: '',

      // Pre-declare every error key so Alpine tracks them from the start.
      errors: {
        impressionMethod: null,
      },

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        // Hydrate from server-side prefill (if editing existing case)
        if (window.__impressionsPrefill) {
          this._hydrateFromPrefill(window.__impressionsPrefill);
        } else {
          // Fallback to localStorage if draft exists
          var draft = window.AddCaseState && window.AddCaseState.impressions;
          if (draft && draft.impressionMethodId) {
            this._hydrate(draft);
          }
        }

        var self = this;
        window.ImpressionsSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate:     function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Data loading ────────────────────────────────────────────────────────

      _hydrate: function (d) {
        this.impressionMethodId = d.impressionMethodId || '';
      },

      _hydrateFromPrefill: function (p) {
        if (!p) return;
        if (p.impressionMethod === 'physical') {
          this.impressionMethodId = 'pvs';
        } else if (p.scannerId) {
          this.impressionMethodId = p.scannerId;
        }
      },

      // ── Handlers ────────────────────────────────────────────────────────────

      onMethodChange: function () {
        this.errors.impressionMethod = null;
        this.syncToState();
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        
        var methodId = this.impressionMethodId;
        var payload = {
          impressionMethod: methodId === 'pvs' ? 'physical' : 'digital',
          scannerId: methodId === 'pvs' ? null : (parseInt(methodId) || null)
        };

        window.AddCaseState.impressions = {
          impressionMethodId: methodId,
        };

        if (window.AddCaseSave) window.AddCaseSave.markDirty();

        // Server-side persistence if case exists
        if (window.CASE_ID && window.CASE_ID !== 'new') {
          this._persistToServer(payload);
        }
      },

      _persistTimeout: null,
      _persistToServer: function (payload) {
        clearTimeout(this._persistTimeout);
        this._persistTimeout = setTimeout(function () {
          window.CaseApi.saveImpressions(window.CASE_ID, payload)
            .then(function () {
              if (window.AddCaseSave) window.AddCaseSave.markSaved();
            })
            .catch(function (err) { console.error('[Impressions] Save failed', err); });
        }, 1000);
      },

      // ── Per-field validation ─────────────────────────────────────────────────

      validateField: function (field) {
        if (field === 'impressionMethod') {
          this.errors.impressionMethod = !this.impressionMethodId
            ? 'Please select an impression method.'
            : null;
        }
      },

      // ── Full-section validation (called by submit flow) ──────────────────────

      validateAll: function () {
        this.validateField('impressionMethod');
        var self = this;
        return Object.keys(this.errors).every(function (k) { return !self.errors[k]; });
      },

    };
  };

})();
