<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $subject ?? 'Flikma' }}</title></head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f5f9;padding:28px 12px;">
    <tr><td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(16,24,40,.08);">
            <tr><td style="background:#0f766e;padding:20px 28px;"><span style="font-size:22px;font-weight:700;color:#ffffff;">Flikma</span></td></tr>
            {{-- written by platform admins in the newsletter editor --}}
            <tr><td style="padding:28px;font-size:15px;line-height:1.7;color:#374151;">{!! $html ?? '' !!}</td></tr>
            <tr><td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:14px 28px;font-size:12px;color:#9ca3af;">You receive this as a Flikma customer{{ !empty($company) ? ' (' . $company . ')' : '' }} &middot; &copy; {{ date('Y') }}</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
