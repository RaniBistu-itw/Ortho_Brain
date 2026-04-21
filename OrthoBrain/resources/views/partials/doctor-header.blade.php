@php
    $navDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
    $navName   = $navDoctor ? trim($navDoctor->first_name . ' ' . $navDoctor->last_name) : auth()->user()->email;
    $initial   = strtoupper(substr($navName ?: 'U', 0, 1));
    $navAvatar = $navDoctor?->avatarUrl();
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
