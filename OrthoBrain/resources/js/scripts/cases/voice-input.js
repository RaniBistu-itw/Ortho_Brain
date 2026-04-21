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
  var supported = !!SR;

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
    if (!textarea || textarea._voiceAttached) return;
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
        console.warn('[VoiceInput] recognition error:', evt.error);
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
      catch (e) { console.warn('[VoiceInput] start failed:', e); finishRecording(); }
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
    if (window.feather) window.feather.replace({ width: 14, height: 14 });
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Alpine CDN auto-starts on DOMContentLoaded. Give it a tick to render x-show/x-if,
    // then sweep; a second sweep at 800ms catches anything that hydrates later.
    setTimeout(refresh, 150);
    setTimeout(refresh, 800);
  });

  window.VoiceInput = {
    attach: attach,
    refresh: refresh,
    supported: supported,
  };
})();
