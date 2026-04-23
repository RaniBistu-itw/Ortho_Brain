@php
    $navDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
    $navName   = $navDoctor ? trim($navDoctor->first_name . ' ' . $navDoctor->last_name) : auth()->user()->email;
    $initial   = strtoupper(substr($navName ?: 'U', 0, 1));
    $navAvatar = $navDoctor?->avatarUrl();

    $navActivePractice = currentPractice();
    $navOtherPractices = $navDoctor
        ? $navDoctor->activePractices()
            ->when($navActivePractice, fn ($q) => $q->where('practices.id', '!=', $navActivePractice->id))
            ->get()
        : collect();
    $navPendingCount  = $navDoctor ? $navDoctor->pendingPractices()->count() : 0;
    $navUnreadNotifs  = auth()->user()?->unreadNotifications()->count() ?? 0;
@endphp

<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow">
    <div class="navbar-container d-flex content">
        <div class="bookmark-wrapper d-flex align-items-center">
            <ul class="nav navbar-nav d-xl-none">
                <li class="nav-item">
                    <a class="nav-link menu-toggle" href="javascript:void(0);">
                        <i class="ficon" data-feather="menu"></i>
                    </a>
                </li>
            </ul>
        </div>

        <ul class="nav navbar-nav align-items-center ms-auto">
            {{-- Practice switcher: shown when doctor has an active practice + at least one other --}}
            @if($navActivePractice)
                <li class="nav-item dropdown me-1">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Switch practice">
                        <i class="bi bi-building me-1"></i>
                        <span class="d-none d-md-inline" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $navActivePractice->name }}</span>
                        @if($navPendingCount > 0)
                            <span class="badge bg-warning ms-1" title="{{ $navPendingCount }} pending request(s)">{{ $navPendingCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="min-width:240px;">
                        <h6 class="dropdown-header">Active Practice</h6>
                        <div class="dropdown-item-text">
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
                                        <i class="bi bi-arrow-left-right me-2"></i>{{ $p->name }}
                                    </button>
                                </form>
                            @endforeach
                        @endif
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('doctor.profile.index') }}#practices">
                            <i class="bi bi-gear me-2"></i>Manage Practices
                            @if($navPendingCount > 0)
                                <span class="badge bg-warning float-end">{{ $navPendingCount }} pending</span>
                            @endif
                        </a>
                    </div>
                </li>
            @endif

            {{-- Notifications bell --}}
            <li class="nav-item dropdown dropdown-notification me-1">
                <a class="nav-link" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                    <i class="bi bi-bell" style="font-size:1.1rem;"></i>
                    @if($navUnreadNotifs > 0)
                        <span class="badge rounded-pill bg-danger" style="position:absolute;top:6px;right:0;font-size:0.65rem;">{{ $navUnreadNotifs > 9 ? '9+' : $navUnreadNotifs }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end" style="min-width:320px;max-width:360px;">
                    <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        @if($navUnreadNotifs > 0)
                            <span class="badge bg-light-primary">{{ $navUnreadNotifs }} new</span>
                        @endif
                    </h6>
                    <div class="dropdown-divider m-0"></div>
                    @php $recent = auth()->user()?->notifications()->limit(8)->get() ?? collect(); @endphp
                    @forelse($recent as $n)
                        @php
                            $d = $n->data;
                            $title = $d['title'] ?? 'Notification';
                            $body  = $d['body']  ?? '';
                            $url   = $d['url']   ?? route('doctor.profile.index') . '#practices';
                            $icon  = ($d['kind'] ?? '') === 'rejected' ? 'x-circle text-danger' : 'check-circle text-success';
                        @endphp
                        <a class="dropdown-item d-flex align-items-start py-2" href="{{ $url }}" style="white-space:normal;">
                            <i class="bi bi-{{ $icon }} me-2 mt-1"></i>
                            <span style="flex:1;">
                                <span class="d-block fw-bold">{{ $title }}</span>
                                <small class="text-muted d-block">{{ $body }}</small>
                                <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                            </span>
                            @if(!$n->read_at)<span class="badge bg-primary ms-1" style="height:8px;width:8px;padding:0;"></span>@endif
                        </a>
                    @empty
                        <div class="dropdown-item-text text-muted text-center py-3">No notifications yet.</div>
                    @endforelse
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#" data-theme-toggle title="Toggle dark / light mode" aria-label="Toggle dark mode">
                    <i class="ficon" data-feather="moon" data-theme-toggle-icon></i>
                </a>
            </li>
            <li class="nav-item dropdown dropdown-user">
                <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="#"
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="user-nav d-sm-flex d-none">
                        <span class="user-name fw-bolder">{{ $navName }}</span>
                        <span class="user-status">Doctor</span>
                    </div>
                    <span class="avatar">
                        @if($navAvatar)
                            <img src="{{ $navAvatar }}" alt="avatar" style="width:32px; height:32px; object-fit:cover; border-radius:50%;">
                        @else
                            <span class="avatar-content">{{ $initial }}</span>
                        @endif
                        <span class="avatar-status-online"></span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-user">
                    <a class="dropdown-item" href="{{ route('doctor.profile.index') }}">
                        <i class="me-50" data-feather="user"></i> My Profile
                    </a>
                    <a class="dropdown-item" href="{{ route('doctor.profile.settings') }}">
                        <i class="me-50" data-feather="lock"></i> Change Password
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="me-50" data-feather="power"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
