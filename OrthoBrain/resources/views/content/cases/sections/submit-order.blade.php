<section id="submit-order" class="case-section" x-data="submitOrderSection()">
  <div class="case-section__header">
    <h4 class="case-section__title">Submit Order</h4>
    <p class="case-section__subtitle text-muted">Submitter attestation</p>
  </div>
  <div class="case-section__body">

    {{-- Submitter Initials --}}
    <div class="mb-1">
      <label for="so-initials" class="form-label fw-semibold">
        Submitter Initials <span class="text-danger">*</span>
      </label>
      <input type="text"
             id="so-initials"
             class="form-control so-initials-input"
             x-model="submitterInitials"
             @input="onInitialsInput()"
             @blur="validateField('submitterInitials'); syncToState();"
             :class="{ 'is-invalid': errors.submitterInitials }"
             placeholder="e.g. JD"
             maxlength="5"
             autocomplete="off">
      <div class="form-text text-muted">2–5 letters only (e.g. JD, ABcd).</div>
      <div class="small text-danger mt-25"
           x-show="errors.submitterInitials"
           x-text="errors.submitterInitials"
           style="display:none;"></div>
    </div>

    {{-- Terms Agreement --}}
    <div class="mb-1">
      <div class="form-check">
        <input type="checkbox"
               id="so-terms"
               class="form-check-input"
               x-model="termsAgreed"
               @change="onTermsChange()"
               :class="{ 'is-invalid': errors.termsAgreed }">
        <label class="form-check-label so-terms-label" for="so-terms">
          I agree to our
          {{-- TODO: link to real T&C page --}}
          <a href="#">Terms and Conditions</a>
          for the
          {{-- TODO: link to real Doctor Agreement --}}
          <a href="#">Doctor Agreement</a>
          and
          {{-- TODO: link to real Registration Agreement --}}
          <a href="#">Registration Agreement</a>
          and I have reviewed the submission script. They are in excellent shape and ready to submit.
        </label>
      </div>
      <div class="small text-danger mt-25"
           x-show="errors.termsAgreed"
           x-text="errors.termsAgreed"
           style="display:none;"></div>
    </div>

  </div>
</section>
