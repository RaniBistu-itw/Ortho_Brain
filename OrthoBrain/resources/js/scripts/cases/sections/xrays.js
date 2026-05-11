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

      // B-1b: read-only mode for non-DRAFT cases. See prescription.js comment
      // for context. Bound to :disabled on the date input + bulk file input,
      // and to x-show on the bulk Upload Images button. Per-tile buttons
      // (Replace/Remove/Crop) live in the shared media-tile component and
      // are gated there via the same getter (parent Alpine scope).
      get isReadOnly() {
        return !!(window.AddCaseState && window.AddCaseState.isReadOnly);
      },

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

        // Always run _hydrate — server prefill (window.__caseMediaPrefill) is
        // the source of truth and lives outside the local AddCaseState draft.
        // Same parity fix as photographs.js: gating on the draft hid server
        // data on fresh page loads / cleared localStorage / different browsers.
        var draft = (window.AddCaseState && window.AddCaseState.xrays) || {};
        this._hydrate(draft);

        window.XRaysSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate: function (d) { self._hydrate(d); },
          flushUnsynced: function () { self._flushUnsynced(); },
        };

        this.syncToState();
      },

      // Re-upload any x-ray tiles whose blobs are in memory but never reached
      // the server (typically because _persistTile skipped while caseId was
      // 'new'). X-rays have NO IDB fallback, so without this they're lost on
      // first save-draft. Called from add-case.js after shell creation.
      _flushUnsynced: function () {
        var caseId = this._getCaseId();
        if (!window.CaseMediaApi || !caseId || caseId === 'new') return;
        var self = this;
        XRAY_TILE_ORDER.forEach(function (id) {
          var tile = self.tiles[id];
          if (!tile || !tile.filled) return;
          var blob = tile.croppedBlob || tile.originalFile;
          if (blob) self._persistTile(id, blob);
        });
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
          var self = this;
          window.CaseMediaApi.upload(caseId, 'xray', tileId, blob, {
            filename: 'xray-' + tileId,
            cropParams: cropParams,
          }).catch(function (err) {
            console.warn('CaseMediaApi.upload (xray) failed', tileId, err);
            self._handleUploadFailure(tileId, err);
          });
        }
      },

      // On upload rejection: clear the tile so the user doesn't see a
      // "filled" preview that the server hasn't accepted. X-rays have no
      // IDB fallback (unlike photographs), so the local blob would
      // disappear on refresh anyway — surfacing the failure tells the user
      // what happened and to retry.
      _handleUploadFailure: function (tileId, err) {
        var self = this;
        // 429: server is rate-limiting uploads. The local preview + IDB blob are
        // still valid — preserve the tile so the user can retry without re-selecting.
        if (err && err.status === 429) {
          var msg429 = window.MediaTileHelpers.upgradeUploadError(err, this.getTileLabel(tileId));
          this.bulkError = msg429;
          setTimeout(function () { self.bulkError = null; }, 6000);
          return;
        }
        if (this.tiles[tileId].previewUrl) {
          URL.revokeObjectURL(this.tiles[tileId].previewUrl);
        }
        this.tiles[tileId].filled = false;
        this.tiles[tileId].originalFile = null;
        this.tiles[tileId].croppedBlob = null;
        this.tiles[tileId].previewUrl = null;
        this.tiles[tileId].cropParams = null;
        this.syncToState();

        var msg = window.MediaTileHelpers.upgradeUploadError(err, this.getTileLabel(tileId));
        this.bulkError = msg;
        setTimeout(function () { self.bulkError = null; }, 6000);
      },

      _forgetTile: function (tileId) {
        var caseId = this._getCaseId();
        if (window.CaseMediaApi && caseId && caseId !== 'new') {
          var self = this;
          window.CaseMediaApi.destroy(caseId, 'xray', tileId)
            .catch(function (err) {
              console.warn('CaseMediaApi.destroy (xray) failed', tileId, err);
              // Destroy failure means the tile is locally cleared but the
              // server still holds the row — next reload would re-populate
              // it. Surface so the user knows the removal didn't stick.
              var msg = window.MediaTileHelpers.upgradeUploadError(err, self.getTileLabel(tileId));
              self.bulkError = msg;
              setTimeout(function () { self.bulkError = null; }, 6000);
            });
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
        // B-1b follow-up: defense-in-depth read-only guard. See photographs.js
        // for the full rationale — _processFile is the chokepoint for media
        // writes and is reached via several entry points.
        if (this.isReadOnly) return;
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

      onTileClick: function (tileId, evt) {
        // See photographs.js for the full rationale — short version: ignore
        // synthetic click events (isTrusted === false) so the bubble from
        // input.click() can't re-enter this handler and open the modal +
        // a second file dialog at once.
        if (evt && evt.isTrusted === false) return;

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
        return XRAY_TILE_LABELS[tileId] || tileId;
      },

      replaceTile: function () {
        // B-1b follow-up: read-only guard. See photographs.js comment.
        if (this.isReadOnly) return;
        var tileId = this.tileModal.activeTileId;
        if (!tileId) return;
        // Bypass the post-change recency guard from PR #78 — user explicitly
        // asked to replace, and the guard would otherwise silently no-op a
        // Replace clicked within 800ms of an upload.
        if (this._lastChangeByTile) delete this._lastChangeByTile[tileId];
        this._pendingReplaceTileId = tileId;
        this._closeTileModal();
      },

      removeTile: function (tileId) {
        // B-1b follow-up: read-only guard. See photographs.js comment.
        if (this.isReadOnly) return;
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

        var self = this;

        // Same URL→blob fetch pattern as photographs.js cropTile — see the
        // comment there for the full rationale. X-rays share the same
        // hydration shape (originalFile null after server prefill) and the
        // same fix applies. No AI re-classify call here (xrays has no AI).
        setTimeout(async function () {
          var blob = self.tiles[tileId].originalFile;

          if (!blob && self.tiles[tileId].previewUrl) {
            try {
              var res = await fetch(self.tiles[tileId].previewUrl, { credentials: 'same-origin' });
              if (!res.ok) throw { status: res.status, body: {} };
              var rawBlob = await res.blob();
              // Wrap as File — see photographs.js cropTile for rationale.
              blob = new File([rawBlob], tileId, { type: rawBlob.type });
              self.tiles[tileId].originalFile = blob;
            } catch (err) {
              var msg = window.MediaTileHelpers.upgradeCropFetchError(err, self.getTileLabel(tileId));
              self.bulkError = msg;
              setTimeout(function () { self.bulkError = null; }, 6000);
              return;
            }
          }

          if (!blob) {
            console.warn('cropTile (xray): tile filled but has no blob and no URL', tileId);
            return;
          }

          var preview = await window.MediaTileHelpers.createPreviewUrl(blob);
          window.CropModalController.open({
            imageUrl: preview.url,
            onApply: function (croppedBlob, cropParams) {
              URL.revokeObjectURL(preview.url);
              // Validate size BEFORE overwriting tile state — same rationale
              // as photographs.js cropTile: canvas re-encode can inflate blobs.
              if (croppedBlob.size > XRAY_CONFIG.maxSizeBytes) {
                self.bulkError = 'Cropped image exceeds 5 MB. Try a smaller selection or use the original.';
                setTimeout(function () { self.bulkError = null; }, 6000);
                return;
              }
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
        // B-1b follow-up: read-only guard. See photographs.js comment for
        // the desktop-file-drop gap that this closes.
        if (this.isReadOnly) return;
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

      _swapOrMoveTiles: async function (sourceId, targetId) {
        var src = this.tiles[sourceId];
        var tgt = this.tiles[targetId];

        // Capture pre-swap state — drives persistence strategy below. When a
        // tile holds only a server-side previewUrl (no blob), the old code
        // would skip the reorder call, silently leaving server state stale.
        // Detect that and route to the reorder endpoint instead.
        var srcHadBlob   = !!(src.croppedBlob || src.originalFile);
        var tgtHadBlob   = !!(tgt.croppedBlob || tgt.originalFile);
        var srcWasFilled = src.filled;
        var tgtWasFilled = tgt.filled;

        // Mixed-case guard: when both tiles aren't URL-only, the destroy+upload
        // path runs below. If ONE tile is URL-only, _forgetTile would DELETE
        // its server record. Fetch URL-only sides into blobs pre-swap so every
        // re-persist has actual content. (Both-URL-only is the fast path below.)
        var bothUrlOnly = srcWasFilled && !srcHadBlob && tgtWasFilled && !tgtHadBlob;
        var moveUrlOnly = srcWasFilled && !srcHadBlob && !tgtWasFilled;
        if (!bothUrlOnly && !moveUrlOnly) {
          try {
            if (srcWasFilled && !srcHadBlob && src.previewUrl) {
              var srcRes = await fetch(src.previewUrl, { credentials: 'same-origin' });
              if (!srcRes.ok) throw new Error('source URL fetch ' + srcRes.status);
              src.originalFile = await srcRes.blob();
              // Re-anchor preview to the blob — the destroy+upload below
              // invalidates the original server URL, which would otherwise
              // appear as a broken image on the OTHER tile after the swap.
              src.previewUrl = URL.createObjectURL(src.originalFile);
              srcHadBlob = true;
            }
            if (tgtWasFilled && !tgtHadBlob && tgt.previewUrl) {
              var tgtRes = await fetch(tgt.previewUrl, { credentials: 'same-origin' });
              if (!tgtRes.ok) throw new Error('target URL fetch ' + tgtRes.status);
              tgt.originalFile = await tgtRes.blob();
              tgt.previewUrl = URL.createObjectURL(tgt.originalFile);
              tgtHadBlob = true;
            }
          } catch (e) {
            console.warn('xrays: pre-swap URL→blob fetch failed, aborting swap', e);
            return;
          }
        }

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

        // URL-only swap/move → server-side reorder so the in-memory swap is
        // mirrored on disk. Without this, reopening the case shows x-rays in
        // their original slots.
        var caseId = this._getCaseId();
        if (bothUrlOnly || moveUrlOnly) {
          if (window.CaseMediaApi && caseId && caseId !== 'new') {
            var self = this;
            window.CaseMediaApi.reorder(caseId, 'xray', sourceId, targetId)
              .catch(function (err) {
                console.warn('CaseMediaApi.reorder failed', err);
                // Reorder failure means tiles are locally swapped but the
                // server still has the original layout — silent state
                // divergence on next reload. Surface so the user knows.
                var msg = window.MediaTileHelpers.upgradeUploadError(err, self.getTileLabel(targetId));
                self.bulkError = msg;
                setTimeout(function () { self.bulkError = null; }, 6000);
              });
          }
        }
      },

      // ── Bulk upload ─────────────────────────────────────────────────────────

      openBulkPicker: function () {
        var input = document.getElementById('bulk-upload-xrays');
        if (input) input.click();
      },

      onBulkInputChange: async function () {
        var input = document.getElementById('bulk-upload-xrays');
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
