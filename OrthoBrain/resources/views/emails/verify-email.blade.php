<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify your email</title>
</head>
<body style="margin:0; padding:0; background:#f8f8f8; font-family: Inter, Arial, sans-serif; color:#5e5873;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#fff; border-radius:8px; box-shadow:0 4px 24px rgba(34,41,47,0.08); overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px; border-bottom:1px solid #ebe9f1;">
                            <div style="font-size:1.6rem; font-weight:500; letter-spacing:-0.01em;">
                                <span style="color:#5bc0de;">ortho</span><span style="color:#8cc63f;">brain</span><span style="color:#8cc63f; font-size:0.65rem; vertical-align:super;">&trade;</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 12px; font-size:1.3rem; font-weight:500; color:#5e5873;">Confirm your email</h1>
                            <p style="margin:0 0 16px; font-size:0.95rem; line-height:1.6;">
                                Hi {{ $email }},
                            </p>
                            <p style="margin:0 0 24px; font-size:0.95rem; line-height:1.6;">
                                Thanks for registering with OrthoBrain. To finish setting up your account, please verify your email address using either method below.
                            </p>

                            <p style="margin:0 0 8px; font-size:0.85rem; color:#6e6b7b; text-transform:uppercase; letter-spacing:0.08em;">
                                Your verification code
                            </p>
                            <div style="text-align:center; margin:8px 0 24px;">
                                <div style="display:inline-block; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:8px; padding:18px 28px; font-family:'Courier New', monospace; font-size:2rem; font-weight:700; letter-spacing:0.5em; color:#0f172a;">
                                    {{ $otp }}
                                </div>
                            </div>

                            <div style="text-align:center; margin:8px 0 28px;">
                                <a href="{{ $verifyUrl }}"
                                   style="display:inline-block; background:#5bc0de; color:#fff !important; text-decoration:none; padding:12px 32px; border-radius:6px; font-weight:500; font-size:0.95rem; box-shadow:0 2px 4px rgba(91,192,222,0.4);">
                                    Verify my email
                                </a>
                            </div>

                            <p style="margin:0 0 12px; font-size:0.85rem; color:#6e6b7b; line-height:1.6;">
                                The code and link expire in <strong>30 minutes</strong>. If you didn't create an OrthoBrain account, you can safely ignore this email.
                            </p>
                            <p style="margin:16px 0 0; font-size:0.8rem; color:#b9b9c3; word-break:break-all; line-height:1.5;">
                                If the button doesn't work, copy and paste this URL into your browser:<br>
                                <span style="color:#5bc0de;">{{ $verifyUrl }}</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px; background:#fafafa; border-top:1px solid #ebe9f1; font-size:0.75rem; color:#b9b9c3; text-align:center;">
                            &copy; {{ date('Y') }} OrthoBrain. This is an automated message — please do not reply.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
