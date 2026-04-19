<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0, minimal-ui" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('admin.brand.name') }}</title>

    <link rel="apple-touch-icon" href="{{ asset('vuexy/images/ico/favicon-32x32.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('vuexy/images/ico/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    {{-- Vuexy vendor CSS --}}
    <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/forms/select/select2.min.css') }}" />

    {{-- Vuexy theme CSS --}}
    <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/themes/dark-layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/themes/bordered-layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/themes/semi-dark-layout.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/base/core/menu/menu-types/vertical-menu.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/orthobrain-overrides.css') }}" />

    {{-- Bootstrap Icons (already used across OrthoBrain views) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @stack('styles')
</head>

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static  menu-expanded"
      data-open="click" data-menu="vertical-menu-modern" data-col="2-columns">

    {{-- BEGIN: Header / Navbar --}}
    @include('partials.header')
    {{-- END: Header --}}

    {{-- BEGIN: Main Menu / Sidebar --}}
    @include('partials.sidebar')
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

    {{-- BEGIN: Footer --}}
    <footer class="footer footer-static footer-light">
        <p class="clearfix mb-0">
            <span class="float-md-start d-block d-md-inline-block mt-25">
                COPYRIGHT &copy; {{ date('Y') }}
                <a class="ms-25" href="#" target="_blank">{{ config('admin.brand.name') }}</a>,
                <span class="d-none d-sm-inline-block">All rights Reserved</span>
            </span>
        </p>
    </footer>
    <button class="btn btn-primary btn-icon scroll-top" type="button"><i data-feather="arrow-up"></i></button>
    {{-- END: Footer --}}

    {{-- Vuexy Vendor JS --}}
    <script src="{{ asset('vuexy/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('vuexy/vendors/js/ui/jquery.sticky.js') }}"></script>
    <script src="{{ asset('vuexy/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ asset('vuexy/vendors/js/forms/validation/jquery.validate.min.js') }}"></script>

    {{-- Vuexy Theme JS --}}
    <script src="{{ asset('vuexy/js/core/app-menu.js') }}"></script>
    <script src="{{ asset('vuexy/js/core/app.js') }}"></script>
    <script src="{{ asset('vuexy/js/core/scripts.js') }}"></script>

    <script>
        $(window).on('load', function () {
            if (window.feather) {
                feather.replace({ width: 14, height: 14 });
            }
        });

        // ─── CSRF for AJAX ─────────────────────────────
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        // ─── Searchable dropdown helper (Select2) ──────
        window.obSearchable = function (selector, opts) {
            const $el = $(selector);
            if (!$el.length || typeof $.fn.select2 !== 'function') return $el;
            $el.each(function () {
                const $s = $(this);
                if ($s.data('select2')) return;
                const placeholder = $s.data('placeholder')
                    || $s.find('option[value=""]').first().text()
                    || 'Select...';
                $s.select2(Object.assign({
                    placeholder: placeholder,
                    allowClear: !$s.prop('required') && !!$s.find('option[value=""]').length,
                    width: '100%',
                    dropdownParent: $s.closest('.modal').length ? $s.closest('.modal') : document.body,
                }, opts || {}));
            });
            return $el;
        };

        window.obSearchableRefresh = function (selector) {
            const $el = $(selector);
            $el.each(function () {
                const $s = $(this);
                if (!$s.data('select2')) return;
                $s.select2('destroy');
                window.obSearchable($s);
            });
        };

        // ─── Cascading dropdown helper ─────────────────
        window.obCascade = function ({ parent, child, url, paramName, placeholder, preselectId }) {
            const $p = $(parent), $c = $(child);
            if (!$p.length || !$c.length) return;

            function load(triggerChange) {
                const id = $p.val();
                $c.prop('disabled', true).html('<option value="">' + placeholder + '</option>');
                if (!id) {
                    if (triggerChange) $c.trigger('change');
                    return;
                }
                $.getJSON(url, { [paramName]: id })
                    .done(function (items) {
                        items.forEach(function (it) {
                            $c.append(new Option(it.name, it.id));
                        });
                        let preselected = false;
                        if (preselectId) {
                            $c.val(String(preselectId));
                            preselectId = null;
                            preselected = true;
                        }
                        $c.prop('disabled', false);
                        window.obSearchableRefresh($c);
                        if (triggerChange || preselected) $c.trigger('change');
                    })
                    .fail(function (xhr) {
                        console.error('obCascade failed:', url, xhr.status, xhr.responseText);
                        $c.html('<option value="">Failed to load</option>');
                    });
            }

            $p.on('change', function () { load(false); });
            if ($p.val()) load(false);
        };

        // ─── Preloaded cascade (with child→parent backfill) ────────────────
        window.obPreloadedCascade = function ({ parent, child, parentAttr, backfill = true }) {
            const $p = $(parent), $c = $(child);
            if (!$p.length || !$c.length) return;

            const allOptions = $c.find('option').toArray().map(o => o.cloneNode(true));

            function filterChild() {
                const parentId = $p.val();
                const currentVal = $c.val();
                $c.empty();
                let kept = false;
                allOptions.forEach(opt => {
                    const optParent = opt.getAttribute(parentAttr);
                    if (opt.value === '' || !parentId || optParent === String(parentId)) {
                        $c.append(opt.cloneNode(true));
                        if (opt.value === currentVal) kept = true;
                    }
                });
                $c.val(kept ? currentVal : '');
                window.obSearchableRefresh($c);
                if (currentVal && !kept) $c.trigger('change');
            }

            $p.on('change', filterChild);

            if (backfill) {
                $c.on('change', function () {
                    const sel = $c.find('option:selected')[0];
                    if (!sel) return;
                    const parentId = sel.getAttribute(parentAttr);
                    if (parentId && !$p.val()) {
                        $p.val(parentId);
                        $p.trigger('change');
                    }
                });
            }

            if ($p.val()) filterChild();
        };

        // ─── Auto-submit filter forms ──────────────────
        window.obAutoFilter = function (formSelector) {
            const $form = $(formSelector);
            if (!$form.length) return;

            let textTimer, cascadeTimer;

            $form.on('change', 'select:not([data-ob-cascade-parent])', function () {
                clearTimeout(cascadeTimer);
                $form.trigger('submit');
            });

            $form.on('change', 'select[data-ob-cascade-parent]', function () {
                clearTimeout(cascadeTimer);
                cascadeTimer = setTimeout(function () { $form.trigger('submit'); }, 1500);
            });

            $form.on('input', 'input[type="text"], input[type="search"], input[type="number"]', function () {
                clearTimeout(textTimer);
                textTimer = setTimeout(function () { $form.trigger('submit'); }, 450);
            });
        };

        // Collapsible sidebar sections (Manage Types, Locations). Remembers state
        // per section in localStorage. Sections containing the active route stay open.
        window.obSidebarToggle = function () {
            const KEY = 'ob_sidebar_sections';
            let state = {};
            try { state = JSON.parse(localStorage.getItem(KEY) || '{}'); } catch (e) {}

            document.querySelectorAll('.main-menu .navigation-header.ob-section-head').forEach(function (header) {
                const key = header.dataset.obSection;
                if (!key) return;

                // Collect sibling nav-items until the next section header.
                const items = [];
                let sib = header.nextElementSibling;
                while (sib && !sib.classList.contains('navigation-header')) {
                    items.push(sib);
                    sib = sib.nextElementSibling;
                }

                const hasActive = items.some(function (it) { return it.classList.contains('active'); });
                const collapsed = state[key] === true && !hasActive;

                function apply(isCollapsed) {
                    header.classList.toggle('ob-section-collapsed', isCollapsed);
                    items.forEach(function (it) { it.style.display = isCollapsed ? 'none' : ''; });
                }

                apply(collapsed);

                header.addEventListener('click', function () {
                    const isCollapsed = !header.classList.contains('ob-section-collapsed');
                    apply(isCollapsed);
                    state[key] = isCollapsed;
                    try { localStorage.setItem(KEY, JSON.stringify(state)); } catch (e) {}
                });
            });
        };
        $(function () { window.obSidebarToggle(); });

        // ─── Delete confirmation ───────────────────────
        $(document).on('submit', 'form.js-delete-form', function (e) {
            if (!confirm($(this).data('confirm') || 'Are you sure?')) e.preventDefault();
        });

        // ─── Auto-dismiss flash alerts ─────────────────
        setTimeout(function () { $('.ob-flash').fadeOut(400); }, 5000);

        // ─── jQuery Validate defaults (Vuexy / Bootstrap 5 feedback) ───
        $(function () {
            $('form.ob-form-validate').each(function () {
                $(this).validate({
                    ignore: ':hidden:not(.js-searchable)',
                    errorClass: 'is-invalid',
                    validClass: 'is-valid',
                    errorElement: 'div',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback d-block');
                        const $wrap = element.closest('.input-group').length
                            ? element.closest('.input-group')
                            : element;
                        error.insertAfter($wrap);
                    },
                    highlight: function (el) { $(el).addClass('is-invalid').removeClass('is-valid'); },
                    unhighlight: function (el) { $(el).removeClass('is-invalid').addClass('is-valid'); },
                });
            });
        });

        // ─── Auto-init searchable dropdowns ────────────
        $(function () { window.obSearchable('select.js-searchable'); });
    </script>

    @stack('scripts')
</body>
</html>
