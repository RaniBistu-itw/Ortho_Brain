<section id="prescription" class="case-section">
  <div class="case-section__header">
    <h4 class="case-section__title">Prescription</h4>
    <p class="case-section__subtitle text-muted">Prescription Information</p>
  </div>

  <div class="case-section__body"
       x-data="prescriptionSection()"
       @tooth-picker-change.window="onToothPickerChange($event)">

    {{-- ── 1. Arches to be Treated ───────────────────────────────────────────── --}}
    <div class="mb-1">
      <p class="form-label fw-semibold mb-50">
        Arches to be Treated <span class="text-danger">*</span>
      </p>
      <div class="d-flex flex-column gap-25">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-arches-both"
                 name="rx-arches" value="both"
                 x-model="arches" @change="syncToState()">
          <label class="form-check-label" for="rx-arches-both">Both Maxillary and Mandibular</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-arches-maxillary"
                 name="rx-arches" value="maxillary"
                 x-model="arches" @change="syncToState()">
          <label class="form-check-label" for="rx-arches-maxillary">Maxillary only</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-arches-mandibular"
                 name="rx-arches" value="mandibular"
                 x-model="arches" @change="syncToState()">
          <label class="form-check-label" for="rx-arches-mandibular">Mandibular only</label>
        </div>
      </div>
      <div class="small text-danger mt-25" x-show="errors.arches" x-text="errors.arches" style="display:none;"></div>
    </div>

    <hr class="my-1">

    {{-- ── 2. Preferences ────────────────────────────────────────────────────── --}}
    <div class="mb-1">
      <p class="form-label fw-semibold mb-50">Preferences</p>

      <div class="rx-pref-list">

        {{-- IPR Protocol --}}
        <div class="rx-pref-row">
          <div class="d-flex align-items-center gap-75">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch"
                     id="rx-ipr-toggle" x-model="ipr.on" @change="onIprToggle()">
              <label class="form-check-label fw-medium" for="rx-ipr-toggle">IPR Protocol</label>
            </div>
          </div>
          <div class="rx-pref-inner" x-show="ipr.on" x-transition style="display:none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ipr-defer"
                     name="rx-ipr" value="defer"
                     x-model="ipr.value" @change="validateField('ipr'); syncToState()">
              <label class="form-check-label" for="rx-ipr-defer">Defer to orthobrain®</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ipr-no"
                     name="rx-ipr" value="no-ipr"
                     x-model="ipr.value" @change="validateField('ipr'); syncToState()">
              <label class="form-check-label" for="rx-ipr-no">No IPR</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ipr-other"
                     name="rx-ipr" value="other"
                     x-model="ipr.value" @change="validateField('ipr'); syncToState()">
              <label class="form-check-label" for="rx-ipr-other">Other</label>
            </div>
            <div class="small text-danger mt-25" x-show="errors.ipr" x-text="errors.ipr" style="display:none;"></div>
          </div>
        </div>

        {{-- Attachments --}}
        <div class="rx-pref-row">
          <div class="d-flex align-items-center gap-75">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch"
                     id="rx-att-toggle" x-model="attachments.on" @change="onAttachmentsToggle()">
              <label class="form-check-label fw-medium" for="rx-att-toggle">Attachments</label>
            </div>
          </div>
          <div class="rx-pref-inner" x-show="attachments.on" x-transition style="display:none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-att-step1"
                     name="rx-attachments" value="step-1"
                     x-model="attachments.value" @change="validateField('attachmentsValue'); syncToState()">
              <label class="form-check-label" for="rx-att-step1">At Aligner Step 1</label>
            </div>
            <div class="form-check d-flex align-items-center gap-50 flex-wrap">
              <input class="form-check-input" type="radio" id="rx-att-specific"
                     name="rx-attachments" value="specific-step"
                     x-model="attachments.value" @change="validateField('attachmentsValue'); syncToState()">
              <label class="form-check-label mb-0" for="rx-att-specific">At Aligner Step</label>
              <input type="number"
                     class="rx-step-input form-control"
                     min="1"
                     placeholder="#"
                     x-show="attachments.value === 'specific-step'"
                     x-model.number="attachments.step"
                     @input="syncToState()"
                     @blur="validateField('attachmentsStep')"
                     style="display:none;">
            </div>
            <div class="small text-danger mt-25" x-show="errors.attachmentsValue" x-text="errors.attachmentsValue" style="display:none;"></div>
            <div class="small text-danger mt-25" x-show="errors.attachmentsStep" x-text="errors.attachmentsStep" style="display:none;"></div>
          </div>
        </div>

        {{-- Elastics / Bonded Buttons --}}
        <div class="rx-pref-row">
          <div class="d-flex align-items-center gap-75">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch"
                     id="rx-ela-toggle" x-model="elastics.on" @change="onElasticsToggle()">
              <label class="form-check-label fw-medium" for="rx-ela-toggle">Elastics/Bonded Buttons</label>
            </div>
          </div>
          <div class="rx-pref-inner" x-show="elastics.on" x-transition style="display:none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ela-yes"
                     name="rx-elastics" value="yes"
                     x-model="elastics.value" @change="validateField('elastics'); syncToState()">
              <label class="form-check-label" for="rx-ela-yes">Yes</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ela-no"
                     name="rx-elastics" value="no"
                     x-model="elastics.value" @change="validateField('elastics'); syncToState()">
              <label class="form-check-label" for="rx-ela-no">No</label>
            </div>
            <div class="small text-danger mt-25" x-show="errors.elastics" x-text="errors.elastics" style="display:none;"></div>
          </div>
        </div>

        {{-- Extractions if suggested --}}
        <div class="rx-pref-row">
          <div class="d-flex align-items-center gap-75">
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch"
                     id="rx-ext-toggle" x-model="extractions.on" @change="onExtractionsToggle()">
              <label class="form-check-label fw-medium" for="rx-ext-toggle">Extractions if suggested</label>
            </div>
          </div>
          <div class="rx-pref-inner" x-show="extractions.on" x-transition style="display:none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ext-yes"
                     name="rx-extractions" value="yes"
                     x-model="extractions.value" @change="validateField('extractions'); syncToState()">
              <label class="form-check-label" for="rx-ext-yes">Yes</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="rx-ext-no"
                     name="rx-extractions" value="no"
                     x-model="extractions.value" @change="validateField('extractions'); syncToState()">
              <label class="form-check-label" for="rx-ext-no">No</label>
            </div>
            <div class="small text-danger mt-25" x-show="errors.extractions" x-text="errors.extractions" style="display:none;"></div>
          </div>
        </div>

      </div>{{-- /.rx-pref-list --}}
    </div>

    <hr class="my-1">

    {{-- ── 3. Tooth Movement Restrictions ───────────────────────────────────── --}}
    <div class="mb-1 rx-restriction-block">
      <p class="form-label fw-semibold mb-50">
        Tooth Movement Restrictions <span class="text-danger">*</span>
        <small class="text-muted fw-normal">(eg: bridges, ankylosed teeth, implants, etc.)</small>
      </p>
      <div class="d-flex flex-column gap-25">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-tmr-none"
                 name="rx-tmr" value="none"
                 x-model="tmrMode" @change="onTmrModeChange()">
          <label class="form-check-label" for="rx-tmr-none">None</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-tmr-select"
                 name="rx-tmr" value="select"
                 x-model="tmrMode" @change="onTmrModeChange()">
          <label class="form-check-label" for="rx-tmr-select">Select Teeth that Should not be Moved</label>
        </div>
      </div>
      <div x-show="tmrMode === 'select'" x-transition style="display:none;">
        @include('content.cases.components.tooth-picker', [
          'instanceId'      => 'tmr',
          'selectedInitial' => '[]',
        ])
      </div>
      <div class="small text-danger mt-25" x-show="errors.tmrMode" x-text="errors.tmrMode" style="display:none;"></div>
      <div class="small text-danger mt-25" x-show="errors.tmrTeeth" x-text="errors.tmrTeeth" style="display:none;"></div>
    </div>

    <hr class="my-1">

    {{-- ── 4. Attachment Restrictions ────────────────────────────────────────── --}}
    <div class="mb-1 rx-restriction-block">
      <p class="form-label fw-semibold mb-50">
        Attachment Restrictions <span class="text-danger">*</span>
        <small class="text-muted fw-normal">(eg: crowns, bridges, veneers, buccal amalgams)</small>
      </p>
      <div class="d-flex flex-column gap-25">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-ar-none"
                 name="rx-ar" value="none"
                 x-model="arMode" @change="onArModeChange()">
          <label class="form-check-label" for="rx-ar-none">None</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-ar-select"
                 name="rx-ar" value="select"
                 x-model="arMode" @change="onArModeChange()">
          <label class="form-check-label" for="rx-ar-select">Select Teeth that Should not Have Attachments</label>
        </div>
      </div>
      <div x-show="arMode === 'select'" x-transition style="display:none;">
        @include('content.cases.components.tooth-picker', [
          'instanceId'      => 'ar',
          'selectedInitial' => '[]',
        ])
      </div>
      <div class="small text-danger mt-25" x-show="errors.arMode" x-text="errors.arMode" style="display:none;"></div>
      <div class="small text-danger mt-25" x-show="errors.arTeeth" x-text="errors.arTeeth" style="display:none;"></div>
    </div>

    <hr class="my-1">

    {{-- ── 5. Additional Comments ────────────────────────────────────────────── --}}
    <div class="mb-1">
      <label class="form-label fw-semibold" for="rx-additional-comments">Additional Comments</label>
      <textarea class="form-control"
                id="rx-additional-comments"
                rows="3"
                maxlength="5000"
                placeholder="Please Explain"
                x-model="additionalComments"
                @input="onAdditionalCommentsInput()"></textarea>
      <div class="text-muted small mt-25">
        <span x-text="additionalCommentsLen">0</span> / 5000 characters
      </div>
    </div>

    <hr class="my-1">

    {{-- ── 6. Future Restorative Work ────────────────────────────────────────── --}}
    <div class="mb-0">
      <p class="form-label fw-semibold mb-50">
        Future Restorative Work <span class="text-danger">*</span>
      </p>
      <div class="d-flex flex-column gap-25">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-frw-no"
                 name="rx-frw" value="no"
                 x-model="frwHasWork" @change="syncToState()">
          <label class="form-check-label" for="rx-frw-no">No</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="rx-frw-yes"
                 name="rx-frw" value="yes"
                 x-model="frwHasWork" @change="syncToState()">
          <label class="form-check-label" for="rx-frw-yes">Yes</label>
        </div>
      </div>
      <div x-show="frwHasWork === 'yes'" x-transition style="display:none;" class="mt-75">
        <label class="form-label" for="rx-frw-explanation">Please Explain <span class="text-danger">*</span></label>
        <textarea class="form-control"
                  id="rx-frw-explanation"
                  rows="3"
                  maxlength="5000"
                  placeholder="Please Explain"
                  x-model="frwExplanation"
                  @input="onFrwExplanationInput()"
                  @blur="validateField('frwExplanation')"></textarea>
        <div class="text-muted small mt-25">
          <span x-text="frwExplanationLen">0</span> / 5000 characters
        </div>
        <div class="small text-danger mt-25" x-show="errors.frwExplanation" x-text="errors.frwExplanation" style="display:none;"></div>
      </div>
      <div class="small text-danger mt-25" x-show="errors.frwHasWork" x-text="errors.frwHasWork" style="display:none;"></div>
    </div>

  </div>{{-- /.case-section__body --}}
</section>
