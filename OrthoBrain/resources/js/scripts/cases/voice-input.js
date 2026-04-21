// Reusable Web Speech dictation for any textarea on the page.
// Auto-attaches a floating mic button to every textarea carrying data-voice-input
// (and, by convention, every textarea with maxlength="5000").
//
// Public API:
//   window.VoiceInput.attach(textareaEl)   // opt-in attachment
//   window.VoiceInput.refresh()            // re-scan the DOM (e.g., after partials render)
//
// Browser support: Chrome/Edge. Firefox ships SpeechRecognition behind a flag.
// If unsupported, no buttons are rendered.

(function () {
  'use strict';

  var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  var supported = !!SR;

  function attach(textarea) {
    if (!textarea || textarea._voiceAttached) return;
    if (!supported) return;
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
    btn.setAttribute('aria-label', 'Dictate with voice');
    btn.title = 'Dictate with voice';
    btn.innerHTML = '<i data-feather="mic"></i>';
    wrapper.appendChild(btn);

    var recognition = null;
    var baseline = '';
    var recording = false;
    var MAXLEN = parseInt(textarea.getAttribute('maxlength'), 10) || 5000;

    function ensureRecognition() {
      if (recognition) return recognition;
      recognition = new SR();
      recognition.continuous = true;
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
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
      };

      recognition.onend = function () { finishRecording(); };
      recognition.onerror = function (evt) {
        console.warn('[VoiceInput]', evt.error);
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
      try { ensureRecognition().start(); }
      catch (e) { console.warn('[VoiceInput] start failed', e); finishRecording(); }
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

    // Stop recording if the textarea is removed from DOM.
    textarea.addEventListener('blur', function () {
      // Keep dictation going when user clicks the mic (which briefly blurs the
      // textarea). Only stop on explicit click of the mic or end event.
    });
  }

  function refresh() {
    if (!supported) return;
    var selector = 'textarea[data-voice-input], textarea[maxlength="5000"]';
    document.querySelectorAll(selector).forEach(attach);
    if (window.feather) window.feather.replace({ width: 14, height: 14 });
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Alpine auto-starts via CDN on DOMContentLoaded too; run after a tick so
    // x-model bindings settle before we restructure the DOM around textareas.
    setTimeout(refresh, 150);
  });

  window.VoiceInput = {
    attach: attach,
    refresh: refresh,
    supported: supported,
  };
})();
