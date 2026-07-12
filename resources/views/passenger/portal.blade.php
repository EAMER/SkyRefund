<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        .wrap { max-width: 980px; margin: 2rem auto; padding: 2rem; background: linear-gradient(135deg, #ffffff, #f8fbff); border-radius: 16px; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
        .grid { display: grid; gap: 1rem; grid-template-columns: 1fr 1fr; }
        .card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; background: white; }
        .hero { background: linear-gradient(135deg, #eff6ff, #ecfeff); border: 1px solid #bfdbfe; border-radius: 14px; padding: 1rem 1.1rem; margin-bottom: 1rem; }
        .muted { color: #64748b; }
        .pill { display:inline-block; padding:.35rem .6rem; border-radius:999px; font-size:.8rem; font-weight:700; background: #dbeafe; color: #1d4ed8; }
        .progress { height: 10px; border-radius:999px; background:#e2e8f0; overflow:hidden; margin: .5rem 0 1rem; }
        .progress > span { display:block; height:100%; border-radius:999px; background:linear-gradient(90deg, #2563eb, #14b8a6); width:60%; }
        ul { padding-left: 1rem; }
        input, button, select { padding: .7rem; border-radius: 10px; border: 1px solid #cbd5e1; }
        button { background: #2563eb; color: white; border: none; cursor: pointer; }
        .success { color: #0f766e; margin-top: .75rem; }
        @media (max-width: 760px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <h1 style="margin:0;">Refund Status</h1>
            <p class="muted" style="margin:.3rem 0 0;">Reference: <strong>{{ $refund->reference }}</strong></p>
            <div style="margin-top:.6rem;"><span class="pill">{{ $refund->current_status }}</span></div>
        </div>
        <div class="progress"><span></span></div>
        <div class="grid">
            <div class="card">
                <h3>Overview</h3>
                <p><strong>Status:</strong> {{ $refund->current_status }}</p>
                <p><strong>Department:</strong> {{ $refund->current_department }}</p>
                <p><strong>Priority:</strong> {{ $refund->priority }}</p>
                <p><strong>Passenger:</strong> {{ $refund->first_name }} {{ $refund->last_name }}</p>
                <p><strong>Email:</strong> {{ $refund->email }}</p>
                <a href="/portal/{{ $refund->id }}/receipt" target="_blank"><button type="button">Download receipt</button></a>
            </div>
            <div class="card">
                <h3>Upload documents</h3>
                @if(session('status'))
                    <p class="success">{{ session('status') }}</p>
                @endif
                <form method="POST" action="/portal/{{ $refund->id }}/upload" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="attachments[]" multiple required>
                    <select name="attachment_types[]" required>
                        <option value="OTHER">Other</option>
                        <option value="TICKET">Ticket</option>
                        <option value="PAYMENT">Payment</option>
                        <option value="ID">ID</option>
                    </select>
                    <button type="submit">Upload</button>
                </form>
            </div>
        </div>
        <div class="card" style="margin-top: 1rem;">
            <h3>Timeline</h3>
            <ul>
                @forelse($refund->statusLogs as $log)
                    <li>
                        <strong>{{ $log->new_status }}</strong>
                        @if($log->note) — {{ $log->note }} @endif
                        <div class="muted">{{ $log->created_at->format('d M Y H:i') }}</div>
                    </li>
                @empty
                    <li>No timeline events yet.</li>
                @endforelse
            </ul>
        </div>
        <div class="card" style="margin-top: 1rem;">
            <h3>Uploaded documents</h3>
            <ul>
                @forelse($refund->attachments as $attachment)
                    <li>{{ $attachment->original_name }} ({{ $attachment->type }})</li>
                @empty
                    <li>No documents uploaded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</body>
</html>
