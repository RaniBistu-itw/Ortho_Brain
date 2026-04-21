{{--
  Shared crop modal — included once in add-case.blade.php.
  Managed by window.CropModalController (crop-modal.js).
  NOT inside any Alpine x-data scope.
--}}
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="cropModalLabel">Crop Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="crop-modal__image-container">
          <img id="cropModalImage" src="" alt="Crop source" style="max-width:100%;">
        </div>
        <div class="crop-modal__controls mt-1 d-flex gap-50 flex-wrap">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="cropRotateLeft">
            Rotate Left 90°
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="cropRotateRight">
            Rotate Right 90°
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="cropReset">
            Reset
          </button>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="cropApplyBtn">Apply</button>
      </div>

    </div>
  </div>
</div>
