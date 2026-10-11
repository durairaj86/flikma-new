<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Flikma — your account is ready</title>
</head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#16324a;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#eef2f7;">Your account is ready. Here is a quick look at what makes running your freight business easier.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;">
<tr><td align="center" style="padding:30px 14px;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:20px;border:1px solid #e6ecf3;box-shadow:0 6px 26px rgba(22,50,74,.08);">

    <tr><td style="height:5px;line-height:5px;font-size:0;background-color:#4f46e5;background-image:linear-gradient(90deg,#4f46e5,#7c3aed,#0d9488);border-radius:20px 20px 0 0;">&nbsp;</td></tr>

    {{-- HERO --}}
    <tr>
        <td align="center" style="padding:36px 32px 6px;">
            <div style="font-size:15px;font-weight:800;letter-spacing:.4px;color:#4f46e5;">Flikma</div>
            <div style="font-size:50px;line-height:1;margin-top:16px;">🎉</div>
            <h1 style="margin:16px 0 8px;font-size:26px;line-height:1.25;color:#16324a;letter-spacing:-.4px;font-weight:800;">Welcome to Flikma, {{ $userName }}!</h1>
            <p style="margin:0;font-size:15px;line-height:1.65;color:#5b7186;">Your account is ready{!! !empty($companyName) ? ' for <strong style="color:#16324a;">' . e($companyName) . '</strong>' : '' !!}. Here is a quick look at what makes running your freight business easier.</p>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:22px 32px 8px;">
            <a href="{{ $dashboardUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:12px;">Continue to Flikma</a>
        </td>
    </tr>

    {{-- FEATURES --}}
    <tr>
        <td style="padding:30px 32px 2px;">
            <h2 style="margin:0 0 3px;font-size:19px;font-weight:800;color:#16324a;letter-spacing:-.3px;">Special Features</h2>
            <p style="margin:0;font-size:13px;color:#5b7186;">The first three are powered by AI.</p>
        </td>
    </tr>
    <tr>
        <td style="padding:10px 20px 4px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                @foreach(array_chunk([
                    ['✨', 'Receipt to expense, with AI', 'Scan a receipt and AI fills the expense and suggests the right account.', '#eef4ff'],
                    ['⚡', 'Supplier bill in one click', 'Upload a supplier bill, check what AI filled in, then press Save.', '#ecfdf5'],
                    ['💬', 'Ask AI about your jobs', 'Tap the AI button on Jobs: “What needs my attention today?”', '#f5f3ff'],
                    ['🚢', 'Jobs and shipment tracking', 'Milestones, documents and a link your customers can follow.', '#ffffff'],
                    ['🧾', 'Invoices and VAT', 'Customer and supplier invoices, credit notes and collections.', '#ffffff'],
                    ['📊', 'Reports you can trust', 'Profit per job, statements, ageing and trial balance.', '#ffffff'],
                ], 2) as $pair)
                    <tr>
                        @foreach($pair as [$icon, $title, $text, $bg])
                            <td width="50%" valign="top" style="padding:6px 8px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }};border:1px solid #e6ecf3;border-radius:16px;">
                                    <tr><td style="padding:16px 16px 0;font-size:24px;line-height:1;">{{ $icon }}</td></tr>
                                    <tr><td style="padding:9px 16px 0;font-size:14px;font-weight:800;color:#16324a;line-height:1.35;">{{ $title }}</td></tr>
                                    <tr><td style="padding:4px 16px 16px;font-size:13px;line-height:1.5;color:#5b7186;">{{ $text }}</td></tr>
                                </table>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>

    {{-- AI CREDITS NOTE --}}
    <tr>
        <td style="padding:14px 32px 6px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2ff;border:1px solid #d9e0fb;border-radius:14px;">
                <tr><td style="padding:15px 18px;font-size:14px;line-height:1.6;color:#312e81;">
                    <strong style="color:#4f46e5;">⭐ AI runs on AI credits.</strong>
                    Write to <a href="mailto:{{ $supportEmail }}" style="color:#4f46e5;font-weight:700;text-decoration:none;">{{ $supportEmail }}</a> and we will add credits to your account so you can try the AI features. AI results are suggestions, so please check them before you save.
                </td></tr>
            </table>
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:20px 32px 6px;">
            <a href="{{ $dashboardUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:12px;">Continue to Flikma</a>
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:22px 32px 36px;font-size:12.5px;color:#9aa8bb;line-height:1.7;">
            If you have any questions, write to <a href="mailto:{{ $supportEmail }}" style="color:#5b7186;text-decoration:underline;">{{ $supportEmail }}</a>.<br>
            Thanks,<br>
            <strong style="color:#5b7186;">The Flikma team</strong>
        </td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;">
    <tr><td align="center" style="padding:16px 20px 0;font-size:12px;line-height:1.6;color:#94a3b8;">You received this because you verified your email on Flikma. &copy; {{ date('Y') }} Flikma</td></tr>
</table>

</td></tr>
</table>
</body>
</html>
