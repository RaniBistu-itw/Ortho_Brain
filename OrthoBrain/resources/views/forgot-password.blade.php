<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0, minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - OrthoBrain</title>

    <link rel="apple-touch-icon" href="{{ asset('vuexy/images/ico/favicon-32x32.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('vuexy/images/ico/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('vuexy/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('vuexy/css/overrides.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static"
      data-open="click" data-menu="vertical-menu-modern" data-col="blank-page">

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-body">
                <div class="auth-wrapper auth-basic px-2">
                    <div class="auth-inner my-2">
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center mb-2">
                                    <svg width="60" height="52" viewBox="0 0 64 64" fill="none" stroke="#b8b8b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mb-1">
                                        <path d="M32 12c-3.5 0-5.5 2-5.5 4 0 2-2 4-4.5 4-4.5 0-7 3-7 7.5 0 2.5-2 4-3 6-1.5 3.5 1 7 4 7 1 0 2 1 2 2.5 0 3.5 3.5 5.5 6.5 5.5 2 0 3-1.5 4.5-3 2-2 5.5-2 7.5 0 1.5 1.5 2.5 3 4.5 3 3 0 6.5-2 6.5-5.5 0-1.5 1-2.5 2-2.5 3 0 5.5-3.5 4-7-1-2-3-3.5-3-6 0-4.5-2.5-7.5-7-7.5-2.5 0-4.5-2-4.5-4 0-2-2-4-5.5-4z" fill="#ffffff"/>
                                        <path d="M32 16v18M23 26c2 1 2 5 0 7M41 26c-2 1-2 5 0 7"/>
                                        <path d="M30 46 l-4 8 h6 l-2 6 8-10 h-6 z" fill="#b8b8b8" stroke="none"/>
                                    </svg>
                                    <h2 class="mb-0" style="font-weight:500;letter-spacing:-0.02em;">
                                        <span style="color:#5bc0de">ortho</span><span style="color:#8cc63f">brain</span><small style="color:#8cc63f">™</small>
                                    </h2>
                                    <p class="text-muted fst-italic" style="font-size:12px;">Orthodontics for Your Dental Practice</p>
                                </div>

                                <h4 class="card-title mb-1">Forgot Password? 🔒</h4>
                                <p class="card-text mb-2">Enter your email and we'll send you a link to reset your password.</p>

                                @if($status ?? false)
                                    <div class="alert alert-success" role="alert">
                                        <div class="alert-body">We have emailed your password reset link.</div>
                                    </div>
                                @endif

                                <form class="auth-forgot-password-form mt-2" action="{{ url('/forgot-password') }}" method="POST">
                                    @csrf
                                    <div class="mb-1">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required autofocus>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                                </form>

                                <p class="text-center mt-2">
                                    <a href="{{ url('/login') }}"><i data-feather="chevron-left"></i> Back to login</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vuexy/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('vuexy/js/core/app.js') }}"></script>
    <script>
        $(window).on('load', function () { if (window.feather) feather.replace({ width: 14, height: 14 }); });
    </script>
</body>
</html>
