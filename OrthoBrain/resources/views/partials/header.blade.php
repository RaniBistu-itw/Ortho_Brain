@php
    use App\Http\Controllers\Admin\NotificationController as AdminNotifController;

    $adminUser    = auth()->user();
    $adminFirst   = $adminUser->admin?->first_name ?? '';
    $adminLast    = $adminUser->admin?->last_name ?? '';
    $adminName    = trim("{$adminFirst} {$adminLast}");
    if ($adminName === '') { $adminName = 'Admin'; }
    $adminInitial = strtoupper(substr($adminName, 0, 1));
    $adminAvatar  = $adminUser->admin?->avatarUrl();
    $adminEmail   = $adminUser->email;

    // Today's-activity feed for the bell. Server-rendered; client-side
    // localStorage tracks per-day read/dismissed state.
    $adminNotifItems = app(AdminNotifController::class)->collect();
    $adminBellCount  = $adminNotifItems->count();
@endphp

<style>
    /* ─── Admin top nav — same chrome as the doctor side ──────────────────── */
    nav.header-navbar.floating-nav.doc-nav-shell > .navbar-container { padding: 0; }
    nav.header-navbar.floating-nav.doc-nav-shell { padding: 0.55rem 0.9rem; }

    .doc-nav {
        display: flex; align-items: center; gap: 0.5rem;
        width: 100%;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .doc-nav__toggle {
        display: none;
        background: none; border: 0; padding: 0.4rem; border-radius: 0.5rem; color: #4b5563; cursor: pointer;
    }
    .doc-nav__toggle:hover { background: #f3f4f6; }
    @media (max-width: 1199.98px) { .doc-nav__toggle { display: inline-flex; } }

    .doc-nav__spacer { flex: 1; }

    /* Icon buttons (theme + bell) */
    .doc-nav__icon-btn {
        position: relative;
        display: inline-flex; align-items: center; justify-content: center;
        width: 38px; height: 38px;
        border-radius: 0.6rem;
        color: #4b5563;
        background: transparent;
        border: 0;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
        text-decoration: none;
    }
    .doc-nav__icon-btn:hover { background: #f3f4f6; color: #111827; }
    .doc-nav__icon-btn i { font-size: 1.05rem; }
    .doc-nav__icon-btn .doc-nav__count-dot {
        position: absolute;
        top: 4px; right: 4px;
        min-width: 16px; height: 16px;
        padding: 0 4px;
        background: #ea5455;
        color: #fff;
        border-radius: 999px;
        border: 2px solid #fff;
        font-size: 0.62rem;
        font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
        line-height: 1;
    }

    /* User chip */
    .doc-nav__user-btn {
        display: inline-flex; align-items: center; gap: 0.45rem;
        padding: 0.25rem 0.6rem 0.25rem 0.35rem;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 999px;
        cursor: pointer;
        color: #374151;
        text-decoration: none;
    }
    .doc-nav__user-btn:hover { background: #f3f4f6; border-color: #e5e7eb; color: #111827; }
    .doc-nav__user-btn i.bi-chevron-down { font-size: 0.7rem; opacity: 0.7; }
    .doc-nav__avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5bc0de, #8cc63f);
        color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.82rem; font-weight: 700;
        overflow: hidden;
    }
    .doc-nav__avatar img { width: 100%; height: 100%; object-fit: cover; }

    /* Shared dropdown chrome */
    .doc-nav .dropdown-menu {
        min-width: 260px;
        border: 1px solid #ebe9f1;
        border-radius: 0.75rem;
        box-shadow: 0 12px 32px rgba(24, 28, 40, 0.1);
        padding: 0.5rem 0;
        margin-top: 0.4rem;
        font-family: inherit;
    }
    .doc-nav .dropdown-menu .dropdown-header {
        padding: 0.35rem 1rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #9ca3af;
        background: transparent;
    }
    .doc-nav .dropdown-menu .dropdown-divider { margin: 0.4rem 0; border-color: #f3f2f7; }
    .doc-nav .dropdown-menu .dropdown-item {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.55rem 1rem;
        font-size: 0.88rem;
        color: #374151;
        border: 0;
        background: transparent;
        width: 100%;
    }
    .doc-nav .dropdown-menu .dropdown-item:hover { background: #f3f4f6; color: #111827; }
    .doc-nav .dropdown-menu .dropdown-item i { width: 16px; color: #6b7280; }
    .doc-nav .dropdown-menu .dropdown-item:hover i { color: #374151; }

    /* User dropdown identity block */
    .doc-nav__user-head { padding: 0.65rem 1rem 0.55rem; }
    .doc-nav__user-head-name { font-weight: 700; color: #111827; font-size: 0.95rem; line-height: 1.2; }
    .doc-nav__user-head-sub  { font-size: 0.78rem; color: #6b7280; line-height: 1.3; margin-top: 0.15rem; word-break: break-all; }

    /* Notifications list (matches doctor styling) */
    .doc-nav__notif-empty { padding: 1.5rem 1rem; text-align: center; color: #9ca3af; font-size: 0.85rem; }
    .doc-nav__notif-item {
        position: relative;
        display: flex; align-items: flex-start; gap: 0.6rem;
        padding: 0.85rem 2.2rem 0.85rem 1rem;
        white-space: normal;
        border-bottom: 1px solid #f3f2f7;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border-left: 3px solid #5bc0de;
        background: #fff;
    }
    .doc-nav__notif-item:last-child { border-bottom: 0; }
    .doc-nav__notif-item:hover { background: #f8fafc; transform: translateX(2px); }
    .doc-nav__notif-item.is-read {
        background: #f9fafb;
        border-left-color: transparent !important;
        opacity: 0.6;
    }
    .doc-nav__notif-item.is-read .doc-nav__notif-title { color: #64748b; font-weight: 500; }
    .doc-nav__notif-item.is-read .doc-nav__notif-body  { color: #94a3b8; }
    .doc-nav__notif-item.is-read .doc-nav__notif-time  { color: #cbd5e1; }
    .doc-nav__notif-item.is-read i { opacity: 0.5; }
    .doc-nav__notif-item.is-read:hover { background: #f1f5f9; }

    .doc-nav__notif-item .bi-person-plus    { color: #2563eb; margin-top: 0.15rem; }
    .doc-nav__notif-item .bi-building-add   { color: #059669; margin-top: 0.15rem; }
    .doc-nav__notif-item .bi-folder-plus    { color: #7c3aed; margin-top: 0.15rem; }

    .doc-nav__notif-title { font-weight: 700; color: #1e293b; font-size: 0.9rem; line-height: 1.2; display: block; }
    .doc-nav__notif-body  { color: #475569; font-size: 0.82rem; margin-top: 0.25rem; display: block; line-height: 1.4; }
    .doc-nav__notif-time  { color: #94a3b8; font-size: 0.72rem; margin-top: 0.4rem; display: block; font-weight: 500; }

    .doc-nav__notif-unread {
        width: 8px; height: 8px;
        background: #5bc0de;
        border-radius: 50%;
        margin-top: 0.4rem;
        flex-shrink: 0;
        box-shadow: 0 0 0 2px #fff, 0 0 8px rgba(91, 192, 222, 0.4);
    }

    .doc-nav__notif-close {
        position: absolute;
        top: 0.55rem; right: 0.5rem;
        background: transparent;
        border: 0;
        width: 22px; height: 22px;
        border-radius: 999px;
        color: #9ca3af;
        cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        line-height: 1;
        transition: background 0.15s, color 0.15s;
    }
    .doc-nav__notif-close i { font-size: 0.78rem; }
    .doc-nav__notif-close:hover { background: #fee2e2; color: #c53030; }

    /* Notif action row + scroll + footer */
    .doc-nav__notif-actions {
        display: flex;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem 0.35rem;
    }
    .doc-nav__notif-action-btn {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        font-size: 0.78rem; font-weight: 600;
        border: 1px solid #e5e7eb; border-radius: 0.45rem;
        background: #fff; color: #374151;
        cursor: pointer; line-height: 1;
        transition: all 0.15s ease;
    }
    .doc-nav__notif-action-btn i { font-size: 0.82rem; }
    .doc-nav__notif-action-btn:hover:not(:disabled) { background: #f3f4f6; border-color: #d1d5db; color: #111827; }
    .doc-nav__notif-action-btn:disabled { opacity: 0.38; cursor: not-allowed; }
    .doc-nav__notif-action-btn.is-danger { color: #dc2626; border-color: #fca5a5; }
    .doc-nav__notif-action-btn.is-danger:hover:not(:disabled) { background: #fef2f2; border-color: #ef4444; }

    .doc-nav__notif-scroll {
        max-height: 340px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .doc-nav__notif-footer {
        padding: 0.5rem 0.75rem;
        border-top: 1px solid #f3f2f7;
    }
    .doc-nav__notif-viewall {
        width: 100%;
        padding: 0.4rem;
        font-size: 0.82rem; font-weight: 600;
        color: #1e6c85;
        background: transparent;
        border: 0;
        border-radius: 0.45rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .doc-nav__notif-viewall:hover { background: rgba(91,192,222,0.08); }

    /* Dark-mode overrides */
    html[data-theme="dark"] .doc-nav__icon-btn { color: #9ca3af; }
    html[data-theme="dark"] .doc-nav__icon-btn:hover { background: #374151; color: #fff; }
    html[data-theme="dark"] .doc-nav__user-btn { color: #e5e7eb; }
    html[data-theme="dark"] .doc-nav__user-btn:hover { background: #374151; border-color: #4b5563; color: #fff; }
    html[data-theme="dark"] .doc-nav .dropdown-menu {
        background: #1f2a37;
        border-color: #374151;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);
    }
    html[data-theme="dark"] .doc-nav .dropdown-menu .dropdown-item { color: #e5e7eb; }
    html[data-theme="dark"] .doc-nav .dropdown-menu .dropdown-item:hover { background: #374151; color: #fff; }
    html[data-theme="dark"] .doc-nav__user-head-name { color: #f3f4f6; }
    html[data-theme="dark"] .doc-nav__user-head-sub { color: #9ca3af; }
    html[data-theme="dark"] .doc-nav .dropdown-menu .dropdown-divider { border-color: #374151; }
    html[data-theme="dark"] .doc-nav__notif-item { background: #1f2a37; color: #e5e7eb; border-bottom-color: #374151; }
    html[data-theme="dark"] .doc-nav__notif-item:hover { background: #374151; }
    html[data-theme="dark"] .doc-nav__notif-item.is-read { background: #1a2330; }
    html[data-theme="dark"] .doc-nav__notif-title { color: #f1f5f9; }
    html[data-theme="dark"] .doc-nav__notif-body  { color: #94a3b8; }
    html[data-theme="dark"] .doc-nav__notif-action-btn { background: #1f2a37; color: #e5e7eb; border-color: #374151; }
    html[data-theme="dark"] .doc-nav__notif-action-btn:hover:not(:disabled) { background: #374151; color: #fff; }
    html[data-theme="dark"] .doc-nav__notif-close:hover { background: #451a1a; color: #fca5a5; }
    html[data-theme="dark"] .doc-nav__notif-footer { border-top-color: #374151; }
</style>

<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow doc-nav-shell">
<div class="navbar-container d-flex content">
<div class="doc-nav">
    <button type="button" class="doc-nav__toggle menu-toggle" aria-label="Toggle menu">
        <i data-feather="menu"></i>
    </button>

    <div class="doc-nav__spacer"></div>

    {{-- Theme toggle --}}
    <a class="doc-nav__icon-btn" href="#" data-theme-toggle title="Toggle dark / light mode" aria-label="Toggle dark mode">
        <i class="bi bi-moon" data-theme-toggle-icon></i>
    </a>

    {{-- Notifications bell --}}
    <div class="dropdown">
        <a class="doc-nav__icon-btn" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            @if($adminBellCount > 0)
                <span class="doc-nav__count-dot">{{ $adminBellCount > 9 ? '9+' : $adminBellCount }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end" style="min-width:340px;max-width:360px;" id="adminNotifDropdown">
            <h6 class="dropdown-header" style="display:flex;justify-content:space-between;align-items:center;">
                <span>Today's Activity</span>
                @if($adminBellCount > 0)
                    <span style="font-size:0.7rem;font-weight:600;color:#1e6c85;" data-notif-count-label>{{ $adminBellCount }} new</span>
                @endif
            </h6>
            <div class="doc-nav__notif-actions">
                <button type="button" class="doc-nav__notif-action-btn" data-notif-mark-all
                        @if($adminBellCount === 0) disabled @endif
                        title="Mark all as read">
                    <i class="bi bi-check2-all"></i> Mark all read
                </button>
                <button type="button" class="doc-nav__notif-action-btn is-danger" data-notif-delete-all
                        @if($adminNotifItems->isEmpty()) disabled @endif
                        title="Hide all of today's items">
                    <i class="bi bi-trash"></i> Clear all
                </button>
            </div>
            <div class="dropdown-divider" style="margin: 0 0 0.25rem;"></div>
            <div class="doc-nav__notif-scroll" data-notif-list>
                @forelse($adminNotifItems as $item)
                    @php
                        $icon = match($item['kind']) {
                            'doctor'   => 'person-plus',
                            'practice' => 'building-add',
                            'case'     => 'folder-plus',
                            default    => 'bell',
                        };
                    @endphp
                    <a class="doc-nav__notif-item" href="{{ $item['url'] }}" data-notif-key="{{ $item['key'] }}">
                        <i class="bi bi-{{ $icon }}"></i>
                        <span style="flex:1;">
                            <span class="doc-nav__notif-title">{{ $item['title'] }}</span>
                            <span class="doc-nav__notif-body">{{ $item['body'] }}</span>
                            <span class="doc-nav__notif-time">{{ $item['time'] }}</span>
                        </span>
                        <span class="doc-nav__notif-unread"></span>
                        <button type="button" class="doc-nav__notif-close" data-notif-dismiss="{{ $item['key'] }}"
                                title="Dismiss" aria-label="Dismiss">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </a>
                @empty
                    <div class="doc-nav__notif-empty" data-notif-empty>No new activity today.</div>
                @endforelse
            </div>
            <div class="doc-nav__notif-footer">
                <button type="button" class="doc-nav__notif-viewall" data-notif-viewall>
                    View all notifications
                </button>
            </div>
        </div>
    </div>

    {{-- User dropdown --}}
    <div class="dropdown">
        <a class="doc-nav__user-btn" href="#" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
            <span class="doc-nav__avatar">
                @if($adminAvatar)
                    <img src="{{ $adminAvatar }}" alt="avatar">
                @else
                    {{ $adminInitial }}
                @endif
            </span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <div class="doc-nav__user-head">
                <div class="doc-nav__user-head-name">{{ $adminName }}</div>
                <div class="doc-nav__user-head-sub">Administrator @if($adminEmail) &middot; {{ $adminEmail }} @endif</div>
            </div>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                <i class="bi bi-person"></i>My Profile
            </a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item" style="color:#c53030;">
                    <i class="bi bi-box-arrow-right"></i>Sign out
                </button>
            </form>
        </div>
    </div>
</div>{{-- /.doc-nav --}}
</div>{{-- /.navbar-container --}}
</nav>

{{-- "View All" modal — sibling of <nav>, NOT nested inside any dropdown,
     so Bootstrap can position the backdrop and dialog correctly. --}}
<div class="modal fade" id="adminNotifModal" tabindex="-1" aria-hidden="true" aria-labelledby="adminNotifModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 540px;">
        <div class="modal-content">
            <div class="modal-header" style="padding: 0.85rem 1.1rem;">
                <h5 class="modal-title" id="adminNotifModalLabel" style="font-size:1rem;font-weight:700;margin:0;">
                    Today's activity
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="doc-nav__notif-actions" style="padding: 0.6rem 1.1rem; border-bottom: 1px solid #f3f2f7;">
                <button type="button" class="doc-nav__notif-action-btn" data-notif-mark-all
                        title="Mark all as read">
                    <i class="bi bi-check2-all"></i> Mark all read
                </button>
                <button type="button" class="doc-nav__notif-action-btn is-danger" data-notif-delete-all
                        title="Hide all of today's items">
                    <i class="bi bi-trash"></i> Clear all
                </button>
            </div>
            <div class="modal-body" data-notif-modal-body style="padding: 0;">
                <div class="doc-nav__notif-empty">Loading…</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const ROUTE_INDEX  = @json(route('admin.notifications.index'));
    const STORAGE_KEY  = 'ob_admin_notif_state_v1';
    const TODAY_KEY    = (function () {
        const d = new Date();
        return d.getFullYear() + '-'
             + String(d.getMonth() + 1).padStart(2, '0') + '-'
             + String(d.getDate()).padStart(2, '0');
    })();

    // ── Per-day localStorage state. Old days are pruned on access so we don't
    //    accumulate stale data forever. ─────────────────────────────────────
    function loadState() {
        let all;
        try { all = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {}; }
        catch (e) { all = {}; }
        Object.keys(all).forEach(k => { if (k !== TODAY_KEY) delete all[k]; });
        if (!all[TODAY_KEY]) all[TODAY_KEY] = { read: [], dismissed: [] };
        return all;
    }
    function saveState(all) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(all)); } catch (e) {}
    }
    function getDay() { return loadState()[TODAY_KEY]; }
    function setDay(day) { const all = loadState(); all[TODAY_KEY] = day; saveState(all); }

    function markRead(key)   { const d = getDay(); if (!d.read.includes(key)) d.read.push(key); setDay(d); }
    function markDismiss(key){ const d = getDay(); if (!d.dismissed.includes(key)) d.dismissed.push(key); setDay(d); }
    function markAllRead(keys) { const d = getDay(); keys.forEach(k => { if (!d.read.includes(k)) d.read.push(k); }); setDay(d); }
    function markAllDismiss(keys){ const d = getDay(); keys.forEach(k => { if (!d.dismissed.includes(k)) d.dismissed.push(k); }); setDay(d); }

    // ── Bell badge sync ─────────────────────────────────────────────────────
    function setBellCount(n) {
        const bell = document.querySelector('.doc-nav__icon-btn[title="Notifications"]');
        if (!bell) return;
        let dot = bell.querySelector('.doc-nav__count-dot');
        const label = document.querySelector('[data-notif-count-label]');
        if (n > 0) {
            if (!dot) {
                dot = document.createElement('span');
                dot.className = 'doc-nav__count-dot';
                bell.appendChild(dot);
            }
            dot.textContent = n > 9 ? '9+' : n;
            if (label) { label.textContent = n + ' new'; label.style.display = ''; }
        } else {
            if (dot) dot.remove();
            if (label) { label.textContent = ''; label.style.display = 'none'; }
        }
    }

    // ── Apply localStorage state to a container's notif rows ────────────────
    function applyState(container) {
        if (!container) return { unread: 0, visible: 0 };
        const day = getDay();
        let unread = 0, visible = 0;
        container.querySelectorAll('[data-notif-key]').forEach(el => {
            const key = el.dataset.notifKey;
            if (day.dismissed.includes(key)) {
                el.style.display = 'none';
                return;
            }
            el.style.display = '';
            visible++;
            if (day.read.includes(key)) {
                el.classList.add('is-read');
                el.querySelector('.doc-nav__notif-unread')?.remove();
            } else {
                unread++;
            }
        });
        return { unread, visible };
    }

    function syncDropdownButtons(container) {
        const items   = [...container.querySelectorAll('[data-notif-key]')]
                        .filter(el => el.style.display !== 'none');
        const unread  = items.filter(el => !el.classList.contains('is-read')).length;
        const markBtn = container.querySelector('[data-notif-mark-all]');
        const delBtn  = container.querySelector('[data-notif-delete-all]');
        if (markBtn) {
            if (unread > 0) markBtn.removeAttribute('disabled');
            else markBtn.setAttribute('disabled', '');
        }
        if (delBtn) {
            if (items.length > 0) delBtn.removeAttribute('disabled');
            else delBtn.setAttribute('disabled', '');
        }
    }

    function refreshEmptyState(container, emptyText) {
        const list = container.querySelector('[data-notif-list]') || container.querySelector('[data-notif-modal-body]');
        if (!list) return;
        const visible = [...container.querySelectorAll('[data-notif-key]')]
                        .filter(el => el.style.display !== 'none');
        const existingEmpty = list.querySelector('[data-notif-empty]');
        if (visible.length === 0 && !existingEmpty) {
            const div = document.createElement('div');
            div.className = 'doc-nav__notif-empty';
            div.dataset.notifEmpty = '';
            div.textContent = emptyText;
            list.appendChild(div);
        } else if (visible.length > 0 && existingEmpty) {
            existingEmpty.remove();
        }
    }

    // ─── Bell dropdown wiring ──────────────────────────────────────────────
    const dropdown = document.getElementById('adminNotifDropdown');
    if (dropdown) {
        const r = applyState(dropdown);
        setBellCount(r.unread);
        syncDropdownButtons(dropdown);

        dropdown.addEventListener('click', function (e) {
            // Dismiss button (X)
            const dismissBtn = e.target.closest('[data-notif-dismiss]');
            if (dismissBtn) {
                e.preventDefault();
                e.stopPropagation();
                const key = dismissBtn.dataset.notifDismiss;
                const row = dismissBtn.closest('[data-notif-key]');
                markDismiss(key);
                if (row) row.style.display = 'none';
                const stats = applyState(dropdown);
                setBellCount(stats.unread);
                syncDropdownButtons(dropdown);
                refreshEmptyState(dropdown, 'No new activity today.');
                return;
            }

            // Click on the row → mark read + navigate naturally
            const link = e.target.closest('[data-notif-key]');
            if (link && !link.classList.contains('is-read')) {
                markRead(link.dataset.notifKey);
                link.classList.add('is-read');
                link.querySelector('.doc-nav__notif-unread')?.remove();
                const stats = applyState(dropdown);
                setBellCount(stats.unread);
                syncDropdownButtons(dropdown);
            }
        });

        dropdown.querySelector('[data-notif-mark-all]')?.addEventListener('click', function () {
            const keys = [...dropdown.querySelectorAll('[data-notif-key]')]
                .filter(el => el.style.display !== 'none')
                .map(el => el.dataset.notifKey);
            markAllRead(keys);
            applyState(dropdown);
            setBellCount(0);
            syncDropdownButtons(dropdown);
        });

        dropdown.querySelector('[data-notif-delete-all]')?.addEventListener('click', function () {
            const keys = [...dropdown.querySelectorAll('[data-notif-key]')]
                .filter(el => el.style.display !== 'none')
                .map(el => el.dataset.notifKey);
            markAllDismiss(keys);
            keys.forEach(k => {
                const el = dropdown.querySelector(`[data-notif-key="${k}"]`);
                if (el) el.style.display = 'none';
            });
            setBellCount(0);
            syncDropdownButtons(dropdown);
            refreshEmptyState(dropdown, 'No new activity today.');
        });
    }

    // ─── "View All" modal wiring ───────────────────────────────────────────
    const modalEl = document.getElementById('adminNotifModal');
    let _modal = null;
    const getModal = () => {
        if (_modal || !modalEl) return _modal;
        if (!window.bootstrap?.Modal) return null;
        _modal = new bootstrap.Modal(modalEl);
        return _modal;
    };

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function iconFor(kind) {
        switch (kind) {
            case 'doctor':   return 'person-plus';
            case 'practice': return 'building-add';
            case 'case':     return 'folder-plus';
            default:         return 'bell';
        }
    }

    function renderModalItems(items) {
        const body = modalEl.querySelector('[data-notif-modal-body]');
        if (!items.length) {
            body.innerHTML = '<div class="doc-nav__notif-empty" data-notif-empty>No new activity today.</div>';
            return;
        }
        body.innerHTML = items.map(n => `
            <a class="doc-nav__notif-item" href="${escapeHtml(n.url || '#')}" data-notif-key="${escapeHtml(n.key)}">
                <i class="bi bi-${iconFor(n.kind)}"></i>
                <span style="flex:1;">
                    <span class="doc-nav__notif-title">${escapeHtml(n.title)}</span>
                    <span class="doc-nav__notif-body">${escapeHtml(n.body)}</span>
                    <span class="doc-nav__notif-time">${escapeHtml(n.time)}</span>
                </span>
                <span class="doc-nav__notif-unread"></span>
                <button type="button" class="doc-nav__notif-close" data-notif-dismiss="${escapeHtml(n.key)}"
                        title="Dismiss" aria-label="Dismiss">
                    <i class="bi bi-x-lg"></i>
                </button>
            </a>`).join('');
    }

    function loadModal() {
        const body = modalEl.querySelector('[data-notif-modal-body]');
        body.innerHTML = '<div class="doc-nav__notif-empty">Loading…</div>';
        fetch(ROUTE_INDEX, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(j => {
            renderModalItems(j.items || []);
            applyState(modalEl);
            syncDropdownButtons(modalEl);
        })
        .catch(() => {
            body.innerHTML = '<div class="doc-nav__notif-empty">Could not load notifications.</div>';
        });
    }

    document.querySelector('[data-notif-viewall]')?.addEventListener('click', function () {
        const m = getModal();
        if (!m) return;
        // Hide the bell dropdown so the modal backdrop sits cleanly
        const bellToggle = document.querySelector('[data-bs-toggle="dropdown"][title="Notifications"]');
        if (bellToggle && window.bootstrap?.Dropdown) {
            bootstrap.Dropdown.getInstance(bellToggle)?.hide();
        }
        loadModal();
        m.show();
    });

    if (modalEl) {
        modalEl.addEventListener('click', function (e) {
            const dismissBtn = e.target.closest('[data-notif-dismiss]');
            if (dismissBtn) {
                e.preventDefault();
                e.stopPropagation();
                const key = dismissBtn.dataset.notifDismiss;
                markDismiss(key);
                // Hide everywhere
                document.querySelectorAll(`[data-notif-key="${key}"]`).forEach(el => el.style.display = 'none');
                if (dropdown) {
                    const stats = applyState(dropdown);
                    setBellCount(stats.unread);
                    syncDropdownButtons(dropdown);
                    refreshEmptyState(dropdown, 'No new activity today.');
                }
                syncDropdownButtons(modalEl);
                refreshEmptyState(modalEl, 'No new activity today.');
                return;
            }
            const link = e.target.closest('[data-notif-key]');
            if (link && !link.classList.contains('is-read')) {
                markRead(link.dataset.notifKey);
                document.querySelectorAll(`[data-notif-key="${link.dataset.notifKey}"]`).forEach(el => {
                    el.classList.add('is-read');
                    el.querySelector('.doc-nav__notif-unread')?.remove();
                });
                if (dropdown) {
                    const stats = applyState(dropdown);
                    setBellCount(stats.unread);
                    syncDropdownButtons(dropdown);
                }
                syncDropdownButtons(modalEl);
            }
        });

        modalEl.querySelector('[data-notif-mark-all]')?.addEventListener('click', function () {
            const keys = [...modalEl.querySelectorAll('[data-notif-key]')]
                .filter(el => el.style.display !== 'none')
                .map(el => el.dataset.notifKey);
            markAllRead(keys);
            applyState(modalEl);
            if (dropdown) { applyState(dropdown); }
            setBellCount(0);
            syncDropdownButtons(modalEl);
            if (dropdown) syncDropdownButtons(dropdown);
        });

        modalEl.querySelector('[data-notif-delete-all]')?.addEventListener('click', function () {
            const keys = [...modalEl.querySelectorAll('[data-notif-key]')]
                .filter(el => el.style.display !== 'none')
                .map(el => el.dataset.notifKey);
            markAllDismiss(keys);
            keys.forEach(k => {
                document.querySelectorAll(`[data-notif-key="${k}"]`).forEach(el => el.style.display = 'none');
            });
            setBellCount(0);
            syncDropdownButtons(modalEl);
            refreshEmptyState(modalEl, 'No new activity today.');
            if (dropdown) {
                syncDropdownButtons(dropdown);
                refreshEmptyState(dropdown, 'No new activity today.');
            }
        });
    }
})();
</script>
@endpush
