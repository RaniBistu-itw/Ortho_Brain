(function () {
  'use strict';

  var PHOTO_TILE_ORDER = [
    'profile', 'frontal-rest', 'frontal-smile',
    'upper-occlusal', 'frontal-bite', 'lower-occlusal',
    'right-buccal', 'frontal-retracted', 'left-buccal',
  ];

  var MIN_PHOTOS = 4;

  window.perfectSmilePlanSection = function () {
    return {
      narrative: '',
      isGenerating: false,
      errorMessage: null,
      lastSource: null,

      // Reactive mirrors of cross-component state (photographs live in a
      // separate Alpine root; window.AddCaseState is a plain object, so we
      // have to refresh these by polling to opt into Alpine reactivity).
      uploadedCount: 0,
      hasEnoughPhotos: false,
      caseIsSaved: false,

      _abortController: null,
      _pollHandle: null,

      init: function () {
        var draft = window.AddCaseState && window.AddCaseState.perfectSmilePlan;
        if (draft && typeof draft.narrative === 'string') {
          this.narrative = draft.narrative;
        }
        this.syncToState();

        var self = this;
        function refresh() {
          var state = window.AddCaseState && window.AddCaseState.photographs;
          var count = 0;
          if (state && state.tiles) {
            PHOTO_TILE_ORDER.forEach(function (id) {
              if (state.tiles[id] && state.tiles[id].filled) count += 1;
            });
          }
          self.uploadedCount    = count;
          self.hasEnoughPhotos  = count >= MIN_PHOTOS;
          var id = self._caseId();
          self.caseIsSaved      = !!id && id !== 'new';
        }
        refresh();
        this._pollHandle = setInterval(refresh, 1500);

        window.PerfectSmilePlanSection = {
          generate: function () { self.generate(); },
          get narrative() { return self.narrative; },
        };
      },

      // ── Actions ───────────────────────────────────────────────────────────
      generate: async function () {
        if (this.isGenerating || !this.hasEnoughPhotos) return;
        if (!this.caseIsSaved) {
          this.errorMessage = 'Save the case as a draft first, then try again.';
          return;
        }
        if (!window.CaseApi || !window.CaseApi.generateSmilePlan) {
          this.errorMessage = 'Smile plan API is not available.';
          return;
        }

        var blobs = await this._collectBlobs();
        var filledTileIds = Object.keys(blobs);
        if (filledTileIds.length < MIN_PHOTOS) {
          this.errorMessage = 'Need at least ' + MIN_PHOTOS + ' uploaded photos.';
          return;
        }

        this.errorMessage = null;
        this.isGenerating = true;
        this.lastSource = null;

        this._abortController = new AbortController();
        var self = this;
        try {
          var res = await window.CaseApi.generateSmilePlan(this._caseId(), blobs, this._abortController.signal);
          self.narrative = (res && res.narrative) || '';
          self.lastSource = res && res.generatedAt ? 'Generated ' + self._formatStamp(res.generatedAt) : null;
          self.syncToState();
          if (window.feather) window.feather.replace();
        } catch (err) {
          if (err && err.name === 'AbortError') {
            // User-cancelled — don't surface an error.
          } else if (err && err.status === 503) {
            self.errorMessage = 'AI is temporarily unavailable. Please try again or write the plan manually below.';
          } else {
            self.errorMessage = (err && err.data && err.data.message) || 'Smile plan generation failed.';
            console.warn('generateSmilePlan failed', err);
          }
        } finally {
          self.isGenerating = false;
          self._abortController = null;
        }
      },

      cancel: function () {
        if (this._abortController) this._abortController.abort();
      },

      copyToClipboard: function () {
        if (!this.narrative) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(this.narrative).catch(function () { /* ignore */ });
        }
      },

      syncToState: function () {
        if (!window.AddCaseState) return;
        window.AddCaseState.perfectSmilePlan = {
          narrative: this.narrative,
        };
        if (window.AddCaseSave) window.AddCaseSave.markDirty();
      },

      // ── Internal helpers ──────────────────────────────────────────────────
      _caseId: function () {
        if (window.AddCaseSave && typeof window.AddCaseSave.currentCaseId === 'function') {
          var id = window.AddCaseSave.currentCaseId();
          if (id) return String(id);
        }
        if (window.CASE_ID) return String(window.CASE_ID);
        if (window.AddCaseState && window.AddCaseState.caseId) return String(window.AddCaseState.caseId);
        return 'new';
      },

      _collectBlobs: async function () {
        var caseId = this._caseId();
        var result = {};
        if (!window.CaseImageStore) return result;
        // Iterate tiles, ask IndexedDB for each blob that the photographs section reports as filled.
        var state = window.AddCaseState && window.AddCaseState.photographs;
        if (!state || !state.tiles) return result;

        for (var i = 0; i < PHOTO_TILE_ORDER.length; i++) {
          var tileId = PHOTO_TILE_ORDER[i];
          if (!state.tiles[tileId] || !state.tiles[tileId].filled) continue;
          try {
            var blob = await window.CaseImageStore.get(caseId, 'photographs', tileId);
            if (blob) result[tileId] = blob;
          } catch (e) {
            console.warn('CaseImageStore.get failed', tileId, e);
          }
        }
        return result;
      },

      _formatStamp: function (iso) {
        try {
          var d = new Date(iso);
          return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
          return iso;
        }
      },
    };
  };

})();
