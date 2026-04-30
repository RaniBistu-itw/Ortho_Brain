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

        var body = new URLSearchParams();
        body.set('_token', CSRF);
        body.set('status', next);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString(),
            credentials: 'same-origin'
        })
        .then(function (res) {
            return res.json().then(function (json) { return { ok: res.ok, body: json }; });
        })
        .then(function (r) {
            if (!r.ok || !r.body || r.body.ok === false) {
                throw new Error((r.body && r.body.message) || 'Could not update status.');
            }
            var newStatus = r.body.status || next;
            sel.setAttribute('data-status', newStatus);
            sel.value = newStatus;

            if (previous !== newStatus) {
                if (previous === 'ACTIVE')   bumpStat('active',   -1);
                if (previous === 'INACTIVE') bumpStat('inactive', -1);
                if (newStatus === 'ACTIVE')   bumpStat('active',   +1);
                if (newStatus === 'INACTIVE') bumpStat('inactive', +1);
            }

            notify('success', r.body.message || 'Status updated.');
        })
        .catch(function (err) {
            sel.value = previous;
            sel.setAttribute('data-status', previous);
            notify('error', (err && err.message) || 'Could not update status.');
        })
        .then(function () {
            sel.disabled = false;
            sel.removeAttribute('aria-busy');
        });
    });
})();
</script>
@endpush
@endonce
