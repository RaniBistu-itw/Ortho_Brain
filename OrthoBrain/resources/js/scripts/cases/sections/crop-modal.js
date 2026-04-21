// Shared crop modal controller — wraps Cropper.js in a Bootstrap 5 modal.
// Usage:
//   window.CropModalController.open({ imageUrl, onApply, onCancel })
//   onApply receives (croppedBlob, cropParams)

window.CropModalController = {
  _cropperInstance: null,
  _currentOnApply: null,
  _currentOnCancel: null,
  _modalEl: null,
  _modalInstance: null,
  _imgEl: null,

  init: function () {
    this._modalEl = document.getElementById('cropModal');
    if (!this._modalEl) return;

    this._imgEl = document.getElementById('cropModalImage');
    this._modalInstance = new bootstrap.Modal(this._modalEl);

    var self = this;

    document.getElementById('cropApplyBtn').addEventListener('click', function () {
      self._applyAndClose();
    });

    document.getElementById('cropRotateLeft').addEventListener('click', function () {
      if (self._cropperInstance) self._cropperInstance.rotate(-90);
    });

    document.getElementById('cropRotateRight').addEventListener('click', function () {
      if (self._cropperInstance) self._cropperInstance.rotate(90);
    });

    document.getElementById('cropReset').addEventListener('click', function () {
      if (self._cropperInstance) self._cropperInstance.reset();
    });

    // Destroy Cropper on ALL close paths (X button, ESC, backdrop click, Cancel button)
    this._modalEl.addEventListener('hide.bs.modal', function () {
      self._destroyCropper();
      var cb = self._currentOnCancel;
      self._currentOnApply = null;
      self._currentOnCancel = null;
      if (cb) cb();
    });
  },

  open: function (options) {
    var imageUrl = options.imageUrl;
    var onApply = options.onApply;
    var onCancel = options.onCancel || null;

    this._currentOnApply = onApply;
    this._currentOnCancel = onCancel;

    this._imgEl.src = imageUrl;
    this._modalInstance.show();

    var self = this;
    // Initialise Cropper only after the modal is fully visible
    this._modalEl.addEventListener('shown.bs.modal', function () {
      self._cropperInstance = new window.Cropper(self._imgEl, {
        viewMode: 1,
        autoCropArea: 1,
        responsive: true,
        checkOrientation: true,
      });
    }, { once: true });
  },

  _applyAndClose: function () {
    if (!this._cropperInstance) return;
    var self = this;
    this._cropperInstance.getCroppedCanvas().toBlob(function (blob) {
      var cropParams = self._cropperInstance.getData();
      var onApply = self._currentOnApply;
      // Clear callbacks BEFORE closing so hide.bs.modal doesn't fire onCancel
      self._currentOnApply = null;
      self._currentOnCancel = null;
      self._destroyCropper();
      self._modalInstance.hide();
      if (onApply) onApply(blob, cropParams);
    });
  },

  _destroyCropper: function () {
    if (this._cropperInstance) {
      this._cropperInstance.destroy();
      this._cropperInstance = null;
    }
  },
};

document.addEventListener('DOMContentLoaded', function () {
  window.CropModalController.init();
});
