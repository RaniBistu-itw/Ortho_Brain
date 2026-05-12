// Reusable Web Speech dictation for any textarea on the page.
// Auto-attaches a floating mic button to every textarea carrying data-voice-input
// (and, by convention, every textarea with maxlength="5000").
//
// Public API:
//   window.VoiceInput.attach(textareaEl)   // opt-in attachment
//   window.VoiceInput.refresh()            // re-scan the DOM (e.g., after partials render)
//
// Browser support: Chrome/Edge. Firefox ships SpeechRecognition behind a flag.
// In unsupported browsers we still render the mic; clicking shows a toast.

(function () {
  'use strict';

  var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  // Voice input requires HTTPS — Chrome exposes the API on HTTP
  // but fires 'not-allowed' silently when .start() is called.
  // ngrok and production are HTTPS; local dev http:// is not.
  var isSecure = location.protocol === 'https:'
                 || location.hostname === 'localhost'
                 || location.hostname === '127.0.0.1';
  var supported = !!SR && isSecure;

  function showUnsupportedToast() {
    var existing = document.getElementById('voice-input-toast');
    if (existing) { existing.remove(); }

    var toast = document.createElement('div');
    toast.id = 'voice-input-toast';
    toast.className = 'voice-input-toast';
    toast.innerHTML = 'Voice dictation requires Chrome or Edge.';
    document.body.appendChild(toast);
    setTimeout(function () { toast.classList.add('voice-input-toast--visible'); }, 10);
    setTimeout(function () {
      toast.classList.remove('voice-input-toast--visible');
      setTimeout(function () { toast.remove(); }, 300);
    }, 3000);
  }

  function attach(textarea) {
    // Do not attach mic button if voice input is unsupported.
    // Buttons are built dynamically here — returning early
    // means no broken/disabled UI is ever inserted into the DOM.
    if (!supported) return;
    if (!textarea) return;

    // Self-heal: after a wire:navigate morph, Idiomorph keeps
    // the textarea (stable id) but drops the client-injected
    // .voice-input-wrapper because it's absent from server HTML.
    // The _voiceAttached flag survives on the DOM node, so
    // without this reset attach() bails and mic never comes back.
    if (textarea._voiceAttached) {
      var existingWrapper = textarea.closest('.voice-input-wrapper');
      if (existingWrapper
          && existingWrapper.querySelector('.voice-input-mic')) {
        return;
      }
      textarea._voiceAttached = false;
    }

    textarea._voiceAttached = true;

    var parent = textarea.parentNode;
    if (!parent) return;

    var wrapper = document.createElement('span');
    wrapper.className = 'voice-input-wrapper';
    parent.insertBefore(wrapper, textarea);
    wrapper.appendChild(textarea);

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'voice-input-mic';
    btn.setAttribute('aria-label', supported ? 'Dictate with voice' : 'Voice dictation unavailable');
    btn.title = supported ? 'Dictate with voice' : 'Voice dictation requires Chrome or Edge';
    btn.innerHTML = '<i data-feather="mic"></i>';
    if (!supported) btn.classList.add('voice-input-mic--disabled');
    wrapper.appendChild(btn);

    if (!supported) {
      btn.addEventListener('click', function (evt) {
        evt.preventDefault();
        showUnsupportedToast();
      });
      return;
    }

    var recognition = null;
    var baseline = '';
    var recording = false;
    var MAXLEN = parseInt(textarea.getAttribute('maxlength'), 10) || 5000;

    function ensureRecognition() {
      if (recognition) return recognition;
      recognition = new SR();
      recognition.continuous = false;
      recognition.interimResults = true;
      recognition.lang = 'en-US';

      recognition.onresult = function (evt) {
        var finalStr = '';
        var interimStr = '';
        for (var i = 0; i < evt.results.length; i++) {
          var r = evt.results[i];
          if (r.isFinal) finalStr += r[0].transcript;
          else interimStr += r[0].transcript;
        }

        var parts = [];
        if (baseline && baseline.trim()) parts.push(baseline.trimEnd());
        if (finalStr && finalStr.trim()) parts.push(finalStr.trim());
        if (interimStr && interimStr.trim()) parts.push(interimStr.trim());
        var combined = parts.join(' ');
        if (combined.length > MAXLEN) combined = combined.substring(0, MAXLEN);

        textarea.value = combined;
        textarea.dispatchEvent(new InputEvent('input', {
          bubbles: true,
          composed: true
        }));
      };

      recognition.onend = function () {
        finishRecording();
      };
      recognition.onerror = function (evt) {
        console.warn('[VoiceInput] recognition error:', evt.error, 'origin=', location.origin);

        // Permissions API tells us *why* not-allowed fired: prompt (never asked /
        // dismissed), denied (explicit block), or granted (real Chrome bug, rare).
        // Per-origin: localhost:8001 and 127.0.0.1:8001 have separate state.
        var permPromise = (navigator.permissions && navigator.permissions.query)
          ? navigator.permissions.query({ name: 'microphone' }).catch(function () { return null; })
          : Promise.resolve(null);

        permPromise.then(function (perm) {
          var state = perm ? perm.state : 'unknown';
          console.info('[VoiceInput] mic permission state for', location.origin, '=', state);

          var msg = null;
          if (evt.error === 'not-allowed') {
            if (state === 'denied') {
              msg = 'Microphone is BLOCKED for ' + location.host + '. Open chrome://settings/content/microphone and remove it from the Block list, then reload.';
            } else if (state === 'prompt') {
              msg = 'Microphone permission needed — click the lock icon for ' + location.host + ' and set Microphone to Allow, then reload.';
            } else if (state === 'granted') {
              msg = 'Mic permission granted but Chrome refused dictation. Try restarting Chrome, or open chrome://settings/content/microphone.';
            } else {
              msg = 'Microphone blocked — check site permissions for ' + location.host + '.';
            }
          } else if (evt.error === 'service-not-allowed') {
            msg = 'Voice service blocked — try opening the page as http://localhost:' + (location.port || '80') + ' instead of 127.0.0.1.';
          } else if (evt.error === 'audio-capture') {
            msg = 'No microphone detected.';
          } else if (evt.error === 'network') {
            msg = 'Voice recognition needs network access.';
          }
          if (msg && window.MediaTileHelpers && window.MediaTileHelpers.showToast) {
            window.MediaTileHelpers.showToast(msg, 5000);
          }
        });

        finishRecording();
      };
      return recognition;
    }

    function startRecording() {
      if (recording) return;
      recording = true;
      baseline = textarea.value || '';
      btn.classList.add('voice-input-mic--active');
      btn.title = 'Stop dictation';

      // SR.start() handles its own permission prompt. Don't preflight with
      // getUserMedia — the two permission gates can disagree in Chrome,
      // producing false "denied" toasts even after the user clicks Allow.
      try {
        ensureRecognition().start();
      } catch (e) {
        console.warn('[VoiceInput] start failed:', e);
        finishRecording();
      }
    }

    function finishRecording() {
      if (!recording) return;
      recording = false;
      btn.classList.remove('voice-input-mic--active');
      btn.title = 'Dictate with voice';
      try { if (recognition) recognition.stop(); } catch (e) { /* already stopped */ }
    }

    btn.addEventListener('click', function (evt) {
      evt.preventDefault();
      if (recording) finishRecording();
      else startRecording();
    });
  }

  function refresh() {
    var selector = 'textarea[data-voice-input], textarea[maxlength="5000"]';
    var list = document.querySelectorAll(selector);
    var before = 0, after = 0;
    list.forEach(function (el) {
      if (el._voiceAttached) before++;
      attach(el);
      if (el._voiceAttached) after++;
    });
    var newly = after - before;
    console.info('[VoiceInput] refresh — found=' + list.length + ' newly_attached=' + newly + ' supported=' + supported);
    if (newly > 0 && window.feather) {
      window.feather.replace({ width: 14, height: 14 });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    refresh();

    var refreshPending = false;
    const observer = new MutationObserver(function () {
      if (refreshPending) return;
      refreshPending = true;
      requestAnimationFrame(function () {
        refreshPending = false;
        refresh();
      });
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  });

  // Livewire wire:navigate hooks. Idiomorph drops the .voice-input-wrapper
  // we inject (it's not in the server response) but keeps the textareas via
  // their stable ids. Without clearing _voiceAttached on navigating, the
  // subsequent refresh() would bail and the mic would never reappear.
  if (!window.__voiceInputNavBound) {
    window.__voiceInputNavBound = true;

    document.addEventListener('livewire:navigating', function () {
      var attached = document.querySelectorAll('textarea');
      attached.forEach(function (el) {
        if (el._voiceAttached) el._voiceAttached = false;
      });
    });

    document.addEventListener('livewire:navigated', function () {
      refresh();
    });
  }

  window.VoiceInput = {
    attach: attach,
    refresh: refresh,
    supported: supported,
  };
})();
