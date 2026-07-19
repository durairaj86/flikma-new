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
            <td>{{ $contactMessage->name }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $contactMessage->email }}</td>
        </tr>
        @if($contactMessage->company)
        <tr>
            <td><strong>Company:</strong></td>
            <td>{{ $contactMessage->company }}</td>
        </tr>
        @endif
        @if($contactMessage->phone)
        <tr>
            <td><strong>Phone:</strong></td>
            <td>{{ $contactMessage->phone }}</td>
        </tr>
        @endif
        @if($contactMessage->interest)
        <tr>
            <td><strong>Interested In:</strong></td>
            <td>{{ $contactMessage->interest }}</td>
        </tr>
        @endif
    </table>

    @if($contactMessage->message)
        <p><strong>Message:</strong></p>
        <p style="background:#f4f8ff; padding:14px 18px; border-radius:8px;">{{ $contactMessage->message }}</p>
    @endif

    <p style="color:#8a93a6; font-size:13px; margin-top:24px;">
        Submitted {{ $contactMessage->created_at->format('d M Y, h:i A') }} via the Flikma website contact form.
    </p>

</body>
</html>
