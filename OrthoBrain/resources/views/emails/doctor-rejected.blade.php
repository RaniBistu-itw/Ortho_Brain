<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application update</title>
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
                            <h1 style="margin:0 0 12px; font-size:1.3rem; font-weight:500; color:#5e5873;">Update on your application</h1>
                            <p style="margin:0 0 16px; font-size:0.95rem; line-height:1.6;">
                                Dear Dr. {{ $firstName }},
                            </p>
                            <p style="margin:0 0 16px; font-size:0.95rem; line-height:1.6;">
                                After reviewing your application, we're unable to approve your OrthoBrain account at this time.
                            </p>
                            <div style="background:#fbeaea; border-left:3px solid #ea5455; padding:12px 16px; border-radius:4px; margin:0 0 20px;">
                                <div style="font-size:0.78rem; font-weight:600; color:#ea5455; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px;">Reason</div>
                                <div style="font-size:0.92rem; color:#5e5873; line-height:1.5; white-space:pre-wrap;">{{ $reason }}</div>
                            </div>
                            <p style="margin:0 0 16px; font-size:0.95rem; line-height:1.6;">
                                You're welcome to reapply with the same email once the issue is addressed.
                            </p>
                            <div style="text-align:center; margin:24px 0;">
                                <a href="{{ $reapplyUrl }}"
                                   style="display:inline-block; background:#5bc0de; color:#fff !important; text-decoration:none; padding:12px 32px; border-radius:6px; font-weight:500; font-size:0.95rem; box-shadow:0 2px 4px rgba(91,192,222,0.4);">
                                    Reapply
                                </a>
                            </div>
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
