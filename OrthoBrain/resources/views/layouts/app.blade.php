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
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/themes/dark-layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/themes/bordered-layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/themes/semi-dark-layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/core/menu/menu-types/vertical-menu.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/orthobrain-overrides.css') }}" />
    {{-- OrthoBrain palette — navy + Inter. Loaded LAST so it wins over Vuexy. --}}
    <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @livewireStyles

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

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static  menu-expanded"
      data-open="click" data-menu="vertical-menu-modern" data-col="2-columns">

    {{-- BEGIN: Header / Navbar --}}
    @include('partials.doctor-header')
    {{-- END: Header --}}

    {{-- BEGIN: Main Menu / Sidebar --}}
    @include('partials.doctor-sidebar')
    {{-- END: Main Menu --}}

    {{-- BEGIN: Content --}}
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">@yield('page_title', 'Dashboard')</h2>
                            @hasSection('breadcrumbs')
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        @yield('breadcrumbs')
                                    </ol>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
    {{-- END: Content --}}

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <footer class="footer footer-static footer-light">
        <p class="clearfix mb-0">
            <span class="float-md-start d-block d-md-inline-block mt-25">
                COPYRIGHT &copy; {{ date('Y') }}
                <a class="ms-25" href="#">orthobrain</a>,
                <span class="d-none d-sm-inline-block">All rights Reserved</span>
            </span>
        </p>
    </footer>
    <button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>

    {{-- Vuexy Vendor + Theme JS — data-navigate-once. See admin.blade.php
         for the full rationale; short version: re-running these on body morph
         orphans monkey-patches, double-binds Bootstrap dropdowns and Vuexy's
         .menu-toggle, and breaks navbar/sidebar interactivity. --}}
    <script src="{{ asset('vuexy/vendors/js/vendors.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/vendors/js/ui/jquery.sticky.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/vendors/js/forms/select/select2.full.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/js/core/app-menu.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/js/core/app.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/js/core/scripts.js') }}" data-navigate-once></script>
    <script src="{{ asset('js/theme-toggle.js') }}?v={{ @filemtime(public_path('js/theme-toggle.js')) ?: time() }}" data-navigate-once></script>

    <script data-navigate-once>
    // ════════════════════════════════════════════════════════════════════════
    // ONE-TIME LAYOUT INIT — see admin.blade.php for rationale. Doctor side is
    // lighter (no helper library), so this block is small.
    // ════════════════════════════════════════════════════════════════════════
    if (!window.__obDoctorLayoutInited) {
        window.__obDoctorLayoutInited = true;

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    // PER-PAGE INIT — runs on first window.load AND on every livewire:navigated.
    // ════════════════════════════════════════════════════════════════════════
    // ────────────────────────────────────────────────────────────────────
    // Viewport ↔ body class sync. See admin.blade.php for full rationale.
    // Short version: Vuexy's app-menu.js doesn't react to resize, AND
    // wire:navigate's body morph resets body.class to the server-rendered
    // desktop default — so we have to re-apply mobile classes ourselves on
    // every breakpoint crossing AND every navigation.
    // ────────────────────────────────────────────────────────────────────
    window.obSyncViewportClasses = function () {
        const body     = document.body;
        const menuType = body.dataset.menu || 'vertical-menu-modern';
        const isMobile = window.matchMedia('(max-width: 1199.98px)').matches;
        const overlay  = document.querySelector('.sidenav-overlay');

        if (isMobile) {
            body.classList.remove(menuType);
            body.classList.add('vertical-overlay-menu');
            body.classList.remove('menu-expanded', 'menu-open');
            if (!body.classList.contains('menu-hide')) body.classList.add('menu-hide');
            document.querySelectorAll('nav.header-navbar').forEach(n => n.classList.add('fixed-top'));
        } else {
            body.classList.remove('vertical-overlay-menu');
            body.classList.add(menuType);
            body.classList.remove('menu-open', 'menu-hide');
            if (!body.classList.contains('menu-expanded')
                && !body.classList.contains('menu-collapsed')) {
                body.classList.add('menu-expanded');
            }
            document.querySelectorAll('nav.header-navbar').forEach(n => n.classList.remove('fixed-top'));
        }
        if (overlay) overlay.classList.remove('show');
        document.querySelectorAll('.menu-toggle').forEach(el => el.classList.remove('is-active'));
    };

    window.obCloseMobileDrawer = function () {
        if (!window.matchMedia('(max-width: 1199.98px)').matches) return;
        const body = document.body;
        if (!body.classList.contains('menu-open')) return;
        body.classList.remove('menu-open', 'menu-expanded');
        body.classList.add('menu-hide');
        const overlay = document.querySelector('.sidenav-overlay');
        if (overlay) overlay.classList.remove('show');
        document.querySelectorAll('.menu-toggle').forEach(el => el.classList.remove('is-active'));
    };

    window.obDoctorPageInit = function () {
        document.documentElement.classList.remove('loading');

        // Re-establish viewport-correct body classes after wire:navigate's
        // body morph resets them to the server-rendered desktop default.
        window.obSyncViewportClasses();

        if (window.feather) feather.replace({ width: 14, height: 14 });

        // Active state is now server-rendered via request()->routeIs(...) on
        // every navigate (sidebar is no longer x-persist'd).

        // Flash auto-fade (matches admin behaviour).
        setTimeout(function () { $('.ob-flash').fadeOut(400); }, 5000);
    };


    // ════════════════════════════════════════════════════════════════════════
    // NAVIGATE HOOKS — guarded so re-execution doesn't stack listeners.
    // ════════════════════════════════════════════════════════════════════════
    if (!window.__obDoctorNavigateBound) {
        window.__obDoctorNavigateBound = true;

        document.addEventListener('livewire:navigating', function () {
            // Close mobile drawer using Vuexy's exact close sequence.
            window.obCloseMobileDrawer();

            if (window.jQuery) jQuery(document).off('.ob-page');

            document.documentElement.classList.remove('loading');
        });

        document.addEventListener('livewire:navigated', function () {
            window.obDoctorPageInit();
        });

        $(window).on('load', function () { window.obDoctorPageInit(); });

        // Viewport-crossing sync — see admin.blade.php for full rationale.
        window.matchMedia('(max-width: 1199.98px)').addEventListener('change', function () {
            window.obSyncViewportClasses();
        });
    }
    </script>

    {{-- Handler-dedupe monkey-patch — see admin.blade.php for explanation. --}}
    <script data-navigate-once>
        (function () {
            if (!window.jQuery || jQuery.fn.__obPagePatched) return;
            jQuery.fn.__obPagePatched = true;
            const origOn = jQuery.fn.on;
            jQuery.fn.on = function (types) {
                if (this.length
                    && (this[0] === document || this[0] === document.body)
                    && typeof types === 'string'
                    && !/\.ob-(page|layout)\b/.test(types)
                ) {
                    arguments[0] = types.split(' ').map(t => t + '.ob-page').join(' ');
                }
                return origOn.apply(this, arguments);
            };
        })();
    </script>

    @stack('scripts')

    @livewireScripts
</body>
</html>
