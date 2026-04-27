(function () {
  'use strict';

  window.shippingAddressSection = function () {
    return {

      // ── Reactive state ──────────────────────────────────────────────────────

      savedAddressId: '',
      practice:       '',
      doctorName:     '',
      streetAddress:  '',
      streetAddress2: '',
      zipId:          null,
      zipQuery:       '',      // display text for the ZIP combobox input
      zipDropdownOpen: false,
      city:           '',
      state:          '',
      country:        '',

      // Pre-declare every error key so Alpine tracks them from the start.
      errors: {
        streetAddress: null,
        zipId:         null,
        city:          null,
        state:         null,
        country:       null,
      },

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        var draft = window.AddCaseState && window.AddCaseState.shippingAddress;
        if (draft && (draft.streetAddress || draft.zipId)) {
          this._hydrate(draft);
        } else {
          this._prefillFromClinic();
        }

        var self = this;
        window.ShippingAddressSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate:     function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Data loading ────────────────────────────────────────────────────────

      // Prefer the real active-practice address (embedded in the page by
      // add-case.blade.php). Falls back to the mock for dev/offline scenarios.
      _prefillFromClinic: function () {
        var c = window.ACTIVE_PRACTICE_ADDRESS || window.MOCK_CLINIC_ADDRESS;
        if (!c) return;
        this.practice       = c.practiceName   || c.practice    || '';
        this.doctorName     = c.doctorName     || '';
        this.streetAddress  = c.streetAddress  || '';
        this.streetAddress2 = c.streetAddress2 || '';
        this.zipId          = c.zipId          || null;
        // If a raw zip code came through (real data, no MOCK_ZIP_ENTRIES match),
        // use it as the display label so the user sees something familiar.
        if (c.zipCode && !this.zipQuery) {
          this.zipQuery = c.zipCode;
        } else {
          this._resolveZipQuery(c.zipId);
        }
        this.city    = c.city    || '';
        this.state   = c.state   || '';
        this.country = c.country || '';
      },

      _hydrate: function (d) {
        this.savedAddressId = d.savedAddressId || '';
        this.practice       = d.practice       || '';
        this.doctorName     = d.doctorName     || '';
        this.streetAddress  = d.streetAddress  || '';
        this.streetAddress2 = d.streetAddress2 || '';
        this.zipId          = d.zipId          || null;
        this._resolveZipQuery(d.zipId);
        this.city    = d.city    || '';
        this.state   = d.state   || '';
        this.country = d.country || '';
      },

      // Resolve the data source: prefer the seeded ZIPCODE_ENTRIES, fall back
      // to the legacy mock for any tooling that still references it.
      _zipEntries: function () {
        return window.ZIPCODE_ENTRIES || window.MOCK_ZIP_ENTRIES || [];
      },

      // Set zipQuery to the display label matching the given zipId.
      _resolveZipQuery: function (zipId) {
        if (!zipId) { this.zipQuery = ''; return; }
        var entry = this._zipEntries().find(function (e) { return e.id === zipId; });
        this.zipQuery = entry ? entry.displayLabel : zipId;
      },

      // ── ZIP combobox helpers ────────────────────────────────────────────────

      filteredZips: function () {
        var entries = this._zipEntries();
        var q = (this.zipQuery || '').toLowerCase().trim();
        // Cap unfiltered list — a 600+ option dropdown chokes the browser.
        if (!q) return entries.slice(0, 50);
        var matches = entries.filter(function (e) {
          var code = e.code || e.zip || '';
          return e.displayLabel.toLowerCase().indexOf(q) !== -1
              || code.toLowerCase().indexOf(q) !== -1;
        });
        return matches.slice(0, 50);
      },

      selectZip: function (entry) {
        this.zipId           = entry.id;
        this.zipQuery        = entry.displayLabel;
        this.zipDropdownOpen = false;
        // CASCADE: auto-fill city, state, country
        this.city    = entry.city;
        this.state   = entry.state;
        this.country = entry.country;
        // Clear related errors
        this.errors.zipId   = null;
        this.errors.city    = null;
        this.errors.state   = null;
        this.errors.country = null;
        this.syncToState();
      },

      onZipBlur: function () {
        // Delay so @mousedown.prevent on dropdown options fires first.
        var self = this;
        setTimeout(function () {
          self.zipDropdownOpen = false;
          // Restore display label if user typed without selecting a new entry.
          self._resolveZipQuery(self.zipId);
          self.validateField('zipId');
        }, 200);
      },

      // ── Saved Address handler ───────────────────────────────────────────────

      onSavedAddressChange: function () {
        var id   = this.savedAddressId;
        var addr = (window.MOCK_SAVED_ADDRESSES || []).find(function (a) { return a.id === id; });
        if (!addr) return;
        this.streetAddress  = addr.streetAddress  || '';
        this.streetAddress2 = addr.streetAddress2 || '';
        this.zipId          = addr.zipId          || null;
        this._resolveZipQuery(addr.zipId);
        this.city    = addr.city    || '';
        this.state   = addr.state   || '';
        this.country = addr.country || '';
        // Clear field errors after a clean fill
        this.errors.streetAddress = null;
        this.errors.zipId         = null;
        this.errors.city          = null;
        this.errors.state         = null;
        this.errors.country       = null;
        this.syncToState();
      },

      // ── Country change handler ──────────────────────────────────────────────

      onCountryChange: function () {
        // Reset ZIP-derived fields — they are country-specific.
        this.zipId    = null;
        this.zipQuery = '';
        this.city     = '';
        this.state    = '';
        this.errors.zipId  = null;
        this.errors.city   = null;
        this.errors.state  = null;
        this.syncToState();
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        window.AddCaseState.shippingAddress = {
          savedAddressId: this.savedAddressId,
          practice:       this.practice,
          doctorName:     this.doctorName,
          streetAddress:  this.streetAddress,
          streetAddress2: this.streetAddress2,
          zipId:          this.zipId,
          city:           this.city,
          state:          this.state,
          country:        this.country,
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
      },

      // ── Per-field validation ─────────────────────────────────────────────────

      validateField: function (field) {
        switch (field) {

          case 'streetAddress':
            this.errors.streetAddress = !(this.streetAddress || '').trim()
              ? 'Street address is required.'
              : null;
            break;

          case 'zipId':
            this.errors.zipId = !this.zipId
              ? 'Please select a valid ZIP code.'
              : null;
            break;

          case 'city':
            this.errors.city = !(this.city || '').trim()
              ? 'City is required.'
              : null;
            break;

          case 'state':
            this.errors.state = !(this.state || '').trim()
              ? 'State / Province is required.'
              : null;
            break;

          case 'country':
            this.errors.country = !['US', 'CA', 'AU'].includes(this.country)
              ? 'Please select a country.'
              : null;
            break;
        }
      },

      // ── Full-section validation (called by submit flow) ──────────────────────

      validateAll: function () {
        this.validateField('streetAddress');
        this.validateField('zipId');
        this.validateField('city');
        this.validateField('state');
        this.validateField('country');
        var self = this;
        return Object.keys(this.errors).every(function (k) { return !self.errors[k]; });
      },

    };
  };

})();
