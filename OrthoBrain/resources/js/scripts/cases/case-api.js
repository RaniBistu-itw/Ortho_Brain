// Thin wrapper around Laravel's cases/prescription endpoints.
// Usage:
//   await window.CaseApi.createShell()                  -> { id, redirect }
//   await window.CaseApi.savePrescription(id, payload)  -> { ok, savedAt }
//   await window.CaseApi.submitCase(id)                 -> { ok, redirect, message }
(function () {
  'use strict';

  function csrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function baseHeaders() {
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    };
  }

  async function request(method, url, body) {
    var res = await fetch(url, {
      method: method,
      credentials: 'same-origin',
      headers: baseHeaders(),
      body: body ? JSON.stringify(body) : undefined,
    });

    var text = await res.text();
    var data = null;
    try { data = text ? JSON.parse(text) : null; } catch (e) { /* non-JSON response */ }

    if (!res.ok) {
      var err = new Error('Request failed: ' + res.status);
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  }

  function base() {
    return window.CASE_API_BASE || '/dev/cases';
  }

  // Multipart variant — do NOT set Content-Type; the browser adds the boundary.
  async function multipart(url, formData, signal) {
    var res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
      signal: signal,
    });
    var text = await res.text();
    var data = null;
    try { data = text ? JSON.parse(text) : null; } catch (e) { /* non-JSON */ }
    if (!res.ok) {
      var err = new Error('Request failed: ' + res.status);
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  }

  window.CaseApi = {
    createShell: function () {
      // Admin context never creates shells (admins always edit existing cases),
      // so this stays anchored to the doctor route.
      return request('POST', '/dev/cases', {});
    },

    savePrescription: function (caseId, payload) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/prescription', payload);
    },

    submitCase: function (caseId) {
      return request('POST', '/dev/cases/' + encodeURIComponent(caseId) + '/submit', {});
    },

    updateStatus: function (caseId, status) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/status', { status: status });
    },

    // AI — real-time photo QC after upload. Non-blocking caller-side.
    // Returns { expected_tile, predicted_tile, confidence, reason, mismatch }.
    classifyPhoto: function (caseId, tileId, blob, signal) {
      var fd = new FormData();
      fd.append('tile_id', tileId);
      fd.append('photo', blob, tileId + '.bin');
      return multipart(base() + '/' + encodeURIComponent(caseId) + '/photos/classify', fd, signal);
    },

    // AI — generate the Perfect Smile Plan narrative from the uploaded photos.
    // `tileBlobs` is { tileId: Blob, ... }. Returns { narrative, generatedAt, photoCount }.
    generateSmilePlan: function (caseId, tileBlobs, signal) {
      var fd = new FormData();
      Object.keys(tileBlobs).forEach(function (tileId) {
        var blob = tileBlobs[tileId];
        if (blob) {
          fd.append('photos[' + tileId + ']', blob, tileId + '.bin');
        }
      });
      return multipart(base() + '/' + encodeURIComponent(caseId) + '/smile-plan/generate', fd, signal);
    },
  };
})();
