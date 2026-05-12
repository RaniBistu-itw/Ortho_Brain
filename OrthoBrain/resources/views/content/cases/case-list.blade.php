@extends('layouts.app')

@section('title', 'Cases')
@section('page_title', 'Cases')

@push('styles')
<style>
  #cases-list .ob-input-icon { position: relative; }
  #cases-list .ob-input-icon > svg {
    position: absolute;
    left: 0.75rem; top: 50%; transform: translateY(-50%);
    width: 16px; height: 16px;
    color: #9a9aab;
    pointer-events: none;
  }
  #cases-list .ob-input-icon .form-control { padding-left: 2.35rem; border-radius: 0.5rem; }
  #cases-list .ob-input-icon .form-control:focus {
    border-color: var(--ob-primary, #00bad1);
    box-shadow: 0 0 0 3px var(--ob-primary-softer, rgba(0, 186, 209, 0.18));
  }
  #cases-list-content.is-loading { opacity: 0.55; pointer-events: none; transition: opacity .12s; }
</style>
@endpush

@section('content')
<section id="cases-list">
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title mb-0">Cases</h4>
      <a href="{{ route('doctor.cases.create') }}" class="btn btn-primary">
        <i data-feather="plus" class="me-25"></i> New Case
      </a>
    </div>

    <div class="card-body border-bottom py-1">
      <form method="GET" action="{{ route('doctor.cases.index') }}" id="casesSearchForm" class="row g-2 align-items-center">
        @if($activeStatus)
          <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif
        @if($staleOnly)
          <input type="hidden" name="stale" value="1">
        @endif
        @if(request('order'))
          <input type="hidden" name="order" value="{{ request('order') }}">
        @endif
        @if(request('sort'))
          <input type="hidden" name="sort" value="{{ request('sort') }}">
          <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
        @endif
        <div class="col-md-4">
          <div class="ob-input-icon">
            <i data-feather="search"></i>
            <input type="text" name="search" placeholder="Search by Case ID or Patient Name"
                   value="{{ $searchTerm }}" class="form-control" autocomplete="off">
          </div>
        </div>
        <div class="col-md-2" id="cases-clear-wrap" @if($searchTerm === '') style="display:none" @endif>
          <button type="button" id="cases-clear-btn" class="ob-btn-clear w-100">
            <i data-feather="x"></i> Clear
          </button>
        </div>
      </form>
    </div>

    <div id="cases-list-content">
      @include('content.cases._case-list-table')
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function (e) {
  var tr = e.target.closest('tr[data-row-href]')
  if (!tr) return
  if (e.target.closest('a, button, form, input, select, textarea, label, [data-no-row-click]')) return
  if (window.getSelection && String(window.getSelection())) return
  var href = tr.getAttribute('data-row-href')
  if (!href) return
  if (e.ctrlKey || e.metaKey) {
    window.open(href, '_blank', 'noopener')
  } else {
    window.location.href = href
  }
})
document.addEventListener('auxclick', function (e) {
  if (e.button !== 1) return
  var tr = e.target.closest('tr[data-row-href]')
  if (!tr) return
  if (e.target.closest('a, button, form, input, select, textarea, label, [data-no-row-click]')) return
  var href = tr.getAttribute('data-row-href')
  if (!href) return
  e.preventDefault()
  window.open(href, '_blank', 'noopener')
})

document.addEventListener('DOMContentLoaded', function () {
  var form      = document.getElementById('casesSearchForm');
  var input     = form && form.querySelector('input[name="search"]');
  var content   = document.getElementById('cases-list-content');
  var clearBtn  = document.getElementById('cases-clear-btn');
  var clearWrap = document.getElementById('cases-clear-wrap');
  if (!form || !input || !content) return;

  var debounceTimer = null;
  var inflight      = null;

  function buildUrl(extra) {
    var url = new URL(form.action, window.location.origin);
    new FormData(form).forEach(function (v, k) {
      if (String(v) !== '') url.searchParams.set(k, v);
    });
    if (extra) Object.keys(extra).forEach(function (k) {
      if (extra[k] === null) url.searchParams.delete(k);
      else url.searchParams.set(k, extra[k]);
    });
    return url;
  }

  function syncClearVisibility() {
    if (!clearWrap) return;
    clearWrap.style.display = input.value === '' ? 'none' : '';
  }

  function performFetch(url, pushHistory) {
    if (inflight) inflight.abort();
    var ctrl = new AbortController();
    inflight = ctrl;
    content.classList.add('is-loading');

    fetch(url.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
      credentials: 'same-origin',
      signal: ctrl.signal,
    })
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.text();
    })
    .then(function (html) {
      content.innerHTML = html;
      if (window.feather) window.feather.replace();
      var fullPath = url.pathname + url.search;
      if (pushHistory) history.pushState({}, '', fullPath);
      else             history.replaceState({}, '', fullPath);
    })
    .catch(function (err) {
      if (err.name === 'AbortError') return;
      console.warn('cases search fetch failed; full reload', err);
      window.location.assign(url.toString());
    })
    .finally(function () {
      content.classList.remove('is-loading');
      if (inflight === ctrl) inflight = null;
    });
  }

  // Typing → debounced fetch with replaceState (no history pollution).
  input.addEventListener('input', function () {
    syncClearVisibility();
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () {
      performFetch(buildUrl({ page: null }), false);
    }, 350);
  });

  // Enter → immediate fetch with pushState.
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearTimeout(debounceTimer);
    performFetch(buildUrl({ page: null }), true);
  });

  // Clear button → wipe input, fetch.
  if (clearBtn) clearBtn.addEventListener('click', function () {
    input.value = '';
    syncClearVisibility();
    clearTimeout(debounceTimer);
    performFetch(buildUrl({ search: null, page: null }), true);
    input.focus();
  });

  // Filter pills, sort headers, pagination, in-content "Clear filters" links → AJAX.
  // Only same-path links are intercepted so per-row Edit/View links navigate normally.
  content.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#') return;
    var u;
    try { u = new URL(href, window.location.origin); } catch (err) { return; }
    if (u.pathname !== window.location.pathname) return; // different route → normal nav
    e.preventDefault();
    performFetch(u, true);
  });

  // Back / forward → re-fetch so DOM matches URL.
  window.addEventListener('popstate', function () {
    performFetch(new URL(window.location.href), false);
  });

  // Restore cursor-at-end on initial load (preserves prior UX).
  if (input.value) {
    var len = input.value.length;
    input.focus();
    try { input.setSelectionRange(len, len); } catch (err) {}
  }
})

document.addEventListener('DOMContentLoaded', function () {
  var flashRaw = sessionStorage.getItem('caseSubmittedFlash')
  if (!flashRaw) return
  sessionStorage.removeItem('caseSubmittedFlash')

  try {
    var flash = JSON.parse(flashRaw)
    if (!flash.message) return
    showSuccessToast(flash.message)
  } catch (err) {
    console.error('Failed to parse flash:', err)
  }
})

function showSuccessToast(message) {
  var toastHtml = '<div class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-4"' +
    ' role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1080;">' +
    '<div class="d-flex">' +
    '<div class="toast-body">' + message + '</div>' +
    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
    '</div></div>'
  var wrapper = document.createElement('div')
  wrapper.innerHTML = toastHtml
  var toastEl = wrapper.firstElementChild
  document.body.appendChild(toastEl)
  var toast = new bootstrap.Toast(toastEl, { delay: 5000 })
  toast.show()
  toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove() })
}
</script>
@endpush
