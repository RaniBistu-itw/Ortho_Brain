// Server-side persistence for Photographs & X-Rays.
// Companion to case-image-store.js (IndexedDB) — server is source of truth,
// IDB stays as offline cache.
//
// Endpoints (registered inside the dev/active.practice middleware group, see
// routes/web.php):
//   POST /dev/cases/{case}/media/upload                                 multipart
//   POST /dev/cases/{case}/media/{section}/{tile_id}/destroy
// Both endpoints use POST (not DELETE) to dodge the PHP 8.3 + Symfony 8
// request_parse_body() fatal on DELETE requests with JSON bodies.
// Admin variant routes are intentionally not provided here — admins read via
// the $caseMedia inlined into window.__caseMediaPrefill on the edit view.
(function () {
  'use strict';

  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function apiBase() {
    return (window.CASE_API_BASE || '/dev/cases');
  }

  function isUploadable(caseId) {
    // Brand-new shells haven't been persisted yet — caller should call
    // ensureShellCreated() in add-case.js BEFORE invoking us.
    return caseId && caseId !== 'new';
  }

  window.CaseMediaApi = {

    /**
     * Upload (or replace) a single tile's image.
     * Returns { ok, id, section, tile_id, url, mime, size } on 200,
     * or rejects with { status, body } on non-2xx.
     */
    upload: function (caseId, section, tileId, blob, options) {
      options = options || {};
      if (!isUploadable(caseId)) {
        return Promise.reject({ status: 0, body: { error: 'no-case-id' } });
      }

      var fd = new FormData();
      fd.append('section', section);
      fd.append('tile_id', tileId);
      // Filename matters less since the controller renames to a UUID; pass
      // a sensible default so multer-style middleware sees a name.
      var ext = (blob.type && blob.type.split('/')[1]) || 'bin';
      var filename = (options.filename || (tileId + '.' + ext));
      fd.append('file', blob, filename);
      if (options.cropParams) {
        fd.append('crop_params', JSON.stringify(options.cropParams));
      }

      return fetch(apiBase() + '/' + encodeURIComponent(caseId) + '/media/upload', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json',
        },
        body: fd,
      }).then(function (res) {
        if (!res.ok) {
          return res.json().catch(function () { return {}; }).then(function (body) {
            return Promise.reject({ status: res.status, body: body });
          });
        }
        return res.json();
      });
    },

    /**
     * Delete a tile's image. Idempotent — server returns ok even if it was
     * already gone. Uses POST (not DELETE) to avoid the PHP 8.3 fatal.
     */
    destroy: function (caseId, section, tileId) {
      if (!isUploadable(caseId)) {
        return Promise.resolve({ ok: true, message: 'no-case-id' });
      }
      var url = apiBase() + '/' + encodeURIComponent(caseId)
              + '/media/' + encodeURIComponent(section)
              + '/' + encodeURIComponent(tileId)
              + '/destroy';
      return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json',
        },
      }).then(function (res) {
        if (!res.ok) {
          return res.json().catch(function () { return {}; }).then(function (body) {
            return Promise.reject({ status: res.status, body: body });
          });
        }
        return res.json();
      });
    },

    /**
     * Read the prefill that the Blade inlined on page render. Returns an
     * array shaped [{section, tileId, url, mime, size, cropParams}, ...].
     */
    prefill: function () {
      return Array.isArray(window.__caseMediaPrefill) ? window.__caseMediaPrefill : [];
    },
  };

})();
