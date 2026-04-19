<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('admin.brand.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    {{-- Fallback Tailwind CDN + Vuexy color extension (matches login page) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        vuexy: {
                            primary: '#5bc0de',
                            hover:   '#46b8da',
                            green:   '#8cc63f',
                            text:    '#6e6b7b',
                            heading: '#5e5873',
                            border:  '#d8d6de',
                            bg:      '#f8f8f8',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Select2 (searchable dropdowns) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        /* Match Select2 to the Vuexy/Tailwind look used elsewhere */
        .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d8d6de;
            border-radius: 0.375rem;
            min-height: 38px;
            padding: 3px 4px;
            font-size: 0.875rem;
            color: #6e6b7b;
            background-color: #fff;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            color: #6e6b7b;
            padding-left: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #5bc0de;
            outline: none;
        }
        .select2-dropdown {
            border: 1px solid #d8d6de;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #5bc0de;
            color: #fff;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d8d6de;
            border-radius: 0.25rem;
            padding: 6px 8px;
            font-size: 0.875rem;
            outline: none;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #5bc0de;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-[#f8f8f8] text-[#6e6b7b] font-sans">

    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <div class="flex-1 flex flex-col ml-[260px]">
            @include('partials.header')

            <main class="flex-1 p-6">
                @include('partials.flash')
                @yield('content')
            </main>

            <footer class="px-6 py-4 text-center text-xs text-[#b9b9c3] border-t border-[#ebe9f1] bg-white">
                &copy; {{ date('Y') }} {{ config('admin.brand.name') }}®. All rights reserved.
            </footer>
        </div>
    </div>

    {{-- jQuery + jQuery Validate + Select2 via CDN --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // ─── CSRF for AJAX ─────────────────────────────
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        // ─── Searchable dropdown helper (Select2) ──────
        // Upgrades a native <select> into a Select2 searchable dropdown.
        // Call directly with a selector, or add class "js-searchable" and it
        // will be picked up automatically on DOM ready.
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

        // Refresh Select2 after the native options have been rebuilt.
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
        // Wires a parent <select> to refresh a child <select> via AJAX.
        //   parent / child   – jQuery selectors
        //   url              – JSON endpoint returning [{id,name}, …]
        //   paramName        – query-string key for the parent id
        //   placeholder      – shown when child is empty
        //   preselectId      – optional id to re-select after load (edit pages)
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
        // Use when the child dropdown is already rendered with all options in
        // the DOM, each carrying a `data-parent-id` attribute pointing at its
        // parent. Behaves two-way:
        //   • parent change  → filter child options to that parent's children
        //                       (clears child value if it no longer matches)
        //   • child change   → if parent is empty, set parent from the child's
        //                       data-parent-id (the "child knows its parent").
        // Params:
        //   parent, child   – jQuery selectors
        //   parentAttr      – data attribute on each child <option> (e.g. 'data-country-id')
        //   backfill        – default true; set false to skip child→parent wiring
        window.obPreloadedCascade = function ({ parent, child, parentAttr, backfill = true }) {
            const $p = $(parent), $c = $(child);
            if (!$p.length || !$c.length) return;

            // Snapshot all options once so we can re-filter without losing any.
            const allOptions = $c.find('option').toArray().map(o => o.cloneNode(true));

            function filterChild() {
                const parentId = $p.val();
                const currentVal = $c.val();
                $c.empty();
                let kept = false;
                allOptions.forEach(opt => {
                    const optParent = opt.getAttribute(parentAttr);
                    // Keep placeholder (empty value) always; keep options with matching parent,
                    // or all options when no parent is selected.
                    if (opt.value === '' || !parentId || optParent === String(parentId)) {
                        $c.append(opt.cloneNode(true));
                        if (opt.value === currentVal) kept = true;
                    }
                });
                $c.val(kept ? currentVal : '');
                window.obSearchableRefresh($c);
                // If the child lost its value because it no longer belongs to the
                // new parent, fire a change so downstream cascades (e.g. city) reset.
                if (currentVal && !kept) $c.trigger('change');
            }

            $p.on('change', filterChild);

            if (backfill) {
                $c.on('change', function () {
                    const sel = $c.find('option:selected')[0];
                    if (!sel) return;
                    const parentId = sel.getAttribute(parentAttr);
                    if (parentId && !$p.val()) {
                        // Temporarily detach the parent's change handler so we
                        // don't re-filter and wipe out the child selection.
                        $p.val(parentId);
                        // Still fire change so other listeners (auto-submit,
                        // further cascades) react naturally.
                        $p.trigger('change');
                    }
                });
            }

            if ($p.val()) filterChild();
        };

        // ─── Auto-submit filter forms (no Filter button needed) ─────────────
        // Regular selects submit instantly on change.
        // Cascade parents submit after a short delay so the user has time to
        // pick the child; picking the child cancels the pending parent submit
        // and fires its own.
        // Text/search/number inputs submit after a debounced pause.
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

        // ─── Sidebar collapsible section toggle (optional) ──────────────────
        // Activates when sidebar items carry:
        //   <button data-ob-section="locations">...
        //   <div    data-ob-section-body="locations">...
        // Remembers open/collapsed state per section in localStorage.
        window.obSidebarToggle = function () {
            const KEY = 'ob_sidebar_sections';
            const state = JSON.parse(localStorage.getItem(KEY) || '{}');

            document.querySelectorAll('[data-ob-section]').forEach(function (header) {
                const key = header.dataset.obSection;
                const body = document.querySelector('[data-ob-section-body="' + key + '"]');
                if (!body) return;

                const hasActive = body.querySelector('a.is-active');
                const collapsed = state[key] === true && !hasActive;
                if (collapsed) body.classList.add('hidden');

                const chev = header.querySelector('.ob-chevron');
                if (chev) chev.classList.toggle('rotate-[-90deg]', collapsed);

                header.addEventListener('click', function () {
                    const isHidden = body.classList.toggle('hidden');
                    if (chev) chev.classList.toggle('rotate-[-90deg]', isHidden);
                    state[key] = isHidden;
                    localStorage.setItem(KEY, JSON.stringify(state));
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

        // ─── Init jQuery Validate on forms ─────────────
        $(function () {
            $('form.ob-form-validate').each(function () {
                $(this).validate({
                    // Don't skip Select2-hidden native selects.
                    ignore: ':hidden:not(.js-searchable)',
                    errorClass: 'border-red-500',
                    errorElement: 'p',
                    errorPlacement: function (error, element) {
                        error.addClass('text-red-500 text-xs mt-1');
                        error.insertAfter(element.closest('div').length ? element.closest('div') : element);
                    },
                });
            });
        });

        // ─── Auto-init searchable dropdowns ────────────
        $(function () { window.obSearchable('select.js-searchable'); });
    </script>
    @stack('scripts')
</body>
</html>