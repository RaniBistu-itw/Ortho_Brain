<section id="shipping-address" class="case-section" x-data="shippingAddressSection()">
  <div class="case-section__header">
    <h4 class="case-section__title">Shipping Address</h4>
    <p class="case-section__subtitle text-muted">Where to ship this order</p>
  </div>
  <div class="case-section__body">

    {{-- Row 1: Saved Address (full width) --}}
    {{-- TODO: replace with API call to GET /api/doctors/{id}/saved-addresses --}}
    <div class="mb-1">
      <label for="sa-saved" class="form-label fw-semibold">Saved Address</label>
      <select id="sa-saved" class="form-select"
              x-model="savedAddressId"
              @change="onSavedAddressChange()">
        <option value="">— Select a saved address —</option>
        <option value="saved-1">Main Office</option>
        <option value="saved-2">Satellite Clinic</option>
      </select>
      <div class="form-text text-muted">Selecting a saved address will fill the fields below.</div>
    </div>

    {{-- Row 2: Practice | Doctor Name (read-only context labels) --}}
    <div class="row g-1 mb-1">
      <div class="col-12 col-md-6">
        <p class="sa-readonly-label mb-0">Practice</p>
        <p class="sa-readonly-value mb-0" x-text="practice || '—'"></p>
      </div>
      <div class="col-12 col-md-6">
        <p class="sa-readonly-label mb-0">Doctor Name</p>
        <p class="sa-readonly-value mb-0" x-text="doctorName || '—'"></p>
      </div>
    </div>

    {{-- Row 3: Street Address | Street Address 2 --}}
    <div class="row g-1 mb-1">
      <div class="col-12 col-md-6">
        <label for="sa-street" class="form-label fw-semibold">
          Street Address <span class="text-danger">*</span>
        </label>
        <div class="input-group has-validation">
          <span class="input-group-text">
            <i data-feather="map-pin" style="width:14px;height:14px;"></i>
          </span>
          <input type="text" id="sa-street" class="form-control"
                 x-model="streetAddress"
                 @input="syncToState()"
                 @blur="validateField('streetAddress')"
                 :class="{ 'is-invalid': errors.streetAddress }"
                 placeholder="Street address"
                 maxlength="200">
        </div>
        <div class="small text-danger mt-25"
             x-show="errors.streetAddress"
             x-text="errors.streetAddress"
             style="display:none;"></div>
      </div>
      <div class="col-12 col-md-6">
        <label for="sa-street2" class="form-label fw-semibold">Street Address 2</label>
        <div class="input-group">
          <span class="input-group-text">
            <i data-feather="map-pin" style="width:14px;height:14px;"></i>
          </span>
          <input type="text" id="sa-street2" class="form-control"
                 x-model="streetAddress2"
                 @input="syncToState()"
                 placeholder="Suite, floor, unit…"
                 maxlength="200">
        </div>
      </div>
    </div>

    {{-- Row 4: ZIP combobox | City --}}
    <div class="row g-1 mb-1">
      <div class="col-12 col-md-6">
        <label for="sa-zip" class="form-label fw-semibold">
          ZIP / Postal Code <span class="text-danger">*</span>
        </label>
        {{-- Custom combobox: type to filter MOCK_ZIP_ENTRIES; selecting cascades city/state/country --}}
        <div class="sa-zip-combobox" @click.outside="zipDropdownOpen = false">
          <input type="text" id="sa-zip" class="form-control"
                 x-model="zipQuery"
                 @focus="zipDropdownOpen = true"
                 @input="zipDropdownOpen = true"
                 @keydown.escape="zipDropdownOpen = false"
                 @blur="onZipBlur()"
                 :class="{ 'is-invalid': errors.zipId }"
                 placeholder="Search ZIP or postal code…"
                 autocomplete="off">
          <div class="sa-zip-dropdown"
               x-show="zipDropdownOpen && filteredZips().length > 0"
               style="display:none;">
            <template x-for="entry in filteredZips()" :key="entry.id">
              <div class="sa-zip-option" @mousedown.prevent="selectZip(entry)">
                <span x-text="entry.displayLabel"></span>
              </div>
            </template>
          </div>
        </div>
        <div class="small text-danger mt-25"
             x-show="errors.zipId"
             x-text="errors.zipId"
             style="display:none;"></div>
      </div>
      <div class="col-12 col-md-6">
        <label for="sa-city" class="form-label fw-semibold">
          City <span class="text-danger">*</span>
        </label>
        <input type="text" id="sa-city" class="form-control"
               x-model="city"
               @input="syncToState()"
               @blur="validateField('city')"
               :class="{ 'is-invalid': errors.city }"
               placeholder="City"
               maxlength="100">
        <div class="small text-danger mt-25"
             x-show="errors.city"
             x-text="errors.city"
             style="display:none;"></div>
      </div>
    </div>

    {{-- Row 5: State/Province | Country --}}
    <div class="row g-1 mb-1">
      <div class="col-12 col-md-6">
        <label for="sa-state" class="form-label fw-semibold">
          State / Province <span class="text-danger">*</span>
        </label>
        <input type="text" id="sa-state" class="form-control"
               x-model="state"
               @input="syncToState()"
               @blur="validateField('state')"
               :class="{ 'is-invalid': errors.state }"
               placeholder="State or province"
               maxlength="100">
        <div class="small text-danger mt-25"
             x-show="errors.state"
             x-text="errors.state"
             style="display:none;"></div>
      </div>
      <div class="col-12 col-md-6">
        <label for="sa-country" class="form-label fw-semibold">
          Country <span class="text-danger">*</span>
        </label>
        <select id="sa-country" class="form-select"
                x-model="country"
                @change="onCountryChange()"
                @blur="validateField('country')"
                :class="{ 'is-invalid': errors.country }">
          <option value="">— Select country —</option>
          <template x-for="c in (window.COUNTRY_ENTRIES || [])" :key="c.code">
            <option :value="c.code" x-text="c.name"></option>
          </template>
        </select>
        <div class="small text-danger mt-25"
             x-show="errors.country"
             x-text="errors.country"
             style="display:none;"></div>
      </div>
    </div>

  </div>
</section>
