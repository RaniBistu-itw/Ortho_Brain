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
    maxSizeBytes: 5 * 1024 * 1024,
    allowedMimeTypes: ['image/png', 'image/gif', 'image/jpeg', 'image/tiff', 'image/bmp', 'image/heic'],
    formatError: 'Invalid file format. Accepted: png, gif, jpeg, jpg, tiff, bmp, heic.',
    acceptAttribute: 'image/png,image/gif,image/jpeg,image/tiff,image/bmp,image/heic',
  };

  window.photographsSection = function () {
    return {

      // ── Pre-declared reactive state ─────────────────────────────────────────
      dateOfPhotos: '',

      tiles: {
        'profile':           { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'frontal-rest':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'frontal-smile':     { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'upper-occlusal':    { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'frontal-bite':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'lower-occlusal':    { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'right-buccal':      { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'frontal-retracted': { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
        'left-buccal':       { filled: false, originalFile: null, croppedBlob: null, previewUrl: null, cropParams: null, isDragOver: false, aiState: 'idle', aiWarning: null, aiConfidence: null },
      },

      tileModal: {
        activeTileId: '',
      },

      cameraModal: {
        activeTileId: '',
        facingMode: 'environment',
        canSwitch: false,
        isReady: false,
        isCapturing: false,
        error: null,
      },

      cameraSupported: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),

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
      _cameraModalInstance: null,
      _cameraStream: null,

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

        var camModalEl = document.getElementById('photoCameraModal');
        if (camModalEl && this.cameraSupported) {
          this._cameraModalInstance = new bootstrap.Modal(camModalEl);
          camModalEl.addEventListener('shown.bs.modal', function () {
            self._startCamera();
            if (window.feather) window.feather.replace();
          });
          camModalEl.addEventListener('hidden.bs.modal', function () {
            self._stopCameraStream();
            self.cameraModal.activeTileId = '';
            self.cameraModal.error = null;
            self.cameraModal.isReady = false;
            self.cameraModal.isCapturing = false;
          });

          if (navigator.mediaDevices.enumerateDevices) {
            navigator.mediaDevices.enumerateDevices().then(function (devices) {
              var cams = devices.filter(function (d) { return d.kind === 'videoinput'; });
              self.cameraModal.canSwitch = cams.length > 1;
            }).catch(function () { /* ignore — we'll just hide the switch button */ });
          }
        }

        // Always run _hydrate — server prefill (window.__caseMediaPrefill via
        // CaseMediaApi.prefill) is the source of truth and lives outside the
        // local AddCaseState draft. Gating on the draft skipped server data
        // for fresh page loads / cleared localStorage / different browsers.
        var draft = (window.AddCaseState && window.AddCaseState.photographs) || {};
        this._hydrate(draft);

        window.PhotographsSection = {
          validateAll: function () { return self.validateAll(); },
          hydrate: function (d) { self._hydrate(d); },
          flushUnsynced: function () { self._flushUnsynced(); },
        };

        this.syncToState();
      },

      // Re-upload any tiles whose blobs are in memory/IDB but never reached
      // the server (typically because _persistTile skipped while caseId was
      // 'new'). Called from add-case.js after ensureShellCreated() mints a
      // real case ID — without this, media uploaded before the first Save
      // Draft is stranded client-side.
      _flushUnsynced: function () {
        var caseId = this._getCaseId();
        if (!window.CaseMediaApi || !caseId || caseId === 'new') return;
        var self = this;
        PHOTO_TILE_ORDER.forEach(function (id) {
          var tile = self.tiles[id];
          if (!tile || !tile.filled) return;
          var blob = tile.croppedBlob || tile.originalFile;
          if (blob) self._persistTile(id, blob);
        });
      },

      // ── Draft hydration ─────────────────────────────────────────────────────

      _hydrate: function (d) {
        this.dateOfPhotos = d.dateOfPhotos || '';
        var self = this;
        var caseId = this._getCaseId();
        var pendingRestores = [];

        // Server-side prefill (window.__caseMediaPrefill) is the source of
        // truth — it survives across browsers and is what admins see. Build
        // a quick lookup keyed by tile_id so we can hydrate from the URL.
        var serverByTile = {};
        if (window.CaseMediaApi) {
          window.CaseMediaApi.prefill().forEach(function (m) {
            if (m && m.section === 'photograph' && m.tileId) {
              serverByTile[m.tileId] = m;
            }
          });
        }

        PHOTO_TILE_ORDER.forEach(function (id) {
          self.tiles[id].filled = false;
          self.tiles[id].originalFile = null;
          self.tiles[id].croppedBlob = null;
          self.tiles[id].previewUrl = null;
          self.tiles[id].cropParams = (d.tiles && d.tiles[id]) ? (d.tiles[id].cropParams || null) : null;

          // 1) Server URL wins — show it immediately, no blob fetch needed.
          var server = serverByTile[id];
          if (server && server.url) {
            self.tiles[id].filled = true;
            self.tiles[id].previewUrl = server.url;
            self.tiles[id].cropParams = server.cropParams || self.tiles[id].cropParams;
            // Originalfile stays null — re-cropping will refetch the source
            // via fetch() if the user opens the crop modal again.
            pendingRestores.push(Promise.resolve({ id: id, ok: true }));
            return;
          }

          // 2) Fallback: IndexedDB hydration for the same browser that
          //    uploaded (e.g. brand-new shell that hasn't synced to server).
          if (d.tiles && d.tiles[id] && d.tiles[id].filled && window.CaseImageStore) {
            pendingRestores.push(
              window.CaseImageStore.get(caseId, 'photographs', id).then(function (blob) {
                if (!blob) return { id: id, ok: false };
                self.tiles[id].filled = true;
                self.tiles[id].originalFile = blob;
                self.tiles[id].croppedBlob = null;
                self.tiles[id].previewUrl = URL.createObjectURL(blob);
                return { id: id, ok: true };
              }).catch(function () { return { id: id, ok: false }; })
            );
          } else if (d.tiles && d.tiles[id] && d.tiles[id].filled) {
            pendingRestores.push(Promise.resolve({ id: id, ok: false }));
          }
        });

        Promise.all(pendingRestores).then(function (results) {
          var anyMissing = results.some(function (r) { return !r.ok; });
          if (anyMissing) self.showReuploadAlert = true;
        });
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
          // Fire-and-forget: persist binary so the preview survives a refresh.
          // TODO: replace with presigned S3 PUT — same call site, same semantics.
          this._persistTile(tileId, file);
          // Fire-and-forget: ask the AI whether this image matches the tile.
          this._classifyTile(tileId, file);
          return true;
        } catch (err) {
          this.bulkError = 'Failed to process image. Please try another file.';
          var self = this;
          setTimeout(function () { self.bulkError = null; }, 6000);
          return false;
        }
      },

      _persistTile: function (tileId, blob) {
        var caseId = this._getCaseId();
        // Local IDB cache — fast same-browser hydration + offline robustness.
        if (window.CaseImageStore) {
          window.CaseImageStore.put(caseId, 'photographs', tileId, blob)
            .catch(function (e) { console.warn('CaseImageStore.put failed', tileId, e); });
        }
        // Server upload — source of truth. Fire-and-forget; the local preview
        // is already showing, so user doesn't wait on the network.
        if (window.CaseMediaApi && caseId && caseId !== 'new') {
          var cropParams = this.tiles[tileId] && this.tiles[tileId].cropParams;
          window.CaseMediaApi.upload(caseId, 'photograph', tileId, blob, {
            filename: 'photograph-' + tileId,
            cropParams: cropParams,
          }).catch(function (err) {
            console.warn('CaseMediaApi.upload failed', tileId, err);
          });
        }
      },

      // Ask the AI whether `blob` matches the pose for `tileId`. Non-blocking.
      // Skips silently if the case is still a 'new' shell — no endpoint to call.
      _classifyTile: function (tileId, blob) {
        var caseId = this._getCaseId();
        if (!caseId || caseId === 'new') return;
        if (!window.CaseApi || !window.CaseApi.classifyPhoto) return;

        var self = this;
        this.tiles[tileId].aiState = 'checking';
        this.tiles[tileId].aiWarning = null;

        window.CaseApi.classifyPhoto(caseId, tileId, blob)
          .then(function (res) {
            // Tile may have been cleared/replaced mid-flight — ignore stale result.
            if (!self.tiles[tileId].filled) return;

            if (res && res.mismatch) {
              self.tiles[tileId].aiState = 'warn';
              self.tiles[tileId].aiConfidence = res.confidence;
              self.tiles[tileId].aiWarning = 'Looks like ' + self.getTileLabel(res.predicted_tile);
            } else {
              self.tiles[tileId].aiState = 'ok';
              self.tiles[tileId].aiConfidence = res ? res.confidence : null;
              self.tiles[tileId].aiWarning = null;
            }
            if (window.feather) window.feather.replace();
          })
          .catch(function (err) {
            // AI is best-effort. Clear the checking state so the UI doesn't look stuck.
            self.tiles[tileId].aiState = 'idle';
            self.tiles[tileId].aiWarning = null;
            if (err && err.status !== 503) {
              console.warn('classifyPhoto failed', tileId, err);
            }
          });
      },

      _resetTileAi: function (tileId) {
        this.tiles[tileId].aiState = 'idle';
        this.tiles[tileId].aiWarning = null;
        this.tiles[tileId].aiConfidence = null;
      },

      _forgetTile: function (tileId) {
        var caseId = this._getCaseId();
        if (window.CaseImageStore) {
          window.CaseImageStore.remove(caseId, 'photographs', tileId)
            .catch(function (e) { console.warn('CaseImageStore.remove failed', tileId, e); });
        }
        if (window.CaseMediaApi && caseId && caseId !== 'new') {
          window.CaseMediaApi.destroy(caseId, 'photograph', tileId)
            .catch(function (err) { console.warn('CaseMediaApi.destroy failed', tileId, err); });
        }
      },

      _getCaseId: function () {
        if (window.AddCaseSave && typeof window.AddCaseSave.currentCaseId === 'function') {
          var id = window.AddCaseSave.currentCaseId();
          if (id) return String(id);
        }
        if (window.CASE_ID) return String(window.CASE_ID);
        if (window.AddCaseState && window.AddCaseState.caseId) return String(window.AddCaseState.caseId);
        return 'new';
      },

      // ── Tile click ──────────────────────────────────────────────────────────

      onTileClick: function (tileId, evt) {
        // Guard against synthetic click events (event.isTrusted === false).
        // The hidden <input type="file"> fires input.click() programmatically
        // when the user clicks an empty tile; the resulting synthetic click
        // event bubbles up to this .media-tile div despite @click.stop on
        // the input (cross-browser quirk — observed on both Chrome and
        // Firefox). Without this guard, the bubble re-enters onTileClick
        // AFTER _processFile has set tile.filled=true, which then opens
        // the photo modal AND queues a second OS file dialog, giving the
        // user a confusing "modal + dialog both open at once" experience.
        // Real user clicks (mouse, touch, keyboard Enter/Space) are
        // event.isTrusted === true and pass through normally.
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
        return PHOTO_TILE_LABELS[tileId] || tileId;
      },

      replaceTile: function () {
        var tileId = this.tileModal.activeTileId;
        if (!tileId) return;
        // User explicitly asked to replace — bypass the post-change recency
        // guard from PR #78. Without this, clicking Replace within 800ms of
        // an upload silently no-ops (modal closes, no file dialog opens).
        if (this._lastChangeByTile) delete this._lastChangeByTile[tileId];
        this._pendingReplaceTileId = tileId;
        this._closeTileModal();
        // _openFilePicker is called from the hidden.bs.modal handler
      },

      removeTile: function (tileId) {
        var id = tileId || this.tileModal.activeTileId;
        if (!id || !this.tiles[id].filled) return;

        // Snapshot for undo (refs, not clones — blobs are immutable)
        var snapshot = {
          originalFile: this.tiles[id].originalFile,
          croppedBlob: this.tiles[id].croppedBlob,
          previewUrl:  this.tiles[id].previewUrl,
          cropParams:  this.tiles[id].cropParams,
          aiState:     this.tiles[id].aiState,
          aiWarning:   this.tiles[id].aiWarning,
          aiConfidence: this.tiles[id].aiConfidence,
        };

        // Visually clear, but DO NOT revoke the previewUrl or forget the blob
        // until the undo window expires. Undo restores from the snapshot refs.
        this.tiles[id].filled = false;
        this.tiles[id].originalFile = null;
        this.tiles[id].croppedBlob = null;
        this.tiles[id].previewUrl = null;
        this.tiles[id].cropParams = null;
        this._resetTileAi(id);
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
            self.tiles[id].aiState = snapshot.aiState;
            self.tiles[id].aiWarning = snapshot.aiWarning;
            self.tiles[id].aiConfidence = snapshot.aiConfidence;
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
        this._resetTileAi(tileId);
        this.syncToState();
        this._forgetTile(tileId);
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
                self._persistTile(tileId, croppedBlob);
                // Cropped blob can change what the AI sees — re-classify.
                self._classifyTile(tileId, croppedBlob);
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

      _swapOrMoveTiles: async function (sourceId, targetId) {
        var src = this.tiles[sourceId];
        var tgt = this.tiles[targetId];

        // Capture pre-swap state — drives persistence strategy below. When a
        // tile holds only a server-side previewUrl (no blob), the old code
        // would call _forgetTile() during persistence, silently DELETING the
        // record. Detect that and route to the reorder endpoint instead.
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
            console.warn('photographs: pre-swap URL→blob fetch failed, aborting swap', e);
            return;
          }
        }

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

        // URL-only swaps/moves (server-loaded tiles with no client blob) need
        // the server to rename the tile_id on existing rows. Calling
        // destroy+upload here would delete the rows. (bothUrlOnly / moveUrlOnly
        // were captured pre-swap above — see the mixed-case fetch guard.)
        var caseId = this._getCaseId();
        if (bothUrlOnly || moveUrlOnly) {
          if (window.CaseMediaApi && caseId && caseId !== 'new') {
            window.CaseMediaApi.reorder(caseId, 'photograph', sourceId, targetId)
              .catch(function (err) { console.warn('CaseMediaApi.reorder failed', err); });
          }
          // Same content, just moved — no AI re-classify needed.
          return;
        }

        // Re-write IDB so blobs follow their new tile slots after the swap/move.
        var newSrcBlob = this.tiles[sourceId].croppedBlob || this.tiles[sourceId].originalFile;
        var newTgtBlob = this.tiles[targetId].croppedBlob || this.tiles[targetId].originalFile;
        if (newSrcBlob) { this._persistTile(sourceId, newSrcBlob); } else { this._forgetTile(sourceId); }
        if (newTgtBlob) { this._persistTile(targetId, newTgtBlob); } else { this._forgetTile(targetId); }

        // Blobs moved between slots — old AI verdicts are now meaningless. Re-classify.
        this._resetTileAi(sourceId);
        this._resetTileAi(targetId);
        if (newSrcBlob) this._classifyTile(sourceId, newSrcBlob);
        if (newTgtBlob) this._classifyTile(targetId, newTgtBlob);
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
          if (emptyTiles.length === 0) {
            this.bulkError = 'All photo slots are filled. Remove or replace an existing photo before uploading more.';
          } else {
            this.bulkError = 'Only ' + emptyTiles.length + ' photo slot(s) remain — please select at most ' + emptyTiles.length + ' file(s), or remove existing photos first.';
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

      // ── Camera capture ──────────────────────────────────────────────────────

      onTileCameraClick: function (tileId) {
        if (!this.cameraSupported) {
          this._openFilePicker(tileId);
          return;
        }
        this.cameraModal.activeTileId = tileId;
        this.cameraModal.error = null;
        this.cameraModal.isReady = false;
        this.cameraModal.isCapturing = false;
        // Stream starts on shown.bs.modal so the <video> element is in the DOM.
        if (this._cameraModalInstance) this._cameraModalInstance.show();
      },

      _startCamera: async function () {
        var self = this;
        this._stopCameraStream();
        this.cameraModal.isReady = false;
        this.cameraModal.error = null;
        try {
          this._cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: this.cameraModal.facingMode, width: { ideal: 1920 }, height: { ideal: 1080 } },
            audio: false,
          });
          var video = this.$refs.cameraVideo;
          if (!video) {
            this._stopCameraStream();
            this.cameraModal.error = 'Camera UI not ready. Close and try again.';
            return;
          }
          video.srcObject = this._cameraStream;
          video.onloadedmetadata = function () { self.cameraModal.isReady = true; };
        } catch (err) {
          this._stopCameraStream();
          if (err && (err.name === 'NotAllowedError' || err.name === 'SecurityError')) {
            this.cameraModal.error = 'Camera access blocked. Allow it in browser settings or use the file picker instead.';
          } else if (err && err.name === 'NotFoundError') {
            this.cameraModal.error = 'No camera detected on this device.';
          } else {
            this.cameraModal.error = 'Could not start camera: ' + (err && err.message ? err.message : 'unknown error');
          }
        }
      },

      switchCamera: function () {
        this.cameraModal.facingMode = (this.cameraModal.facingMode === 'user') ? 'environment' : 'user';
        this._startCamera();
      },

      capturePhoto: async function () {
        if (!this.cameraModal.isReady || this.cameraModal.isCapturing) return;
        var tileId = this.cameraModal.activeTileId;
        if (!tileId) return;

        this.cameraModal.isCapturing = true;
        try {
          var video = this.$refs.cameraVideo;
          var canvas = document.createElement('canvas');
          canvas.width  = video.videoWidth  || 1280;
          canvas.height = video.videoHeight || 720;
          var ctx = canvas.getContext('2d');
          // Front-cam preview is mirrored via CSS for natural feedback;
          // un-mirror at capture so the saved frame matches reality.
          // Rear-cam preview is not mirrored, so leave the frame as-is.
          if (this.cameraModal.facingMode === 'user') {
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
          }
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

          var self = this;
          var blob = await new Promise(function (resolve) {
            canvas.toBlob(function (b) { resolve(b); }, 'image/jpeg', 0.92);
          });
          if (!blob) {
            this.cameraModal.error = 'Capture failed. Try again.';
            return;
          }
          var file = new File([blob], 'camera-' + tileId + '-' + Date.now() + '.jpg', { type: 'image/jpeg' });
          if (this._cameraModalInstance) this._cameraModalInstance.hide();
          await self._processFile(tileId, file);
        } finally {
          this.cameraModal.isCapturing = false;
        }
      },

      _stopCameraStream: function () {
        if (this._cameraStream) {
          this._cameraStream.getTracks().forEach(function (t) { t.stop(); });
          this._cameraStream = null;
        }
        var video = this.$refs && this.$refs.cameraVideo;
        if (video) { video.srcObject = null; }
      },

    };
  };

})();
