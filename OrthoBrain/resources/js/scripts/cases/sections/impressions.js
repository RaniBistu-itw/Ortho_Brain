(function () {
  'use strict';

  window.impressionsSection = function () {
    return {

      // ── Reactive state ──────────────────────────────────────────────────────

      impressionMethodId: '',

      // Pre-declare every error key so Alpine tracks them from the start.
      errors: {
        impressionMethod: null,
      },

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        var draft = window.AddCaseState && window.AddCaseState.impressions;
        if (draft && draft.impressionMethodId) {
          this._hydrate(draft);
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

      // ── Handlers ────────────────────────────────────────────────────────────

      onMethodChange: function () {
        this.errors.impressionMethod = null;
        this.syncToState();
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        window.AddCaseState.impressions = {
          impressionMethodId: this.impressionMethodId,
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
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
