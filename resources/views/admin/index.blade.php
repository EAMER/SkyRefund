<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyRefund Admin</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #040816;
            --panel: #0f172a;
            --panel-2: #111c34;
            --border: #24324f;
            --muted: #94a3b8;
            --text: #f8fafc;
            --accent: #38bdf8;
            --accent-2: #34d399;
            --warn: #f59e0b;
            --danger: #fb7185;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: radial-gradient(circle at top left, #11213f 0%, var(--bg) 45%);
            color: var(--text);
            min-height: 100vh;
        }
        .app { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        aside { background: rgba(2, 8, 23, 0.92); border-right: 1px solid var(--border); padding: 1.2rem; }
        .brand { display:flex; align-items:center; gap:0.8rem; margin-bottom:1.2rem; }
        .brand-mark { width:44px; height:44px; border-radius:12px; display:grid; place-items:center; font-weight:700; color:#052e2b; background: linear-gradient(135deg, var(--accent), var(--accent-2)); }
        .brand h2 { margin:0; font-size:1rem; }
        .brand p { margin:0.15rem 0 0; color:var(--muted); font-size:0.8rem; }
        .nav a { display:flex; align-items:center; gap:0.7rem; color:var(--text); text-decoration:none; padding:0.7rem 0.9rem; margin:0.25rem 0; border-radius:10px; transition: all .2s ease; }
        .nav a:hover, .nav a.active { background: rgba(56, 189, 248, 0.15); color: var(--accent); }
        main { padding: 1.2rem; }
        .card { background: rgba(15, 23, 42, 0.94); border:1px solid var(--border); border-radius:16px; padding:1rem; box-shadow: 0 10px 30px rgba(2, 8, 23, .25); }
        .card + .card { margin-top: 1rem; }
        .header-row { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
        .hero { background: linear-gradient(135deg, rgba(56,189,248,.2), rgba(52,211,153,.12)); border:1px solid var(--border); border-radius:18px; padding:1rem 1.1rem; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; gap:1rem; }
        .hero .eyebrow { color: var(--accent); font-size: 0.78rem; text-transform: uppercase; letter-spacing: .16em; }
        .stats-grid { display:grid; gap:1rem; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); margin-bottom:1rem; }
        .stat { background: linear-gradient(135deg, rgba(56,189,248,.22), rgba(52,211,153,.15)); border:1px solid var(--border); border-radius:14px; padding:1rem; }
        .stat .label { color:var(--muted); font-size:0.8rem; text-transform:uppercase; letter-spacing:.12em; }
        .stat .value { font-size:1.4rem; font-weight:700; margin-top:.35rem; }
        .grid-2 { display:grid; gap:1rem; grid-template-columns: 1.3fr .9fr; }
        table { width:100%; border-collapse:collapse; margin-top:.5rem; }
        th, td { padding:0.7rem; border-bottom:1px solid var(--border); text-align:left; font-size:0.92rem; }
        th { color:var(--muted); font-weight:600; }
        tbody tr:hover { background: rgba(255,255,255,.03); }
        .pill { display:inline-block; padding:.3rem .55rem; border-radius:999px; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
        .pill.new { background: rgba(56,189,248,.18); color: #7dd3fc; }
        .pill.pending { background: rgba(245,158,11,.18); color:#fbbf24; }
        .pill.completed { background: rgba(52,211,153,.18); color:#6ee7b7; }
        .pill.rejected { background: rgba(251,113,133,.18); color:#fda4af; }
        .toolbar { display:flex; flex-wrap:wrap; gap:.6rem; margin-bottom:.8rem; }
        input, select, button, textarea { padding:0.7rem .8rem; border-radius:10px; border:1px solid var(--border); background: #020617; color: var(--text); }
        input:focus, select:focus, button:focus, textarea:focus { outline:2px solid rgba(56,189,248,.3); border-color:var(--accent); }
        button { cursor:pointer; font-weight:700; }
        textarea { width:100%; min-height:80px; resize:vertical; }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #2563eb); color:white; }
        .btn-ghost { background: rgba(255,255,255,.04); color:var(--text); }
        .btn-success { background: rgba(52,211,153,.16); color:#a7f3d0; }
        .btn-danger { background: rgba(251,113,133,.16); color:#fecdd3; }
        .hidden { display:none !important; }
        .login-card { max-width:460px; margin:2rem auto; }
        .stack-list { list-style:none; padding:0; margin:0; }
        .stack-list li { display:flex; justify-content:space-between; align-items:center; padding:.65rem 0; border-bottom:1px solid var(--border); }
        .progress-list { display:grid; gap:.8rem; }
        .progress-row { display:grid; gap:.35rem; }
        .progress-label { display:flex; justify-content:space-between; font-size:.9rem; color:var(--muted); }
        .progress-track { width:100%; height:8px; border-radius:999px; background: rgba(255,255,255,.08); overflow:hidden; }
        .progress-fill { height:100%; border-radius:999px; background: linear-gradient(90deg, var(--accent), var(--accent-2)); width:0; transition: width .25s ease; }
        tbody tr.clickable-row { cursor:pointer; }
        tbody tr.clickable-row:hover { background: rgba(56,189,248,.08); }
        .muted { color:var(--muted); }
        pre { white-space:pre-wrap; overflow:auto; background:#020617; padding:.9rem; border-radius:10px; border:1px solid var(--border); }
        @media (max-width: 960px) {
            .app { grid-template-columns: 1fr; }
            aside { border-right:0; border-bottom:1px solid var(--border); }
            .grid-2 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<div class="app">
    <aside>
        <div class="brand">
            <div class="brand-mark">SR</div>
            <div>
                <h2>SkyRefund</h2>
                <p>Operations Center</p>
            </div>
        </div>
        <div class="nav">
            <a href="#" class="active" data-view="dashboard">◉ Overview</a>
            <a href="#" data-view="refunds">◌ Refund List</a>
            <a href="#" data-view="details">◌ Refund Detail</a>
            <a href="#" data-view="users">◌ User Ops</a>
            <a href="#" data-view="reports">◌ Reports</a>
            <a href="#" data-view="settings">◌ Settings</a>
        </div>
    </aside>
    <main>
        <section id="login" class="card login-card">
            <div class="header-row">
                <div>
                    <p class="muted">Secure sign in</p>
                    <h3>Admin Console</h3>
                </div>
            </div>
            <input id="email" placeholder="Email" value="admin@example.com">
            <input id="password" type="password" placeholder="Password" value="password">
            <button class="btn-primary" onclick="login()">Sign in</button>
            <p id="loginMessage" class="muted"></p>
        </section>

        <div id="appContent" class="hidden">
            <div class="hero">
                <div>
                    <div class="eyebrow" id="pageLabel">Operations overview</div>
                    <h2 id="pageTitle" style="margin:0.2rem 0 0;">Dashboard</h2>
                </div>
                <button class="btn-ghost" onclick="logout()">Logout</button>
            </div>

            <section id="dashboardView" class="view-pane">
                <div class="stats-grid" id="stats"></div>
                <div class="grid-2">
                    <div class="card">
                        <div class="header-row">
                            <h3>Recent activity</h3>
                            <button class="btn-ghost" onclick="loadRefunds()">Refresh</button>
                        </div>
                        <table id="recentTable"></table>
                    </div>
                    <div class="card">
                        <h3>Workflow pulse</h3>
                        <div class="progress-list">
                            <div class="progress-row">
                                <div class="progress-label"><span>New requests</span><strong id="newRequests">0</strong></div>
                                <div class="progress-track"><div class="progress-fill" id="newBar"></div></div>
                            </div>
                            <div class="progress-row">
                                <div class="progress-label"><span>Pending commercial</span><strong id="pendingCommercial">0</strong></div>
                                <div class="progress-track"><div class="progress-fill" id="pendingCommercialBar"></div></div>
                            </div>
                            <div class="progress-row">
                                <div class="progress-label"><span>Pending audit</span><strong id="pendingAudit">0</strong></div>
                                <div class="progress-track"><div class="progress-fill" id="pendingAuditBar"></div></div>
                            </div>
                            <div class="progress-row">
                                <div class="progress-label"><span>Completed</span><strong id="completedCount">0</strong></div>
                                <div class="progress-track"><div class="progress-fill" id="completedBar"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="refundsView" class="view-pane hidden">
                <div class="card">
                    <div class="header-row">
                        <h3>Refund queue</h3>
                        <button class="btn-ghost" onclick="loadRefunds()">Reload</button>
                    </div>
                    <div class="toolbar">
                        <input id="search" placeholder="Search by reference, name, or email">
                        <select id="statusFilter">
                            <option value="">All statuses</option>
                            <option value="NEW_REQUEST">New Request</option>
                            <option value="PENDING_COMMERCIAL">Pending Commercial</option>
                            <option value="PENDING_AUDIT">Pending Audit</option>
                            <option value="PENDING_FINANCE">Pending Finance</option>
                            <option value="PENDING_TREASURY">Pending Treasury</option>
                            <option value="REFUND_COMPLETED">Completed</option>
                            <option value="REJECTED">Rejected</option>
                            <option value="CANCELLED">Cancelled</option>
                        </select>
                        <select id="priorityFilter">
                            <option value="">All priorities</option>
                            <option value="LOW">Low</option>
                            <option value="MEDIUM">Medium</option>
                            <option value="HIGH">High</option>
                        </select>
                        <button class="btn-primary" onclick="loadRefunds()">Search</button>
                    </div>
                    <table id="refundTable"></table>
                </div>
            </section>

            <section id="detailsView" class="view-pane hidden">
                <div class="card">
                    <div class="header-row">
                        <h3>Refund detail</h3>
                        <button class="btn-ghost" onclick="loadRefundDetails()">Load</button>
                    </div>
                    <div class="toolbar">
                        <input id="refundId" placeholder="Refund ID or reference">
                        <button class="btn-primary" onclick="loadRefundDetails()">Open</button>
                    </div>
                    <div class="toolbar">
                        <textarea id="actionNote" placeholder="Add a note or reason for the selected workflow action"></textarea>
                    </div>
                    <div class="toolbar">
                        <button class="btn-success" onclick="applyAction('approve')">Approve</button>
                        <button class="btn-danger" onclick="applyAction('reject')">Reject</button>
                        <button class="btn-ghost" onclick="applyAction('return')">Return</button>
                        <button class="btn-ghost" onclick="applyAction('complete')">Complete</button>
                    </div>
                    <pre id="detailBox">Select a refund to inspect its payload.</pre>
                    <div class="muted" id="actionFeedback" style="margin-top:.8rem;">No workflow action has been run yet.</div>
                </div>
            </section>

            <section id="usersView" class="view-pane hidden">
                <div class="card">
                    <h3>User operations</h3>
                    <p class="muted">The next step can be wiring role lists, password resets, and audit review here.</p>
                </div>
            </section>
            <section id="reportsView" class="view-pane hidden">
                <div class="card">
                    <h3>Export reports</h3>
                    <div class="toolbar">
                        <button class="btn-primary" onclick="downloadReport('daily')">Export Daily</button>
                        <button class="btn-primary" onclick="downloadReport('monthly')">Export Monthly</button>
                        <button class="btn-primary" onclick="downloadReport('airline')">Export Airline</button>
                    </div>
                </div>
            </section>
            <section id="settingsView" class="view-pane hidden">
                <div class="card">
                    <h3>System settings</h3>
                    <p class="muted">Configuration placeholders for workflow defaults and notification preferences.</p>
                </div>
            </section>
        </div>
    </main>
</div>
<script>
    let token = null;
    function showView(view){
        document.querySelectorAll('.view-pane').forEach(s => s.classList.add('hidden'));
        const target = document.getElementById(view + 'View');
        if (target) target.classList.remove('hidden');
        document.querySelectorAll('.nav a').forEach(a => a.classList.toggle('active', a.dataset.view === view));
        document.getElementById('pageTitle').textContent = view.charAt(0).toUpperCase() + view.slice(1);
        document.getElementById('pageLabel').textContent = view === 'dashboard' ? 'Operations overview' : 'Admin workspace';
    }
    document.querySelectorAll('.nav a').forEach(a => a.addEventListener('click', e => { e.preventDefault(); showView(a.dataset.view); }));

    function renderMessage(message, type = 'info') {
        const el = document.getElementById('loginMessage');
        if (!el) return;
        el.textContent = message || '';
        el.style.color = type === 'error' ? '#fda4af' : '#94a3b8';
    }

    async function api(path, options = {}) {
        const headers = {};
        if (token) headers['Authorization'] = 'Bearer ' + token;
        if (options.body) headers['Content-Type'] = 'application/json';
        const response = await fetch('/api' + path, { ...options, headers });
        const data = await response.json().catch(() => null);
        return { response, data };
    }

    async function login(){
        const { response, data } = await api('/admin/login', {
            method: 'POST',
            body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            })
        });
        if (response.ok && data && data.token) {
            token = data.token;
            document.getElementById('login').classList.add('hidden');
            document.getElementById('appContent').classList.remove('hidden');
            showView('dashboard');
            loadDashboard();
            loadRefunds();
            renderMessage('Signed in successfully.');
        } else {
            renderMessage(data?.message || 'Login failed.', 'error');
        }
    }

    async function logout(){
        if (token) {
            await api('/admin/logout', { method: 'POST' });
        }
        token = null;
        document.getElementById('appContent').classList.add('hidden');
        document.getElementById('login').classList.remove('hidden');
        renderMessage('');
    }

    async function loadDashboard(){
        const { data } = await api('/admin/dashboard');
        const stats = document.getElementById('stats');
        if (!data) return;
        stats.innerHTML = `
            <div class='stat'><div class='label'>Total refunds</div><div class='value'>${data.total_refunds || 0}</div></div>
            <div class='stat'><div class='label'>New requests</div><div class='value'>${data.new_requests || 0}</div></div>
            <div class='stat'><div class='label'>Pending commercial</div><div class='value'>${data.pending_commercial || 0}</div></div>
            <div class='stat'><div class='label'>Completed</div><div class='value'>${data.completed || 0}</div></div>
        `;
        const totals = [
            { id: 'newRequests', barId: 'newBar', value: data.new_requests || 0 },
            { id: 'pendingCommercial', barId: 'pendingCommercialBar', value: data.pending_commercial || 0 },
            { id: 'pendingAudit', barId: 'pendingAuditBar', value: data.pending_audit || 0 },
            { id: 'completedCount', barId: 'completedBar', value: data.completed || 0 },
        ];
        const maxValue = Math.max(...totals.map(item => item.value), 1);
        totals.forEach(item => {
            const label = document.getElementById(item.id);
            const bar = document.getElementById(item.barId);
            if (label) label.textContent = item.value;
            if (bar) bar.style.width = `${Math.max(8, Math.round((item.value / maxValue) * 100))}%`;
        });
    }

    async function loadRefunds(){
        const search = document.getElementById('search').value;
        const status = document.getElementById('statusFilter').value;
        const priority = document.getElementById('priorityFilter').value;
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (priority) params.set('priority', priority);
        const { data } = await api('/admin/refunds?' + params.toString());
        const table = document.getElementById('refundTable');
        const recentTable = document.getElementById('recentTable');
        if (!data || !data.data) return;
        const rows = data.data.slice(0, 6).map(item => {
            const statusClass = (item.current_status || '').toLowerCase().includes('complete') ? 'completed' : (item.current_status || '').toLowerCase().includes('reject') ? 'rejected' : 'pending';
            return `<tr class="clickable-row" data-id="${item.id}" data-ref="${item.reference || '-'}" onclick="selectRefund(this)"><td>${item.reference || '-'}</td><td>${(item.first_name || '') + ' ' + (item.last_name || '')}</td><td><span class="pill ${statusClass}">${item.current_status || '-'}</span></td><td>${item.priority || '-'}</td></tr>`;
        }).join('');
        table.innerHTML = '<tr><th>Reference</th><th>Passenger</th><th>Status</th><th>Priority</th></tr>' + rows;
        recentTable.innerHTML = '<tr><th>Reference</th><th>Status</th><th>Priority</th></tr>' + rows;
    }

    function selectRefund(row) {
        const id = row.dataset.id;
        if (!id) return;
        document.getElementById('refundId').value = id;
        document.getElementById('detailBox').textContent = `Loading refund ${row.dataset.ref || id}...`;
        loadRefundDetails();
    }

    async function loadRefundDetails(){
        const id = document.getElementById('refundId').value;
        if (!id) return;
        const { response, data } = await api('/admin/refunds/' + encodeURIComponent(id));
        if (!response.ok || !data) {
            document.getElementById('detailBox').textContent = 'Refund could not be loaded.';
            return;
        }
        const summary = {
            id: data.id,
            reference: data.reference,
            status: data.current_status,
            priority: data.priority,
            department: data.current_department,
            passenger: `${data.first_name || ''} ${data.last_name || ''}`.trim(),
            email: data.email,
            airline: data.airline?.name || null,
            created_at: data.created_at,
        };
        document.getElementById('detailBox').textContent = JSON.stringify(summary, null, 2);
    }

    async function applyAction(action){
        const id = document.getElementById('refundId').value;
        if (!id) return;

        const note = document.getElementById('actionNote').value.trim();
        const payload = action === 'reject'
            ? { reason: note || 'Rejected from admin console.' }
            : { note: note || '' };

        const { response, data } = await api('/admin/refunds/' + encodeURIComponent(id) + '/' + action, {
            method: 'PATCH',
            body: JSON.stringify(payload)
        });

        const message = data?.message || (response.ok ? 'Action applied.' : 'Action could not be completed.');
        document.getElementById('actionFeedback').textContent = message;
        if (response.ok) {
            loadDashboard();
            loadRefunds();
            loadRefundDetails();
        }
    }

    function downloadReport(type){
        window.location.href = '/api/admin/reports/export?type=' + type;
    }
</script>
</body>
</html>
