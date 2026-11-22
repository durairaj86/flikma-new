<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Your follow-ups today</title></head>
<body style="margin:0;padding:0;background:#f3f5f9;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#1f2937;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f5f9;padding:28px 12px;">
    <tr><td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(16,24,40,.08);">
            <tr><td style="background:#0f766e;padding:20px 28px;"><span style="font-size:22px;font-weight:700;color:#ffffff;">Flikma</span></td></tr>
            <tr><td style="padding:24px 28px 8px;font-size:15px;line-height:1.6;color:#374151;">
                Hello {{ $name }},<br>
                these follow-ups are due today or overdue:
            </td></tr>
            <tr><td style="padding:0 28px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size:14px;border-collapse:collapse;">
                    @foreach($rows as $r)
                        <tr>
                            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;vertical-align:top;width:120px;color:{{ $r['overdue'] ? '#b91c1c' : '#374151' }};font-weight:{{ $r['overdue'] ? '700' : '400' }};">
                                {{ $r['when'] }}@if($r['overdue'])<br><span style="font-size:12px;">overdue</span>@endif
                            </td>
                            <td style="padding:10px 0 10px 12px;border-bottom:1px solid #e5e7eb;vertical-align:top;">
                                <div style="color:#6b7280;font-size:12px;">{{ $r['kind'] }} {{ $r['no'] }}</div>
                                <div style="font-weight:700;">{{ $r['name'] }}</div>
                                <div>{{ $r['todo'] }}</div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td></tr>
            <tr><td style="padding:22px 28px 28px;">
                <a href="{{ $url }}" style="display:inline-block;background:#0f766e;color:#ffffff;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:700;">Open my follow-ups</a>
            </td></tr>
            <tr><td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:14px 28px;font-size:12px;color:#9ca3af;">Sent with Flikma &middot; &copy; {{ date('Y') }}</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
