window.MediaTileHelpers = {
  // Translate a CaseMediaApi rejection ({ status, body }) into a user-facing
  // message naming the failed tile. Used by photographs.js + xrays.js so the
  // bulkError banner explains *why* an upload/destroy/reorder failed instead
  // of dropping the failure into console.warn (the bug class fixed in this
  // PR — see CLAUDE.md Entry 10).
  //
  // 422-vs-413 nuance: Laravel's `max:` rule returns 422 with errors.file,
  // not 413. We sniff body.errors.file for size-related copy ("greater",
  // "exceed", etc.) so it surfaces as "File too large" rather than the
  // generic "not a supported image" message. 413 stays mapped for forward
  // compatibility — proxy/PHP server-level limits (Nginx
  // client_max_body_size, post_max_size) DO fire genuine 413s.
  upgradeUploadError: function (err, tileLabel) {
    var label = tileLabel || 'image';
    var status = err && err.status;
    var body = (err && err.body) || {};

    var fileErrs = body.errors && body.errors.file;
    var isSize422 = status === 422 && Array.isArray(fileErrs) &&
      fileErrs.some(function (m) { return /(greater|exceed|larger|too large|max)/i.test(m); });
    if (isSize422 || status === 413) return 'File too large. Max 5 MB.';
    if (status === 422) {
      return "Couldn't upload " + label + ". The file isn't a supported image (JPG, PNG, HEIC).";
    }
    if (status >= 500 && status < 600) return 'Server error. Try again or contact support if it persists.';
    if (!status || status === 0) return "Couldn't reach the server. Check your connection and try again.";
    return "Couldn't upload " + label + '. Try again.';
  },

  // Returns { valid: boolean, error: string | null }
  validateFile: function (file, options) {
    var maxSizeBytes = options.maxSizeBytes;
    var allowedMimeTypes = options.allowedMimeTypes;
    var formatError = options.formatError || 'Invalid file format.';

    if (file.size > maxSizeBytes) {
      return { valid: false, error: 'File exceeds ' + Math.round(maxSizeBytes / 1024 / 1024) + 'MB limit.' };
    }

    // HEIC files often report empty MIME type in browsers; check extension too
    var fileName = file.name.toLowerCase();
    var isHeic = fileName.endsWith('.heic') || fileName.endsWith('.heif') || file.type === 'image/heic';
    if (isHeic && allowedMimeTypes.includes('image/heic')) {
      return { valid: true, error: null };
    }
    if (!allowedMimeTypes.includes(file.type)) {
      return { valid: false, error: formatError };
    }
    return { valid: true, error: null };
  },

  // Convert HEIC file to JPEG blob for preview
  convertHeicForPreview: async function (file) {
    try {
      var result = await window.heic2any({
        blob: file,
        toType: 'image/jpeg',
        quality: 0.85,
      });
      // heic2any returns Blob or Blob[]; normalize to single Blob
      return Array.isArray(result) ? result[0] : result;
    } catch (err) {
      console.error('HEIC conversion failed:', err);
      throw err;
    }
  },

  // Create an object URL, handling HEIC conversion first if needed.
  // Returns { url: string, cleanup: () => void }
  createPreviewUrl: async function (file) {
    var fileName = file.name.toLowerCase();
    var isHeic = fileName.endsWith('.heic') || fileName.endsWith('.heif') || file.type === 'image/heic';

    var blobForPreview = file;
    if (isHeic) {
      blobForPreview = await this.convertHeicForPreview(file);
    }

    var url = URL.createObjectURL(blobForPreview);
    return {
      url: url,
      cleanup: function () { URL.revokeObjectURL(url); },
    };
  },

  // ── Undo-toast for tile removal ──────────────────────────────────────────
  // Single-slot toast. Latest-wins: a second call commits the prior pending
  // action immediately (via its commit callback) and replaces the toast.
  _undoToastState: null,

  showUndoToast: function (label, onUndo, onCommit, autoCommitMs) {
    var self = this;
    var container = document.getElementById('media-undo-toast');
    if (!container) return;

    // Commit any prior pending toast first.
    if (this._undoToastState) {
      clearTimeout(this._undoToastState.timerId);
      try { this._undoToastState.onCommit && this._undoToastState.onCommit(); } catch (e) {}
      this._undoToastState = null;
    }

    var labelEl = container.querySelector('.media-undo-toast__label');
    var btnEl = container.querySelector('.media-undo-toast__undo');
    if (!labelEl || !btnEl) return;

    labelEl.textContent = label;
    container.classList.add('is-visible');
    container.setAttribute('aria-hidden', 'false');

    var handleUndo = function () {
      if (!self._undoToastState) return;
      clearTimeout(self._undoToastState.timerId);
      var cb = self._undoToastState.onUndo;
      self._undoToastState = null;
      self._hideUndoToastContainer();
      try { cb && cb(); } catch (e) {}
    };

    // Replace handler (avoid stacking listeners across successive toasts).
    btnEl.onclick = handleUndo;

    var timerId = setTimeout(function () {
      if (!self._undoToastState) return;
      var cb = self._undoToastState.onCommit;
      self._undoToastState = null;
      self._hideUndoToastContainer();
      try { cb && cb(); } catch (e) {}
    }, autoCommitMs || 5000);

    this._undoToastState = {
      onUndo: onUndo,
      onCommit: onCommit,
      timerId: timerId,
    };
  },

  _hideUndoToastContainer: function () {
    var container = document.getElementById('media-undo-toast');
    if (!container) return;
    container.classList.remove('is-visible');
    container.setAttribute('aria-hidden', 'true');
    var btnEl = container.querySelector('.media-undo-toast__undo');
    if (btnEl) {
      btnEl.onclick = null;
      btnEl.style.display = '';
    }
  },

  // Simple notification toast — no action button. Shares the same DOM slot as
  // showUndoToast; if a pending undo exists, commit it first (latest-wins).
  showToast: function (label, autoCloseMs) {
    var self = this;
    var container = document.getElementById('media-undo-toast');
    if (!container) return;

    if (this._undoToastState) {
      clearTimeout(this._undoToastState.timerId);
      try { this._undoToastState.onCommit && this._undoToastState.onCommit(); } catch (e) {}
      this._undoToastState = null;
    }
    if (this._plainToastTimer) {
      clearTimeout(this._plainToastTimer);
      this._plainToastTimer = null;
    }

    var labelEl = container.querySelector('.media-undo-toast__label');
    var btnEl = container.querySelector('.media-undo-toast__undo');
    if (!labelEl) return;

    labelEl.textContent = label;
    if (btnEl) btnEl.style.display = 'none';
    container.classList.add('is-visible');
    container.setAttribute('aria-hidden', 'false');

    this._plainToastTimer = setTimeout(function () {
      self._hideUndoToastContainer();
      self._plainToastTimer = null;
    }, autoCloseMs || 2000);
  },
};
