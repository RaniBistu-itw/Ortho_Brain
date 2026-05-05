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
      {{-- Sourced from the scanners master table (admin-managed at /admin/scanners). --}}
      <select id="imp-method" class="form-select"
              x-model="impressionMethodId"
              @change="onMethodChange()"
              @blur="validateField('impressionMethod')"
              :class="{ 'is-invalid': errors.impressionMethod }">
        <option value="">Select scanner model or PVS</option>
        @forelse($scanners ?? [] as $s)
          <option value="{{ $s->id }}">{{ $s->name }}</option>
        @empty
          <option value="" disabled>No scanners configured. Admin → /admin/scanners.</option>
        @endforelse
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
