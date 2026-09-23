<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyRefund Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
<div class="app">
    <aside>
        <div class="brand">
            <img src="{{ asset('image/Refunlogo.jpg') }}"
            alt="SkyRefund Logo"
            class="brand-mark">
            <div>
                <h2>SkyRefund</h2>
                <p>Operations Center</p>
            </div>
        </div>
        <div class="nav">
            <a href="#" class="active" data-view="dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Overview
            </a>
            <a href="#" data-view="refunds">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                Refund List
            </a>
            <a href="#" data-view="details">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg>
                Refund Detail
            </a>
            <a href="#" data-view="users">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                User Ops
            </a>
            <a href="#" data-view="reports">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 12v4"/><path d="M14 10v6"/></svg>
                Reports
            </a>
            <a href="#" data-view="settings">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
            </a>
        </div>
        <div class="sidebar-footer">v1.0 · Admin Console</div>
    </aside>
    <main>
        <section id="login" class="card login-card" style="margin: 3rem auto;">
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
            <div class="topbar">
                <div class="breadcrumb">ADMIN WORKSPACE / <strong id="pageTitle">Overview</strong></div>
                <div class="topbar-right">
                    <button class="icon-btn" title="Search" onclick="focusSearch()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <div class="user-chip">
                        <div class="avatar" id="avatarInitials">--</div>
                        <div class="user-meta">
                            <strong id="adminName">—</strong>
                            <span id="adminRole"></span>
                        </div>
                    </div>
                    <button class="btn-ghost btn-logout" onclick="logout()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </div>
            </div>

            <div class="page-body">

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
                <div class="toolbar">
                    <input id="refundId" placeholder="Refund ID or reference">
                    <button class="btn-primary" onclick="loadRefundDetails()">Open</button>
                </div>

                <!-- Templates: JS relocates these nodes into the built hero / notes panel on load -->
                <div id="actionButtons" class="detail-hero-actions" style="display:none;">
                    <button class="btn-primary" data-action="approve" onclick="applyAction('approve')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Approve
                    </button>
                    <button class="btn-outline-danger" data-action="reject" onclick="applyAction('reject')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Reject
                    </button>
                    <button class="btn-outline-amber" data-action="return" onclick="applyAction('return')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                        Return
                    </button>
                    <button class="btn-ghost" data-action="complete" onclick="applyAction('complete')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="9 12 12 15 16 10"/></svg>
                        Complete
                    </button>
                </div>

                <div id="notesPanelTemplate" style="display:none;">
                    <textarea id="actionNote" maxlength="500" placeholder="Add a note about this refund..." oninput="updateNoteCounter()"></textarea>
                    <div class="note-footer"><span class="note-counter" id="noteCounter">0 / 500</span></div>
                    <button class="btn-addnote" type="button" onclick="stageNote()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Add Note
                    </button>
                    <p class="muted" id="actionFeedback">No workflow action has been run yet.</p>
                </div>

                <div id="detailBox">
                    <div class="card"><p class="muted">Select a refund to inspect its payload.</p></div>
                </div>
            </section>

            <section id="usersView" class="view-pane hidden">
                <div class="card">
                    <div class="card-header">
                        <h3>User operations</h3>
                        <button class="btn-ghost" onclick="loadUsers()">Refresh</button>
                    </div>

                    <table class="data-table" id="userTable">
                        <tr><th colspan="7" class="muted">Loading users...</th></tr>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Add user</h3>
                    </div>
                    <div class="toolbar" id="addUserCard">
                        <input id="newUserName" placeholder="Full name">
                        <input id="newUserEmail" type="email" placeholder="Email">
                        <input id="newUserPassword" type="password" placeholder="Password">
                        <select id="newUserRole"><option value="">Select role</option></select>
                        <select id="newUserDepartment"><option value="">Select department </option></select>
                        
                        <select id="newUserAirline">
                            <option value="">Select airline</option>
                        </select>
                        <button class="btn-primary" onclick="createUser()">Create user</button>
                    </div>
                    <p class="muted" id="createUserStatus"></p>
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
        </div>
    </main>
</div>
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
</body>
</html>
