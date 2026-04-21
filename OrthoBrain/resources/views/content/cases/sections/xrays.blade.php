<section id="xrays" class="case-section"
         x-data="xraysSection()">

  <div class="case-section__header">
    <h4 class="case-section__title">X-Rays</h4>
    <p class="case-section__subtitle text-muted">Upload X-Ray Photos</p>
  </div>

  <div class="case-section__body">

    {{-- Re-upload notice after draft hydration (prototype: binaries lost on refresh) --}}
    <div class="alert alert-warning alert-dismissible fade show mb-1"
         x-show="showReuploadAlert"
         x-transition
         role="alert"
         style="display:none;">
      X-rays from a previous session need to be re-uploaded. Image files aren't saved in draft mode yet.
      <button type="button" class="btn-close" @click="showReuploadAlert = false" aria-label="Close"></button>
    </div>

    {{-- Bulk upload / validation error banner --}}
    <div class="alert alert-danger alert-dismissible fade show mb-1"
         x-show="bulkError"
         x-transition
         role="alert"
         style="display:none;">
      <span x-text="bulkError"></span>
      <button type="button" class="btn-close" @click="bulkError = null" aria-label="Close"></button>
    </div>

    {{-- Intro text --}}
    <p class="text-muted small mb-1">
      X-rays are required to proceed with this submission. X-rays provide the orthodontist with essential information needed to properly diagnose and plan orthodontic care. The orthodontist cannot Perfect Smile Plan without updated x-rays (taken within the last year). Please upload either a panoramic x-ray or a full mouth series (as one image/file). A lateral cephalogram is optional. We recommend uploading all of the corresponding images that you have for the patient. Files should be formatted as JPG, BMP, TIF, HEIC or PNG.
    </p>

    {{-- Date of X-Rays --}}
    <div class="mb-1">
      <label class="form-label fw-semibold" for="xray-date">
        Date of X-Rays <span class="text-danger">*</span>
      </label>
      <input type="date"
             class="form-control xrays-date-input"
             id="xray-date"
             x-model="dateOfXrays"
             @change="validateField('dateOfXrays'); syncToState()">
      <div class="small text-danger mt-25"
           x-show="errors.dateOfXrays"
           x-text="errors.dateOfXrays"
           style="display:none;"></div>
    </div>

    {{-- 3-tile row --}}
    @php
      $xrayTiles = [
        ['id' => 'lateral-ceph',      'label' => 'Lateral Cephalogram', 'placeholder' => 'xray-photo-01.jpg'],
        ['id' => 'panoramic',         'label' => 'Panoramic X-Ray',     'placeholder' => 'xray-photo-02.jpg'],
        ['id' => 'full-mouth-series', 'label' => 'Full Mouth Series',   'placeholder' => 'xray-photo-03.jpg'],
      ];
    @endphp

    <div class="media-grid media-grid--3tile mb-75">
      @foreach($xrayTiles as $tile)
        @include('content.cases.components.media-tile', [
          'tileId'          => $tile['id'],
          'poseLabel'       => $tile['label'],
          'placeholderPath' => asset('images/case-placeholders/xrays/' . $tile['placeholder']),
          'sectionType'     => 'xray',
        ])
      @endforeach
    </div>

    {{-- Section-level submit validation error --}}
    <div class="small text-danger mb-75"
         x-show="errors.xrayRequired"
         x-text="errors.xrayRequired"
         style="display:none;"></div>

    {{-- Bulk upload --}}
    <div class="mb-1">
      <button type="button" class="btn btn-outline-primary btn-sm" @click="openBulkPicker()">
        Upload Images
      </button>
      <input type="file"
             id="bulk-upload-xrays"
             class="d-none"
             multiple
             accept="image/jpeg,image/bmp,image/tiff,image/heic,image/png"
             @change="onBulkInputChange()">
    </div>

    {{-- Bottom note banner --}}
    <div class="alert alert-info mb-1" role="alert">
      <strong>Note:</strong> Uploaded images appear as thumbnails &mdash; please be assured we have received the entire file.
    </div>

    {{-- Tile modal (view / replace / remove / crop) --}}
    <div class="modal fade" id="xrayTileModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"
                x-text="tileModal.activeTileId ? getTileLabel(tileModal.activeTileId) : 'X-Ray'"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-1">
            <img x-show="tileModal.activeTileId && tiles[tileModal.activeTileId] && tiles[tileModal.activeTileId].previewUrl"
                 :src="tileModal.activeTileId && tiles[tileModal.activeTileId] ? (tiles[tileModal.activeTileId].previewUrl || '') : ''"
                 class="img-fluid rounded"
                 alt="X-Ray Preview"
                 style="max-height:55vh; display:none;">
          </div>
          <div class="modal-footer flex-wrap gap-50">
            <button type="button" class="btn btn-outline-primary btn-sm" @click="replaceTile()">Replace</button>
            <button type="button" class="btn btn-outline-danger btn-sm" @click="removeTile()">Remove</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="cropTile()">Crop</button>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /.case-section__body --}}
</section>
