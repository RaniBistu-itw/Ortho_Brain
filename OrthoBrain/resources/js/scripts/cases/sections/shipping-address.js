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
      cityId:         null,
      state:          '',
      stateId:        null,
      country:        '',
      countryId:      null,

      // ZIP combobox cache. zipResults backs the visible dropdown (refreshed
      // by debounced server search); zipCache memoises individual entries
      // by id so hydration / re-renders don't re-fetch known IDs.
      zipResults: [],
      zipCache: {},
      _zipSearchTimer: null,

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
        // Hydrate from server-side prefill (if editing existing case)
        if (window.__shippingAddressPrefill) {
          this._hydrateFromPrefill(window.__shippingAddressPrefill);
        } else {
          // Fallback to localStorage draft
          var draft = window.AddCaseState && window.AddCaseState.shippingAddress;
          if (draft && (draft.streetAddress || draft.zipId)) {
            this._hydrate(draft);
          } else {
            this._prefillFromClinic();
          }
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
        this.cityId         = c.cityId         || null;
        this.stateId        = c.stateId        || null;
        this.countryId      = c.countryId      || null;

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
        this.cityId         = d.cityId         || null;
        this.stateId        = d.stateId        || null;
        this.countryId      = d.countryId      || null;
        this._resolveZipQuery(d.zipId);
        this.city    = d.city    || '';
        this.state   = d.state   || '';
        this.country = d.country || '';
      },

      _hydrateFromPrefill: function (p) {
        if (!p) return;
        this.practice       = p.practice       || '';
        this.doctorName     = p.doctorName     || '';
        this.streetAddress  = p.streetAddress  || '';
        this.streetAddress2 = p.streetAddress2 || '';
        this.zipId          = p.zipId          || null;
        this.cityId         = p.cityId         || null;
        this.stateId        = p.stateId        || null;
        this.countryId      = p.countryId      || null;
        this._resolveZipQuery(p.zipId);
      },

      // Set zipQuery to the display label matching the given zipId. If the
      // entry isn't already cached, fetch it by id from the search endpoint
      // so hydration of an existing draft doesn't fall back to "raw zip id"
      // text.
      _resolveZipQuery: function (zipId) {
        if (!zipId) { this.zipQuery = ''; return; }
        var cached = this.zipCache[zipId];
        if (cached) {
          this.zipQuery = cached.displayLabel;
          return;
        }
        var self = this;
        fetch('/dev/zipcodes/search?ids%5B%5D=' + encodeURIComponent(zipId), {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' },
        }).then(function (res) {
          return res.ok ? res.json() : [];
        }).then(function (rows) {
          var entry = (rows && rows[0]) || null;
          if (entry) {
            self.zipCache[entry.id] = entry;
            self.zipQuery = entry.displayLabel;
          } else {
            self.zipQuery = String(zipId);
          }
        }).catch(function () { self.zipQuery = String(zipId); });
      },

      // ── ZIP combobox helpers ────────────────────────────────────────────────

      // Alpine binds `<template x-for="entry in filteredZips()">`. Keep this
      // synchronous and just return the most recent server-search results
      // — the actual fetch is fired by onZipQueryInput below.
      filteredZips: function () {
        return this.zipResults;
      },

      // Triggered by @input on the zip search field. Debounced ~250ms so
      // we don't hit the server on every keystroke.
      onZipQueryInput: function () {
        var q = (this.zipQuery || '').trim();
        if (q === '') {
          this.zipResults = [];
          return;
        }
        var self = this;
        clearTimeout(this._zipSearchTimer);
        this._zipSearchTimer = setTimeout(function () { self._fetchZips(q); }, 250);
      },

      _fetchZips: function (q) {
        var self = this;
        fetch('/dev/zipcodes/search?q=' + encodeURIComponent(q), {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' },
        }).then(function (res) {
          return res.ok ? res.json() : [];
        }).then(function (rows) {
          self.zipResults = rows || [];
          (rows || []).forEach(function (e) { self.zipCache[e.id] = e; });
        }).catch(function () { self.zipResults = []; });
      },

      selectZip: function (entry) {
        this.zipId           = entry.id;
        this.zipQuery        = entry.displayLabel;
        this.zipDropdownOpen = false;
        // Memoise so a later _resolveZipQuery() doesn't re-fetch this id.
        this.zipCache[entry.id] = entry;
        // CASCADE: auto-fill city, state, country
        this.city      = entry.city;
        this.cityId    = entry.cityId;
        this.state     = entry.state;
        this.stateId   = entry.stateId;
        this.country   = entry.country;
        this.countryId = entry.countryId;
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
        var payload = {
          savedAddressId: this.savedAddressId,
          practice:       this.practice,
          doctorName:     this.doctorName,
          streetAddress:  this.streetAddress,
          streetAddress2: this.streetAddress2,
          zipId:          this.zipId,
          city:           this.city,
          cityId:         this.cityId,
          state:          this.state,
          stateId:        this.stateId,
          country:        this.country,
          countryId:      this.countryId,
        };

        window.AddCaseState.shippingAddress = JSON.parse(JSON.stringify(payload));
        
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
          window.CaseApi.saveShipping(window.CASE_ID, payload)
            .then(function () {
              if (window.AddCaseSave) window.AddCaseSave.markSaved();
            })
            .catch(function (err) { console.error('[Shipping] Save failed', err); });
        }, 1000);
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
