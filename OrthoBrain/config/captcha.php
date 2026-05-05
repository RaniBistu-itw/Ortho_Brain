<?php

return [
    // Google reCAPTCHA v3 keys. Leave both blank to disable verification
    // (useful on localhost without internet access — the middleware logs
    // a warning and lets the request through instead of hard-failing).
    'site_key'   => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),

    // Score threshold (0.0 = bot, 1.0 = human). Google's docs recommend
    // 0.5 as a starting point; raise for stricter pages, lower if real
    // users get false-positive blocked.
    'min_score'  => (float) env('RECAPTCHA_MIN_SCORE', 0.5),

    // Network timeout for the verify call (seconds). Keep tight so a
    // Google outage can't stall the form for users.
    'timeout'    => (int) env('RECAPTCHA_TIMEOUT', 4),

    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
];
