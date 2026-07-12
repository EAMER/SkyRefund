<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyRefund Passenger Portal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        .wrap { max-width: 720px; margin: 3rem auto; padding: 2rem; background: linear-gradient(135deg, #ffffff, #f8fbff); border-radius: 16px; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
        form { display: flex; gap: .75rem; flex-wrap: wrap; }
        input, button { padding: .75rem; border-radius: 10px; border: 1px solid #cbd5e1; }
        button { background: #2563eb; color: white; border: none; cursor: pointer; }
        .error { color: #dc2626; margin-top: .75rem; }
        .muted { color: #64748b; }
        .hero { background: linear-gradient(135deg, #eff6ff, #ecfeff); border:1px solid #bfdbfe; border-radius:14px; padding:1rem 1.1rem; margin-bottom:1rem; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <h1 style="margin:0;">Passenger Portal</h1>
            <p class="muted" style="margin:.4rem 0 0;">Track your refund request, upload extra documents, and download a confirmation receipt.</p>
        </div>
        <form method="GET" action="/portal">
            <input type="text" name="reference" placeholder="Enter refund reference" required>
            <button type="submit">Open portal</button>
        </form>
        @if(isset($error))
            <p class="error">{{ $error }}</p>
        @endif
    </div>
</body>
</html>
