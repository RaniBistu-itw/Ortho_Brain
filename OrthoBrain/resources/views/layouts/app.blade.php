<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0, minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>orthoBrain - @yield('title', 'Dashboard')</title>

    <link rel="apple-touch-icon" href="{{ asset('vuexy/images/ico/favicon-32x32.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('vuexy/images/ico/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/forms/select/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/core/menu/menu-types/vertical-menu.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/orthobrain-overrides.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Tailwind CDN kept for legacy doctor-side pages (register, profile/index) whose forms
         still use utility classes. Remove once those pages are fully ported to Vuexy markup. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { vuexy: {
                primary: '#7367f0', hover: '#665ee0', green: '#28c76f',
                text: '#6e6b7b', heading: '#5e5873', border: '#d8d6de', bg: '#f8f8f8',
            }}}}
        }
    </script>

    @stack('styles')
</head>

<body class="horizontal-layout horizontal-menu navbar-floating footer-static  menu-expanded"
      data-open="click" data-menu="horizontal-menu" data-col="1-column">

    @php
        $navDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
        $navName   = $navDoctor ? trim($navDoctor->first_name . ' ' . $navDoctor->last_name) : auth()->user()->email;
        $initial   = strtoupper(substr($navName ?: 'U', 0, 1));
    @endphp

    <nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav navbar-light navbar-shadow">
        <div class="navbar-container d-flex content">
            <div class="d-flex align-items-center">
                <span class="fw-bold fst-italic text-dark">Designed for OrthoDentists&trade;</span>
            </div>

            <ul class="nav navbar-nav align-items-center ms-auto">
                <li class="nav-item dropdown dropdown-user">
                    <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="#"
                       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="user-nav d-sm-flex d-none">
                            <span class="user-name fw-bolder">{{ $navName }}</span>
                            <span class="user-status">Doctor</span>
                        </div>
                        <span class="avatar">
                            <span class="avatar-content">{{ $initial }}</span>
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

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-body">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="footer footer-static footer-light">
        <p class="clearfix mb-0">
            <span class="float-md-start d-block d-md-inline-block mt-25">
                COPYRIGHT &copy; {{ date('Y') }}
                <a class="ms-25" href="#">orthoBrain</a>,
                <span class="d-none d-sm-inline-block">All rights Reserved</span>
            </span>
        </p>
    </footer>

    <script src="{{ asset('vuexy/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('vuexy/vendors/js/ui/jquery.sticky.js') }}"></script>
    <script src="{{ asset('vuexy/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ asset('vuexy/js/core/app-menu.js') }}"></script>
    <script src="{{ asset('vuexy/js/core/app.js') }}"></script>
    <script src="{{ asset('vuexy/js/core/scripts.js') }}"></script>

    <script>
        $(window).on('load', function () {
            if (window.feather) { feather.replace({ width: 14, height: 14 }); }
        });
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
    </script>

    @stack('scripts')
</body>
</html>
