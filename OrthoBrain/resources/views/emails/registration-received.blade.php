<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application received</title>
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
                            <h1 style="margin:0 0 12px; font-size:1.3rem; font-weight:500; color:#5e5873;">We've received your application</h1>
                            <p style="margin:0 0 16px; font-size:0.95rem; line-height:1.6;">
                                Hi Dr. {{ $firstName }},
                            </p>
                            <p style="margin:0 0 16px; font-size:0.95rem; line-height:1.6;">
                                Thanks for applying to OrthoBrain. Your registration is under admin review — we'll email you the moment there's a decision.
                            </p>
                            <p style="margin:0 0 0; font-size:0.95rem; line-height:1.6;">
                                This usually takes <strong>1–2 business days</strong>. No action is required from you in the meantime.
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
