<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OrthoBrain — Verify your email</title>

    <link rel="stylesheet" href="{{ asset('css/base/themes/orthobrain-palette.css') }}?v={{ @filemtime(public_path('css/base/themes/orthobrain-palette.css')) ?: time() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; color: #1E293B; background: #F8FAFC; }
        .verify-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .verify-card { width: 100%; max-width: 480px; background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(15, 23, 42, 0.08); padding: 2.5rem 2rem; }
        .verify-logo { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; }
        .verify-logo .logo-name { font-size: 1.5rem; font-weight: 500; letter-spacing: -0.01em; }
        .verify-logo .logo-name .p1 { color: #5bc0de; }
        .verify-logo .logo-name .p2 { color: #8cc63f; }
        .verify-logo .logo-name .tm { color: #8cc63f; font-size: 0.5em; vertical-align: super; }

        .verify-icon-wrap { width: 64px; height: 64px; margin: 0 auto 1.25rem; border-radius: 50%; background: #DBEAFE; display: flex; align-items: center; justify-content: center; color: #2563EB; font-size: 1.75rem; }

        h1.verify-heading { text-align: center; font-size: 1.5rem; font-weight: 700; color: #0F172A; margin: 0 0 0.5rem; }
        p.verify-sub { text-align: center; font-size: 0.95rem; color: #64748B; margin: 0 0 1.5rem; line-height: 1.5; }
        p.verify-email-line { text-align: center; font-size: 0.9rem; color: #475569; margin: 0 0 1.75rem; }
        p.verify-email-line strong { color: #0F172A; }

        .otp-input-row { display: flex; gap: 0.5rem; justify-content: center; margin: 0 0 1rem; }
        .otp-box {
            width: 46px; height: 56px;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            background: #fff;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            color: #0F172A;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .otp-box:focus { border-color: #3B82F6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12); }
        .otp-box.is-invalid { border-color: #EF4444; }
        .otp-error { color: #DC2626; font-size: 0.82rem; text-align: center; margin: 0 0 1rem; }
        .otp-hidden { position: absolute; left: -9999px; }

        .verify-btn {
            width: 100%; border: 0; cursor: pointer; color: #fff;
            font: inherit; font-weight: 600; font-size: 0.98rem;
            padding: 0.9rem 1rem; border-radius: 12px;
            background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.28);
            transition: transform .15s ease, box-shadow .2s ease;
        }
        .verify-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(37, 99, 235, 0.38); }
        .verify-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .verify-foot { text-align: center; margin-top: 1.5rem; font-size: 0.88rem; color: #64748B; }
        .verify-foot button { background: none; border: 0; color: #2563EB; font-weight: 600; cursor: pointer; padding: 0; font-size: inherit; }
        .verify-foot button:hover { text-decoration: underline; }
        .verify-foot button:disabled { opacity: 0.5; cursor: not-allowed; text-decoration: none; }

        .alert-banner { padding: 0.75rem 1rem; border-radius: 10px; font-size: 0.88rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .alert-success { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .alert-error   { background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5; }
    </style>
</head>
<body>
    <main class="verify-shell">
        <div class="verify-card">
            <div class="verify-logo">
                <span class="logo-name"><span class="p1">ortho</span><span class="p2">brain</span><span class="tm">&trade;</span></span>
            </div>

            <div class="verify-icon-wrap">
                <i class="bi bi-envelope-check"></i>
            </div>

            <h1 class="verify-heading">Verify your email</h1>
            <p class="verify-sub">Enter the 6-digit code we sent to your inbox, or click the link in the email.</p>

            @if($email)
                <p class="verify-email-line">Code sent to <strong>{{ $email }}</strong></p>
            @endif

            @if (session('status'))
                <div class="alert-banner alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
            @if($errors->has('email') && ! $errors->has('otp'))
                <div class="alert-banner alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @endif

            <form id="otp-form" action="{{ route('verify-email.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="text" id="otp-hidden" name="otp" inputmode="numeric"
                       autocomplete="one-time-code" maxlength="6" pattern="\d{6}"
                       class="otp-hidden" required value="{{ old('otp') }}">

                <div class="otp-input-row" id="otp-row">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text"
                               class="otp-box @if($errors->has('otp')) is-invalid @endif"
                               inputmode="numeric"
                               maxlength="1"
                               data-otp-idx="{{ $i }}"
                               autocomplete="off">
                    @endfor
                </div>

                @error('otp')
                    <p class="otp-error">{{ $message }}</p>
                @enderror

                <button type="submit" class="verify-btn">Verify Email</button>
            </form>

            <div class="verify-foot">
                Didn't receive the code?
                <form id="resend-form" action="{{ route('verify-email.resend') }}" method="POST" style="display:inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" id="resend-btn">Resend</button>
                </form>
                &nbsp;·&nbsp;
                <a href="{{ route('login') }}" style="color:#64748B; text-decoration:none;">
                    <i class="bi bi-chevron-left"></i> Back to login
                </a>
            </div>
        </div>
    </main>

    <script>
        (function () {
            // 6 visible boxes drive a single hidden input — keeps Laravel's
            // server-side validation simple while giving the user a polished
            // OTP-style entry experience.
            const boxes = Array.from(document.querySelectorAll('.otp-box'));
            const hidden = document.getElementById('otp-hidden');
            const form = document.getElementById('otp-form');
            if (!boxes.length || !hidden || !form) return;

            // Hydrate from any existing old('otp') value.
            const existing = (hidden.value || '').slice(0, 6).split('');
            existing.forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });

            const syncHidden = () => { hidden.value = boxes.map(b => b.value).join(''); };

            boxes.forEach((box, idx) => {
                box.addEventListener('input', (e) => {
                    box.value = box.value.replace(/\D/g, '').slice(0, 1);
                    syncHidden();
                    if (box.value && idx < boxes.length - 1) boxes[idx + 1].focus();
                });
                box.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !box.value && idx > 0) {
                        boxes[idx - 1].focus();
                        boxes[idx - 1].value = '';
                        syncHidden();
                        e.preventDefault();
                    }
                    if (e.key === 'ArrowLeft' && idx > 0) boxes[idx - 1].focus();
                    if (e.key === 'ArrowRight' && idx < boxes.length - 1) boxes[idx + 1].focus();
                });
                box.addEventListener('paste', (e) => {
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    const digits = (text || '').replace(/\D/g, '').slice(0, 6);
                    if (!digits) return;
                    e.preventDefault();
                    digits.split('').forEach((d, i) => { if (boxes[i]) boxes[i].value = d; });
                    syncHidden();
                    boxes[Math.min(digits.length, boxes.length - 1)].focus();
                });
            });

            // Auto-focus the first empty box on page load.
            const firstEmpty = boxes.find(b => !b.value);
            if (firstEmpty) firstEmpty.focus();

            form.addEventListener('submit', () => syncHidden());
        })();
    </script>
</body>
</html>
