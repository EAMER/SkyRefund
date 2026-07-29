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
        .toolbar { display:flex; flex-wrap:wrap; gap:.6rem; margin-bottom:.8rem; align-items:center; }
        input, select, button, textarea { padding:0.7rem .8rem; border-radius:10px; border:1px solid var(--border); background: #020617; color: var(--text); font-family: inherit; }
        input:focus, select:focus, button:focus, textarea:focus { outline:2px solid rgba(56,189,248,.3); border-color:var(--accent); }
        button { cursor:pointer; font-weight:700; }
        button:disabled { opacity:.5; cursor:not-allowed; }
        textarea { width:100%; min-height:80px; resize:vertical; }
        .detail-section { margin-bottom: 24px; }
.detail-section-title {
    color: #8fa3b8;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.08em;
    margin-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 6px;
}
.ticket-card {
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 12px;
}
.detail-row-block { flex-direction: column; align-items: flex-start; gap: 4px; }
.detail-value-block { color: #e8f0f7; margin: 0; }
.attachment-list { list-style: none; padding: 0; margin: 0; }
.attachment-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.amount-editor {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-top: 10px;
    flex-wrap: wrap;
}
.amount-input { width: 100px; }
.amount-reason-input { flex: 1; min-width: 150px; }
.amount-status { color: #8fa3b8; font-size: 12px; }
.attachment-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.attachment-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    width: 110px;
}
.attachment-thumb {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1);
    cursor: pointer;
    background: rgba(255,255,255,0.03);
}
.attachment-name {
    font-size: 11px;
    color: #8fa3b8;
    text-align: center;
    word-break: break-word;
}
.attachment-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.attachment-modal-image {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
}
.attachment-modal-close {
    position: absolute;
    top: 24px;
    right: 32px;
    background: none;
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.role-select, .dept-select {
    background: rgba(255,255,255,0.05);
    color: #e8f0f7;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px;
    padding: 4px 6px;
    font-size: 12px;
}
.user-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.audit-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}
.audit-controls select {
    background: rgba(255,255,255,0.05);
    color: #e8f0f7;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 13px;
}
.btn-small { padding: 4px 10px; font-size: 12px; }
.history-list { display: flex; flex-direction: column; gap: 12px; }
.history-entry { border-left: 2px solid rgba(110,231,183,0.4); padding-left: 12px; }
.history-line { color: #e8f0f7; font-weight: 500; }
.history-meta { color: #8fa3b8; font-size: 12px; margin-top: 2px; }
.history-note { color: #c7d5e0; font-size: 13px; margin-top: 4px; font-style: italic; }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #2563eb); color:white; }
        .btn-ghost { background: rgba(255,255,255,.04); color:var(--text); }
        .btn-success { background: rgba(52,211,153,.16); color:#a7f3d0; }
        .btn-danger { background: rgba(251,113,133,.16); color:#fecdd3; }
        .hidden { display:none !important; }
        .pagination { display:flex; gap:.5rem; align-items:center; justify-content:flex-end; margin-top:.8rem; }
        .pagination span { color: var(--muted); font-size:.85rem; }
        .detail-grid {display: flex; flex-direction: column; gap: 10px; }
        .detail-row {display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .detail-label {color: #8fa3b8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
        .detail-value {color: #e8f0f7; font-weight: 500; }
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
        .error-text { color: var(--danger); }
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
            <input id="email" placeholder="Email">
            <input id="password" type="password" placeholder="Password">
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
                        <button class="btn-primary" onclick="loadRefunds(1)">Search</button>
                    </div>
                    <table id="refundTable"></table>
                    <div class="pagination">
                        <button class="btn-ghost" id="prevPageBtn" onclick="changePage(-1)">Prev</button>
                        <span id="pageIndicator">Page 1</span>
                        <button class="btn-ghost" id="nextPageBtn" onclick="changePage(1)">Next</button>
                    </div>
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
                    <div class="toolbar" id="actionButtons">
                        <button class="btn-success" data-action="approve" onclick="applyAction('approve')">Approve</button>
                        <button class="btn-danger" data-action="reject" onclick="applyAction('reject')">Reject</button>
                        <button class="btn-ghost" data-action="return" onclick="applyAction('return')">Return</button>
                        <button class="btn-ghost" data-action="complete" onclick="applyAction('complete')">Complete</button>
                    </div>
                    <pre id="detailBox">Select a refund to inspect its payload.</pre>
                    <div class="muted" id="actionFeedback" style="margin-top:.8rem;">No workflow action has been run yet.</div>
                </div>
            </section>

            <section id="usersView" class="view-pane hidden">
    <div class="card">
        <div class="card-header">
            <h3>User operations</h3>
            <button class="btn-ghost" onclick="loadUsers()">Refresh</button>
        </div>

        <table class="data-table" id="userTable">
            <tr><th colspan="6" class="muted">Loading users...</th></tr>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Audit log</h3>
            <div class="audit-controls">
                <select id="auditUserFilter" onchange="loadAuditLog(this.value || null)">
                    <option value="">All users</option>
                </select>
                <button class="btn-ghost" onclick="loadAuditLog(document.getElementById('auditUserFilter').value || null)">Refresh</button>
            </div>
        </div>
        <div id="auditLogBox">
            <p class="muted">Loading audit log...</p>
        </div>
    </div>
</section>
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
                    <p class="muted" id="reportStatus"></p>
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
    let currentPage = 1;
    let totalPages = 1;
    // Roles allowed to trigger each workflow action. Adjust once RBAC (Phase 5) ships server-side;
    // this is a UI convenience gate only -- the server must enforce permissions independently.
    const ACTION_ROLES = {
        approve: ['refund_officer','commercial', 'audit', 'finance', 'super_admin'],
        reject: ['refund_officer','commercial', 'audit', 'finance', 'super_admin'],
        return: ['refund_officer', 'commercial', 'audit', 'finance', 'super_admin'],
        complete: ['treasury', 'super_admin'],
    };
    let currentAdminRole = null;

    // ---------- Security helper ----------
    // Escapes HTML special characters before interpolating any server-provided
    // string into innerHTML, to prevent stored XSS via passenger-supplied fields
    // (name, reference, email, etc.) rendered in the admin console.
    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

function showView(view){
    document.querySelectorAll('.view-pane').forEach(s => s.classList.add('hidden'));
    const target = document.getElementById(view + 'View');
    if (target) target.classList.remove('hidden');
    document.querySelectorAll('.nav a').forEach(a => a.classList.toggle('active', a.dataset.view === view));
    document.getElementById('pageTitle').textContent = view.charAt(0).toUpperCase() + view.slice(1);
    document.getElementById('pageLabel').textContent = view === 'dashboard' ? 'Operations overview' : 'Admin workspace';

    if (view === 'users') {
        loadUsers();
        loadAuditLog();
    }
}

document.querySelectorAll('.nav a').forEach(a => a.addEventListener('click', e => { e.preventDefault(); showView(a.dataset.view); }));

function renderMessage(message, type = 'info') {
    const el = document.getElementById('loginMessage');
    if (!el) return;
    el.textContent = message || '';
    el.className = type === 'error' ? 'error-text' : 'muted';
}

    function renderMessage(message, type = 'info') {
        const el = document.getElementById('loginMessage');
        if (!el) return;
        el.textContent = message || '';
        el.className = type === 'error' ? 'error-text' : 'muted';
    }

    async function api(path, options = {}) {
        try {
            const headers = { ...(options.headers || {}) };
            if (token) headers['Authorization'] = 'Bearer ' + token;
            if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
            const response = await fetch('/api' + path, { ...options, headers });
            if (response.status === 401) {
                // Token expired or invalid -- force back to login rather than silently failing.
                token = null;
                document.getElementById('appContent').classList.add('hidden');
                document.getElementById('login').classList.remove('hidden');
                renderMessage('Your session expired. Please sign in again.', 'error');
                return { response, data: null };
            }
            let data = null;
            try {
                data = await response.json();
            } catch (_) {
                data = null;
            }
            return { response, data };
        } catch (networkError) {
            console.error('API request failed:', networkError);
            return { response: { ok: false, status: 0 }, data: null };
        }
    }

async function login(){

    const email =
        document.getElementById('email').value.trim();

    const password =
        document.getElementById('password').value;


    if (!email || !password) {

        renderMessage(
            'Enter both email and password.',
            'error'
        );

        return;
    }


    const { response, data } = await api(
        '/v1/admin/login',
        {
            method:'POST',

            body: JSON.stringify({
                email,
                password
            })
        }
    );


    console.log(data);


    if(response.ok && data?.token){

        token = data.token;

        currentAdminRole =
            (data.user?.role || '').toLowerCase() || null;


        document
            .getElementById('login')
            .classList.add('hidden');


        document
            .getElementById('appContent')
            .classList.remove('hidden');


        document
            .getElementById('password')
            .value = '';


        showView('dashboard');

        loadDashboard();

        loadRefunds(1);

        applyRoleGating();


        renderMessage(
            'Signed in successfully.'
        );


    }else{

        renderMessage(
            data?.message ||
            'Login failed.',
            'error'
        );

    }
}

    async function logout(){
        if (token) {
            await api('/v1/admin/logout', { method: 'POST' });
        }
        token = null;
        currentAdminRole = null;
        document.getElementById('appContent').classList.add('hidden');
        document.getElementById('login').classList.remove('hidden');
        renderMessage('');
    }

    // Disables workflow-action buttons the signed-in role isn't permitted to use.
    // This is UI convenience only; the server endpoint must independently enforce
    // authorization regardless of what the client sends.
    function applyRoleGating() {
        document.querySelectorAll('#actionButtons button[data-action]').forEach(btn => {
            const action = btn.dataset.action;
            const allowed = !currentAdminRole || (ACTION_ROLES[action] || []).includes(currentAdminRole);
            btn.disabled = !allowed;
            btn.title = allowed ? '' : 'Your role does not permit this action';
        });
    }

    async function loadDashboard(){
        const { data } = await api('/v1/admin/dashboard');
        const stats = document.getElementById('stats');
        if (!data) return;
        const totalRefunds = Number(data.total_refunds) || 0;
        const newReq = Number(data.new_requests) || 0;
        const pendingCommercial = Number(data.pending_commercial) || 0;
        const pendingAudit = Number(data.pending_audit) || 0;
        const completed = Number(data.completed) || 0;

        stats.innerHTML = `
            <div class='stat'><div class='label'>Total refunds</div><div class='value'>${escapeHtml(totalRefunds)}</div></div>
            <div class='stat'><div class='label'>New requests</div><div class='value'>${escapeHtml(newReq)}</div></div>
            <div class='stat'><div class='label'>Pending commercial</div><div class='value'>${escapeHtml(pendingCommercial)}</div></div>
            <div class='stat'><div class='label'>Completed</div><div class='value'>${escapeHtml(completed)}</div></div>
        `;
        const totals = [
            { id: 'newRequests', barId: 'newBar', value: newReq },
            { id: 'pendingCommercial', barId: 'pendingCommercialBar', value: pendingCommercial },
            { id: 'pendingAudit', barId: 'pendingAuditBar', value: pendingAudit },
            { id: 'completedCount', barId: 'completedBar', value: completed },
        ];
        const maxValue = Math.max(...totals.map(item => item.value), 1);
        totals.forEach(item => {
            const label = document.getElementById(item.id);
            const bar = document.getElementById(item.barId);
            if (label) label.textContent = item.value;
            if (bar) bar.style.width = `${Math.max(8, Math.round((item.value / maxValue) * 100))}%`;
        });
    }

    const ROLE_OPTIONS = ['REFUND_OFFICER', 'COMMERCIAL', 'AUDIT', 'FINANCE', 'TREASURY', 'SUPER_ADMIN'];
const DEPARTMENT_OPTIONS = ['REFUND', 'COMMERCIAL', 'AUDIT', 'FINANCE', 'TREASURY'];

async function loadUsers() {
    const table = document.getElementById('userTable');
    const { response, data } = await api('/v1/admin/users');

    if (!response.ok || !data?.success) {
        table.innerHTML = '<tr><th colspan="6" class="muted">Could not load users.</th></tr>';
        return;
    }

    const header = `
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Department</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    `;

    const rows = data.data.map(buildUserRow).join('');

    table.innerHTML = header + (rows || '<tr><td colspan="6" class="muted">No users found.</td></tr>');

    populateAuditUserFilter(data.data);
}

function populateAuditUserFilter(users) {
    const select = document.getElementById('auditUserFilter');
    if (!select) return;

    const currentValue = select.value;

    const options = users
        .map(u => `<option value="${u.id}">${escapeHtml(u.name)}</option>`)
        .join('');

    select.innerHTML = '<option value="">All users</option>' + options;
    select.value = currentValue;
}
function buildUserRow(user) {
    const roleOptions = ROLE_OPTIONS.map(r =>
        `<option value="${r}" ${user.role === r ? 'selected' : ''}>${formatLabel(r)}</option>`
    ).join('');

    const deptOptions = DEPARTMENT_OPTIONS.map(d =>
        `<option value="${d}" ${user.department === d ? 'selected' : ''}>${formatLabel(d)}</option>`
    ).join('');

    const statusPill = user.active
        ? `<span class="pill completed">Active</span>`
        : `<span class="pill rejected">Inactive</span>`;

    return `
        <tr data-user-id="${user.id}">
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>
                <select class="role-select" data-user-id="${user.id}">
                    ${roleOptions}
                </select>
            </td>
            <td>
                <select class="dept-select" data-user-id="${user.id}">
                    ${deptOptions}
                </select>
            </td>
            <td>${statusPill}</td>
            <td class="user-actions">
                <button class="btn-ghost btn-small" onclick="saveUserRole(${user.id})">Save</button>
                <button class="btn-ghost btn-small" onclick="toggleUserActive(${user.id})">
                    ${user.active ? 'Deactivate' : 'Activate'}
                </button>
                <button class="btn-ghost btn-small" onclick="resetUserPassword(${user.id}, '${escapeHtml(user.email)}')">Reset password</button>
            </td>
        </tr>
    `;
}

async function saveUserRole(userId) {
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    const role = row.querySelector('.role-select').value;
    const department = row.querySelector('.dept-select').value;

    const { response, data } = await api(`/v1/admin/users/${userId}/role`, {
        method: 'PATCH',
        body: JSON.stringify({ role, department }),
    });

    alert(data?.message || (response.ok ? 'Updated.' : 'Update failed.'));
    if (response.ok) loadUsers();
}

async function toggleUserActive(userId) {
    const { response, data } = await api(`/v1/admin/users/${userId}/toggle-active`, {
        method: 'PATCH',
    });

    alert(data?.message || (response.ok ? 'Updated.' : 'Update failed.'));
    if (response.ok) loadUsers();
}

async function resetUserPassword(userId, email) {
    if (!confirm(`Send a password reset email to ${email}?`)) return;

    const { response, data } = await api(`/v1/admin/users/${userId}/reset-password`, {
        method: 'POST',
    });

    alert(data?.message || (response.ok ? 'Password reset.' : 'Reset failed.'));
}

async function loadAuditLog(userId) {
    const box = document.getElementById('auditLogBox');
    box.innerHTML = '<p class="muted">Loading...</p>';

    const params = userId ? `?user_id=${userId}` : '';
    const { response, data } = await api(`/v1/admin/audit-log${params}`);

    if (!response.ok || !data?.success) {
        box.innerHTML = '<p class="muted">Could not load audit log.</p>';
        return;
    }

    box.innerHTML = '';

    if (data.status_changes?.length) {
        box.appendChild(buildAuditGroup('Status changes', data.status_changes, log =>
            `${log.user?.name ?? 'System'} changed status: ${formatLabel(log.old_status) || 'Start'} → ${formatLabel(log.new_status)} on refund ${log.refund?.reference ?? log.refund_id}`
        ));
    }

    if (data.amount_changes?.length) {
        box.appendChild(buildAuditGroup('Amount changes', data.amount_changes, log =>
            `${log.user?.name ?? 'System'} changed refund amount: ${formatMoney(log.old_amount)} → ${formatMoney(log.new_amount)} (${log.reason})`
        ));
    }

    if (!data.status_changes?.length && !data.amount_changes?.length) {
        box.innerHTML = '<p class="muted">No audit activity found.</p>';
    }
}

function buildAuditGroup(title, logs, describe) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-section';
    wrap.appendChild(makeSectionTitle(title));

    const list = document.createElement('div');
    list.className = 'history-list';

    for (const log of logs) {
        const entry = document.createElement('div');
        entry.className = 'history-entry';

        const line = document.createElement('div');
        line.className = 'history-line';
        line.textContent = describe(log);

        const meta = document.createElement('div');
        meta.className = 'history-meta';
        meta.textContent = formatDate(log.created_at);

        entry.append(line, meta);
        list.appendChild(entry);
    }

    wrap.appendChild(list);
    return wrap;
}

    function statusPillClass(status) {
        const s = (status || '').toLowerCase();
        if (s.includes('complete')) return 'completed';
        if (s.includes('reject')) return 'rejected';
        if (s.includes('new')) return 'new';
        return 'pending';
    }

    function buildRefundRow(item) {
        const statusClass = statusPillClass(item.current_status);
        const id = escapeHtml(item.id);
        const ref = escapeHtml(item.reference || '-');
        const name = escapeHtml(`${item.first_name || ''} ${item.last_name || ''}`.trim() || '-');
        const status = escapeHtml(item.current_status || '-');
        const priority = escapeHtml(item.priority || '-');
        return `<tr class="clickable-row" data-id="${id}" data-ref="${ref}" onclick="selectRefund(this)">` +
            `<td>${ref}</td><td>${name}</td><td><span class="pill ${statusClass}">${status}</span></td><td>${priority}</td></tr>`;
    }

    async function loadRefunds(page) {

    if (page) currentPage = page;

    const search = document.getElementById('search').value;
    const status = document.getElementById('statusFilter').value;
    const priority = document.getElementById('priorityFilter').value;

    const params = new URLSearchParams();

    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (priority) params.set('priority', priority);

    params.set('page', currentPage);
    params.set('per_page', '25');

    const { data } = await api('/v1/admin/refunds?' + params.toString());

    console.log(data);

    const table = document.getElementById('refundTable');
    const recentTable = document.getElementById('recentTable');

    // Check response
    if (
        !data ||
        !data.success ||
        !data.data ||
        !Array.isArray(data.data.data)
    ) {

        table.innerHTML =
            '<tr><th colspan="4" class="muted">No refunds could be loaded.</th></tr>';

        recentTable.innerHTML =
            '<tr><th colspan="3" class="muted">No recent activity.</th></tr>';

        return;
    }

    // Refund list
    const refunds = data.data.data;

    // Pagination
    currentPage = data.data.current_page;
    totalPages = data.data.last_page;

    // Refund table
    const allRows = refunds.map(buildRefundRow).join('');

    table.innerHTML =
        `
        <tr>
            <th>Reference</th>
            <th>Passenger</th>
            <th>Status</th>
            <th>Priority</th>
        </tr>
        ` +
        (
            allRows ||
            '<tr><td colspan="4" class="muted">No refunds found.</td></tr>'
        );

    // Dashboard preview
    const recentRows = refunds
        .slice(0, 6)
        .map(buildRefundRow)
        .join('');

    recentTable.innerHTML =
        `
        <tr>
            <th>Reference</th>
            <th>Passenger</th>
            <th>Status</th>
            <th>Priority</th>
        </tr>
        ` +
        (
            recentRows ||
            '<tr><td colspan="4" class="muted">No recent activity.</td></tr>'
        );

    // Pagination buttons
    document.getElementById('pageIndicator').textContent =
        `Page ${currentPage} of ${totalPages}`;

    document.getElementById('prevPageBtn').disabled =
        currentPage <= 1;

    document.getElementById('nextPageBtn').disabled =
        currentPage >= totalPages;
}

    function changePage(delta) {
        const next = currentPage + delta;
        if (next < 1 || next > totalPages) return;
        loadRefunds(next);
    }

    function selectRefund(row) {
        const id = row.dataset.id;
        if (!id) return;
        document.getElementById('refundId').value = id;
        document.getElementById('detailBox').textContent = `Loading refund ${row.dataset.ref || id}...`;
        showView('details');
        loadRefundDetails();
    }

    async function loadRefundDetails(){
    const id = document.getElementById('refundId').value.trim();
    const detailBox = document.getElementById('detailBox');

    if (!id) {
        detailBox.textContent = 'Enter a refund ID or reference to load.';
        return;
    }

    const { response, data } = await api('/v1/admin/refunds/' + encodeURIComponent(id));
    if (!response.ok || !data) {
        detailBox.textContent = 'Refund could not be loaded.';
        return;
    }

    const refund = data.refund;
    detailBox.innerHTML = '';

    detailBox.appendChild(buildSummarySection(refund));

    if (refund.analyses?.length) {
        detailBox.appendChild(buildAiSection(refund.analyses[refund.analyses.length - 1]));
    }

    if (refund.tickets?.length) {
        detailBox.appendChild(buildTicketsSection(refund.tickets , refund.id));
    }

    if (refund.attachments?.length) {
        detailBox.appendChild(buildAttachmentsSection(refund.attachments, refund.id));
    }

    if (refund.status_logs?.length) {
        detailBox.appendChild(buildHistorySection(refund.status_logs));
    }
}

function makeSectionTitle(text) {
    const h = document.createElement('h4');
    h.className = 'detail-section-title';
    h.textContent = text;
    return h;
}

function buildSummarySection(refund) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-section';
    wrap.appendChild(makeSectionTitle('Summary'));

    const grid = document.createElement('div');
    grid.className = 'detail-grid';

    const rows = [
        ['Reference', refund.reference],
        ['Status', formatLabel(refund.current_status)],
        ['Priority', formatLabel(refund.priority)],
        ['Department', formatLabel(refund.current_department)],
        ['Passenger', `${refund.first_name} ${refund.last_name}`],
        ['Email', refund.email],
        ['Phone', refund.phone],
        ['Airline', refund.airline?.name ?? '—'],
        ['Assigned to', refund.assignee?.name ?? 'Unassigned'],
        ['Created', formatDate(refund.created_at)],
    ];

    for (const [label, value] of rows) {
        grid.appendChild(makeDetailRow(label, value));
    }

    wrap.appendChild(grid);
    return wrap;
}

function buildAiSection(analysis) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-section';
    wrap.appendChild(makeSectionTitle('AI Risk Analysis'));

    const grid = document.createElement('div');
    grid.className = 'detail-grid';

    grid.appendChild(makeDetailRow('Risk score', analysis.risk_score ?? '—'));
    grid.appendChild(makeDetailRow('Flags', (analysis.flags || []).map(formatLabel).join(', ') || 'None'));

    if (analysis.reasoning) {
        const reasoningRow = document.createElement('div');
        reasoningRow.className = 'detail-row detail-row-block';
        const label = document.createElement('span');
        label.className = 'detail-label';
        label.textContent = 'Reasoning';
        const value = document.createElement('p');
        value.className = 'detail-value-block';
        value.textContent = analysis.reasoning;
        reasoningRow.append(label, value);
        grid.appendChild(reasoningRow);
    }

    wrap.appendChild(grid);
    return wrap;
}

function buildTicketsSection(tickets, refundId) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-section';
    wrap.appendChild(makeSectionTitle(`Tickets (${tickets.length})`));

    for (const ticket of tickets) {
        const card = document.createElement('div');
        card.className = 'ticket-card';

        const rows = [
            ['Booking ref', ticket.booking_reference],
            ['Ticket number', ticket.ticket_number],
            ['Passenger', ticket.passenger_name],
            ['Flight', ticket.flight_number],
            ['Route', `${ticket.origin_airport} → ${ticket.destination_airport}`],
            ['Departure', formatDate(ticket.departure_datetime)],
            ['Cabin', formatLabel(ticket.cabin_class)],
            ['Fare paid', formatMoney(ticket.fare_paid, ticket.currency)],
            ['Status', formatLabel(ticket.ticket_status)],
        ];

        for (const [label, value] of rows) {
            card.appendChild(makeDetailRow(label, value));
        }

        card.appendChild(buildAmountEditor(refundId, ticket));

        if (ticket.remarks) {
            card.appendChild(makeDetailRow('Remarks', ticket.remarks));
        }

        wrap.appendChild(card);
    }

    return wrap;
}

function buildAmountEditor(refundId, ticket) {
    const row = document.createElement('div');
    row.className = 'amount-editor';

    const label = document.createElement('span');
    label.className = 'detail-label';
    label.textContent = 'Refund amount';

    const input = document.createElement('input');
    input.type = 'number';
    input.step = '0.01';
    input.min = '0';
    input.max = ticket.fare_paid;
    input.value = ticket.refund_amount;
    input.className = 'amount-input';

    const reasonInput = document.createElement('input');
    reasonInput.type = 'text';
    reasonInput.placeholder = 'Reason for change';
    reasonInput.className = 'amount-reason-input';

    const saveBtn = document.createElement('button');
    saveBtn.type = 'button';
    saveBtn.className = 'btn-ghost btn-small';
    saveBtn.textContent = 'Save';

    const status = document.createElement('span');
    status.className = 'amount-status';

    saveBtn.addEventListener('click', async () => {
        const amount = parseFloat(input.value);
        const reason = reasonInput.value.trim();

        if (isNaN(amount) || amount < 0) {
            status.textContent = 'Enter a valid amount.';
            return;
        }
        if (reason.length < 5) {
            status.textContent = 'Reason must be at least 5 characters.';
            return;
        }

        status.textContent = 'Saving...';

        const { response, data } = await api(
            `/v1/admin/refunds/${refundId}/tickets/${ticket.id}/amount`,
            {
                method: 'PATCH',
                body: JSON.stringify({ amount, reason }),
            }
        );

        status.textContent = data?.message || (response.ok ? 'Updated.' : 'Update failed.');
        if (response.ok) {
            reasonInput.value = '';
        }
    });

    row.append(label, input, reasonInput, saveBtn, status);
    return row;
}
function buildAttachmentsSection(attachments, refundId) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-section';
    wrap.appendChild(makeSectionTitle(`Attachments (${attachments.length})`));

    const grid = document.createElement('div');
    grid.className = 'attachment-grid';

    for (const att of attachments) {
        const item = document.createElement('div');
        item.className = 'attachment-item';

        const label = document.createElement('span');
        label.className = 'attachment-name';
        label.textContent = att.original_name || `Attachment #${att.id}`;

        const isImage = (att.mime_type || '').startsWith('image/');

        if (isImage) {
            const thumb = document.createElement('img');
            thumb.className = 'attachment-thumb';
            thumb.alt = att.original_name || 'Attachment preview';
            loadAttachmentBlob(refundId, att.id).then(url => {
                if (url) thumb.src = url;
            });
            thumb.addEventListener('click', () => openAttachmentModal(refundId, att));
            item.append(thumb, label);
        } else {
            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.className = 'btn-ghost btn-small';
            viewBtn.textContent = 'View';
            viewBtn.addEventListener('click', () => openAttachmentInNewTab(refundId, att.id));
            item.append(label, viewBtn);
        }

        grid.appendChild(item);
    }

    wrap.appendChild(grid);
    return wrap;
}

async function loadAttachmentBlob(refundId, attachmentId) {
    try {
        const headers = {};
        if (token) headers['Authorization'] = 'Bearer ' + token;
        const response = await fetch(`/api/v1/admin/refunds/${refundId}/attachments/${attachmentId}`, { headers });
        if (!response.ok) return null;
        const blob = await response.blob();
        return URL.createObjectURL(blob);
    } catch (err) {
        console.error('Attachment preview failed:', err);
        return null;
    }
}

async function openAttachmentInNewTab(refundId, attachmentId) {
    const url = await loadAttachmentBlob(refundId, attachmentId);
    if (!url) {
        alert('Could not load attachment.');
        return;
    }
    window.open(url, '_blank');
}

async function openAttachmentModal(refundId, attachment) {
    const url = await loadAttachmentBlob(refundId, attachment.id);
    if (!url) {
        alert('Could not load attachment.');
        return;
    }

    const overlay = document.createElement('div');
    overlay.className = 'attachment-modal-overlay';

    const img = document.createElement('img');
    img.className = 'attachment-modal-image';
    img.src = url;
    img.alt = attachment.original_name || 'Attachment';

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'attachment-modal-close';
    closeBtn.textContent = '✕';
    closeBtn.addEventListener('click', () => overlay.remove());

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.remove();
    });

    overlay.append(img, closeBtn);
    document.body.appendChild(overlay);
}

function buildHistorySection(logs) {
    const wrap = document.createElement('div');
    wrap.className = 'detail-section';
    wrap.appendChild(makeSectionTitle('Status history'));

    const list = document.createElement('div');
    list.className = 'history-list';

    for (const log of logs) {
        const entry = document.createElement('div');
        entry.className = 'history-entry';

        const line = document.createElement('div');
        line.className = 'history-line';
        line.textContent = `${formatLabel(log.old_status) || 'Start'} → ${formatLabel(log.new_status)}`;

        const meta = document.createElement('div');
        meta.className = 'history-meta';
        meta.textContent = `${log.user?.name ?? 'System'} · ${formatDate(log.created_at)}`;

        entry.append(line, meta);

        if (log.note) {
            const note = document.createElement('div');
            note.className = 'history-note';
            note.textContent = log.note;
            entry.appendChild(note);
        }

        list.appendChild(entry);
    }

    wrap.appendChild(list);
    return wrap;
}

function makeDetailRow(label, value) {
    const row = document.createElement('div');
    row.className = 'detail-row';

    const labelEl = document.createElement('span');
    labelEl.className = 'detail-label';
    labelEl.textContent = label;

    const valueEl = document.createElement('span');
    valueEl.className = 'detail-value';
    valueEl.textContent = (value === null || value === undefined || value === '') ? '—' : value;

    row.append(labelEl, valueEl);
    return row;
}

function formatMoney(value, currency) {
    if (value === null || value === undefined) return '—';
    const num = Number(value);
    return `${currency ?? ''} ${num.toFixed(2)}`.trim();
}

function formatLabel(value) {
    if (!value) return '—';
    return value.toString().toLowerCase().replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleString(undefined, {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}


    async function applyAction(action){
        const id = document.getElementById('refundId').value.trim();
        if (!id) {
            document.getElementById('actionFeedback').textContent = 'Load a refund before applying an action.';
            return;
        }

        const note = document.getElementById('actionNote').value.trim();
        const payload = action === 'reject'
            ? { reason: note || 'Rejected from admin console.' }
            : { note: note || '' };

        const { response, data } = await api('/v1/admin/refunds/' + encodeURIComponent(id) + '/' + action, {
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

    // Fixed: previously used window.location.href, which drops the Authorization
    // header on navigation and would 401 against a Bearer-token-protected export
    // endpoint. Now fetches with the token, then triggers a client-side download
    // from the returned blob.
    async function downloadReport(type){
        const statusEl = document.getElementById('reportStatus');
        statusEl.textContent = 'Preparing export...';
        try {
            const headers = {};
            if (token) headers['Authorization'] = 'Bearer ' + token;
            const response = await fetch('/api/v1/admin/reports/export?type=' + encodeURIComponent(type), { headers });
            if (!response.ok) {
                statusEl.textContent = `Export failed (status ${response.status}).`;
                return;
            }
            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename="?([^"]+)"?/);
            const filename = match ? match[1] : `skyrefund-${type}-report`;

            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            statusEl.textContent = `Export ready: ${filename}`;
        } catch (err) {
            console.error('Report export failed:', err);
            statusEl.textContent = 'Export failed due to a network error.';
        }
    }
</script>
</body>
</html> 