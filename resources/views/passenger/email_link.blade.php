<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyRefund Tracking Link</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; padding:24px; color:#0f172a;">
    <div style="max-width:560px; margin:0 auto; background:white; padding:24px; border-radius:16px;">
        <h2>Track your refund</h2>
        <p>Your refund reference is: <strong>{{ $refund->reference }}</strong></p>
        <p>Use the link below to track the progress of your refund request.</p>
        <p><a href="{{ url('/portal?reference=' . $refund->reference) }}" style="display:inline-block; padding:10px 16px; background:#2563eb; color:white; text-decoration:none; border-radius:10px;">Open tracking portal</a></p>
        <p style="color:#64748b;">If you did not request this update, you can ignore this message.</p>
    </div>
</body>
</html>
