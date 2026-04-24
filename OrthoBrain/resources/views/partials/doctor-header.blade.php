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
    $navPendingCount  = $navDoctor ? $navDoctor->pendingPractices()->count() : 0;
    $navUnreadNotifs  = auth()->user()?->unreadNotifications()->count() ?? 0;
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

    /* Theme toggle row */
    .doc-nav__theme-row {
        display: flex; align-items: center; gap: 0.6rem;
        padding: 0.55rem 1rem;
        font-size: 0.88rem;
        color: #374151;
        cursor: pointer;
    }
    .doc-nav__theme-row:hover { background: #f3f4f6; }
    .doc-nav__theme-row i { width: 16px; color: #6b7280; }
    .doc-nav__theme-switch {
        margin-left: auto;
        width: 30px; height: 18px;
        border-radius: 999px;
        background: #e5e7eb;
        position: relative;
        transition: background 0.15s ease;
    }
    .doc-nav__theme-switch::after {
        content: '';
        position: absolute;
        top: 2px; left: 2px;
        width: 14px; height: 14px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        transition: transform 0.15s ease;
    }
    html[data-theme="dark"] .doc-nav__theme-switch { background: #5bc0de; }
    html[data-theme="dark"] .doc-nav__theme-switch::after { transform: translateX(12px); }

    /* Switcher + notifications specific */
    .doc-nav__switcher-active {
        display: flex; align-items: flex-start; gap: 0.55rem;
        padding: 0.5rem 1rem 0.6rem;
    }
    .doc-nav__switcher-active i { color: #8cc63f; margin-top: 0.15rem; }
    .doc-nav__switcher-active strong { color: #111827; }

    .doc-nav__notif-empty { padding: 1.5rem 1rem; text-align: center; color: #9ca3af; font-size: 0.85rem; }
    .doc-nav__notif-item {
        display: flex; align-items: flex-start; gap: 0.6rem;
        padding: 0.7rem 1rem;
        white-space: normal;
        border-bottom: 1px solid #f3f2f7;
        text-decoration: none;
        color: inherit;
    }
    .doc-nav__notif-item:last-child { border-bottom: 0; }
    .doc-nav__notif-item:hover { background: #f9fafb; }
    .doc-nav__notif-item .bi-check-circle { color: #5a8f21; margin-top: 0.15rem; }
    .doc-nav__notif-item .bi-x-circle { color: #c53030; margin-top: 0.15rem; }
    .doc-nav__notif-title { font-weight: 600; color: #111827; font-size: 0.88rem; }
    .doc-nav__notif-body  { color: #4b5563; font-size: 0.8rem; margin-top: 0.1rem; }
    .doc-nav__notif-time  { color: #9ca3af; font-size: 0.72rem; margin-top: 0.2rem; display: block; }
    .doc-nav__notif-unread {
        width: 8px; height: 8px;
        background: #5bc0de;
        border-radius: 50%;
        margin-top: 0.4rem;
        flex-shrink: 0;
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
    html[data-theme="dark"] .doc-nav__theme-row { color: #e5e7eb; }
    html[data-theme="dark"] .doc-nav__theme-row:hover { background: #374151; }
    html[data-theme="dark"] .doc-nav__notif-item { color: #e5e7eb; }
    html[data-theme="dark"] .doc-nav__notif-item:hover { background: #374151; }
    html[data-theme="dark"] .doc-nav__notif-title { color: #f3f4f6; }
    html[data-theme="dark"] .doc-nav__notif-body { color: #9ca3af; }
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

    {{-- Help --}}
    <a class="doc-nav__icon-btn" href="{{ route('doctor.help.index') }}" title="Help &amp; FAQ" aria-label="Help">
        <i class="bi bi-question-circle"></i>
    </a>

    {{-- Notifications bell --}}
    <div class="dropdown">
        <a class="doc-nav__icon-btn" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            @if($navUnreadNotifs > 0)
                <span class="doc-nav__count-dot">{{ $navUnreadNotifs > 9 ? '9+' : $navUnreadNotifs }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end" style="min-width:340px;max-width:360px;">
            <h6 class="dropdown-header" style="display:flex;justify-content:space-between;align-items:center;">
                <span>Notifications</span>
                @if($navUnreadNotifs > 0)
                    <span style="font-size:0.7rem;font-weight:600;color:#1e6c85;">{{ $navUnreadNotifs }} new</span>
                @endif
            </h6>
            <div class="dropdown-divider" style="margin: 0 0 0.25rem;"></div>
            @php $recent = auth()->user()?->notifications()->limit(8)->get() ?? collect(); @endphp
            @forelse($recent as $n)
                @php
                    $d = $n->data;
                    $title = $d['title'] ?? 'Notification';
                    $body  = $d['body']  ?? '';
                    $url   = $d['url']   ?? route('doctor.profile.index', ['tab' => 'practices']);
                    $icon  = ($d['kind'] ?? '') === 'rejected' ? 'x-circle' : 'check-circle';
                @endphp
                <a class="doc-nav__notif-item" href="{{ $url }}">
                    <i class="bi bi-{{ $icon }}"></i>
                    <span style="flex:1;">
                        <span class="doc-nav__notif-title">{{ $title }}</span>
                        <span class="doc-nav__notif-body">{{ $body }}</span>
                        <span class="doc-nav__notif-time">{{ $n->created_at->diffForHumans() }}</span>
                    </span>
                    @if(!$n->read_at)<span class="doc-nav__notif-unread"></span>@endif
                </a>
            @empty
                <div class="doc-nav__notif-empty">No notifications yet.</div>
            @endforelse
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
            <a href="#" class="doc-nav__theme-row" data-theme-toggle aria-label="Toggle dark mode">
                <i class="bi bi-moon" data-theme-toggle-icon></i>
                <span>Dark mode</span>
                <span class="doc-nav__theme-switch" aria-hidden="true"></span>
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
