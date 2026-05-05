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
        // Server-side prefill (edit mode) wins. Falls back to the
        // localStorage draft for new-case in-progress state.
        if (window.__submitOrderPrefill) {
          this._hydrateFromPrefill(window.__submitOrderPrefill);
        } else {
          var draft = window.AddCaseState && window.AddCaseState.submitOrder;
          if (draft) {
            this._hydrate(draft);
          }
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

      _hydrateFromPrefill: function (p) {
        if (!p) return;
        // Plain text input — no $nextTick needed (Entry 6 only applies to
        // <select> options rendered via x-for).
        this.submitterInitials = p.submitterInitials || '';
        // termsAgreed is a per-submission attestation, never prefilled
        // from the server — the admin viewing a submitted case still
        // sees an unchecked box (the historical agreement is implied by
        // the case being SUBMITTED, not by re-displaying the checkbox).
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
