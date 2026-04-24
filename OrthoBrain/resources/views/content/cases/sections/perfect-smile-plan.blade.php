<section id="perfect-smile-plan" class="case-section"
         x-data="perfectSmilePlanSection()">

  <div class="case-section__header">
    <h4 class="case-section__title">Perfect Smile Plan</h4>
    <p class="case-section__subtitle text-muted">AI-assisted clinical narrative from the uploaded photographs. Review and edit before submission.</p>
  </div>

  <div class="case-section__body">

    {{-- Soft warning when too few photos are available to generate something useful --}}
    <div class="alert alert-info mb-1"
         x-show="!hasEnoughPhotos"
         style="display:none;">
      Upload at least <strong>4</strong> photographs (ideally all 9) to generate a smile plan draft.
      <span x-show="uploadedCount > 0" x-text="'Currently uploaded: ' + uploadedCount + ' of 9.'"></span>
    </div>

    {{-- Error banner --}}
    <div class="alert alert-danger alert-dismissible fade show mb-1"
         x-show="errorMessage"
         role="alert"
         style="display:none;">
      <span x-text="errorMessage"></span>
      <button type="button" class="btn-close" @click="errorMessage = null" aria-label="Close"></button>
    </div>

    {{-- Action row --}}
    <div class="d-flex flex-wrap align-items-center gap-50 mb-1">
      <button type="button"
              class="btn btn-primary btn-sm d-flex align-items-center gap-25"
              :disabled="!hasEnoughPhotos || isGenerating || !caseIsSaved"
              @click="generate()">
        <i data-feather="cpu"></i>
        <span x-text="narrative ? 'Regenerate from Photos' : 'Generate from Photos'"></span>
      </button>

      <button type="button"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-25"
              x-show="isGenerating"
              @click="cancel()"
              style="display:none;">
        <i data-feather="x"></i> Cancel
      </button>

      <button type="button"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-25 ms-auto"
              x-show="narrative && !isGenerating"
              @click="copyToClipboard()"
              style="display:none;">
        <i data-feather="copy"></i> Copy
      </button>

      <span class="text-muted font-small-2"
            x-show="lastSource"
            x-text="lastSource"
            style="display:none;"></span>
    </div>

    {{-- Case-shell-required notice --}}
    <div class="alert alert-warning mb-1"
         x-show="!caseIsSaved"
         style="display:none;">
      Save the draft first (top bar → <strong>Save Draft</strong>) so the AI has a case to attach the plan to.
    </div>

    {{-- Skeleton while generating --}}
    <div class="perfect-smile-plan__skeleton mb-1"
         x-show="isGenerating"
         style="display:none;">
      <div class="placeholder-glow">
        <span class="placeholder col-4"></span>
        <span class="placeholder col-10"></span>
        <span class="placeholder col-8"></span>
        <span class="placeholder col-9"></span>
        <span class="placeholder col-3 mt-1"></span>
        <span class="placeholder col-7"></span>
        <span class="placeholder col-11"></span>
      </div>
    </div>

    {{-- Editable textarea, populated by AI, doctor-editable afterwards --}}
    <label for="smile-plan-narrative" class="form-label fw-semibold" x-show="narrative || !isGenerating">
      Clinical narrative (editable)
    </label>
    <textarea id="smile-plan-narrative"
              class="form-control perfect-smile-plan__textarea"
              rows="16"
              x-model="narrative"
              @input="syncToState()"
              x-show="narrative || !isGenerating"
              placeholder="Click 'Generate from Photos' to let the AI draft a clinical narrative from the uploaded images. You can freely edit the result before submission."></textarea>

    {{-- ──────────────────────────────────────────────────────────── --}}
    {{-- Before/After Smile Visualisation                              --}}
    {{-- ──────────────────────────────────────────────────────────── --}}
    <hr class="my-2">

    <div class="d-flex align-items-baseline gap-50 mb-50">
      <h6 class="mb-0 fw-semibold">Before / After Smile Visualisation</h6>
      <span class="badge bg-light-info">AI</span>
    </div>
    <p class="text-muted font-small-2 mb-1">
      Generate a predicted post-treatment view from the Frontal Smile photograph and the current prescription. The result is an AI visualisation, not a clinical guarantee.
    </p>

    <div class="alert alert-info mb-1"
         x-show="!frontalSmileReady"
         style="display:none;">
      Upload the <strong>Frontal Smile</strong> photograph first (in the Photographs section).
    </div>

    <div class="alert alert-danger alert-dismissible fade show mb-1"
         x-show="visualiseError"
         role="alert"
         style="display:none;">
      <span x-text="visualiseError"></span>
      <button type="button" class="btn-close" @click="visualiseError = null" aria-label="Close"></button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-50 mb-1">
      <button type="button"
              class="btn btn-primary btn-sm d-flex align-items-center gap-25"
              :disabled="!frontalSmileReady || !caseIsSaved || isVisualising"
              @click="visualise()">
        <i data-feather="image"></i>
        <span x-text="previewImage ? 'Regenerate Visualisation' : 'Visualise Outcome'"></span>
      </button>

      <button type="button"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-25"
              x-show="isVisualising"
              @click="cancelVisualise()"
              style="display:none;">
        <i data-feather="x"></i> Cancel
      </button>

      <button type="button"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-25"
              x-show="previewImage && !isVisualising"
              @click="downloadPreview()"
              style="display:none;">
        <i data-feather="download"></i> Download
      </button>

      <button type="button"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-25"
              x-show="previewImage && !isVisualising"
              @click="clearPreview()"
              style="display:none;">
        <i data-feather="eye-off"></i> Hide
      </button>

      <span class="text-muted font-small-2 ms-auto"
            x-show="previewProvider && previewGeneratedAt"
            x-text="'Generated via ' + previewProvider"
            style="display:none;"></span>
    </div>

    {{-- Side-by-side before/after grid. Stacks on narrow screens. --}}
    <div class="row g-1"
         x-show="previewOriginal || previewImage || isVisualising"
         style="display:none;">

      <div class="col-md-6">
        <div class="perfect-smile-plan__preview-card">
          <div class="perfect-smile-plan__preview-label">Current smile</div>
          <img :src="previewOriginal || ''"
               x-show="previewOriginal"
               class="perfect-smile-plan__preview-img"
               alt="Current frontal smile"
               style="display:none;">
        </div>
      </div>

      <div class="col-md-6">
        <div class="perfect-smile-plan__preview-card">
          <div class="perfect-smile-plan__preview-label">Predicted outcome</div>

          {{-- Loading skeleton --}}
          <div class="placeholder-glow perfect-smile-plan__preview-skeleton"
               x-show="isVisualising"
               style="display:none;">
            <span class="placeholder w-100"></span>
          </div>

          {{-- Predicted image + watermark overlay --}}
          <div class="perfect-smile-plan__preview-wrapper"
               x-show="previewImage && !isVisualising"
               style="display:none;">
            <img :src="previewImage || ''"
                 class="perfect-smile-plan__preview-img"
                 alt="Predicted post-treatment smile">
            <div class="perfect-smile-plan__preview-watermark">
              AI visualisation — not a clinical guarantee
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
