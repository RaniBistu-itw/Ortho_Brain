<section id="impressions" class="case-section" x-data="impressionsSection()">
  <div class="case-section__header">
    <h4 class="case-section__title">Impressions</h4>
    <p class="case-section__subtitle text-muted">Impression Method</p>
  </div>
  <div class="case-section__body">

    {{-- Impression Method dropdown --}}
    <div class="mb-1">
      <label for="imp-method" class="form-label fw-semibold">
        Impression Method <span class="text-danger">*</span>
      </label>
      {{-- Options are static HTML matching window.MOCK_SCANNERS structure --}}
      {{-- TODO: replace with API call to GET /api/impression-methods once endpoint exists --}}
      <select id="imp-method" class="form-select"
              x-model="impressionMethodId"
              @change="onMethodChange()"
              @blur="validateField('impressionMethod')"
              :class="{ 'is-invalid': errors.impressionMethod }">
        <option value="">Select scanner model or PVS</option>
        <optgroup label="iTero">
          <option value="itero-element-5d">Element 5D</option>
          <option value="itero-element-5d-plus">Element 5D Plus</option>
          <option value="itero-element-flex">Element Flex</option>
        </optgroup>
        <optgroup label="3Shape">
          <option value="3shape-trios-3">Trios 3</option>
          <option value="3shape-trios-4">Trios 4</option>
          <option value="3shape-trios-5">Trios 5</option>
        </optgroup>
        <optgroup label="Medit">
          <option value="medit-i500">i500</option>
          <option value="medit-i700">i700</option>
          <option value="medit-i900">i900</option>
        </optgroup>
        <optgroup label="Planmeca">
          <option value="planmeca-emerald">Emerald</option>
          <option value="planmeca-emerald-s">Emerald S</option>
        </optgroup>
        <optgroup label="Carestream">
          <option value="carestream-cs-3600">CS 3600</option>
          <option value="carestream-cs-3700">CS 3700</option>
          <option value="carestream-cs-3800">CS 3800</option>
        </optgroup>
        <optgroup label="Other / Non-digital">
          <option value="pvs">PVS (Polyvinyl Siloxane)</option>
        </optgroup>
      </select>
      <div class="small text-danger mt-25"
           x-show="errors.impressionMethod"
           x-text="errors.impressionMethod"
           style="display:none;"></div>
    </div>

    {{-- Info banner --}}
    <div class="alert alert-info d-flex align-items-start gap-75 mt-1" role="alert">
      <i data-feather="info" style="width:16px;height:16px;flex-shrink:0;margin-top:2px;"></i>
      <span>
        Please note: If you do not see your scanner on the list, please contact our support line
        <a href="tel:8883300790">888-330-0790</a> and we will be happy to help you.
      </span>
    </div>

    {{-- Warning banner --}}
    <div class="alert alert-warning d-flex align-items-start gap-75" role="alert">
      <i data-feather="alert-triangle" style="width:16px;height:16px;flex-shrink:0;margin-top:2px;"></i>
      <span>
        Note: To ensure quality appliances for you and your patients, all impressions must be current.
        The older they are, the less optimal the fit. The quality of the scans determines the quality
        of the appliances we make for you and your patients.
      </span>
    </div>

  </div>
</section>
