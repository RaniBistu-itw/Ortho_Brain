(function () {
  'use strict';

  // ─── Tooth Picker Alpine component ───────────────────────────────────────────

  window.toothPicker = function (initialSelection) {
    return {
      selected: Array.isArray(initialSelection) ? initialSelection.slice() : [],

      toggle: function (toothId) {
        var i = this.selected.indexOf(toothId);
        if (i >= 0) {
          this.selected.splice(i, 1);
        } else {
          this.selected.push(toothId);
        }
        this.$dispatch('tooth-picker-change', {
          instance: this.$root.dataset.instance,
          selected: this.selected.slice(),
        });
      },

      isSelected: function (toothId) {
        return this.selected.includes(toothId);
      },
    };
  };

  // ─── Prescription Section Alpine component ────────────────────────────────────

  window.prescriptionSection = function () {
    return {

      // B-1b: read-only mode for non-DRAFT cases. Sourced from
      // window.AddCaseState (seeded by add-case.js from window.__isReadOnly).
      // Bound to :disabled on every input/select/textarea in this section's
      // Blade partial. See Docs/case-workflow.md → "Role capabilities > Doctor".
      get isReadOnly() {
        return !!(window.AddCaseState && window.AddCaseState.isReadOnly);
      },

      // ── Reactive state ──────────────────────────────────────────────────────

      arches: 'both',

      ipr:         { on: true, value: '' },
      attachments: { on: true, value: '', step: null },
      elastics:    { on: true, value: '' },
      extractions: { on: true, value: '' },

      tmrMode:  'none',
      tmrTeeth: [],
      arMode:   'none',
      arTeeth:  [],

      additionalComments:    '',
      additionalCommentsLen: 0,

      frwHasWork:       null,
      frwExplanation:   '',
      frwExplanationLen: 0,

      // Pre-declare every error key so Alpine tracks them from the start.
      errors: {
        ipr:              null,
        attachmentsValue: null,
        attachmentsStep:  null,
        elastics:         null,
        extractions:      null,
        tmrTeeth:         null,
        arTeeth:          null,
        frwHasWork:       null,
        frwExplanation:   null,
      },

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        var draft = window.AddCaseState && window.AddCaseState.prescription;
        if (draft && draft.arches) {
          this._hydrate(draft);
        } else {
          this._loadDefaults();
        }

        // Register watches AFTER initial data load so they don't fire during hydration.
        var self = this;

        // Push hydrated tooth selections into each picker's local scope.
        // Pickers own their `selected` array; this keeps UI in sync with restored state.
        this.$nextTick(function () {
          window.dispatchEvent(new CustomEvent('tooth-picker-hydrate', {
            detail: { instance: 'tmr', selected: self.tmrTeeth.slice() },
          }));
          window.dispatchEvent(new CustomEvent('tooth-picker-hydrate', {
            detail: { instance: 'ar',  selected: self.arTeeth.slice() },
          }));
        });

        // Tooth picker: re-validate teeth when mode changes or a tooth is toggled.
        this.$watch('tmrMode',  function () { self.validateField('tmrTeeth'); });
        this.$watch('tmrTeeth', function () { self.validateField('tmrTeeth'); });
        this.$watch('arMode',   function () { self.validateField('arTeeth'); });
        this.$watch('arTeeth',  function () { self.validateField('arTeeth'); });

        // FRW: re-validate hasWork when it changes; clear explanation error when switching to 'no'.
        this.$watch('frwHasWork', function () { self.validateField('frwHasWork'); });

        // Expose public API for the submit-flow validator (later phase).
        window.PrescriptionSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate:     function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Data loading ────────────────────────────────────────────────────────

      _loadDefaults: function () {
        // TODO: replace with API call to GET /api/doctors/{id}/preferences
        var p = window.MOCK_DOCTOR_PREFERENCES;
        if (!p) return;
        this.ipr.on    = p.iprProtocol.enabled;
        this.ipr.value = p.iprProtocol.enabled ? (p.iprProtocol.value || '') : '';

        this.attachments.on    = p.attachments.enabled;
        this.attachments.value = p.attachments.enabled ? (p.attachments.value || '') : '';
        this.attachments.step  = p.attachments.specificStepNumber || null;

        this.elastics.on    = p.elastics.enabled;
        this.elastics.value = p.elastics.enabled ? (p.elastics.value || '') : '';

        this.extractions.on    = p.extractions.enabled;
        this.extractions.value = p.extractions.enabled ? (p.extractions.value || '') : '';
      },

      _hydrate: function (d) {
        if (d.arches) this.arches = d.arches;

        if (d.iprProtocol) {
          this.ipr.on    = !!d.iprProtocol.enabled;
          this.ipr.value = d.iprProtocol.value || '';
        }
        if (d.attachments) {
          this.attachments.on    = !!d.attachments.enabled;
          this.attachments.value = d.attachments.value || '';
          this.attachments.step  = d.attachments.specificStepNumber || null;
        }
        if (d.elastics) {
          this.elastics.on    = !!d.elastics.enabled;
          this.elastics.value = d.elastics.value || '';
        }
        if (d.extractions) {
          this.extractions.on    = !!d.extractions.enabled;
          this.extractions.value = d.extractions.value || '';
        }
        if (d.toothMovementRestrictions) {
          this.tmrMode  = d.toothMovementRestrictions.mode || 'none';
          this.tmrTeeth = (d.toothMovementRestrictions.selectedTeeth || []).slice();
        }
        if (d.attachmentRestrictions) {
          this.arMode  = d.attachmentRestrictions.mode || 'none';
          this.arTeeth = (d.attachmentRestrictions.selectedTeeth || []).slice();
        }
        this.additionalComments    = d.additionalComments || '';
        this.additionalCommentsLen = this.additionalComments.length;

        if (d.futureRestorativeWork) {
          this.frwHasWork        = d.futureRestorativeWork.hasWork || null;
          this.frwExplanation    = d.futureRestorativeWork.explanation || '';
          this.frwExplanationLen = this.frwExplanation.length;
        }
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        window.AddCaseState.prescription = {
          arches: this.arches,
          iprProtocol: {
            enabled: this.ipr.on,
            value: this.ipr.on ? (this.ipr.value || null) : null,
          },
          attachments: {
            enabled: this.attachments.on,
            value: this.attachments.on ? (this.attachments.value || null) : null,
            specificStepNumber: (this.attachments.on && this.attachments.value === 'specific-step')
              ? (this.attachments.step || null) : null,
          },
          elastics: {
            enabled: this.elastics.on,
            value: this.elastics.on ? (this.elastics.value || null) : null,
          },
          extractions: {
            enabled: this.extractions.on,
            value: this.extractions.on ? (this.extractions.value || null) : null,
          },
          toothMovementRestrictions: {
            mode: this.tmrMode,
            selectedTeeth: this.tmrTeeth.slice(),
          },
          attachmentRestrictions: {
            mode: this.arMode,
            selectedTeeth: this.arTeeth.slice(),
          },
          additionalComments: this.additionalComments,
          futureRestorativeWork: {
            hasWork: this.frwHasWork,
            explanation: this.frwExplanation,
          },
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
      },

      // ── Per-field validation ─────────────────────────────────────────────────
      // Called on @change for radios, @blur for text/number inputs, and via $watch
      // for picker selections and FRW hasWork.

      validateField: function (field) {
        switch (field) {

          case 'ipr':
            this.errors.ipr = (this.ipr.on && !this.ipr.value)
              ? 'Please select an IPR protocol option.'
              : null;
            break;

          case 'attachmentsValue':
            this.errors.attachmentsValue = (this.attachments.on && !this.attachments.value)
              ? 'Please select an attachments option.'
              : null;
            // Also clear step error when toggle is off or a different option is picked.
            if (!this.attachments.on || this.attachments.value !== 'specific-step') {
              this.errors.attachmentsStep = null;
            }
            break;

          case 'attachmentsStep':
            if (this.attachments.on && this.attachments.value === 'specific-step') {
              var n = parseInt(this.attachments.step, 10);
              this.errors.attachmentsStep = (!n || n < 1)
                ? 'Enter a valid step number (positive integer).'
                : null;
            } else {
              this.errors.attachmentsStep = null;
            }
            break;

          case 'elastics':
            this.errors.elastics = (this.elastics.on && !this.elastics.value)
              ? 'Please select an elastics/bonded buttons option.'
              : null;
            break;

          case 'extractions':
            this.errors.extractions = (this.extractions.on && !this.extractions.value)
              ? 'Please select an extractions option.'
              : null;
            break;

          case 'tmrTeeth':
            this.errors.tmrTeeth = (this.tmrMode === 'select' && !this.tmrTeeth.length)
              ? 'Please select at least one tooth.'
              : null;
            break;

          case 'arTeeth':
            this.errors.arTeeth = (this.arMode === 'select' && !this.arTeeth.length)
              ? 'Please select at least one tooth.'
              : null;
            break;

          case 'frwHasWork':
            this.errors.frwHasWork = !this.frwHasWork ? 'Please select an option.' : null;
            // Switching back to 'no' also clears the explanation error.
            if (this.frwHasWork !== 'yes') {
              this.errors.frwExplanation = null;
            }
            break;

          case 'frwExplanation':
            this.errors.frwExplanation = (this.frwHasWork === 'yes' && !(this.frwExplanation || '').trim())
              ? 'Please explain the planned restorative work.'
              : null;
            break;
        }
      },

      // ── Toggle handlers ─────────────────────────────────────────────────────
      // Turning OFF clears the value and the associated error.
      // Turning ON leaves value blank — doctor must re-pick (no error shown yet).

      onIprToggle: function () {
        if (!this.ipr.on) {
          this.ipr.value = '';
          this.errors.ipr = null;
        }
        this.syncToState();
      },

      onAttachmentsToggle: function () {
        if (!this.attachments.on) {
          this.attachments.value = '';
          this.attachments.step  = null;
          this.errors.attachmentsValue = null;
          this.errors.attachmentsStep  = null;
        }
        this.syncToState();
      },

      onElasticsToggle: function () {
        if (!this.elastics.on) {
          this.elastics.value = '';
          this.errors.elastics = null;
        }
        this.syncToState();
      },

      onExtractionsToggle: function () {
        if (!this.extractions.on) {
          this.extractions.value = '';
          this.errors.extractions = null;
        }
        this.syncToState();
      },

      // ── Restriction mode handlers ───────────────────────────────────────────
      // Switching to 'none' clears the picker's selected teeth and its local state,
      // so the persisted draft stays consistent with the visible mode.

      onTmrModeChange: function () {
        if (this.tmrMode === 'none') {
          this.tmrTeeth = [];
          window.dispatchEvent(new CustomEvent('tooth-picker-hydrate', {
            detail: { instance: 'tmr', selected: [] },
          }));
        }
        this.syncToState();
      },

      onArModeChange: function () {
        if (this.arMode === 'none') {
          this.arTeeth = [];
          window.dispatchEvent(new CustomEvent('tooth-picker-hydrate', {
            detail: { instance: 'ar', selected: [] },
          }));
        }
        this.syncToState();
      },

      // ── Tooth picker event handler ──────────────────────────────────────────

      onToothPickerChange: function (event) {
        var detail = event.detail;
        if (detail.instance === 'tmr') {
          this.tmrTeeth = detail.selected.slice();
          // $watch('tmrTeeth') will call validateField('tmrTeeth') reactively.
        } else if (detail.instance === 'ar') {
          this.arTeeth = detail.selected.slice();
          // $watch('arTeeth') will call validateField('arTeeth') reactively.
        }
        this.syncToState();
      },

      // ── Character counters ──────────────────────────────────────────────────

      onAdditionalCommentsInput: function () {
        this.additionalCommentsLen = this.additionalComments.length;
        this.syncToState();
      },

      onFrwExplanationInput: function () {
        this.frwExplanationLen = this.frwExplanation.length;
        // Clear error as soon as content is present; @blur will set it if left empty.
        if (this.frwExplanation.trim()) this.errors.frwExplanation = null;
        this.syncToState();
      },

      // ── Full-section validation (called by submit flow) ──────────────────────
      // Returns true if the section is valid, false otherwise.

      validateAll: function () {
        this.validateField('ipr');
        this.validateField('attachmentsValue');
        this.validateField('attachmentsStep');
        this.validateField('elastics');
        this.validateField('extractions');
        this.validateField('tmrTeeth');
        this.validateField('arTeeth');
        this.validateField('frwHasWork');
        this.validateField('frwExplanation');
        // arches always has a default; HTML radio groups can't be fully un-selected.

        var self = this;
        return Object.keys(this.errors).every(function (k) { return !self.errors[k]; });
      },
    };
  };

})();
