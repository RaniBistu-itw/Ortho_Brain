// Thin wrapper around Laravel's cases/prescription/patient endpoints.
// Usage:
//   await window.CaseApi.createShell()                  -> { id, redirect }
//   await window.CaseApi.savePrescription(id, payload)  -> { ok, savedAt }
//   await window.CaseApi.savePatient(id, payload)       -> { ok, patient, caseId }
//   await window.CaseApi.searchPatients(q)              -> [ {id,firstName,...}, ... ]
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

    // Patient identity persistence. Body must
    // satisfy PatientInformationRequest (firstName, lastName, dateOfBirth,
    // biologicalGender, chiefComplaint required). Optional: email, phone,
    // patientChartId, biologicalGenderOther, selectedPatientId.
    savePatient: function (caseId, payload) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/patient', payload);
    },

    // Patient autocomplete for the case-wizard search field. Returns up to
    // 8 matches, scoped to the doctor's roster for the current practice.
    // The endpoint is doctor-only, so admin views won't fire this — admins
    // see the patient via the inlined window.__patientPrefill instead.
    searchPatients: function (q) {
      var url = '/dev/patients/search?q=' + encodeURIComponent(q || '');
      return request('GET', url, undefined);
    },

    // payload: object passed to submit endpoint. Currently { submitter_initials }.
    // Future fields go here without changing the function signature.
    submitCase: function (caseId, payload) {
      return request('POST', '/dev/cases/' + encodeURIComponent(caseId) + '/submit', payload || {});
    },

    updateStatus: function (caseId, status, extras) {
      var body = Object.assign({ status: status }, extras || {});
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/status', body);
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

    // AI image-edit — send the frontal-smile blob, get back a predicted-outcome
    // image as a data URL. Returns { image, provider, generatedAt }.
    generateSmilePreview: function (caseId, frontalSmileBlob, signal) {
      var fd = new FormData();
      fd.append('photo', frontalSmileBlob, 'frontal-smile.bin');
      return multipart(base() + '/' + encodeURIComponent(caseId) + '/smile-preview/generate', fd, signal);
    },

    saveShipping: function (caseId, payload) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/shipping', payload);
    },

    saveImpressions: function (caseId, payload) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/impressions', payload);
    },

    saveAdditionalInfo: function (caseId, payload) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/additional', payload);
    },

    saveSubmitOrder: function (caseId, payload) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/submit-order', payload);
    },

    savePhotographsDate: function (caseId, dateOfPhotos) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/photographs/date', { dateOfPhotos: dateOfPhotos });
    },

    saveXraysDate: function (caseId, dateOfXrays) {
      return request('POST', base() + '/' + encodeURIComponent(caseId) + '/xrays/date', { dateOfXrays: dateOfXrays });
    },
  };
})();
