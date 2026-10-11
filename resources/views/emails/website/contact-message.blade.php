<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; color: #0b1736; line-height: 1.6;">

    <h2 style="color: #0d6efd;">New Contact Form Submission</h2>

    <table cellpadding="6" cellspacing="0">
        <tr>
            <td><strong>Name:</strong></td>
            <td>{{ ($contactMessage['name'] ?? null) }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ ($contactMessage['email'] ?? null) }}</td>
        </tr>
        @if(($contactMessage['company'] ?? null))
        <tr>
            <td><strong>Company:</strong></td>
            <td>{{ ($contactMessage['company'] ?? null) }}</td>
        </tr>
        @endif
        @if(($contactMessage['phone'] ?? null))
        <tr>
            <td><strong>Phone:</strong></td>
            <td>{{ ($contactMessage['phone'] ?? null) }}</td>
        </tr>
        @endif
        @if(($contactMessage['interest'] ?? null))
        <tr>
            <td><strong>Interested In:</strong></td>
            <td>{{ ($contactMessage['interest'] ?? null) }}</td>
        </tr>
        @endif
    </table>

    @if(($contactMessage['message'] ?? null))
        <p><strong>Message:</strong></p>
        <p style="background:#f4f8ff; padding:14px 18px; border-radius:8px;">{{ ($contactMessage['message'] ?? null) }}</p>
    @endif

    <p style="color:#8a93a6; font-size:13px; margin-top:24px;">
        Submitted {{ $contactMessage['submitted'] ?? '' }} via the Flikma website contact form.
    </p>

</body>
</html>
