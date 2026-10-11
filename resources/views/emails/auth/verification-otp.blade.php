<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Your Flikma verification code</title>
</head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#1f2937;">
{{-- Preview text shown next to the subject in the inbox --}}
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f3f5f9;">Your verification code is {{ $code }}. It is valid for {{ $minutes }} minutes.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f5f9;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="width:100%;max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(16,24,40,.08);">
                <tr>
                    <td style="background:#0f766e;padding:26px 32px;">
                        <span style="font-size:24px;font-weight:700;letter-spacing:.4px;color:#ffffff;">Flikma</span>
                        <span style="display:block;margin-top:2px;font-size:12px;color:#ccfbf1;letter-spacing:.3px;">Freight &amp; Logistics Management</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:34px 32px 8px;">
                        <h1 style="margin:0 0 14px;font-size:21px;line-height:1.3;color:#111827;">Verify your email address</h1>
                        <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#374151;">Hello {{ $userName }},</p>
                        <p style="margin:0;font-size:15px;line-height:1.6;color:#374151;">Use the code below to confirm your email address and start using your Flikma account.</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:22px 32px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;">
                            <tr>
                                <td align="center" style="background:#f0fdfa;border:2px dashed #14b8a6;border-radius:12px;padding:22px 10px;">
                                    <span style="font-size:12px;font-weight:700;letter-spacing:1.5px;color:#0f766e;text-transform:uppercase;">Your verification code</span>
                                    <div style="margin-top:8px;font-family:'Courier New',Consolas,monospace;font-size:40px;font-weight:700;letter-spacing:12px;color:#0f172a;padding-left:12px;">{{ $code }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 32px 8px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;background:#fffbeb;border-left:4px solid #f59e0b;border-radius:6px;">
                            <tr>
                                <td style="padding:12px 16px;font-size:14px;line-height:1.6;color:#92400e;">
                                    <strong>This code expires in {{ $minutes }} minutes</strong> and can be used <strong>only once</strong>. If it expires, you can request a new code on the verification page.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 30px;">
                        <p style="margin:0 0 10px;font-size:14px;line-height:1.6;color:#4b5563;"><strong>Keep it private.</strong> Never share this code with anyone. Flikma will never ask you for it by phone, chat or email.</p>
                        <p style="margin:0;font-size:14px;line-height:1.6;color:#4b5563;">If you did not request this code, you can safely ignore this email. Your account stays protected.</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:18px 32px;">
                        <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">This is an automated message from Flikma, sent to {{ $userEmail }}. Please do not reply to this email.</p>
                        <p style="margin:6px 0 0;font-size:12px;color:#9ca3af;">&copy; {{ date('Y') }} Flikma. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
