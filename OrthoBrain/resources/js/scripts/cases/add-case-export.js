// PDF export — POSTs the current draft state alongside the request so the
// server-rendered PDF includes whatever the user has typed but not yet
// submitted (patient info, shipping, etc. — these aren't persisted yet).
(function () {
  'use strict';

  function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function exportPdf() {
    var caseId = window.CASE_ID;
    if (!caseId || caseId === 'new') {
      console.warn('[AddCaseExport] no case id — save the draft first');
      return;
    }

    var btn = document.getElementById('btn-export-pdf');
    if (btn) { btn.disabled = true; btn.dataset.label = btn.textContent.trim(); btn.textContent = 'Generating…'; }

    var base = window.CASE_API_BASE || '/dev/cases';
    var url = base + '/' + caseId + '/export.pdf';

    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/pdf',
      },
      body: JSON.stringify({ addCaseState: window.AddCaseState || {} }),
    })
    .then(function (r) {
      if (!r.ok) throw new Error('Export failed: HTTP ' + r.status);
      return r.blob();
    })
    .then(function (blob) {
      var blobUrl = URL.createObjectURL(blob);
      // Open in a new tab — the browser's PDF viewer renders inline.
      var win = window.open(blobUrl, '_blank');
      // Some popup-blocker setups return null; fall back to a trigger link.
      if (!win) {
        var a = document.createElement('a');
        a.href = blobUrl;
        a.download = 'case-' + caseId + '.pdf';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
      }
      // Revoke after a delay so the new tab has time to load.
      setTimeout(function () { URL.revokeObjectURL(blobUrl); }, 30000);
    })
    .catch(function (err) {
      console.error('[AddCaseExport]', err);
      if (window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
        window.MediaTileHelpers.showToast('PDF export failed', 3000);
      } else {
        alert('PDF export failed. See console for details.');
      }
    })
    .finally(function () {
      if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || 'Export PDF'; if (window.feather) window.feather.replace(); }
    });
  }

  window.AddCaseExport = { exportPdf: exportPdf };
})();
