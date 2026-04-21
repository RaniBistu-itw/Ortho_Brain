(function () {
  'use strict';

  window.submitOrderSection = function () {
    return {

      // ── Reactive state ──────────────────────────────────────────────────────

      submitterInitials: '',
      termsAgreed:       false,

      // Pre-declare every error key so Alpine tracks them from the start.
      errors: {
        submitterInitials: null,
        termsAgreed:       null,
      },

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        var draft = window.AddCaseState && window.AddCaseState.submitOrder;
        if (draft) {
          this._hydrate(draft);
        }

        var self = this;
        window.SubmitOrderSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate:     function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Data loading ────────────────────────────────────────────────────────

      _hydrate: function (d) {
        this.submitterInitials = d.submitterInitials || '';
        this.termsAgreed       = !!d.termsAgreed;
      },

      // ── Handlers ────────────────────────────────────────────────────────────

      onInitialsInput: function () {
        // Clear error in real time once user starts correcting.
        if (this.errors.submitterInitials) {
          this.validateField('submitterInitials');
        }
        this.syncToState();
      },

      onTermsChange: function () {
        this.validateField('termsAgreed');
        this.syncToState();
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        window.AddCaseState.submitOrder = {
          submitterInitials: this.submitterInitials,
          termsAgreed:       this.termsAgreed,
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
      },

      // ── Per-field validation ─────────────────────────────────────────────────

      validateField: function (field) {
        switch (field) {

          case 'submitterInitials': {
            var val = (this.submitterInitials || '').trim();
            if (!val) {
              this.errors.submitterInitials = 'Submitter initials are required.';
            } else if (!/^[A-Za-z]{2,5}$/.test(val)) {
              this.errors.submitterInitials = 'Initials must be 2\u20135 letters only.';
            } else {
              this.errors.submitterInitials = null;
            }
            break;
          }

          case 'termsAgreed':
            this.errors.termsAgreed = !this.termsAgreed
              ? 'You must agree to the terms and conditions to submit.'
              : null;
            break;
        }
      },

      // ── Full-section validation (called by submit flow) ──────────────────────

      validateAll: function () {
        this.validateField('submitterInitials');
        this.validateField('termsAgreed');
        var self = this;
        return Object.keys(this.errors).every(function (k) { return !self.errors[k]; });
      },

    };
  };

})();
