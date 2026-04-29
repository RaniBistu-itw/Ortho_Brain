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
    {{-- OrthoBrain palette — navy + Inter. Loaded LAST so it wins over Vuexy. --}}
    <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/extensions/sweetalert2.min.css') }}" />

    {{-- Bootstrap Icons (already used across OrthoBrain views) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @livewireStyles

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

    {{-- Vuexy Vendor JS — data-navigate-once so wire:navigate's body morph
         doesn't re-execute these on every page change. Re-execution would
         overwrite window.jQuery (orphaning our handler-dedupe monkey-patch),
         re-bind Bootstrap dropdowns to the persisted navbar (causing the
         "open then immediately close" navbar-button bug), and re-bind Vuexy's
         .menu-toggle to the persisted header (causing the mobile drawer to
         toggle N times per click). --}}
    <script src="{{ asset('vuexy/vendors/js/vendors.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/vendors/js/ui/jquery.sticky.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/vendors/js/forms/select/select2.full.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/vendors/js/forms/validation/jquery.validate.min.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/vendors/js/extensions/sweetalert2.all.min.js') }}" data-navigate-once></script>

    {{-- Vuexy Theme JS — data-navigate-once for the same reason. --}}
    <script src="{{ asset('vuexy/js/core/app-menu.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/js/core/app.js') }}" data-navigate-once></script>
    <script src="{{ asset('vuexy/js/core/scripts.js') }}" data-navigate-once></script>
    <script src="{{ asset('js/theme-toggle.js') }}?v={{ @filemtime(public_path('js/theme-toggle.js')) ?: time() }}" data-navigate-once></script>

    <script data-navigate-once>
    // ════════════════════════════════════════════════════════════════════════
    // ONE-TIME LAYOUT INIT — guarded so wire:navigate body-morph re-execution
    // doesn't double-bind the layout-level $(document).on(...) handlers below.
    // Per-page work (feather, select2, validate, flash, sidebar active) lives
    // in obAdminPageInit() and runs on every livewire:navigated.
    // ════════════════════════════════════════════════════════════════════════
    if (!window.__obAdminLayoutInited) {
        window.__obAdminLayoutInited = true;

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
        // The autocall lives in obAdminPageInit() (per-page hook) so it
        // re-binds against the freshly rendered .ob-section-head elements
        // each time the sidebar re-renders on wire:navigate. localStorage
        // preserves user's collapsed-section state across renders.

        // ─── Blocked delete notice (has dependent records) ───
        $(document).on('click', '.js-delete-blocked', function (e) {
            e.preventDefault();
            const reason = $(this).data('reason') || 'This item has dependent records and cannot be deleted.';
            Swal.fire({
                title: 'Cannot delete',
                text: reason,
                icon: 'info',
                confirmButtonText: 'Got it',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });

        // ─── Image upload guard (size + MIME type, client-side) ───
        $(document).on('change', '.js-image-guard', function () {
            const input = this;
            const file = input.files && input.files[0];
            if (!file) return;

            const maxSize = parseInt($(input).data('max-size'), 10) || (2 * 1024 * 1024);
            const allowed = String($(input).data('allowed-types') || 'image/jpeg,image/png')
                .split(',').map(s => s.trim()).filter(Boolean);

            const block = (title, text) => {
                input.value = '';
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    confirmButtonText: 'Got it',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            };

            if (allowed.length && !allowed.includes(file.type)) {
                const labels = allowed.map(t => t.split('/')[1].toUpperCase()).join(' or ');
                return block('Unsupported file type',
                    'Please choose a ' + labels + ' image.');
            }

            if (file.size > maxSize) {
                const maxMb = (maxSize / 1024 / 1024).toFixed(1).replace(/\.0$/, '');
                const actualMb = (file.size / 1024 / 1024).toFixed(1);
                return block('Image is too large',
                    'Maximum size is ' + maxMb + ' MB. Your file is ' + actualMb + ' MB — please resize or compress it and try again.');
            }
        });

        // ─── Image hover preview (custom floating card) ─────────────
        // Any element with [data-preview-src] gets a soft, lightweight
        // preview on hover. Single reused DOM node, delegated listeners.
        //
        // If the element ALSO has [data-preview-gallery] (JSON array of URLs),
        // the card becomes a self-advancing carousel with dot indicators.
        (function () {
            const SHOW_DELAY     = 180;   // ms — avoids flashes on casual passes
            const GAP            = 12;    // px — breathing room from the trigger
            const SLIDE_INTERVAL = 2200;  // ms between carousel slides

            let card = null, cardImg = null, cardDots = null;
            let showTimer = null, slideTimer = null;
            let current = null;
            let gallery = null;   // string[] or null
            let slideIdx = 0;

            function ensureCard() {
                if (card) return;
                card = document.createElement('div');
                card.className = 'ob-image-preview-card';
                cardImg = new Image();
                cardImg.alt = '';
                card.appendChild(cardImg);
                cardDots = document.createElement('div');
                cardDots.className = 'ob-image-preview-dots';
                card.appendChild(cardDots);
                document.body.appendChild(card);
            }

            function place(target) {
                const r = target.getBoundingClientRect();
                const pw = card.offsetWidth, ph = card.offsetHeight;
                const vw = window.innerWidth, vh = window.innerHeight;
                let left = r.right + GAP;
                if (left + pw > vw - 8) left = r.left - pw - GAP;
                if (left < 8) left = Math.max(8, (vw - pw) / 2);
                let top = r.top + r.height / 2 - ph / 2;
                if (top < 8) top = 8;
                if (top + ph > vh - 8) top = vh - ph - 8;
                card.style.left = left + 'px';
                card.style.top  = top + 'px';
            }

            function renderDots() {
                if (!gallery || gallery.length < 2) {
                    cardDots.style.display = 'none';
                    cardDots.innerHTML = '';
                    return;
                }
                cardDots.style.display = '';
                let html = '';
                for (let i = 0; i < gallery.length; i++) {
                    html += '<span class="ob-image-preview-dot' + (i === slideIdx ? ' is-active' : '') + '"></span>';
                }
                cardDots.innerHTML = html;
            }

            function setImage(src, then) {
                const done = function () {
                    if (typeof then === 'function') then();
                };
                if (cardImg.src !== src) {
                    cardImg.onload  = done;
                    cardImg.onerror = done;
                    cardImg.src = src;
                    if (cardImg.complete && cardImg.naturalWidth) done();
                } else {
                    done();
                }
            }

            function startCarousel(target) {
                clearInterval(slideTimer);
                if (!gallery || gallery.length < 2) return;
                slideTimer = setInterval(function () {
                    if (current !== target) { clearInterval(slideTimer); return; }
                    slideIdx = (slideIdx + 1) % gallery.length;
                    cardImg.classList.add('is-fading');
                    setTimeout(function () {
                        setImage(gallery[slideIdx], function () {
                            renderDots();
                            place(target);
                            cardImg.classList.remove('is-fading');
                        });
                    }, 120);
                }, SLIDE_INTERVAL);
            }

            function show(target) {
                ensureCard();
                card.classList.toggle('is-lg', target.getAttribute('data-preview-size') === 'lg');
                const raw = target.getAttribute('data-preview-gallery');
                gallery = null;
                if (raw) {
                    try {
                        const arr = JSON.parse(raw);
                        if (Array.isArray(arr) && arr.length) gallery = arr;
                    } catch (e) { /* ignore malformed gallery */ }
                }
                const firstSrc = (gallery && gallery[0]) || target.getAttribute('data-preview-src');
                if (!firstSrc) return;
                slideIdx = 0;
                setImage(firstSrc, function () {
                    if (current !== target) return;
                    renderDots();
                    place(target);
                    card.classList.add('is-visible');
                    startCarousel(target);
                });
            }

            function hide() {
                if (card) card.classList.remove('is-visible');
                clearInterval(slideTimer);
                current = null;
            }

            document.addEventListener('mouseover', function (e) {
                const t = e.target.closest('[data-preview-src]');
                if (!t || t === current) return;
                current = t;
                clearTimeout(showTimer);
                showTimer = setTimeout(function () {
                    if (current === t) show(t);
                }, SHOW_DELAY);
            });
            document.addEventListener('mouseout', function (e) {
                const t = e.target.closest('[data-preview-src]');
                if (!t) return;
                const related = e.relatedTarget && e.relatedTarget.closest
                    ? e.relatedTarget.closest('[data-preview-src]') : null;
                if (related === t) return;
                clearTimeout(showTimer);
                hide();
            });
            window.addEventListener('scroll', hide, true);
            window.addEventListener('resize', hide);
        })();

        // ─── Row-click navigation (admin index tables) ───
        // Any <tr data-row-href="..."> becomes clickable. Clicks on interactive
        // descendants (links, buttons, forms, form controls, action groups, or
        // anything tagged [data-no-row-click]) are ignored so the existing
        // view/edit/delete buttons keep working as before. Cmd/Ctrl/middle-click
        // opens the show page in a new tab.
        $(document).on('click', 'tr[data-row-href]', function (e) {
            if (e.target.closest('a, button, form, input, select, textarea, label, [data-no-row-click]')) return;
            if (window.getSelection && String(window.getSelection())) return;
            const href = this.getAttribute('data-row-href');
            if (!href) return;
            if (e.ctrlKey || e.metaKey || e.button === 1) {
                window.open(href, '_blank', 'noopener');
            } else {
                window.location.href = href;
            }
        });
        $(document).on('auxclick', 'tr[data-row-href]', function (e) {
            if (e.button !== 1) return;
            if (e.target.closest('a, button, form, input, select, textarea, label, [data-no-row-click]')) return;
            const href = this.getAttribute('data-row-href');
            if (!href) return;
            e.preventDefault();
            window.open(href, '_blank', 'noopener');
        });

        // ─── Delete confirmation (Vuexy SweetAlert2) ───
        $(document).on('submit', 'form.js-delete-form', function (e) {
            const $form = $(this);
            if ($form.data('confirmed')) return;
            e.preventDefault();

            const message = $form.data('confirm') || 'Are you sure?';

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-outline-secondary ms-1'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.value) {
                    $form.data('confirmed', true).trigger('submit');
                }
            });
        });

    } // ── /one-time layout init guard ────────────────────────────

    // ════════════════════════════════════════════════════════════════════════
    // PER-PAGE INIT — runs on first window.load AND on every livewire:navigated
    // (body is morphed on navigate, so freshly-rendered forms / icons / select2
    // controls need re-initialising).
    // ════════════════════════════════════════════════════════════════════════
    window.obAdminPageInit = function () {
        // Vuexy's <html class="loading"> overlay would reappear after a navigate
        // because the destination HTML carries it; strip it defensively.
        document.documentElement.classList.remove('loading');

        // Re-establish viewport-correct body classes. wire:navigate's body
        // morph just reset them to the server-rendered desktop default.
        window.obSyncViewportClasses();

        // Feather icons
        if (window.feather) feather.replace({ width: 14, height: 14 });

        // Select2 auto-init (idempotent — obSearchable skips already-initialised)
        if (window.obSearchable) window.obSearchable('select.js-searchable');

        // jQuery Validate — re-bind on freshly morphed forms
        $('form.ob-form-validate').each(function () {
            const $f = $(this);
            if ($f.data('validator')) return; // already wired
            $f.validate({
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
                highlight:   function (el) { $(el).addClass('is-invalid').removeClass('is-valid'); },
                unhighlight: function (el) { $(el).removeClass('is-invalid').addClass('is-valid'); },
            });
        });

        // Re-bind collapsible sidebar sections — the sidebar re-renders on
        // every navigate (no longer x-persist'd), so we re-attach click
        // handlers to the freshly rendered .ob-section-head elements.
        if (window.obSidebarToggle) window.obSidebarToggle();

        // Flash auto-fade
        setTimeout(function () { $('.ob-flash').fadeOut(400); }, 5000);
    };

    // ────────────────────────────────────────────────────────────────────
    // Viewport ↔ body class sync.
    //
    // Vuexy's app-menu.js calls toOverlayMenu() exactly once at init and never
    // again on resize (verified in public/vuexy/js/core/app-menu.js — the only
    // resize listener at line 980 just updates a --vh CSS var). Mobile drawer
    // styling depends on `body.vertical-overlay-menu`, NOT `body.vertical-menu-
    // modern`. So a desktop→mobile resize leaves the body in the wrong mode,
    // and the drawer is broken until refresh.
    //
    // Compounding: wire:navigate morphs <body>, which resets body.class back to
    // the server-rendered default (vertical-menu-modern menu-expanded). Even if
    // Vuexy's init had correctly set vertical-overlay-menu on a mobile page
    // load, the FIRST sidebar click undoes it. Hence we re-sync on every
    // livewire:navigated as well as every breakpoint crossing.
    //
    // Class semantics (verified in app-menu.js):
    //   Desktop:  body.vertical-menu-modern + body.menu-expanded|menu-collapsed
    //   Mobile :  body.vertical-overlay-menu + body.menu-hide  (drawer closed)
    //                                       + body.menu-open  (drawer open)
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

    // Mirrors Vuexy's drawer-close sequence (app-menu.js:363) — the proper way
    // to close the mobile drawer is to swap menu-open → menu-hide on body, drop
    // overlay.show, and clear .menu-toggle.is-active. Just removing menu-open
    // works visually because of CSS, but leaves the hamburger button in its
    // pressed-looking state and Vuexy's internal state machine inconsistent.
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

    // ════════════════════════════════════════════════════════════════════════
    // NAVIGATE HOOKS
    //   livewire:navigating — clear mobile-drawer state and page-namespaced
    //     delegated handlers BEFORE the next page's body is morphed in.
    //   livewire:navigated  — re-run per-page init AFTER morph completes.
    // Listeners themselves are registered once (guarded) so they don't
    // accumulate on body re-execution.
    // ════════════════════════════════════════════════════════════════════════
    if (!window.__obAdminNavigateBound) {
        window.__obAdminNavigateBound = true;

        document.addEventListener('livewire:navigating', function () {
            // Close mobile drawer using Vuexy's exact close sequence so the
            // hamburger and internal state stay consistent. No-op on desktop.
            window.obCloseMobileDrawer();

            // Drop page-namespaced delegated handlers (see monkey-patch below).
            if (window.jQuery) jQuery(document).off('.ob-page');

            // Pre-emptively strip Vuexy's loading overlay.
            document.documentElement.classList.remove('loading');
        });

        document.addEventListener('livewire:navigated', function () {
            window.obAdminPageInit();
        });

        $(window).on('load', function () { window.obAdminPageInit(); });

        // Viewport-crossing sync — Vuexy's app-menu.js doesn't react to resize
        // (only sets a --vh CSS var). We swap body.vertical-menu-modern ↔
        // body.vertical-overlay-menu ourselves on every breakpoint crossing.
        // matchMedia.change fires exactly once per crossing, so rapid drag
        // resizing across 1200px ends in a coherent state.
        window.matchMedia('(max-width: 1199.98px)').addEventListener('change', function () {
            window.obSyncViewportClasses();
        });
    }
    </script>

    {{-- ────────────────────────────────────────────────────────────────────
         HANDLER-DEDUPE MONKEY-PATCH
         Auto-namespace any $(document).on('click', '.foo', ...) calls that
         appear AFTER this script (i.e. inside @stack('scripts') from pages)
         with .ob-page, so livewire:navigating can clear them in one shot.
         Layout-level $(document).on() above is registered before this patch,
         so it stays un-namespaced and survives navigations.
    ──────────────────────────────────────────────────────────────────── --}}
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
