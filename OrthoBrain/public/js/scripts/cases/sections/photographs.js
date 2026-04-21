(function () {
  'use strict';

  var PHOTO_TILE_ORDER = [
    'profile', 'frontal-rest', 'frontal-smile',
    'upper-occlusal', 'frontal-bite', 'lower-occlusal',
    'right-buccal', 'frontal-retracted', 'left-buccal',
  ];

  var PHOTO_TILE_LABELS = {
    'profile':           'Profile',
    'frontal-rest':      'Frontal Rest',
    'frontal-smile':     'Frontal Smile',
    'upper-occlusal':    'Upper Occlusal',
    'frontal-bite':      'Frontal Bite',
    'lower-occlusal':    'Lower Occlusal',
    'right-buccal':      'Right Buccal',
    'frontal-retracted': 'Frontal Retracted',
    'left-buccal':       'Left Buccal',
  };

  var PHOTO_CONFIG = {
    maxSizeBytes: 20 * 1024 * 1024,
    allowedMimeTypes: ['image/png', 'image/gif', 'image/jpeg', 'image/tiff', 'image/bmp', 'image/heic'],
    formatError: 'Invalid file format. Accepted: png, gif, jpeg, jpg, tiff, bmp, heic.',
    acceptAttribute: 'image/png,image/gif,image/jpeg,image/tiff,image/bmp,image/heic',
  };

  window.photographsSection = function () {
    return {

      // ── Pre-declared reactive state ─────────────────────────────────────────
      dateOfPhotos: '',

      tiles: {
        'profile':           { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'frontal-rest':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'frontal-smile':     { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'upper-occlusal':    { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'frontal-bite':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'lower-occlusal':    { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'right-buccal':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'frontal-retracted': { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'left-buccal':       { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
      },

      tileModal: {
        activeTileId: '',
      },

      dragSourceTileId: null,

      errors: {
        dateOfPhotos: null,
        tiles: null,
      },

      showReuploadAlert: false,
      bulkError: null,
      acceptAttribute: PHOTO_CONFIG.acceptAttribute,

      // Internal (non-reactive in effect, but pre-declared per Alpine rule)
      _tileModalInstance: null,
      _pendingReplaceTileId: null,

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        var self = this;
        var modalEl = document.getElementById('photoTileModal');
        if (modalEl) {
          this._tileModalInstance = new bootstrap.Modal(modalEl);
          modalEl.addEventListener('hidden.bs.modal', function () {
            self.tileModal.activeTileId = '';
            if (self._pendingReplaceTileId) {
              var tid = self._pendingReplaceTileId;
              self._pendingReplaceTileId = null;
              setTimeout(function () { self._openFilePicker(tid); }, 50);
            }
          });
        }

        var draft = window.AddCaseState && window.AddCaseState.photographs;
        if (draft) {
          this._hydrate(draft);
        }

        window.PhotographsSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate: function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Draft hydration ─────────────────────────────────────────────────────

      _hydrate: function (d) {
        this.dateOfPhotos = d.dateOfPhotos || '';
        var hadFilled = false;
        var self = this;
        PHOTO_TILE_ORDER.forEach(function (id) {
          if (d.tiles && d.tiles[id] && d.tiles[id].filled) {
            hadFilled = true;
          }
          // TODO: replace with presigned S3 upload; persist S3 keys in draft state.
          // When swapped to S3, hydration will restore actual image previews from S3 URLs.
          self.tiles[id].filled = false;
          self.tiles[id].originalFile = null;
          self.tiles[id].croppedBlob = null;
          self.tiles[id].previewUrl = null;
          self.tiles[id].cropParams = (d.tiles && d.tiles[id]) ? (d.tiles[id].cropParams || null) : null;
        });
        if (hadFilled) {
          this.showReuploadAlert = true;
        }
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        var tilesState = {};
        var self = this;
        PHOTO_TILE_ORDER.forEach(function (id) {
          tilesState[id] = {
            filled: self.tiles[id].filled,
            cropParams: self.tiles[id].cropParams,
          };
        });
        window.AddCaseState.photographs = {
          dateOfPhotos: this.dateOfPhotos,
          tiles: tilesState,
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
      },

      // ── File processing ─────────────────────────────────────────────────────

      _processFile: async function (tileId, file) {
        var result = window.MediaTileHelpers.validateFile(file, PHOTO_CONFIG);
        if (!result.valid) {
          this.bulkError = result.error;
          var self = this;
          setTimeout(function () { self.bulkError = null; }, 6000);
          return false;
        }

        // Revoke existing preview URL before replacing
        if (this.tiles[tileId].previewUrl) {
          URL.revokeObjectURL(this.tiles[tileId].previewUrl);
        }

        try {
          var preview = await window.MediaTileHelpers.createPreviewUrl(file);
          this.tiles[tileId].filled = true;
          this.tiles[tileId].originalFile = file;
          this.tiles[tileId].croppedBlob = null;
          this.tiles[tileId].previewUrl = preview.url;
          this.tiles[tileId].cropParams = null;
          this.tiles[tileId].isDragOver = false;
          this.syncToState();
          return true;
        } catch (err) {
          this.bulkError = 'Failed to process image. Please try another file.';
          var self = this;
          setTimeout(function () { self.bulkError = null; }, 6000);
          return false;
        }
      },

      // ── Tile click ──────────────────────────────────────────────────────────

      onTileClick: function (tileId) {
        if (this.tiles[tileId].filled) {
          this._openTileModal(tileId);
        } else {
          this._openFilePicker(tileId);
        }
      },

      _openFilePicker: function (tileId) {
        var input = document.getElementById('tile-file-' + tileId);
        if (input) input.click();
      },

      onFileInputChange: async function (tileId) {
        var input = document.getElementById('tile-file-' + tileId);
        if (!input || !input.files.length) return;
        var file = input.files[0];
        await this._processFile(tileId, file);
        input.value = '';
      },

      // ── Tile modal ──────────────────────────────────────────────────────────

      _openTileModal: function (tileId) {
        this.tileModal.activeTileId = tileId;
        if (this._tileModalInstance) this._tileModalInstance.show();
      },

      _closeTileModal: function () {
        if (this._tileModalInstance) this._tileModalInstance.hide();
      },

      getTileLabel: function (tileId) {
        return PHOTO_TILE_LABELS[tileId] || tileId;
      },

      replaceTile: function () {
        var tileId = this.tileModal.activeTileId;
        if (!tileId) return;
        this._pendingReplaceTileId = tileId;
        this._closeTileModal();
        // _openFilePicker is called from the hidden.bs.modal handler
      },

      removeTile: function () {
        var tileId = this.tileModal.activeTileId;
        if (!tileId) return;
        if (!window.confirm('Remove this photo?')) return;
        this._closeTileModal();
        this._clearTile(tileId);
      },

      _clearTile: function (tileId) {
        if (this.tiles[tileId].previewUrl) {
          URL.revokeObjectURL(this.tiles[tileId].previewUrl);
        }
        this.tiles[tileId].filled = false;
        this.tiles[tileId].originalFile = null;
        this.tiles[tileId].croppedBlob = null;
        this.tiles[tileId].previewUrl = null;
        this.tiles[tileId].cropParams = null;
        this.syncToState();
      },

      cropTile: function () {
        var tileId = this.tileModal.activeTileId;
        if (!tileId || !this.tiles[tileId].filled) return;

        this._closeTileModal();

        // Always crop from original file so re-crop stays non-destructive
        var originalFile = this.tiles[tileId].originalFile;
        var self = this;

        setTimeout(function () {
          window.MediaTileHelpers.createPreviewUrl(originalFile).then(function (preview) {
            window.CropModalController.open({
              imageUrl: preview.url,
              onApply: function (croppedBlob, cropParams) {
                URL.revokeObjectURL(preview.url);
                if (self.tiles[tileId].previewUrl) {
                  URL.revokeObjectURL(self.tiles[tileId].previewUrl);
                }
                self.tiles[tileId].croppedBlob = croppedBlob;
                self.tiles[tileId].previewUrl = URL.createObjectURL(croppedBlob);
                self.tiles[tileId].cropParams = cropParams;
                self.syncToState();
              },
              onCancel: function () {
                URL.revokeObjectURL(preview.url);
              },
            });
          });
        }, 400);
      },

      // ── Drag and drop ───────────────────────────────────────────────────────

      onTileDragStart: function (event, tileId) {
        if (!this.tiles[tileId].filled) {
          this.dragSourceTileId = null;
          return;
        }
        this.dragSourceTileId = tileId;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/x-tile-id', tileId);
      },

      onTileDragOver: function (event, tileId) {
        this.tiles[tileId].isDragOver = true;
        event.dataTransfer.dropEffect = 'move';
      },

      onTileDragLeave: function (tileId) {
        this.tiles[tileId].isDragOver = false;
      },

      onTileDrop: async function (event, targetTileId) {
        this.tiles[targetTileId].isDragOver = false;

        var sourceTileId = event.dataTransfer.getData('text/x-tile-id');
        if (sourceTileId && sourceTileId !== targetTileId) {
          // Tile-to-tile drag (swap or move-to-empty)
          this._swapOrMoveTiles(sourceTileId, targetTileId);
          this.dragSourceTileId = null;
          return;
        }

        // Desktop file drag — use first file only
        var files = event.dataTransfer.files;
        if (files && files.length > 0) {
          await this._processFile(targetTileId, files[0]);
        }
      },

      _swapOrMoveTiles: function (sourceId, targetId) {
        var src = this.tiles[sourceId];
        var tgt = this.tiles[targetId];

        if (tgt.filled) {
          // Swap both tiles
          var tmpFilled    = src.filled;
          var tmpFile      = src.originalFile;
          var tmpBlob      = src.croppedBlob;
          var tmpUrl       = src.previewUrl;
          var tmpParams    = src.cropParams;

          this.tiles[sourceId].filled       = tgt.filled;
          this.tiles[sourceId].originalFile = tgt.originalFile;
          this.tiles[sourceId].croppedBlob  = tgt.croppedBlob;
          this.tiles[sourceId].previewUrl   = tgt.previewUrl;
          this.tiles[sourceId].cropParams   = tgt.cropParams;

          this.tiles[targetId].filled       = tmpFilled;
          this.tiles[targetId].originalFile = tmpFile;
          this.tiles[targetId].croppedBlob  = tmpBlob;
          this.tiles[targetId].previewUrl   = tmpUrl;
          this.tiles[targetId].cropParams   = tmpParams;
        } else {
          // Move source to empty target
          this.tiles[targetId].filled       = src.filled;
          this.tiles[targetId].originalFile = src.originalFile;
          this.tiles[targetId].croppedBlob  = src.croppedBlob;
          this.tiles[targetId].previewUrl   = src.previewUrl;
          this.tiles[targetId].cropParams   = src.cropParams;

          this.tiles[sourceId].filled       = false;
          this.tiles[sourceId].originalFile = null;
          this.tiles[sourceId].croppedBlob  = null;
          this.tiles[sourceId].previewUrl   = null;
          this.tiles[sourceId].cropParams   = null;
        }
        this.syncToState();
      },

      // ── Bulk upload ─────────────────────────────────────────────────────────

      openBulkPicker: function () {
        var input = document.getElementById('bulk-upload-photos');
        if (input) input.click();
      },

      onBulkInputChange: async function () {
        var input = document.getElementById('bulk-upload-photos');
        if (!input || !input.files.length) return;

        var files = Array.from(input.files);
        var emptyTiles = PHOTO_TILE_ORDER.filter(function (id) {
          return !this.tiles[id].filled;
        }.bind(this));

        if (files.length > emptyTiles.length) {
          this.bulkError = 'You have ' + emptyTiles.length + ' empty slot(s) remaining, but selected ' + files.length + ' image(s). Please select ' + emptyTiles.length + ' or fewer.';
          input.value = '';
          return;
        }

        this.bulkError = null;
        for (var i = 0; i < files.length; i++) {
          await this._processFile(emptyTiles[i], files[i]);
        }
        input.value = '';
      },

      // ── Validation ──────────────────────────────────────────────────────────

      validateField: function (field) {
        switch (field) {
          case 'dateOfPhotos':
            this.errors.dateOfPhotos = !this.dateOfPhotos ? 'Date of Photos is required.' : null;
            break;
          case 'tiles':
            var allFilled = PHOTO_TILE_ORDER.every(function (id) {
              return this.tiles[id].filled;
            }.bind(this));
            this.errors.tiles = !allFilled ? 'All 9 photos are required to submit.' : null;
            break;
        }
      },

      validateAll: function () {
        this.validateField('dateOfPhotos');
        this.validateField('tiles');
        return !this.errors.dateOfPhotos && !this.errors.tiles;
      },

    };
  };

})();
