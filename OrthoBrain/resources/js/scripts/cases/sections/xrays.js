(function () {
  'use strict';

  var XRAY_TILE_ORDER = ['lateral-ceph', 'panoramic', 'full-mouth-series'];

  var XRAY_TILE_LABELS = {
    'lateral-ceph':      'Lateral Cephalogram',
    'panoramic':         'Panoramic X-Ray',
    'full-mouth-series': 'Full Mouth Series',
  };

  var XRAY_CONFIG = {
    maxSizeBytes: 5 * 1024 * 1024,
    // No GIF — different from Photographs
    allowedMimeTypes: ['image/jpeg', 'image/bmp', 'image/tiff', 'image/heic', 'image/png'],
    formatError: 'Invalid file format. Accepted: JPG, BMP, TIF, HEIC, PNG.',
    acceptAttribute: 'image/jpeg,image/bmp,image/tiff,image/heic,image/png',
  };

  window.xraysSection = function () {
    return {

      // ── Pre-declared reactive state ─────────────────────────────────────────
      dateOfXrays: '',

      tiles: {
        'lateral-ceph':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'panoramic':         { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
        'full-mouth-series': { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false },
      },

      tileModal: {
        activeTileId: '',
      },

      dragSourceTileId: null,

      errors: {
        dateOfXrays: null,
        xrayRequired: null,
      },

      showReuploadAlert: false,
      bulkError: null,
      acceptAttribute: XRAY_CONFIG.acceptAttribute,

      _tileModalInstance: null,
      _pendingReplaceTileId: null,
      _lastPickerOpenAt: 0,
      _pickerLockedTile: null,
      _pickerLockTimer: null,
      _bulkPickerLocked: false,
      _bulkPickerLockTimer: null,

      // ── Alpine lifecycle ────────────────────────────────────────────────────

      init: function () {
        var self = this;
        var modalEl = document.getElementById('xrayTileModal');
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

        var draft = window.AddCaseState && window.AddCaseState.xrays;
        if (draft) {
          this._hydrate(draft);
        }

        window.XRaysSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate: function (d) { self._hydrate(d); },
        };

        this.syncToState();
      },

      // ── Draft hydration ─────────────────────────────────────────────────────

      _hydrate: function (d) {
        this.dateOfXrays = d.dateOfXrays || '';
        var hadFilledNoServer = false;
        var self = this;

        // Server-side prefill (window.__caseMediaPrefill) is the source of
        // truth — keyed by tile_id under the 'xray' section.
        var serverByTile = {};
        if (window.CaseMediaApi) {
          window.CaseMediaApi.prefill().forEach(function (m) {
            if (m && m.section === 'xray' && m.tileId) {
              serverByTile[m.tileId] = m;
            }
          });
        }

        XRAY_TILE_ORDER.forEach(function (id) {
          self.tiles[id].filled = false;
          self.tiles[id].originalFile = null;
          self.tiles[id].croppedBlob = null;
          self.tiles[id].previewUrl = null;
          self.tiles[id].cropParams = (d.tiles && d.tiles[id]) ? (d.tiles[id].cropParams || null) : null;

          var server = serverByTile[id];
          if (server && server.url) {
            self.tiles[id].filled = true;
            self.tiles[id].previewUrl = server.url;
            self.tiles[id].cropParams = server.cropParams || self.tiles[id].cropParams;
            return;
          }

          // Draft thinks this tile was filled but we don't have a server
          // record (and we don't keep an IDB copy for x-rays). Flag the gap.
          if (d.tiles && d.tiles[id] && d.tiles[id].filled) {
            hadFilledNoServer = true;
          }
        });

        if (hadFilledNoServer) {
          this.showReuploadAlert = true;
        }
      },

      _persistTile: function (tileId, blob) {
        var caseId = this._getCaseId();
        if (window.CaseMediaApi && caseId && caseId !== 'new') {
          var cropParams = this.tiles[tileId] && this.tiles[tileId].cropParams;
          window.CaseMediaApi.upload(caseId, 'xray', tileId, blob, {
            filename: 'xray-' + tileId,
            cropParams: cropParams,
          }).catch(function (err) {
            console.warn('CaseMediaApi.upload (xray) failed', tileId, err);
          });
        }
      },

      _forgetTile: function (tileId) {
        var caseId = this._getCaseId();
        if (window.CaseMediaApi && caseId && caseId !== 'new') {
          window.CaseMediaApi.destroy(caseId, 'xray', tileId)
            .catch(function (err) { console.warn('CaseMediaApi.destroy (xray) failed', tileId, err); });
        }
      },

      _getCaseId: function () {
        if (window.AddCaseSave && typeof window.AddCaseSave.currentCaseId === 'function') {
          var id = window.AddCaseSave.currentCaseId();
          if (id) return String(id);
        }
        if (window.CASE_ID) return String(window.CASE_ID);
        return 'new';
      },

      // ── State sync ──────────────────────────────────────────────────────────

      syncToState: function () {
        if (!window.AddCaseState) return;
        var tilesState = {};
        var self = this;
        XRAY_TILE_ORDER.forEach(function (id) {
          tilesState[id] = {
            filled: self.tiles[id].filled,
            cropParams: self.tiles[id].cropParams,
          };
        });
        window.AddCaseState.xrays = {
          dateOfXrays: this.dateOfXrays,
          tiles: tilesState,
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
      },

      // ── File processing ─────────────────────────────────────────────────────

      _processFile: async function (tileId, file) {
        var result = window.MediaTileHelpers.validateFile(file, XRAY_CONFIG);
        if (!result.valid) {
          this.bulkError = result.error;
          var self = this;
          setTimeout(function () { self.bulkError = null; }, 6000);
          return false;
        }

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
          // Fire-and-forget server upload — local preview is already showing.
          this._persistTile(tileId, file);
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
        var t = (Date.now() % 100000);
        // Diagnostic toasts — temporary, removed in the follow-up fix PR.
        if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
          window.MediaTileHelpers.showToast('XR-OPEN ' + tileId + ' @' + t, 2500);
        }

        if (this._pickerLockedTile === tileId) {
          if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
            window.MediaTileHelpers.showToast('XR-SKIP-locked ' + tileId, 2500);
          }
          return;
        }
        var now = Date.now();
        if (this._lastPickerOpenAt && (now - this._lastPickerOpenAt) < 250) {
          if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
            window.MediaTileHelpers.showToast('XR-SKIP-window ' + tileId, 2500);
          }
          return;
        }
        this._lastPickerOpenAt = now;

        var input = document.getElementById('tile-file-' + tileId);
        if (!input) return;

        this._pickerLockedTile = tileId;
        clearTimeout(this._pickerLockTimer);
        var self = this;
        this._pickerLockTimer = setTimeout(function () {
          if (self._pickerLockedTile === tileId) self._pickerLockedTile = null;
        }, 60000);

        input.click();
      },

      onFileInputChange: async function (tileId) {
        var t = (Date.now() % 100000);
        var input = document.getElementById('tile-file-' + tileId);
        var fileCount = input && input.files ? input.files.length : 0;
        if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
          window.MediaTileHelpers.showToast('XR-CHANGE ' + tileId + ' files=' + fileCount + ' @' + t, 2500);
        }

        if (this._pickerLockedTile === tileId) {
          this._pickerLockedTile = null;
          clearTimeout(this._pickerLockTimer);
        }

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
        return XRAY_TILE_LABELS[tileId] || tileId;
      },

      replaceTile: function () {
        var tileId = this.tileModal.activeTileId;
        if (!tileId) return;
        this._pendingReplaceTileId = tileId;
        this._closeTileModal();
      },

      removeTile: function (tileId) {
        var id = tileId || this.tileModal.activeTileId;
        if (!id || !this.tiles[id].filled) return;

        var snapshot = {
          originalFile: this.tiles[id].originalFile,
          croppedBlob: this.tiles[id].croppedBlob,
          previewUrl:  this.tiles[id].previewUrl,
          cropParams:  this.tiles[id].cropParams,
        };

        this.tiles[id].filled = false;
        this.tiles[id].originalFile = null;
        this.tiles[id].croppedBlob = null;
        this.tiles[id].previewUrl = null;
        this.tiles[id].cropParams = null;
        this.syncToState();

        if (this._tileModalInstance) this._closeTileModal();

        var self = this;
        window.MediaTileHelpers.showUndoToast(
          'Removed ' + this.getTileLabel(id),
          function onUndo() {
            self.tiles[id].originalFile = snapshot.originalFile;
            self.tiles[id].croppedBlob = snapshot.croppedBlob;
            self.tiles[id].previewUrl = snapshot.previewUrl;
            self.tiles[id].cropParams = snapshot.cropParams;
            self.tiles[id].filled = true;
            self.syncToState();
            if (window.feather) window.feather.replace();
          },
          function onCommit() {
            if (snapshot.previewUrl) URL.revokeObjectURL(snapshot.previewUrl);
            self._forgetTile(id);
          },
          5000
        );
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
                self._persistTile(tileId, croppedBlob);
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
          this._swapOrMoveTiles(sourceTileId, targetTileId);
          this.dragSourceTileId = null;
          return;
        }

        var files = event.dataTransfer.files;
        if (files && files.length > 0) {
          await this._processFile(targetTileId, files[0]);
        }
      },

      _swapOrMoveTiles: function (sourceId, targetId) {
        var src = this.tiles[sourceId];
        var tgt = this.tiles[targetId];

        if (tgt.filled) {
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
        var t = (Date.now() % 100000);
        if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
          window.MediaTileHelpers.showToast('XR-BULK-OPEN @' + t, 2500);
        }
        if (this._bulkPickerLocked) {
          if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
            window.MediaTileHelpers.showToast('XR-BULK-SKIP locked', 2500);
          }
          return;
        }
        var input = document.getElementById('bulk-upload-xrays');
        if (!input) return;
        this._bulkPickerLocked = true;
        clearTimeout(this._bulkPickerLockTimer);
        var self = this;
        this._bulkPickerLockTimer = setTimeout(function () {
          self._bulkPickerLocked = false;
        }, 60000);
        input.click();
      },

      onBulkInputChange: async function () {
        var t = (Date.now() % 100000);
        var input = document.getElementById('bulk-upload-xrays');
        var fileCount = input && input.files ? input.files.length : 0;
        if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
          window.MediaTileHelpers.showToast('XR-BULK-CHANGE files=' + fileCount + ' @' + t, 2500);
        }

        this._bulkPickerLocked = false;
        clearTimeout(this._bulkPickerLockTimer);

        if (!input || !input.files.length) return;

        var files = Array.from(input.files);
        var emptyTiles = XRAY_TILE_ORDER.filter(function (id) {
          return !this.tiles[id].filled;
        }.bind(this));

        if (files.length > emptyTiles.length) {
          if (emptyTiles.length === 0) {
            this.bulkError = 'All X-ray slots are filled. Remove or replace an existing X-ray before uploading more.';
          } else {
            this.bulkError = 'Only ' + emptyTiles.length + ' X-ray slot(s) remain — please select at most ' + emptyTiles.length + ' file(s), or remove existing X-rays first.';
          }
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
          case 'dateOfXrays':
            this.errors.dateOfXrays = !this.dateOfXrays ? 'Date of X-Rays is required.' : null;
            break;
          case 'xrayRequired':
            var hasRequired = this.tiles['panoramic'].filled || this.tiles['full-mouth-series'].filled;
            this.errors.xrayRequired = !hasRequired
              ? 'Either a Panoramic X-Ray or a Full Mouth Series is required.'
              : null;
            break;
        }
      },

      validateAll: function () {
        this.validateField('dateOfXrays');
        this.validateField('xrayRequired');
        return !this.errors.dateOfXrays && !this.errors.xrayRequired;
      },

    };
  };

})();
