<section id="photographs" class="case-section"
         x-data="photographsSection()">

  <div class="case-section__header">
    <h4 class="case-section__title">Photographs</h4>
    <p class="case-section__subtitle text-muted">Upload Photographs</p>
  </div>

  <div class="case-section__body">

    {{-- Re-upload notice after draft hydration (prototype: binaries lost on refresh) --}}
    <div class="alert alert-warning alert-dismissible fade show mb-1"
         x-show="showReuploadAlert"
         x-transition
         role="alert"
         style="display:none;">
      Photos from a previous session need to be re-uploaded. Image files aren't saved in draft mode yet.
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
      Please drag and drop files from your computer onto the template, or click each photo to upload the corresponding patient photo. Maximum upload size is 20MB. Files should be formatted as png, gif, jpeg, jpg, tiff, bmp, heic.
    </p>

    {{-- Date of Photos --}}
    <div class="mb-1">
      <label class="form-label fw-semibold" for="photo-date">
        Date of Photos <span class="text-danger">*</span>
      </label>
      <input type="date"
             class="form-control photographs-date-input"
             id="photo-date"
             x-model="dateOfPhotos"
             @change="validateField('dateOfPhotos'); syncToState()">
      <div class="small text-danger mt-25"
           x-show="errors.dateOfPhotos"
           x-text="errors.dateOfPhotos"
           style="display:none;"></div>
    </div>

    {{-- 3×3 Photo grid --}}
    @php
      $photoTiles = [
        ['id' => 'profile',           'label' => 'Profile',           'placeholder' => 'placeholder-1.jpg'],
        ['id' => 'frontal-rest',      'label' => 'Frontal Rest',      'placeholder' => 'placeholder-2.jpg'],
        ['id' => 'frontal-smile',     'label' => 'Frontal Smile',     'placeholder' => 'placeholder-3.jpg'],
        ['id' => 'upper-occlusal',    'label' => 'Upper Occlusal',    'placeholder' => 'placeholder-4.jpg'],
        ['id' => 'frontal-bite',      'label' => 'Frontal Bite',      'placeholder' => 'placeholder-5.jpg'],
        ['id' => 'lower-occlusal',    'label' => 'Lower Occlusal',    'placeholder' => 'placeholder-6.jpg'],
        ['id' => 'right-buccal',      'label' => 'Right Buccal',      'placeholder' => 'placeholder-7.jpg'],
        ['id' => 'frontal-retracted', 'label' => 'Frontal Retracted', 'placeholder' => 'placeholder-8.jpg'],
        ['id' => 'left-buccal',       'label' => 'Left Buccal',       'placeholder' => 'placeholder-9.jpg'],
      ];
    @endphp

    <div class="media-grid media-grid--3col mb-75">
      @foreach($photoTiles as $tile)
        @include('content.cases.components.media-tile', [
          'tileId'          => $tile['id'],
          'poseLabel'       => $tile['label'],
          'placeholderPath' => asset('images/case-placeholders/photographs/' . $tile['placeholder']),
          'sectionType'     => 'photograph',
        ])
      @endforeach
    </div>

    {{-- Section-level submit validation error --}}
    <div class="small text-danger mb-75"
         x-show="errors.tiles"
         x-text="errors.tiles"
         style="display:none;"></div>

    {{-- Bulk upload --}}
    <div class="mb-1">
      <button type="button" class="btn btn-outline-primary btn-sm" @click="openBulkPicker()">
        Upload Images
      </button>
      <input type="file"
             id="bulk-upload-photos"
             class="d-none"
             multiple
             accept="image/png,image/gif,image/jpeg,image/tiff,image/bmp,image/heic"
             @change="onBulkInputChange()">
    </div>

    {{-- Tile modal (view / replace / remove / crop) --}}
    <div class="modal fade" id="photoTileModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"
                x-text="tileModal.activeTileId ? getTileLabel(tileModal.activeTileId) : 'Photo'"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center p-1">
            <img x-show="tileModal.activeTileId && tiles[tileModal.activeTileId] && tiles[tileModal.activeTileId].previewUrl"
                 :src="tileModal.activeTileId && tiles[tileModal.activeTileId] ? (tiles[tileModal.activeTileId].previewUrl || '') : ''"
                 class="img-fluid rounded"
                 alt="Photo Preview"
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
