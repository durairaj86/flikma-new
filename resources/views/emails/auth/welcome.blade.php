<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Flikma</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#f4f6fb;">Your email is verified. Scan receipts, save supplier bills in one click and ask AI about your jobs.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb;">
<tr><td align="center" style="padding:28px 14px;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;box-shadow:0 4px 24px rgba(15,23,42,.08);">

        {{-- thin accent line --}}
        <tr><td style="height:5px;line-height:5px;font-size:0;background-color:#4f46e5;background-image:linear-gradient(90deg,#4f46e5,#7c3aed,#0d9488);border-radius:16px 16px 0 0;">&nbsp;</td></tr>

        <tr>
            <td style="padding:34px 36px 0;">
                <span style="font-size:20px;font-weight:800;letter-spacing:.3px;color:#0f172a;">Flikma</span>
            </td>
        </tr>

        <tr>
            <td style="padding:26px 36px 0;">
                <h1 style="margin:0;font-size:28px;line-height:1.25;font-weight:800;color:#0f172a;letter-spacing:-.4px;">Welcome, {{ $userName }}</h1>
                <p style="margin:12px 0 0;font-size:16px;line-height:1.65;color:#475569;">Your email is verified and your account is ready{!! !empty($companyName) ? ' for <strong style="color:#0f172a;">' . e($companyName) . '</strong>' : '' !!}.</p>
            </td>
        </tr>

        <tr>
            <td style="padding:26px 36px 8px;">
                <a href="{{ $dashboardUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 30px;border-radius:10px;">Open your dashboard</a>
            </td>
        </tr>

        <tr><td style="padding:26px 36px 0;"><div style="height:1px;line-height:1px;font-size:0;background:#e8ecf3;">&nbsp;</div></td></tr>

        <tr>
            <td style="padding:24px 36px 4px;">
                <div style="font-size:12px;font-weight:800;letter-spacing:1.4px;color:#4f46e5;">AI THAT SAVES YOU TIME</div>
            </td>
        </tr>

        @foreach([
            ['#eef2ff', '🧾', 'Scan a receipt', 'AI fills the expense and suggests the right account.'],
            ['#ecfdf5', '⚡', 'Supplier bill in one click', 'Upload a bill, check it, press Save.'],
            ['#f5f3ff', '💬', 'Ask AI about your jobs', 'Tap the AI button on Jobs: “What needs my attention today?”'],
        ] as [$tint, $icon, $title, $text])
            <tr>
                <td style="padding:12px 36px 0;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="52" valign="top"><div style="width:40px;height:40px;line-height:40px;text-align:center;font-size:19px;background:{{ $tint }};border-radius:12px;">{{ $icon }}</div></td>
                            <td valign="top" style="padding-top:1px;">
                                <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $title }}</div>
                                <div style="font-size:14px;line-height:1.55;color:#64748b;margin-top:2px;">{{ $text }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach

        <tr>
            <td style="padding:28px 36px 34px;">
                <p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">Questions? Write to <a href="mailto:{{ $supportEmail }}" style="color:#4f46e5;font-weight:700;text-decoration:none;">{{ $supportEmail }}</a>.</p>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
        <tr><td align="center" style="padding:18px 20px 0;font-size:12px;line-height:1.6;color:#94a3b8;">You received this because you verified your email on Flikma. AI results are suggestions, so please check them before you save.<br>&copy; {{ date('Y') }} Flikma</td></tr>
    </table>

</td></tr>
</table>
</body>
</html>
