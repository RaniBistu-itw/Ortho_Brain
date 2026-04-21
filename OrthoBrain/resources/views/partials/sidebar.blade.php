@php
    $active = fn ($pattern) => request()->routeIs($pattern) ? 'active' : '';
    $open   = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p)) ? 'open' : '';
@endphp

<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item me-auto">
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                    <span class="brand-logo">
                        <svg width="34" height="30" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff"/>
                            <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7"/>
                            <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none"/>
                        </svg>
                    </span>
                    <h2 class="brand-text mb-0">
                        <span class="brand-ortho">ortho</span><span class="brand-brain">brain</span><span class="brand-tm">&trade;</span>
                        <span class="brand-tagline">Orthodontics for Your Dental Practice</span>
                    </h2>
                </a>
            </li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>

    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

            <li class="nav-item {{ $active('admin.dashboard') }}">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center">
                    <i data-feather="home"></i>
                    <span class="menu-title text-truncate">Dashboard</span>
                </a>
            </li>

            <li class="nav-item {{ $active('admin.cases.*') }}">
                <a href="{{ route('admin.cases.index') }}" class="d-flex align-items-center">
                    <i data-feather="folder"></i>
                    <span class="menu-title text-truncate">Cases</span>
                </a>
            </li>

            <li class="navigation-header ob-section-head" data-ob-section="manage-types">
                <span>Manage Types</span>
                <svg class="ob-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </li>

            <li class="nav-item {{ $active('admin.product-categories.*') }}">
                <a href="{{ route('admin.product-categories.index') }}" class="d-flex align-items-center">
                    <i data-feather="tag"></i>
                    <span class="menu-title text-truncate">Categories</span>
                </a>
            </li>
            <li class="nav-item {{ $active('admin.product-subcategories.*') }}">
                <a href="{{ route('admin.product-subcategories.index') }}" class="d-flex align-items-center">
                    <i data-feather="tag"></i>
                    <span class="menu-title text-truncate">Sub Categories</span>
                </a>
            </li>
            <li class="nav-item {{ $active('admin.products.*') }}">
                <a href="{{ route('admin.products.index') }}" class="d-flex align-items-center">
                    <i data-feather="box"></i>
                    <span class="menu-title text-truncate">Products</span>
                </a>
            </li>
            <li class="nav-item {{ $active('admin.scanners.*') }}">
                <a href="{{ route('admin.scanners.index') }}" class="d-flex align-items-center">
                    <i data-feather="cpu"></i>
                    <span class="menu-title text-truncate">Scanners</span>
                </a>
            </li>

            <li class="navigation-header ob-section-head" data-ob-section="locations">
                <span>Locations</span>
                <svg class="ob-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </li>

            <li class="nav-item {{ $active('admin.countries.*') }}">
                <a href="{{ route('admin.countries.index') }}" class="d-flex align-items-center">
                    <i data-feather="globe"></i>
                    <span class="menu-title text-truncate">Countries</span>
                </a>
            </li>
            <li class="nav-item {{ $active('admin.states.*') }}">
                <a href="{{ route('admin.states.index') }}" class="d-flex align-items-center">
                    <i data-feather="map"></i>
                    <span class="menu-title text-truncate">States</span>
                </a>
            </li>
            <li class="nav-item {{ $active('admin.cities.*') }}">
                <a href="{{ route('admin.cities.index') }}" class="d-flex align-items-center">
                    <i data-feather="map-pin"></i>
                    <span class="menu-title text-truncate">Cities</span>
                </a>
            </li>
            <li class="nav-item {{ $active('admin.zipcodes.*') }}">
                <a href="{{ route('admin.zipcodes.index') }}" class="d-flex align-items-center">
                    <i data-feather="mail"></i>
                    <span class="menu-title text-truncate">Zip Codes</span>
                </a>
            </li>
        </ul>
    </div>
</div>
