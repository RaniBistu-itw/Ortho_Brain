// Site-wide dark / light theme toggle.
// Vuexy ships dark-layout.css already; we just flip the `dark-layout`
// class on <body> and persist the choice in localStorage.
//
// Usage in markup (already done in doctor-header / partials/header):
//   <a id="theme-toggle" class="nav-link" href="#"><i data-feather="moon"></i></a>
//
// Public API:
//   window.ThemeToggle.set('dark' | 'light')
//   window.ThemeToggle.toggle()
//   window.ThemeToggle.current()  -> 'dark' | 'light'

(function () {
  'use strict';

  var KEY = 'ob.theme';
  var CLASS = 'dark-layout';

  function readStored() {
    try { return localStorage.getItem(KEY); } catch (e) { return null; }
  }
  function writeStored(value) {
    try { localStorage.setItem(KEY, value); } catch (e) { /* quota / disabled */ }
  }

  function apply(theme) {
    var body = document.body;
    if (!body) return;
    if (theme === 'dark') {
      body.classList.add(CLASS);
      body.setAttribute('data-bs-theme', 'dark');
    } else {
      body.classList.remove(CLASS);
      body.setAttribute('data-bs-theme', 'light');
    }
    refreshIcon(theme);
  }

  function refreshIcon(theme) {
    document.querySelectorAll('[data-theme-toggle-icon]').forEach(function (el) {
      // Replace icon: dark current → show 'sun' (click to switch to light)
      // light current → show 'moon' (click to switch to dark)
      var nextIcon = theme === 'dark' ? 'sun' : 'moon';
      el.setAttribute('data-feather', nextIcon);
    });
    if (window.feather) {
      try { window.feather.replace({ width: 18, height: 18 }); } catch (e) { /* feather may have been replaced already */ }
    }
  }

  function current() {
    return document.body && document.body.classList.contains(CLASS) ? 'dark' : 'light';
  }

  function set(theme) {
    apply(theme);
    writeStored(theme);
  }

  function toggle() {
    set(current() === 'dark' ? 'light' : 'dark');
  }

  // Apply stored preference as early as possible to avoid a flash of wrong theme.
  // (We don't have control over <head>; this runs after body parses but before
  //  user can interact, which is acceptable for an in-app preference.)
  document.addEventListener('DOMContentLoaded', function () {
    var stored = readStored();
    if (stored === 'dark' || stored === 'light') {
      apply(stored);
    } else {
      // First visit: respect the OS preference.
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      apply(prefersDark ? 'dark' : 'light');
    }

    document.querySelectorAll('[data-theme-toggle]').forEach(function (el) {
      el.addEventListener('click', function (evt) {
        evt.preventDefault();
        toggle();
      });
    });
  });

  window.ThemeToggle = { set: set, toggle: toggle, current: current };
})();
