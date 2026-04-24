@php
    $navDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
    $navName   = $navDoctor ? trim($navDoctor->first_name . ' ' . $navDoctor->last_name) : auth()->user()->email;
    $initial   = strtoupper(substr($navName ?: 'U', 0, 1));
    $navAvatar = $navDoctor?->avatarUrl();
    $navEmail  = auth()->user()?->email;

    $navActivePractice = currentPractice();
    $navOtherPractices = $navDoctor
        ? $navDoctor->activePractices()
            ->when($navActivePractice, fn ($q) => $q->where('practices.id', '!=', $navActivePractice->id))
            ->get()
        : collect();
    $navPendingList   = $navDoctor ? $navDoctor->pendingPractices()->get() : collect();
    $navPendingCount  = $navPendingList->count();
    $navUnreadNotifs  = auth()->user()?->unreadNotifications()->count() ?? 0;
    $navBellCount     = $navUnreadNotifs + $navPendingCount;
@endphp

<style>
    /* ─── Doctor-side top nav — scoped to avoid collision with Vuexy admin nav ─── */
    /* Reset Vuexy's default nav padding so our flex container controls layout. */
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

    /* Practice switcher pill */
    .doc-nav__practice {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.45rem 0.85rem;
        background: rgba(91, 192, 222, 0.08);
        border: 1px solid rgba(91, 192, 222, 0.28);
        border-radius: 999px;
        color: #1e6c85;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease;
        text-decoration: none;
    }
    .doc-nav__practice:hover { background: rgba(91, 192, 222, 0.14); border-color: rgba(91, 192, 222, 0.48); color: #1e6c85; }
    .doc-nav__practice-label { color: #6b7280; font-weight: 500; font-size: 0.8rem; }
    .doc-nav__practice-name { color: #1e6c85; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .doc-nav__practice i.bi-building { font-size: 1rem; }
    .doc-nav__practice i.bi-chevron-down { font-size: 0.7rem; opacity: 0.7; margin-left: 0.1rem; }

    /* Icon buttons (help + bell) */
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
    .doc-nav__user-head {
        padding: 0.65rem 1rem 0.55rem;
    }
    .doc-nav__user-head-name { font-weight: 700; color: #111827; font-size: 0.95rem; line-height: 1.2; }
    .doc-nav__user-head-sub  { font-size: 0.78rem; color: #6b7280; line-height: 1.3; margin-top: 0.15rem; word-break: break-all; }

    /* Switcher + notifications specific */
    .doc-nav__switcher-active {
        display: flex; align-items: flex-start; gap: 0.55rem;
        padding: 0.5rem 1rem 0.6rem;
    }
    .doc-nav__switcher-active i { color: #8cc63f; margin-top: 0.15rem; }
    .doc-nav__switcher-active strong { color: #111827; }

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
        border-left-color: transparent;
    }
    .doc-nav__notif-item.is-read .doc-nav__notif-title { color: #64748b; font-weight: 500; }
    .doc-nav__notif-item.is-read .doc-nav__notif-body  { color: #94a3b8; }
    .doc-nav__notif-item.is-read .doc-nav__notif-time  { color: #cbd5e1; }
    .doc-nav__notif-item.is-read i { opacity: 0.5; }
    .doc-nav__notif-item.is-read:hover { background: #f1f5f9; }
    
    .doc-nav__notif-item .bi-check-circle { color: #10b981; margin-top: 0.15rem; }
    .doc-nav__notif-item .bi-x-circle { color: #ef4444; margin-top: 0.15rem; }
    .doc-nav__notif-item .bi-hourglass-split { color: #f59e0b; margin-top: 0.15rem; }
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

    /* Dark-mode overrides */
    html[data-theme="dark"] .doc-nav {
        background: #1f2a37;
        border-color: #374151;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }
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
    html[data-theme="dark"] .doc-nav__notif-item { color: #e5e7eb; }
    html[data-theme="dark"] .doc-nav__notif-item:hover { background: #374151; }
    html[data-theme="dark"] .doc-nav__notif-title { color: #f1f5f9; }
    html[data-theme="dark"] .doc-nav__notif-body { color: #94a3b8; }
    html[data-theme="dark"] .doc-nav__notif-close:hover { background: #451a1a; color: #fca5a5; }
</style>

<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow doc-nav-shell">
<div class="navbar-container d-flex content">
<div class="doc-nav">
    <button type="button" class="doc-nav__toggle menu-toggle" aria-label="Toggle menu">
        <i data-feather="menu"></i>
    </button>

    {{-- Practice switcher (only shown when an active practice exists) --}}
    @if($navActivePractice)
        <div class="dropdown">
            <a class="doc-nav__practice" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-building"></i>
                <span class="doc-nav__practice-label">Working at</span>
                <span class="doc-nav__practice-name">{{ $navActivePractice->name }}</span>
                <i class="bi bi-chevron-down"></i>
            </a>
            <div class="dropdown-menu">
                <h6 class="dropdown-header">Working at</h6>
                <div class="doc-nav__switcher-active">
                    <i class="bi bi-star-fill"></i>
                    <strong>{{ $navActivePractice->name }}</strong>
                </div>

                @if($navOtherPractices->isNotEmpty())
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Switch to</h6>
                    @foreach($navOtherPractices as $p)
                        <form method="POST" action="{{ route('doctor.practice.switch') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="practice_id" value="{{ $p->id }}">
                            <button type="submit" class="dropdown-item">
                                <i class="bi bi-arrow-left-right"></i>{{ $p->name }}
                            </button>
                        </form>
                    @endforeach
                @endif

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('doctor.profile.index', ['tab' => 'practices']) }}">
                    <i class="bi bi-gear"></i>Manage my practices
                    @if($navPendingCount > 0)
                        <span class="ms-auto" style="font-size:0.72rem;color:#b9681a;font-weight:700;">{{ $navPendingCount }} pending</span>
                    @endif
                </a>
            </div>
        </div>
    @endif

    <div class="doc-nav__spacer"></div>

    {{-- Theme toggle --}}
    <a class="doc-nav__icon-btn" href="#" data-theme-toggle title="Toggle dark / light mode" aria-label="Toggle dark mode">
        <i class="bi bi-moon" data-theme-toggle-icon></i>
    </a>

    {{-- Help --}}
    <a class="doc-nav__icon-btn" href="{{ route('doctor.help.index') }}" title="Help &amp; FAQ" aria-label="Help">
        <i class="bi bi-question-circle"></i>
    </a>

    {{-- Notifications bell --}}
    <div class="dropdown">
        <a class="doc-nav__icon-btn" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            @if($navBellCount > 0)
                <span class="doc-nav__count-dot">{{ $navBellCount > 9 ? '9+' : $navBellCount }}</span>
            @endif
        </a>
        @php $recent = auth()->user()?->notifications()->limit(8)->get() ?? collect(); @endphp
        @php $totalNotifs = auth()->user()?->notifications()->count() ?? 0; @endphp
        <div class="dropdown-menu dropdown-menu-end" style="min-width:340px;max-width:360px;" id="doctorNotifDropdown">
            <h6 class="dropdown-header" style="display:flex;justify-content:space-between;align-items:center;">
                <span>Notifications</span>
                @if($navBellCount > 0)
                    <span style="font-size:0.7rem;font-weight:600;color:#1e6c85;" data-notif-count-label>{{ $navBellCount }} new</span>
                @endif
            </h6>
            <div class="doc-nav__notif-actions">
                <button type="button" class="doc-nav__notif-action-btn" data-notif-mark-all
                        @if($navUnreadNotifs === 0) disabled @endif
                        title="Mark all unread notifications as read">
                    <i class="bi bi-check2-all"></i> Mark all read
                </button>
                <button type="button" class="doc-nav__notif-action-btn is-danger" data-notif-delete-all
                        @if($totalNotifs === 0) disabled @endif
                        title="Delete every notification record">
                    <i class="bi bi-trash"></i> Delete all
                </button>
            </div>
            <div class="dropdown-divider" style="margin: 0 0 0.25rem;"></div>
            <div class="doc-nav__notif-scroll" data-notif-list>
                @foreach($navPendingList as $pp)
                    <a class="doc-nav__notif-item"
                       href="{{ route('doctor.profile.index', ['tab' => 'practices']) }}"
                       data-pending-id="{{ $pp->pivot->id }}"
                       data-pending-name="{{ $pp->name }}">
                        <i class="bi bi-hourglass-split"></i>
                        <span style="flex:1;">
                            <span class="doc-nav__notif-title">Practice request under review</span>
                            <span class="doc-nav__notif-body">Your request to join <strong>{{ $pp->name }}</strong> is awaiting admin review.</span>
                            @if($pp->pivot?->created_at)
                                <span class="doc-nav__notif-time">Requested {{ $pp->pivot->created_at->diffForHumans() }}</span>
                            @endif
                        </span>
                        <span class="doc-nav__notif-unread"></span>
                        <button type="button" class="doc-nav__notif-close"
                                data-pending-cancel="{{ $pp->pivot->id }}"
                                data-pending-name="{{ $pp->name }}"
                                title="Cancel this request" aria-label="Cancel request">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </a>
                @endforeach
                @forelse($recent as $n)
                    @php
                        $d = $n->data;
                        $title = $d['title'] ?? 'Notification';
                        $body  = $d['body']  ?? '';
                        $url   = $d['url']   ?? route('doctor.profile.index', ['tab' => 'practices']);
                        $icon  = ($d['kind'] ?? '') === 'rejected' ? 'x-circle' : 'check-circle';
                    @endphp
                    <a class="doc-nav__notif-item {{ $n->read_at ? 'is-read' : '' }}"
                       href="{{ $url }}"
                       data-notif-id="{{ $n->id }}">
                        <i class="bi bi-{{ $icon }}"></i>
                        <span style="flex:1;">
                            <span class="doc-nav__notif-title">{{ $title }}</span>
                            <span class="doc-nav__notif-body">{{ $body }}</span>
                            <span class="doc-nav__notif-time">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                        @if(!$n->read_at)<span class="doc-nav__notif-unread"></span>@endif
                        <button type="button" class="doc-nav__notif-close"
                                data-notif-delete="{{ $n->id }}"
                                title="Delete" aria-label="Delete notification">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </a>
                @empty
                    @if($navPendingList->isEmpty())
                        <div class="doc-nav__notif-empty" data-notif-empty>No notifications yet.</div>
                    @endif
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
                @if($navAvatar)
                    <img src="{{ $navAvatar }}" alt="avatar">
                @else
                    {{ $initial }}
                @endif
            </span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <div class="doc-nav__user-head">
                <div class="doc-nav__user-head-name">{{ $navName }}</div>
                <div class="doc-nav__user-head-sub">Doctor @if($navEmail) &middot; {{ $navEmail }} @endif</div>
            </div>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('doctor.profile.index') }}">
                <i class="bi bi-person"></i>My Profile
            </a>
            <a class="dropdown-item" href="{{ route('doctor.profile.settings') }}">
                <i class="bi bi-key"></i>Change Password
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
<div class="modal fade" id="doctorNotifModal" tabindex="-1" aria-hidden="true" aria-labelledby="doctorNotifModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 540px;">
        <div class="modal-content">
            <div class="modal-header" style="padding: 0.85rem 1.1rem;">
                <h5 class="modal-title" id="doctorNotifModalLabel" style="font-size:1rem;font-weight:700;margin:0;">
                    All notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="doc-nav__notif-actions" style="padding: 0.6rem 1.1rem; border-bottom: 1px solid #f3f2f7;">
                <button type="button" class="doc-nav__notif-action-btn" data-notif-mark-all
                        title="Mark all unread notifications as read">
                    <i class="bi bi-check2-all"></i> Mark all read
                </button>
                <button type="button" class="doc-nav__notif-action-btn is-danger" data-notif-delete-all
                        title="Delete every notification record">
                    <i class="bi bi-trash"></i> Delete all
                </button>
            </div>
            <div class="modal-body" data-notif-modal-body style="padding: 0;">
                <div class="doc-nav__notif-empty">Loading…</div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('vuexy/vendors/css/extensions/sweetalert2.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vuexy/vendors/js/extensions/sweetalert2.all.min.js') }}"></script>
<style>
    /* ── Notification action buttons row ── */
    .doc-nav__notif-actions {
        display: flex;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem 0.35rem;
    }
    .doc-nav__notif-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.7rem;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid #e5e7eb;
        border-radius: 0.45rem;
        background: #fff;
        color: #374151;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1;
    }
    .doc-nav__notif-action-btn i { font-size: 0.82rem; }
    .doc-nav__notif-action-btn:hover:not(:disabled) { background: #f3f4f6; border-color: #d1d5db; color: #111827; }
    .doc-nav__notif-action-btn:disabled { opacity: 0.38; cursor: not-allowed; }
    .doc-nav__notif-action-btn.is-danger { color: #dc2626; border-color: #fca5a5; }
    .doc-nav__notif-action-btn.is-danger:hover:not(:disabled) { background: #fef2f2; border-color: #ef4444; }
 
    /* ── Scrollable notif list in dropdown ── */
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
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e6c85;
        background: transparent;
        border: 0;
        border-radius: 0.45rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .doc-nav__notif-viewall:hover { background: rgba(91,192,222,0.08); }
 
    /* ── Read state: clear visual difference ── */
    .doc-nav__notif-item { transition: background 0.2s ease, border-left-color 0.2s ease, opacity 0.2s ease; }
    .doc-nav__notif-item.is-read {
        opacity: 0.6;
        border-left-color: transparent !important;
    }
    .doc-nav__notif-item.is-read .doc-nav__notif-title { font-weight: 500; color: #64748b; }
    .doc-nav__notif-item.is-read .doc-nav__notif-body  { color: #94a3b8; }
    .doc-nav__notif-item.is-read .doc-nav__notif-time  { color: #cbd5e1; }
 
    /* ── Vuexy-style SweetAlert2 overrides ── */
    .swal2-popup.swal2-toast { border-radius: 0.6rem !important; }
</style>
<script>
(function () {
    'use strict';
 
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const routes = {
        index:         @json(route('doctor.notifications.index')),
        readAll:       @json(route('doctor.notifications.read-all')),
        deleteAll:     @json(route('doctor.notifications.destroy-all')),
        read:          @json(url('/dev/notifications')) + '/{id}/read',
        destroy:       @json(url('/dev/notifications')) + '/{id}',
        cancelPending: @json(url('/dev/notifications/pending')) + '/{id}',
    };
    const fallbackUrl = @json(route('doctor.profile.index', ['tab' => 'practices']));
 
    // ─── Fetch helper ────────────────────────────────────────────────────────
    const fetchJson = (url, opts = {}) => fetch(url, {
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        ...opts,
    }).then(r => r.ok ? r.json() : Promise.reject(r));
 
    // Fire-and-forget mark-read that survives page navigation
    const markReadKeepalive = (id) => fetch(
        routes.read.replace('{id}', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            keepalive: true,
        }
    ).catch(() => {});
 
    // ─── SweetAlert2 helpers — Vuexy style ───────────────────────────────────
    /**
     * Proper Vuexy-flavoured confirm dialog.
     * type: 'danger' | 'warning' | 'question'
     */
    function confirmAction(title, text, type = 'warning') {
        return Swal.fire({
            title,
            text,
            icon: type === 'danger' ? 'warning' : type,
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: type === 'danger'
                    ? 'btn btn-danger'
                    : 'btn btn-primary',
                cancelButton: 'btn btn-outline-secondary ms-1',
                popup: 'swal2-vuexy',
            },
            buttonsStyling: false,
            reverseButtons: false,
            focusCancel: true,          // focus Cancel by default — safer UX
        });
    }
 
    /** Vuexy-flavoured danger confirm (red button, stronger warning) */
    function confirmDanger(title, text) {
        return Swal.fire({
            title,
            text,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-outline-secondary ms-1',
            },
            buttonsStyling: false,
            focusCancel: true,
        });
    }
 
    /** Top-right toast */
    function showToast(title, type = 'success') {
        Swal.fire({
            title,
            icon: type,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2800,
            timerProgressBar: true,
            customClass: { popup: 'swal2-vuexy' },
        });
    }
 
    // ─── Bell badge ──────────────────────────────────────────────────────────
    function updateBellCount(bellCount) {
        const bell = document.querySelector('.doc-nav__icon-btn[title="Notifications"]');
        if (!bell) return;
        let dot = bell.querySelector('.doc-nav__count-dot');
        const label = document.querySelector('[data-notif-count-label]');
 
        if (bellCount > 0) {
            if (!dot) {
                dot = document.createElement('span');
                dot.className = 'doc-nav__count-dot';
                bell.appendChild(dot);
            }
            dot.textContent = bellCount > 9 ? '9+' : bellCount;
            if (label) label.textContent = bellCount + ' new';
        } else {
            dot?.remove();
            if (label) {
                label.textContent = '';
                label.style.display = 'none';
            }
        }
    }
 
    // ─── Dropdown button sync ────────────────────────────────────────────────
    function syncDropdownButtons() {
        const dropdown = document.getElementById('doctorNotifDropdown');
        if (!dropdown) return;
 
        const dbItems  = [...dropdown.querySelectorAll('[data-notif-id]')];
        const dbUnread = dbItems.filter(el => !el.classList.contains('is-read')).length;
 
        const markBtn = dropdown.querySelector('[data-notif-mark-all]');
        const delBtn  = dropdown.querySelector('[data-notif-delete-all]');
 
        // Use removeAttribute / setAttribute so it works cross-browser reliably
        if (markBtn) {
            if (dbUnread > 0) markBtn.removeAttribute('disabled');
            else markBtn.setAttribute('disabled', '');
        }
        if (delBtn) {
            if (dbItems.length > 0) delBtn.removeAttribute('disabled');
            else delBtn.setAttribute('disabled', '');
        }
 
        // Show empty state if nothing left
        const list = dropdown.querySelector('[data-notif-list]');
        const pendingItems = dropdown.querySelectorAll('[data-pending-id]');
        if (list && dbItems.length === 0 && pendingItems.length === 0 && !list.querySelector('[data-notif-empty]')) {
            list.innerHTML = '<div class="doc-nav__notif-empty" data-notif-empty>No notifications yet.</div>';
        }
    }
 
    function countPendingInDropdown() {
        const dropdown = document.getElementById('doctorNotifDropdown');
        return dropdown ? dropdown.querySelectorAll('[data-pending-id]').length : 0;
    }
 
    // ─── Mark item read in DOM ───────────────────────────────────────────────
    function markItemReadInDom(el) {
        if (!el || el.classList.contains('is-read')) return;
        el.classList.add('is-read');
        el.querySelector('.doc-nav__notif-unread')?.remove();
    }
 
    // ─── Bell dropdown ───────────────────────────────────────────────────────
    const dropdown = document.getElementById('doctorNotifDropdown');
 
    if (dropdown) {
        // Initialise button states on page load (overrides server-rendered disabled attr)
        syncDropdownButtons();
 
        dropdown.addEventListener('click', function (e) {
 
            // ── X on a DB notification → delete ──────────────────────────────
            const delBtn = e.target.closest('[data-notif-delete]');
            if (delBtn) {
                e.preventDefault();
                e.stopPropagation();
                const id  = delBtn.dataset.notifDelete;
                const row = delBtn.closest('[data-notif-id]');
 
                confirmDanger('Delete notification?', 'This will permanently remove this notification.')
                    .then(result => {
                        if (!result.isConfirmed) return;
                        fetchJson(routes.destroy.replace('{id}', id), { method: 'DELETE' })
                            .then(j => {
                                row?.remove();
                                updateBellCount((j.unread || 0) + countPendingInDropdown());
                                syncDropdownButtons();
                                showToast('Notification deleted');
                            })
                            .catch(() => Swal.fire({ title: 'Error', text: 'Could not delete notification.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
                    });
                return;
            }
 
            // ── X on a pending item → cancel request ─────────────────────────
            const cancelBtn = e.target.closest('[data-pending-cancel]');
            if (cancelBtn) {
                e.preventDefault();
                e.stopPropagation();
                const id   = cancelBtn.dataset.pendingCancel;
                const name = cancelBtn.dataset.pendingName || 'this practice';
 
                confirmAction('Cancel request?', `Stop your request to join "${name}"?`)
                    .then(result => {
                        if (!result.isConfirmed) return;
                        fetchJson(routes.cancelPending.replace('{id}', id), { method: 'DELETE' })
                            .then(j => {
                                cancelBtn.closest('[data-pending-id]')?.remove();
                                updateBellCount(j.bellCount ?? 0);
                                syncDropdownButtons();
                                showToast('Request cancelled');
                            })
                            .catch(() => Swal.fire({ title: 'Error', text: 'Could not cancel the request.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
                    });
                return;
            }
 
            // ── Click on a DB notification row → mark read + navigate ─────────
            const link = e.target.closest('[data-notif-id]');
            if (link) {
                markItemReadInDom(link);
                markReadKeepalive(link.dataset.notifId);
                syncDropdownButtons(); // re-enable Mark All Read if any unread remain
                // Allow the <a> href to navigate naturally
            }
        });
 
        // Mark all read
        dropdown.querySelector('[data-notif-mark-all]')?.addEventListener('click', function () {
            fetchJson(routes.readAll, { method: 'POST' })
                .then(j => {
                    dropdown.querySelectorAll('[data-notif-id]').forEach(el => markItemReadInDom(el));
                    updateBellCount((j.unread || 0) + countPendingInDropdown());
                    syncDropdownButtons();
                    showToast('All notifications marked as read');
                })
                .catch(() => Swal.fire({ title: 'Error', text: 'Could not mark notifications as read.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
        });
 
        // Delete all
        dropdown.querySelector('[data-notif-delete-all]')?.addEventListener('click', function () {
            confirmDanger('Delete all notifications?', 'Every notification will be permanently removed. This cannot be undone.')
                .then(result => {
                    if (!result.isConfirmed) return;
                    fetchJson(routes.deleteAll, { method: 'DELETE' })
                        .then(j => {
                            dropdown.querySelectorAll('[data-notif-id]').forEach(el => el.remove());
                            updateBellCount((j.unread || 0) + countPendingInDropdown());
                            syncDropdownButtons();
                            showToast('All notifications deleted');
                        })
                        .catch(() => Swal.fire({ title: 'Error', text: 'Could not delete notifications.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
                });
        });
    }
 
    // ─── "View All" modal ────────────────────────────────────────────────────
    const modalEl = document.getElementById('doctorNotifModal');
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
 
    function renderModalItems(items) {
        const body = modalEl.querySelector('[data-notif-modal-body]');
        if (!items.length) {
            body.innerHTML = '<div class="doc-nav__notif-empty">No notifications yet.</div>';
            return;
        }
        body.innerHTML = items.map(n => {
            if (n.type === 'pending') {
                return `
                    <a class="doc-nav__notif-item" href="${escapeHtml(fallbackUrl)}"
                       data-pending-id="${n.id}" data-pending-name="${escapeHtml(n.practiceName)}">
                        <i class="bi bi-hourglass-split"></i>
                        <span style="flex:1;">
                            <span class="doc-nav__notif-title">${escapeHtml(n.title)}</span>
                            <span class="doc-nav__notif-body">${escapeHtml(n.body)}</span>
                            <span class="doc-nav__notif-time">${escapeHtml(n.time)}</span>
                        </span>
                        <span class="doc-nav__notif-unread"></span>
                        <button type="button" class="doc-nav__notif-close"
                                data-pending-cancel="${n.id}" data-pending-name="${escapeHtml(n.practiceName)}"
                                title="Cancel this request" aria-label="Cancel request">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </a>`;
            }
            const icon = n.kind === 'rejected' ? 'x-circle' : 'check-circle';
            const url  = escapeHtml(n.url || fallbackUrl);
            return `
                <a class="doc-nav__notif-item ${n.read ? 'is-read' : ''}" href="${url}" data-notif-id="${n.id}">
                    <i class="bi bi-${icon}"></i>
                    <span style="flex:1;">
                        <span class="doc-nav__notif-title">${escapeHtml(n.title)}</span>
                        <span class="doc-nav__notif-body">${escapeHtml(n.body)}</span>
                        <span class="doc-nav__notif-time">${escapeHtml(n.time)}</span>
                    </span>
                    ${n.read ? '' : '<span class="doc-nav__notif-unread"></span>'}
                    <button type="button" class="doc-nav__notif-close"
                            data-notif-delete="${n.id}" title="Delete" aria-label="Delete notification">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </a>`;
        }).join('');
    }
 
    function syncModalButtonsFromDom() {
        if (!modalEl) return;
        const dbItems  = [...modalEl.querySelectorAll('[data-notif-id]')];
        const dbUnread = dbItems.filter(el => !el.classList.contains('is-read')).length;
 
        const markBtn = modalEl.querySelector('[data-notif-mark-all]');
        const delBtn  = modalEl.querySelector('[data-notif-delete-all]');
 
        if (markBtn) {
            if (dbUnread > 0) markBtn.removeAttribute('disabled');
            else markBtn.setAttribute('disabled', '');
        }
        if (delBtn) {
            if (dbItems.length > 0) delBtn.removeAttribute('disabled');
            else delBtn.setAttribute('disabled', '');
        }
    }
 
    function countPendingInModal() {
        return modalEl ? modalEl.querySelectorAll('[data-pending-id]').length : 0;
    }
 
    function refreshModalEmpty() {
        if (!modalEl) return;
        const body = modalEl.querySelector('[data-notif-modal-body]');
        const hasItems = body.querySelector('[data-notif-id], [data-pending-id]');
        if (!hasItems) {
            body.innerHTML = '<div class="doc-nav__notif-empty">No notifications yet.</div>';
        }
    }
 
    function loadModal() {
        const body = modalEl.querySelector('[data-notif-modal-body]');
        body.innerHTML = '<div class="doc-nav__notif-empty">Loading…</div>';
        fetchJson(routes.index)
            .then(j => {
                renderModalItems(j.items);
                syncModalButtonsFromDom();
                updateBellCount(j.bellCount ?? 0);
            })
            .catch(() => {
                body.innerHTML = '<div class="doc-nav__notif-empty">Could not load notifications.</div>';
            });
    }
 
    // Open modal via "View all" button
    document.querySelector('[data-notif-viewall]')?.addEventListener('click', function () {
        const m = getModal();
        if (!m) { console.error('Bootstrap modal not available'); return; }
 
        // Close bell dropdown first so backdrop works correctly
        const bellToggle = document.querySelector('[data-bs-toggle="dropdown"][title="Notifications"]');
        if (bellToggle && window.bootstrap?.Dropdown) {
            bootstrap.Dropdown.getInstance(bellToggle)?.hide();
        }
        loadModal();
        m.show();
    });
 
    if (modalEl) {
        modalEl.addEventListener('click', function (e) {
 
            // ── X on a DB notification → delete ──────────────────────────────
            const delBtn = e.target.closest('[data-notif-delete]');
            if (delBtn) {
                e.preventDefault();
                e.stopPropagation();
                const id  = delBtn.dataset.notifDelete;
                const row = delBtn.closest('[data-notif-id]');
 
                confirmDanger('Delete notification?', 'This will permanently remove this notification.')
                    .then(result => {
                        if (!result.isConfirmed) return;
                        fetchJson(routes.destroy.replace('{id}', id), { method: 'DELETE' })
                            .then(j => {
                                row?.remove();
                                updateBellCount((j.unread || 0) + countPendingInModal());
                                syncModalButtonsFromDom();
                                refreshModalEmpty();
                                showToast('Notification deleted');
                            })
                            .catch(() => Swal.fire({ title: 'Error', text: 'Could not delete notification.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
                    });
                return;
            }
 
            // ── X on a pending item → cancel request ─────────────────────────
            const cancelBtn = e.target.closest('[data-pending-cancel]');
            if (cancelBtn) {
                e.preventDefault();
                e.stopPropagation();
                const id   = cancelBtn.dataset.pendingCancel;
                const name = cancelBtn.dataset.pendingName || 'this practice';
 
                confirmAction('Cancel request?', `Stop your request to join "${name}"?`)
                    .then(result => {
                        if (!result.isConfirmed) return;
                        fetchJson(routes.cancelPending.replace('{id}', id), { method: 'DELETE' })
                            .then(j => {
                                cancelBtn.closest('[data-pending-id]')?.remove();
                                updateBellCount(j.bellCount ?? 0);
                                refreshModalEmpty();
                                showToast('Request cancelled');
                            })
                            .catch(() => Swal.fire({ title: 'Error', text: 'Could not cancel the request.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
                    });
                return;
            }
 
            // ── Click on a DB notification row → mark read ────────────────────
            const link = e.target.closest('[data-notif-id]');
            if (link) {
                markItemReadInDom(link);
                markReadKeepalive(link.dataset.notifId);
                syncModalButtonsFromDom();
            }
        });
 
        // Mark all read — modal
        modalEl.querySelector('[data-notif-mark-all]')?.addEventListener('click', function () {
            fetchJson(routes.readAll, { method: 'POST' })
                .then(j => {
                    modalEl.querySelectorAll('[data-notif-id]').forEach(el => markItemReadInDom(el));
                    updateBellCount((j.unread || 0) + countPendingInModal());
                    syncModalButtonsFromDom();
                    showToast('All notifications marked as read');
                })
                .catch(() => Swal.fire({ title: 'Error', text: 'Could not mark notifications as read.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
        });
 
        // Delete all — modal
        modalEl.querySelector('[data-notif-delete-all]')?.addEventListener('click', function () {
            confirmDanger('Delete all notifications?', 'Every notification will be permanently removed. This cannot be undone.')
                .then(result => {
                    if (!result.isConfirmed) return;
                    fetchJson(routes.deleteAll, { method: 'DELETE' })
                        .then(j => {
                            modalEl.querySelectorAll('[data-notif-id]').forEach(el => el.remove());
                            updateBellCount((j.unread || 0) + countPendingInModal());
                            syncModalButtonsFromDom();
                            refreshModalEmpty();
                            showToast('All notifications deleted');
                        })
                        .catch(() => Swal.fire({ title: 'Error', text: 'Could not delete notifications.', icon: 'error', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false }));
                });
        });
    }
 
})();
</script>
@endpush
