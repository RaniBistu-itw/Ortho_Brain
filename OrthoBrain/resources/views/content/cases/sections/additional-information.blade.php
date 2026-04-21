<section id="additional-information" class="case-section" x-data="additionalInformationSection()">

  {{-- Section Header --}}
  <div class="case-section__header d-flex align-items-start justify-content-between gap-75">
    <div>
      <h4 class="case-section__title">Additional Information</h4>
      <p class="case-section__subtitle text-muted">
        Click + for a more comprehensive plan. We strongly encourage doctors to complete the optional questions by clicking the + icon.
      </p>
    </div>
    <button type="button"
            class="btn btn-outline-secondary btn-sm ai-collapse-btn flex-shrink-0"
            @click="sectionExpanded = !sectionExpanded; syncToState()"
            x-text="sectionExpanded ? '−' : '+'">−</button>
  </div>

  {{-- Section Body --}}
  <div x-show="sectionExpanded" x-transition style="display:none;">

    {{-- ── A. Diagnosis ────────────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">A. Diagnosis</p>
      <div class="ai-chip-row">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-crowding"
                 x-model="diagnosis.crowding" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-crowding">Crowding</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-spacing"
                 x-model="diagnosis.spacing" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-spacing">Spacing</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-deepbite"
                 x-model="diagnosis.deepBite" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-deepbite">Deep Bite</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-openbite"
                 x-model="diagnosis.openBite" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-openbite">Open Bite</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-overjet"
                 x-model="diagnosis.overjet" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-overjet">Overjet</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-ant-cross"
                 x-model="diagnosis.anteriorCrossbite" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-ant-cross">Anterior Crossbite</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-post-cross"
                 x-model="diagnosis.posteriorCrossbite" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-post-cross">Posterior Crossbite</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-class1"
                 x-model="diagnosis.classI" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-class1">Class I</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-class2"
                 x-model="diagnosis.classII" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-class2">Class II</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="ai-dx-class3"
                 x-model="diagnosis.classIII" @change="syncToState()">
          <label class="form-check-label" for="ai-dx-class3">Class III</label>
        </div>
      </div>
    </div>

    <hr class="my-75">

    {{-- ── B. Medical History ──────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">B. Medical History</p>

      <div class="ai-wnl-check form-check mb-75">
        <input class="form-check-input" type="checkbox" id="ai-mh-wnl"
               x-model="medicalHistory.wnl"
               @change="medicalHistory.wnl ? applyWnl('medicalHistory') : syncToState()">
        <label class="form-check-label fw-medium" for="ai-mh-wnl">
          If Medical History is within normal limits check here
        </label>
      </div>

      <div class="ai-toggle-list">

        {{-- Allergies (Pattern D) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-mh-allergy"
                   x-model="medicalHistory.allergies.enabled"
                   @change="onAllergiesToggle()">
            <label class="form-check-label" for="ai-mh-allergy">Allergies</label>
          </div>
          <div class="ai-follow-up" x-show="medicalHistory.allergies.enabled" x-transition style="display:none;">
            <label class="form-label small text-muted mb-25" for="ai-mh-allergy-desc">Please describe</label>
            <textarea class="form-control form-control-sm" id="ai-mh-allergy-desc" rows="2"
                      maxlength="5000" placeholder="Please describe"
                      x-model="medicalHistory.allergies.description"
                      @input="syncToState()"></textarea>
            <div class="d-flex justify-content-end small text-muted mt-25">
              <span x-text="(medicalHistory.allergies.description || '').length + ' / 5000'"></span>
            </div>
          </div>
        </div>

        {{-- Medications (Pattern D + Pattern A) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-mh-meds"
                   x-model="medicalHistory.medications.enabled"
                   @change="onMedicationsToggle()">
            <label class="form-check-label" for="ai-mh-meds">Medications</label>
          </div>
          <div class="ai-follow-up" x-show="medicalHistory.medications.enabled" x-transition style="display:none;">
            <div class="ai-toggle-list">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="ai-mh-meds-immuno"
                       name="ai-mh-meds-val" value="immunosuppressants"
                       x-model="medicalHistory.medications.value"
                       @change="onMedicationsValueChange()">
                <label class="form-check-label" for="ai-mh-meds-immuno">Immunosuppressants</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="ai-mh-meds-bone"
                       name="ai-mh-meds-val" value="boneModifier"
                       x-model="medicalHistory.medications.value"
                       @change="onMedicationsValueChange()">
                <label class="form-check-label" for="ai-mh-meds-bone">Bone Modifier</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="ai-mh-meds-calcium"
                       name="ai-mh-meds-val" value="calciumChannelBlocker"
                       x-model="medicalHistory.medications.value"
                       @change="onMedicationsValueChange()">
                <label class="form-check-label" for="ai-mh-meds-calcium">Calcium Channel Blocker</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="ai-mh-meds-anti"
                       name="ai-mh-meds-val" value="antiConvulsant"
                       x-model="medicalHistory.medications.value"
                       @change="onMedicationsValueChange()">
                <label class="form-check-label" for="ai-mh-meds-anti">Anti-convulsant</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="ai-mh-meds-other"
                       name="ai-mh-meds-val" value="other"
                       x-model="medicalHistory.medications.value"
                       @change="onMedicationsValueChange()">
                <label class="form-check-label" for="ai-mh-meds-other">Other</label>
              </div>
              <div class="ai-specify-input"
                   x-show="medicalHistory.medications.value === 'other'"
                   x-transition style="display:none;">
                <input type="text" class="form-control form-control-sm"
                       placeholder="Please specify" maxlength="200"
                       x-model="medicalHistory.medications.otherText"
                       @input="syncToState()">
              </div>
            </div>
          </div>
        </div>

        {{-- Bone Disorders (Pattern D) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-mh-bone"
                   x-model="medicalHistory.boneDisorders.enabled"
                   @change="onBoneDisordersToggle()">
            <label class="form-check-label" for="ai-mh-bone">Bone Disorders</label>
          </div>
          <div class="ai-follow-up" x-show="medicalHistory.boneDisorders.enabled" x-transition style="display:none;">
            <label class="form-label small text-muted mb-25" for="ai-mh-bone-desc">Please describe</label>
            <textarea class="form-control form-control-sm" id="ai-mh-bone-desc" rows="2"
                      maxlength="5000" placeholder="Please describe"
                      x-model="medicalHistory.boneDisorders.description"
                      @input="syncToState()"></textarea>
            <div class="d-flex justify-content-end small text-muted mt-25">
              <span x-text="(medicalHistory.boneDisorders.description || '').length + ' / 5000'"></span>
            </div>
          </div>
        </div>

      </div>{{-- /.ai-toggle-list --}}
    </div>

    <hr class="my-75">

    {{-- ── C. Dental History ───────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">C. Dental History</p>
      <p class="text-muted small mb-75">Does the patient currently have or a history of:</p>

      <div class="ai-wnl-check form-check mb-75">
        <input class="form-check-input" type="checkbox" id="ai-dh-wnl"
               x-model="dentalHistory.wnl"
               @change="dentalHistory.wnl ? applyWnl('dentalHistory') : syncToState()">
        <label class="form-check-label fw-medium" for="ai-dh-wnl">
          If Dental History is within normal limits check here
        </label>
      </div>

      <div class="ai-toggle-list">

        {{-- Trauma (Pattern D) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-trauma"
                   x-model="dentalHistory.trauma.enabled"
                   @change="onTraumaToggle()">
            <label class="form-check-label" for="ai-dh-trauma">Trauma</label>
          </div>
          <div class="ai-follow-up" x-show="dentalHistory.trauma.enabled" x-transition style="display:none;">
            <label class="form-label small text-muted mb-25" for="ai-dh-trauma-desc">Please describe</label>
            <textarea class="form-control form-control-sm" id="ai-dh-trauma-desc" rows="2"
                      maxlength="5000" placeholder="Please describe"
                      x-model="dentalHistory.trauma.description"
                      @input="syncToState()"></textarea>
            <div class="d-flex justify-content-end small text-muted mt-25">
              <span x-text="(dentalHistory.trauma.description || '').length + ' / 5000'"></span>
            </div>
          </div>
        </div>

        {{-- Poor Prognosis (Pattern D) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-prognosis"
                   x-model="dentalHistory.poorPrognosis.enabled"
                   @change="onPoorPrognosisToggle()">
            <label class="form-check-label" for="ai-dh-prognosis">Poor Prognosis (Hopeless)</label>
          </div>
          <div class="ai-follow-up" x-show="dentalHistory.poorPrognosis.enabled" x-transition style="display:none;">
            <label class="form-label small text-muted mb-25" for="ai-dh-prognosis-desc">Please describe</label>
            <textarea class="form-control form-control-sm" id="ai-dh-prognosis-desc" rows="2"
                      maxlength="5000" placeholder="Please describe"
                      x-model="dentalHistory.poorPrognosis.description"
                      @input="syncToState()"></textarea>
            <div class="d-flex justify-content-end small text-muted mt-25">
              <span x-text="(dentalHistory.poorPrognosis.description || '').length + ' / 5000'"></span>
            </div>
          </div>
        </div>

        {{-- Oral Pathology --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-oral-path"
                 x-model="dentalHistory.oralPathology" @change="syncToState()">
          <label class="form-check-label" for="ai-dh-oral-path">Oral Pathology</label>
        </div>

        {{-- Enamel Pathologies (Pattern E tooltip) --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-enamel"
                 x-model="dentalHistory.enamelPathologies" @change="syncToState()">
          <label class="form-check-label" for="ai-dh-enamel">
            Enamel Pathologies
            {{-- TODO: tooltip content from content review --}}
            <span class="ai-tooltip-icon"
                  data-bs-toggle="tooltip"
                  title="Enamel Pathologies">ⓘ</span>
          </label>
        </div>

        {{-- Attrition --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-attrition"
                 x-model="dentalHistory.attrition" @change="syncToState()">
          <label class="form-check-label" for="ai-dh-attrition">Attrition</label>
        </div>

        {{-- Speech Dysfunction --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-speech"
                 x-model="dentalHistory.speechDysfunction" @change="syncToState()">
          <label class="form-check-label" for="ai-dh-speech">Speech Dysfunction</label>
        </div>

        {{-- Airway Problems --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-airway"
                 x-model="dentalHistory.airwayProblems" @change="syncToState()">
          <label class="form-check-label" for="ai-dh-airway">Airway Problems</label>
        </div>

        {{-- TMJ/Masticatory Issues --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-tmj"
                 x-model="dentalHistory.tmjIssues" @change="syncToState()">
          <label class="form-check-label" for="ai-dh-tmj">TMJ/Masticatory Issues</label>
        </div>

        {{-- Other (Pattern A) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-dh-other"
                   x-model="dentalHistory.other.enabled"
                   @change="onDentalOtherToggle()">
            <label class="form-check-label" for="ai-dh-other">Other</label>
          </div>
          <div class="ai-specify-input" x-show="dentalHistory.other.enabled" x-transition style="display:none;">
            <input type="text" class="form-control form-control-sm"
                   placeholder="Please specify" maxlength="200"
                   x-model="dentalHistory.other.otherText"
                   @input="syncToState()">
          </div>
        </div>

      </div>{{-- /.ai-toggle-list --}}
    </div>

    <hr class="my-75">

    {{-- ── D. Orthodontic History ──────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">D. Orthodontic History of Patient</p>

      <div class="ai-toggle-list">

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-oh-no-prev"
                 x-model="orthodonticHistory.noPreviousTreatment" @change="syncToState()">
          <label class="form-check-label" for="ai-oh-no-prev">No Previous Treatment</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-oh-phase1"
                 x-model="orthodonticHistory.completedPhase1" @change="syncToState()">
          <label class="form-check-label" for="ai-oh-phase1">Completed Phase 1 Interceptive Treatment</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-oh-prev-comp"
                 x-model="orthodonticHistory.previousComprehensive" @change="syncToState()">
          <label class="form-check-label" for="ai-oh-prev-comp">Previous Comprehensive Treatment</label>
        </div>

        {{-- Other (Pattern A) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-oh-other"
                   x-model="orthodonticHistory.other.enabled"
                   @change="onOrthoOtherToggle()">
            <label class="form-check-label" for="ai-oh-other">Other</label>
          </div>
          <div class="ai-specify-input" x-show="orthodonticHistory.other.enabled" x-transition style="display:none;">
            <input type="text" class="form-control form-control-sm"
                   placeholder="Please specify" maxlength="200"
                   x-model="orthodonticHistory.other.otherText"
                   @input="syncToState()">
          </div>
        </div>

      </div>{{-- /.ai-toggle-list --}}
    </div>

    <hr class="my-75">

    {{-- ── E. Family History ───────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">E. Family History</p>

      <div class="ai-toggle-list">

        {{-- None (Pattern B exclusive) --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-fh-none"
                 x-model="familyHistory.none"
                 @change="familyHistory.none ? applyExclusivity(familyHistory, 'none') : syncToState()">
          <label class="form-check-label" for="ai-fh-none">None</label>
        </div>

        {{-- Not Known (Pattern B exclusive) --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-fh-not-known"
                 x-model="familyHistory.notKnown"
                 @change="familyHistory.notKnown ? applyExclusivity(familyHistory, 'notKnown') : syncToState()">
          <label class="form-check-label" for="ai-fh-not-known">Not Known</label>
        </div>

        {{-- Non-exclusive options — locked while None or Not Known is on --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-fh-extractions"
                 x-model="familyHistory.extractions"
                 :disabled="familyHistory.none || familyHistory.notKnown"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-fh-extractions">Relative Had Orthodontic Extractions</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-fh-canines"
                 x-model="familyHistory.impactedCanines"
                 :disabled="familyHistory.none || familyHistory.notKnown"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-fh-canines">Relative Had Impacted Canines</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-fh-ortho-surg"
                 x-model="familyHistory.orthognathicSurgery"
                 :disabled="familyHistory.none || familyHistory.notKnown"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-fh-ortho-surg">Relative Had Orthognathic Surgery</label>
        </div>

      </div>{{-- /.ai-toggle-list --}}
    </div>

    <hr class="my-75">

    {{-- ── F. Periodontal Evaluation ───────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">F. Periodontal Evaluation</p>

      <div class="ai-wnl-check form-check mb-75">
        <input class="form-check-input" type="checkbox" id="ai-pe-wnl"
               x-model="periodontalEvaluation.wnl"
               @change="periodontalEvaluation.wnl ? applyWnl('periodontal') : syncToState()">
        <label class="form-check-label fw-medium" for="ai-pe-wnl">
          If Periodontal Evaluation is within normal limits check here
        </label>
      </div>

      {{-- Oral Hygiene --}}
      <div class="mb-75">
        <p class="form-label fw-medium mb-25">Oral Hygiene</p>
        <div class="ai-toggle-list">
          <div class="form-check">
            <input class="form-check-input" type="radio" id="ai-pe-oh-wnl"
                   name="ai-pe-oral-hygiene" value="wnl"
                   x-model="periodontalEvaluation.oralHygiene" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-oh-wnl">Within Normal Limits</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" id="ai-pe-oh-fair"
                   name="ai-pe-oral-hygiene" value="fair"
                   x-model="periodontalEvaluation.oralHygiene" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-oh-fair">Fair</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" id="ai-pe-oh-poor"
                   name="ai-pe-oral-hygiene" value="poor"
                   x-model="periodontalEvaluation.oralHygiene" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-oh-poor">Poor</label>
          </div>
        </div>
      </div>

      {{-- Frenal Attachments --}}
      <div class="mb-75">
        <p class="form-label fw-medium mb-25">Frenal Attachments</p>
        <div class="ai-toggle-list">
          <div class="form-check">
            <input class="form-check-input" type="radio" id="ai-pe-fa-wnl"
                   name="ai-pe-frenal" value="wnl"
                   x-model="periodontalEvaluation.frenalAttachments" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-fa-wnl">Within Normal Limits</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" id="ai-pe-fa-excessive"
                   name="ai-pe-frenal" value="excessive"
                   x-model="periodontalEvaluation.frenalAttachments" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-fa-excessive">Excessive</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" id="ai-pe-fa-frenectomy"
                   name="ai-pe-frenal" value="frenectomy"
                   x-model="periodontalEvaluation.frenalAttachments" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-fa-frenectomy">History of Frenectomy</label>
          </div>
        </div>
      </div>

      {{-- Gingival Tissue --}}
      <div class="mb-75">
        <p class="form-label fw-medium mb-25">Gingival Tissue</p>
        <div class="ai-toggle-list">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-wnl"
                   x-model="periodontalEvaluation.gingivalTissue.wnl" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-wnl">Within Normal Limits</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-inflamed"
                   x-model="periodontalEvaluation.gingivalTissue.inflamed" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-inflamed">Inflamed</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-bleeding"
                   x-model="periodontalEvaluation.gingivalTissue.bleeding" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-bleeding">Bleeding on Probing</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-pockets"
                   x-model="periodontalEvaluation.gingivalTissue.pockets" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-pockets">4mm or Greater Pockets</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-thin"
                   x-model="periodontalEvaluation.gingivalTissue.thinGingiva" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-thin">Thin Attached Gingiva</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-recession"
                   x-model="periodontalEvaluation.gingivalTissue.recession" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-recession">Recession</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-gt-grafting"
                   x-model="periodontalEvaluation.gingivalTissue.tissueGrafting" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-gt-grafting">History of Tissue Grafting</label>
          </div>
        </div>
      </div>

      {{-- Hard Tissue Evaluation --}}
      <div class="mb-0">
        <p class="form-label fw-medium mb-25">Hard Tissue Evaluation</p>
        <div class="ai-toggle-list">
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-ht-wnl"
                   x-model="periodontalEvaluation.hardTissue.wnl" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-ht-wnl">Within Normal Limits</label>
          </div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-ht-boneloss"
                   x-model="periodontalEvaluation.hardTissue.boneLoss" @change="syncToState()">
            <label class="form-check-label" for="ai-pe-ht-boneloss">Bone Loss/Grafting/Tooth Mobility</label>
          </div>
          {{-- Other (Pattern A) --}}
          <div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="ai-pe-ht-other"
                     x-model="periodontalEvaluation.hardTissue.other.enabled"
                     @change="onHardTissueOtherToggle()">
              <label class="form-check-label" for="ai-pe-ht-other">Other</label>
            </div>
            <div class="ai-specify-input"
                 x-show="periodontalEvaluation.hardTissue.other.enabled"
                 x-transition style="display:none;">
              <input type="text" class="form-control form-control-sm"
                     placeholder="Please specify" maxlength="200"
                     x-model="periodontalEvaluation.hardTissue.other.otherText"
                     @input="syncToState()">
            </div>
          </div>
        </div>
      </div>

    </div>

    <hr class="my-75">

    {{-- ── G. Parafunctional Habits ────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">G. Parafunctional Habits</p>

      <div class="ai-toggle-list">

        {{-- None (Pattern B exclusive) --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-none"
                 x-model="parafunctionalHabits.none"
                 @change="parafunctionalHabits.none ? applyExclusivity(parafunctionalHabits, 'none') : syncToState()">
          <label class="form-check-label" for="ai-ph-none">None</label>
        </div>

        {{-- Non-exclusive options — locked while None is on --}}
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-finger"
                 x-model="parafunctionalHabits.fingerSucking"
                 :disabled="parafunctionalHabits.none"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-ph-finger">Finger Sucking</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-lip"
                 x-model="parafunctionalHabits.lipSucking"
                 :disabled="parafunctionalHabits.none"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-ph-lip">Lip Sucking</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-bruxing"
                 x-model="parafunctionalHabits.bruxing"
                 :disabled="parafunctionalHabits.none"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-ph-bruxing">Bruxing</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-clenching"
                 x-model="parafunctionalHabits.clenching"
                 :disabled="parafunctionalHabits.none"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-ph-clenching">Clenching</label>
        </div>

        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-tongue"
                 x-model="parafunctionalHabits.tongueThrusting"
                 :disabled="parafunctionalHabits.none"
                 @change="syncToState()">
          <label class="form-check-label" for="ai-ph-tongue">Tongue Thrusting</label>
        </div>

        {{-- Other (Pattern A, also locked by None) --}}
        <div>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="ai-ph-other"
                   x-model="parafunctionalHabits.other.enabled"
                   :disabled="parafunctionalHabits.none"
                   @change="onParaOtherToggle()">
            <label class="form-check-label" for="ai-ph-other">Other</label>
          </div>
          <div class="ai-specify-input"
               x-show="parafunctionalHabits.other.enabled"
               x-transition style="display:none;">
            <input type="text" class="form-control form-control-sm"
                   placeholder="Please specify" maxlength="200"
                   x-model="parafunctionalHabits.other.otherText"
                   @input="syncToState()">
          </div>
        </div>

      </div>{{-- /.ai-toggle-list --}}
    </div>

    <hr class="my-75">

    {{-- ── H. Centric Relation/Centric Occlusal Shift ──────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">H. Centric Relation/Centric Occlusal Shift</p>

      <div class="ai-toggle-list">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-cr-not-present"
                 name="ai-centric-relation" value="notPresent"
                 x-model="centricRelation.value" @change="onCentricRelationChange()">
          <label class="form-check-label" for="ai-cr-not-present">Not Present</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-cr-present"
                 name="ai-centric-relation" value="present"
                 x-model="centricRelation.value" @change="onCentricRelationChange()">
          <label class="form-check-label" for="ai-cr-present">Present</label>
        </div>
      </div>
      <div class="ai-follow-up"
           x-show="centricRelation.value === 'present'"
           x-transition style="display:none;">
        <label class="form-label small text-muted mb-25" for="ai-cr-desc">Please describe</label>
        <textarea class="form-control form-control-sm" id="ai-cr-desc" rows="2"
                  maxlength="5000" placeholder="Please describe"
                  x-model="centricRelation.description"
                  @input="syncToState()"></textarea>
        <div class="d-flex justify-content-end small text-muted mt-25">
          <span x-text="(centricRelation.description || '').length + ' / 5000'"></span>
        </div>
      </div>
    </div>

    <hr class="my-75">

    {{-- ── I. Treatment Scope ──────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">I. Treatment Scope</p>

      <div class="ai-toggle-list">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ts-aesthetics"
                 name="ai-treatment-scope" value="aesthetics"
                 x-model="treatmentScope.value" @change="onTreatmentScopeChange()">
          <label class="form-check-label" for="ai-ts-aesthetics">Focus on Aesthetics</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ts-comprehensive"
                 name="ai-treatment-scope" value="comprehensive"
                 x-model="treatmentScope.value" @change="onTreatmentScopeChange()">
          <label class="form-check-label" for="ai-ts-comprehensive">Comprehensive Treatment with a Functional Occlusion</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ts-other"
                 name="ai-treatment-scope" value="other"
                 x-model="treatmentScope.value" @change="onTreatmentScopeChange()">
          <label class="form-check-label" for="ai-ts-other">Other</label>
        </div>
      </div>
      <div class="ai-specify-input"
           x-show="treatmentScope.value === 'other'"
           x-transition style="display:none;">
        <input type="text" class="form-control form-control-sm"
               placeholder="Please specify" maxlength="200"
               x-model="treatmentScope.otherText"
               @input="syncToState()">
      </div>
    </div>

    <hr class="my-75">

    {{-- ── J. Anterior-Posterior Relationship ─────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">J. Anterior-Posterior Relationship</p>

      <div class="ai-toggle-list">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ap-defer"
                 name="ai-anterior-posterior" value="defer"
                 x-model="anteriorPosterior.value" @change="onAnteriorPosteriorChange()">
          <label class="form-check-label" for="ai-ap-defer">Defer to orthobrain®</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ap-maintain"
                 name="ai-anterior-posterior" value="maintain"
                 x-model="anteriorPosterior.value" @change="onAnteriorPosteriorChange()">
          <label class="form-check-label" for="ai-ap-maintain">Maintain</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ap-ipr"
                 name="ai-anterior-posterior" value="iprIfNecessary"
                 x-model="anteriorPosterior.value" @change="onAnteriorPosteriorChange()">
          <label class="form-check-label" for="ai-ap-ipr">Improve with IPR if Necessary</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ap-elastics"
                 name="ai-anterior-posterior" value="elasticsOnly"
                 x-model="anteriorPosterior.value" @change="onAnteriorPosteriorChange()">
          <label class="form-check-label" for="ai-ap-elastics">
            Improve with Elastics Only
            <span class="ai-tooltip-icon"
                  data-bs-toggle="tooltip"
                  title="Patient must be willing to wear full-time bite correction elastics">ⓘ</span>
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ap-elastics-ipr"
                 name="ai-anterior-posterior" value="elasticsAndIpr"
                 x-model="anteriorPosterior.value" @change="onAnteriorPosteriorChange()">
          <label class="form-check-label" for="ai-ap-elastics-ipr">
            Improve with Elastics &amp; IPR
            <span class="ai-tooltip-icon"
                  data-bs-toggle="tooltip"
                  title="Patient must be willing to wear full-time bite correction elastics">ⓘ</span>
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-ap-other"
                 name="ai-anterior-posterior" value="other"
                 x-model="anteriorPosterior.value" @change="onAnteriorPosteriorChange()">
          <label class="form-check-label" for="ai-ap-other">Other</label>
        </div>
      </div>
      <div class="ai-specify-input"
           x-show="anteriorPosterior.value === 'other'"
           x-transition style="display:none;">
        <input type="text" class="form-control form-control-sm"
               placeholder="Please specify" maxlength="200"
               x-model="anteriorPosterior.otherText"
               @input="syncToState()">
      </div>
    </div>

    <hr class="my-75">

    {{-- ── K. Midline Correction ───────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">K. Midline Correction</p>

      <div class="ai-toggle-list">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-mc-yes"
                 name="ai-midline" value="yes"
                 x-model="midlineCorrection" @change="syncToState()">
          <label class="form-check-label" for="ai-mc-yes">Yes</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-mc-no"
                 name="ai-midline" value="no"
                 x-model="midlineCorrection" @change="syncToState()">
          <label class="form-check-label" for="ai-mc-no">No</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-mc-defer"
                 name="ai-midline" value="defer"
                 x-model="midlineCorrection" @change="syncToState()">
          <label class="form-check-label" for="ai-mc-defer">Defer to orthobrain®</label>
        </div>
      </div>
    </div>

    <hr class="my-75">

    {{-- ── L. Overjet/Overbite ─────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">L. Overjet/Overbite</p>

      <div class="ai-toggle-list">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-oo-defer"
                 name="ai-overjet" value="defer"
                 x-model="overjetOverbite" @change="syncToState()">
          <label class="form-check-label" for="ai-oo-defer">Defer to orthobrain®</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-oo-maintain"
                 name="ai-overjet" value="maintain"
                 x-model="overjetOverbite" @change="syncToState()">
          <label class="form-check-label" for="ai-oo-maintain">Maintain</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-oo-improve"
                 name="ai-overjet" value="improve"
                 x-model="overjetOverbite" @change="syncToState()">
          <label class="form-check-label" for="ai-oo-improve">Improve</label>
        </div>
      </div>
    </div>

    <hr class="my-75">

    {{-- ── M. Crossbite ────────────────────────────────────────────────────── --}}
    <div class="ai-subsection">
      <p class="ai-subsection__title">M. Crossbite</p>

      <div class="ai-toggle-list">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-cb-defer"
                 name="ai-crossbite" value="defer"
                 x-model="crossbite" @change="syncToState()">
          <label class="form-check-label" for="ai-cb-defer">Defer to orthobrain®</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-cb-maintain"
                 name="ai-crossbite" value="maintain"
                 x-model="crossbite" @change="syncToState()">
          <label class="form-check-label" for="ai-cb-maintain">Maintain</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="ai-cb-improve"
                 name="ai-crossbite" value="improve"
                 x-model="crossbite" @change="syncToState()">
          <label class="form-check-label" for="ai-cb-improve">Improve</label>
        </div>
      </div>
    </div>

  </div>{{-- /x-show sectionExpanded --}}
</section>
