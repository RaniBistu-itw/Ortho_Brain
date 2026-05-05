{{--
    Inline status dropdown — shared across admin index pages.

    Usage on the row:
        <select class="ob-status-select" data-inline-status
                data-url="{{ route('admin.<resource>.status', $model) }}"
                data-status="{{ $model->status }}">
            <option value="ACTIVE">Active</option>
            <option value="INACTIVE">Inactive</option>
        </select>

    The endpoint is expected to accept POST { status }, return JSON
    { ok: true, status: 'ACTIVE'|'INACTIVE', message?: '...' }.

    KPI counters with data-stat="active" / "inactive" auto-decrement on flip.
--}}
@once
@push('styles')
<style>
    .ob-status-select {
        display: inline-flex;
        align-items: center;
        padding: .25rem 1.55rem .25rem .9rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .04em;
        line-height: 1.2;
        border: 1px solid transparent;
        background-color: rgba(108,117,125,.14);
        color: #6e6b7b;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath fill='none' stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.6' d='M2 4.5l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right .5rem center;
        background-size: .65rem .65rem;
        transition: filter .15s ease, box-shadow .15s ease;
    }
    .ob-status-select:hover { filter: brightness(.96); }
    .ob-status-select:focus,
    .ob-status-select:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), .18);
    }
    .ob-status-select[data-status="ACTIVE"]   { background-color: rgba(var(--bs-success-rgb), .14); color: var(--bs-success); }
    .ob-status-select[data-status="INACTIVE"] { background-color: rgba(var(--bs-danger-rgb), .14);  color: var(--bs-danger); }
    .ob-status-select[disabled],
    .ob-status-select[aria-busy="true"] { opacity: .65; cursor: progress; }
    .ob-status-select option {
        color: #2a2c2f;
        background: #fff;
        font-weight: 500;
        letter-spacing: 0;
    }

    /* Deactivation-confirmation modal body */
    .ob-dep-popup .swal2-title { font-size: 1.25rem; padding: .25rem 0 .5rem; }
    .ob-dep-popup .swal2-html-container { margin: .5rem 1.25rem 1rem; font-size: .9rem; }
    .ob-dep-popup .swal2-icon { margin: 1rem auto .5rem; transform: scale(.85); }
    .ob-dep-popup .swal2-actions { margin-top: .25rem; gap: 1.5rem; }
    .ob-dep-modal { text-align: left; }
    .ob-dep-lede {
        margin: 0 0 .85rem;
        color: #4b4b4b;
        font-size: .95rem;
        line-height: 1.45;
    }
    .ob-dep-list {
        list-style: none;
        margin: 0 0 .9rem;
        padding: .55rem .75rem;
        background: rgba(var(--bs-warning-rgb), .08);
        border: 1px solid rgba(var(--bs-warning-rgb), .25);
        border-radius: .5rem;
    }
    .ob-dep-row {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .25rem 0;
    }
    .ob-dep-row + .ob-dep-row {
        border-top: 1px dashed rgba(var(--bs-warning-rgb), .25);
    }
    .ob-dep-count {
        flex: 0 0 auto;
        min-width: 2rem;
        padding: .15rem .55rem;
        border-radius: 999px;
        background: var(--bs-warning, #ff9f43);
        color: #fff;
        font-weight: 700;
        font-size: .82rem;
        text-align: center;
        line-height: 1.3;
    }
    .ob-dep-label {
        color: #2a2c2f;
        font-size: .92rem;
    }
    .ob-dep-note {
        margin: 0;
        font-size: .8rem;
        color: #8a8a8a;
        line-height: 1.4;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';
    if (window.__obInlineStatusInit) return;
    window.__obInlineStatusInit = true;

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function bumpStat(key, delta) {
        var el = document.querySelector('[data-stat="' + key + '"]');
        if (!el) return;
        var n = parseInt(el.textContent, 10);
        el.textContent = (isNaN(n) ? 0 : n) + delta;
    }

    function notify(icon, title) {
        if (window.Swal && typeof Swal.fire === 'function') {
            Swal.fire({
                toast: true, position: 'top-end', icon: icon, title: title,
                showConfirmButton: false, timer: 2200, timerProgressBar: true
            });
        }
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[c];
        });
    }

    function buildDependentsHtml(payload) {
        var d = payload && payload.dependents ? payload.dependents : {};
        var rows = '';
        Object.keys(d).forEach(function (label) {
            rows += '<li class="ob-dep-row">'
                  +   '<span class="ob-dep-count">' + escapeHtml(d[label]) + '</span>'
                  +   '<span class="ob-dep-label">active ' + escapeHtml(label) + '</span>'
                  + '</li>';
        });
        var name = escapeHtml(payload.subject || 'this record');
        return '<div class="ob-dep-modal">'
             + '<p class="ob-dep-lede">"<strong>' + name + '</strong>" still has active dependents:</p>'
             + '<ul class="ob-dep-list">' + rows + '</ul>'
             + '<p class="ob-dep-note">Deactivating will leave them attached to an inactive parent.</p>'
             + '</div>';
    }

    function postStatus(url, status, force) {
        var body = new URLSearchParams();
        body.set('_token', CSRF);
        body.set('status', status);
        if (force) body.set('force', '1');

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString(),
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().then(function (json) { return { ok: res.ok, body: json }; });
        });
    }

    function applySuccess(sel, previous, body, fallbackNext) {
        var newStatus = body.status || fallbackNext;
        sel.setAttribute('data-status', newStatus);
        sel.value = newStatus;

        if (previous !== newStatus) {
            if (previous === 'ACTIVE')   bumpStat('active',   -1);
            if (previous === 'INACTIVE') bumpStat('inactive', -1);
            if (newStatus === 'ACTIVE')   bumpStat('active',   +1);
            if (newStatus === 'INACTIVE') bumpStat('inactive', +1);
        }
        notify('success', body.message || 'Status updated.');
    }

    function revert(sel, previous) {
        sel.value = previous;
        sel.setAttribute('data-status', previous);
    }

    document.addEventListener('change', function (e) {
        var sel = e.target;
        if (!sel || !sel.matches || !sel.matches('.ob-status-select[data-inline-status]')) return;

        var url = sel.getAttribute('data-url');
        if (!url) return;

        var previous = sel.getAttribute('data-status');
        var next = sel.value;
        if (previous === next) return;

        sel.disabled = true;
        sel.setAttribute('aria-busy', 'true');

        var release = function () {
            sel.disabled = false;
            sel.removeAttribute('aria-busy');
        };

        postStatus(url, next, false)
            .then(function (r) {
                if (r.body && r.body.requires_confirmation) {
                    if (!window.Swal || typeof Swal.fire !== 'function') {
                        revert(sel, previous);
                        notify('error', 'Cannot deactivate: this record has active dependents.');
                        return;
                    }
                    return Swal.fire({
                        title: 'Confirm deactivation',
                        html: buildDependentsHtml(r.body),
                        icon: 'warning',
                        width: 440,
                        showCancelButton: true,
                        confirmButtonText: 'Yes, deactivate',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        focusCancel: true,
                        customClass: {
                            popup: 'ob-dep-popup',
                            confirmButton: 'btn btn-danger',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            revert(sel, previous);
                            return;
                        }
                        return postStatus(url, next, true).then(function (r2) {
                            if (!r2.ok || !r2.body || r2.body.ok === false) {
                                throw new Error((r2.body && r2.body.message) || 'Could not update status.');
                            }
                            applySuccess(sel, previous, r2.body, next);
                        });
                    });
                }

                if (!r.ok || !r.body || r.body.ok === false) {
                    throw new Error((r.body && r.body.message) || 'Could not update status.');
                }
                applySuccess(sel, previous, r.body, next);
            })
            .catch(function (err) {
                revert(sel, previous);
                notify('error', (err && err.message) || 'Could not update status.');
            })
            .then(release, release);
    });
})();
</script>
@endpush
@endonce
