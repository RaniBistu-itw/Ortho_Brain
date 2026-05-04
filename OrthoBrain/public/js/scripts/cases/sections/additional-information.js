(function () {
  'use strict';

  window.additionalInformationSection = function () {
    return {

      // ── State — every reactive property pre-declared at creation ─────────────
      // Follows the exact shape of window.AddCaseState.additionalInformation.

      sectionExpanded: true,

      // A. Diagnosis
      diagnosis: {
        crowding: false, spacing: false, deepBite: false, openBite: false,
        overjet: false, anteriorCrossbite: false, posteriorCrossbite: false,
        classI: false, classII: false, classIII: false,
      },

      // B. Medical History
      medicalHistory: {
        wnl: false,
        allergies:     { enabled: false, description: '' },
        medications:   { enabled: false, value: null, otherText: '' },
        boneDisorders: { enabled: false, description: '' },
      },

      // C. Dental History
      dentalHistory: {
        wnl: false,
        trauma:            { enabled: false, description: '' },
        poorPrognosis:     { enabled: false, description: '' },
        oralPathology:     false,
        enamelPathologies: false,
        attrition:         false,
        speechDysfunction: false,
        airwayProblems:    false,
        tmjIssues:         false,
        other:             { enabled: false, otherText: '' },
      },

      // D. Orthodontic History
      orthodonticHistory: {
        noPreviousTreatment:    false,
        completedPhase1:        false,
        previousComprehensive:  false,
        other: { enabled: false, otherText: '' },
      },

      // E. Family History
      familyHistory: {
        none: false, notKnown: false,
        extractions: false, impactedCanines: false, orthognathicSurgery: false,
      },

      // F. Periodontal Evaluation
      periodontalEvaluation: {
        wnl: false,
        oralHygiene:       null,  // 'wnl' | 'fair' | 'poor'
        frenalAttachments: null,  // 'wnl' | 'excessive' | 'frenectomy'
        gingivalTissue: {
          wnl: false, inflamed: false, bleeding: false, pockets: false,
          thinGingiva: false, recession: false, tissueGrafting: false,
        },
        hardTissue: {
          wnl: false, boneLoss: false,
          other: { enabled: false, otherText: '' },
        },
      },

      // G. Parafunctional Habits
      parafunctionalHabits: {
        none: false, fingerSucking: false, lipSucking: false,
        bruxing: false, clenching: false, tongueThrusting: false,
        other: { enabled: false, otherText: '' },
      },

      // H. Centric Relation
      centricRelation: { value: null, description: '' },  // 'notPresent' | 'present'

      // I. Treatment Scope
      treatmentScope: { value: null, otherText: '' },  // 'aesthetics' | 'comprehensive' | 'other'

      // J. Anterior-Posterior Relationship
      anteriorPosterior: { value: null, otherText: '' },  // 'defer' | 'maintain' | 'iprIfNecessary' | 'elasticsOnly' | 'elasticsAndIpr' | 'other'

      // K / L / M
      midlineCorrection: null,  // 'yes' | 'no' | 'defer'
      overjetOverbite:   null,  // 'defer' | 'maintain' | 'improve'
      crossbite:         null,  // 'defer' | 'maintain' | 'improve'

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        // Hydrate from server-side prefill (if editing existing case)
        if (window.__additionalInfoPrefill) {
          this._hydrate(window.__additionalInfoPrefill);
        } else {
          // Fallback to localStorage if draft exists
          var draft = window.AddCaseState && window.AddCaseState.additionalInformation;
          if (draft) this._hydrate(draft);
        }

        var self = this;
        window.AdditionalInformationSection = {
          hydrate: function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Hydration ──────────────────────────────────────────────────────────

      _hydrate: function (d) {
        if (!d) return;
        if (typeof d.sectionExpanded === 'boolean') this.sectionExpanded = d.sectionExpanded;
        this._safeAssign(this.diagnosis, d.diagnosis);
        this._safeAssign(this.medicalHistory, d.medicalHistory);
        this._safeAssign(this.dentalHistory, d.dentalHistory);
        this._safeAssign(this.orthodonticHistory, d.orthodonticHistory);
        this._safeAssign(this.familyHistory, d.familyHistory);
        this._safeAssign(this.periodontalEvaluation, d.periodontalEvaluation);
        this._safeAssign(this.parafunctionalHabits, d.parafunctionalHabits);
        this._safeAssign(this.centricRelation, d.centricRelation);
        this._safeAssign(this.treatmentScope, d.treatmentScope);
        this._safeAssign(this.anteriorPosterior, d.anteriorPosterior);
        if (d.midlineCorrection !== undefined) this.midlineCorrection = d.midlineCorrection;
        if (d.overjetOverbite   !== undefined) this.overjetOverbite   = d.overjetOverbite;
        if (d.crossbite         !== undefined) this.crossbite         = d.crossbite;
      },

      // Recursively copies from source into target, only for keys already declared in target.
      _safeAssign: function (target, source) {
        if (!target || !source || typeof source !== 'object') return;
        var keys = Object.keys(target);
        for (var i = 0; i < keys.length; i++) {
          var k = keys[i];
          if (source[k] === undefined) continue;
          if (typeof target[k] === 'object' && target[k] !== null) {
            this._safeAssign(target[k], source[k]);
          } else {
            target[k] = source[k];
          }
        }
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        var payload = {
          sectionExpanded:        this.sectionExpanded,
          diagnosis:              this.diagnosis,
          medicalHistory:         this.medicalHistory,
          dentalHistory:          this.dentalHistory,
          orthodonticHistory:     this.orthodonticHistory,
          familyHistory:          this.familyHistory,
          periodontalEvaluation:  this.periodontalEvaluation,
          parafunctionalHabits:   this.parafunctionalHabits,
          centricRelation:        this.centricRelation,
          treatmentScope:         this.treatmentScope,
          anteriorPosterior:      this.anteriorPosterior,
          midlineCorrection:      this.midlineCorrection,
          overjetOverbite:        this.overjetOverbite,
          crossbite:              this.crossbite,
        };

        window.AddCaseState.additionalInformation = JSON.parse(JSON.stringify(payload));
        
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
          window.CaseApi.saveAdditionalInfo(window.CASE_ID, payload)
            .catch(function (err) { console.error('[AdditionalInfo] Save failed', err); });
        }, 1000); // 1s debounce
      },

      // ── Pattern B: Exclusivity ──────────────────────────────────────────────
      // When selectedKey is turned ON, all other boolean keys in the group are
      // cleared. Nested 'other' objects are also cleared.

      applyExclusivity: function (group, selectedKey) {
        var keys = Object.keys(group);
        for (var i = 0; i < keys.length; i++) {
          var k = keys[i];
          if (typeof group[k] === 'boolean') {
            group[k] = (k === selectedKey);
          } else if (k === 'other' && typeof group[k] === 'object') {
            group.other.enabled  = false;
            group.other.otherText = '';
          }
        }
        this.syncToState();
      },

      // ── Pattern C: WNL shortcut ─────────────────────────────────────────────
      // Called only when the WNL checkbox is CHECKED. Unchecking leaves state alone.

      applyWnl: function (subsection) {
        switch (subsection) {

          case 'medicalHistory':
            this.medicalHistory.allergies.enabled     = false;
            this.medicalHistory.allergies.description = '';
            this.medicalHistory.medications.enabled   = false;
            this.medicalHistory.medications.value     = null;
            this.medicalHistory.medications.otherText = '';
            this.medicalHistory.boneDisorders.enabled     = false;
            this.medicalHistory.boneDisorders.description = '';
            break;

          case 'dentalHistory':
            this.dentalHistory.trauma.enabled         = false;
            this.dentalHistory.trauma.description     = '';
            this.dentalHistory.poorPrognosis.enabled  = false;
            this.dentalHistory.poorPrognosis.description = '';
            this.dentalHistory.oralPathology    = false;
            this.dentalHistory.enamelPathologies = false;
            this.dentalHistory.attrition         = false;
            this.dentalHistory.speechDysfunction = false;
            this.dentalHistory.airwayProblems    = false;
            this.dentalHistory.tmjIssues         = false;
            this.dentalHistory.other.enabled     = false;
            this.dentalHistory.other.otherText   = '';
            break;

          case 'periodontal':
            this.periodontalEvaluation.oralHygiene       = 'wnl';
            this.periodontalEvaluation.frenalAttachments = 'wnl';
            var gt = this.periodontalEvaluation.gingivalTissue;
            gt.wnl = true; gt.inflamed = false; gt.bleeding = false;
            gt.pockets = false; gt.thinGingiva = false;
            gt.recession = false; gt.tissueGrafting = false;
            this.periodontalEvaluation.hardTissue.wnl             = true;
            this.periodontalEvaluation.hardTissue.boneLoss        = false;
            this.periodontalEvaluation.hardTissue.other.enabled   = false;
            this.periodontalEvaluation.hardTissue.other.otherText = '';
            break;
        }
        this.syncToState();
      },

      // ── Pattern D: Toggle handlers ──────────────────────────────────────────
      // Clear follow-up content whenever a toggle is turned OFF.

      onAllergiesToggle: function () {
        if (!this.medicalHistory.allergies.enabled)
          this.medicalHistory.allergies.description = '';
        this.syncToState();
      },

      onMedicationsToggle: function () {
        if (!this.medicalHistory.medications.enabled) {
          this.medicalHistory.medications.value    = null;
          this.medicalHistory.medications.otherText = '';
        }
        this.syncToState();
      },

      onMedicationsValueChange: function () {
        if (this.medicalHistory.medications.value !== 'other') {
          this.medicalHistory.medications.otherText = '';
        }
        this.syncToState();
      },

      onBoneDisordersToggle: function () {
        if (!this.medicalHistory.boneDisorders.enabled)
          this.medicalHistory.boneDisorders.description = '';
        this.syncToState();
      },

      onTraumaToggle: function () {
        if (!this.dentalHistory.trauma.enabled)
          this.dentalHistory.trauma.description = '';
        this.syncToState();
      },

      onPoorPrognosisToggle: function () {
        if (!this.dentalHistory.poorPrognosis.enabled)
          this.dentalHistory.poorPrognosis.description = '';
        this.syncToState();
      },

      onDentalOtherToggle: function () {
        if (!this.dentalHistory.other.enabled)
          this.dentalHistory.other.otherText = '';
        this.syncToState();
      },

      onOrthoOtherToggle: function () {
        if (!this.orthodonticHistory.other.enabled)
          this.orthodonticHistory.other.otherText = '';
        this.syncToState();
      },

      onHardTissueOtherToggle: function () {
        if (!this.periodontalEvaluation.hardTissue.other.enabled)
          this.periodontalEvaluation.hardTissue.other.otherText = '';
        this.syncToState();
      },

      onParaOtherToggle: function () {
        if (!this.parafunctionalHabits.other.enabled)
          this.parafunctionalHabits.other.otherText = '';
        this.syncToState();
      },

      // Pattern D + A: centric relation "Present" reveals textarea
      onCentricRelationChange: function () {
        if (this.centricRelation.value !== 'present')
          this.centricRelation.description = '';
        this.syncToState();
      },

      // Pattern A: "Other" radio clears text when a different option is picked
      onTreatmentScopeChange: function () {
        if (this.treatmentScope.value !== 'other')
          this.treatmentScope.otherText = '';
        this.syncToState();
      },

      onAnteriorPosteriorChange: function () {
        if (this.anteriorPosterior.value !== 'other')
          this.anteriorPosterior.otherText = '';
        this.syncToState();
      },
    };
  };

})();
