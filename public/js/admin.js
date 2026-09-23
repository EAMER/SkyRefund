let token = null;
    let currentPage = 1;
    let totalPages = 1;
    let refundDetailsRequestId = 0;
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

function populateNewUserSelects() {
    const roleSelect = document.getElementById('newUserRole');
    const deptSelect = document.getElementById('newUserDepartment');

    roleSelect.innerHTML = '<option value="">Select role</option>' +
        ROLE_OPTIONS.map(r => `<option value="${r}">${formatLabel(r)}</option>`).join('');

    deptSelect.innerHTML = '<option value="">Select department (optional)</option>' +
        DEPARTMENT_OPTIONS.map(d => `<option value="${d}">${formatLabel(d)}</option>`).join('');
}


async function createUser() {
    const status = document.getElementById('createUserStatus');

    const name = document.getElementById('newUserName').value.trim();
    const email = document.getElementById('newUserEmail').value.trim();
    const password = document.getElementById('newUserPassword').value;
    const role = document.getElementById('newUserRole').value;
    const department = document.getElementById('newUserDepartment').value;
    const airlineId = document.getElementById('newUserAirline').value;

    // Validate required fields
    if (!name || !email || !password || !role || !airlineId) {
        status.textContent =
            'Name, email, password, role, and airline are required.';
        return;
    }

    // Validate password
    if (password.length < 8) {
        status.textContent =
            'Password must be at least 8 characters.';
        return;
    }

    status.textContent = 'Creating...';

    const payload = {
        name: name,
        email: email,
        password: password,
        role: role,
        department: department || null,
        airline_id: Number(airlineId)
    };

    console.log("========== CREATE USER DEBUG ==========");
    console.log("airlineId:", airlineId);
    console.log("airlineId type:", typeof airlineId);
    console.log("payload object:", payload);
    console.log("payload JSON:", JSON.stringify(payload));
    console.log("========================================");

    console.log('Creating user with:', payload);

    const { response, data } = await api('/v1/admin/users', {
        method: 'POST',
        body: JSON.stringify(payload),
    });

    console.log('Create user response:', data);

    if (response.ok) {
        status.textContent = 'User created.';

        // Clear form
        document.getElementById('newUserName').value = '';
        document.getElementById('newUserEmail').value = '';
        document.getElementById('newUserPassword').value = '';

        document.getElementById('newUserRole').value = '';
        document.getElementById('newUserDepartment').value = '';
        document.getElementById('newUserAirline').value = '';

        // Refresh users table
        loadUsers();

    } else {
        const firstError = data?.errors
            ? Object.values(data.errors)[0]?.[0]
            : null;

        status.textContent =
            firstError ||
            data?.message ||
            'Could not create user.';
    }
}
const VIEW_LABELS = {
    dashboard: 'Overview',
    refunds: 'Refund List',
    details: 'Refund Detail',
    users: 'User Ops',
    reports: 'Reports',
    settings: 'Settings',
};

function showView(view){
    document.querySelectorAll('.view-pane').forEach(s => s.classList.add('hidden'));
    const target = document.getElementById(view + 'View');
    if (target) target.classList.remove('hidden');
    document.querySelectorAll('.nav a').forEach(a => a.classList.toggle('active', a.dataset.view === view));
    const pageTitleEl = document.getElementById('pageTitle');
    if (pageTitleEl) pageTitleEl.textContent = VIEW_LABELS[view] || (view.charAt(0).toUpperCase() + view.slice(1));

    if (view === 'users') {
        loadUsers();
        loadAuditLog();
        populateNewUserSelects();
        populateAirlineSelect();
    }
}

document.querySelectorAll('.nav a').forEach(a => a.addEventListener('click', e => { e.preventDefault(); showView(a.dataset.view); }));

// Focuses the refund search box; jumps to the Refund List view first if needed.
function focusSearch() {
    showView('refunds');
    const el = document.getElementById('search');
    if (el) el.focus();
}

// Populates the topbar avatar/name/role chip after a successful login.
function setUserHeader(user) {
    const name = user?.name || 'Admin';
    const role = user?.role || '';
    const initials = name.split(/\s+/).filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('') || 'A';

    const avatarEl = document.getElementById('avatarInitials');
    const nameEl = document.getElementById('adminName');
    const roleEl = document.getElementById('adminRole');

    if (avatarEl) avatarEl.textContent = initials;
    if (nameEl) nameEl.textContent = name;
    if (roleEl) roleEl.textContent = formatLabel(role);
}

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
        const headers = {
            'Accept': 'application/json',
            ...(options.headers || {}),
        };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        if (options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json';
        const response = await fetch('/api' + path, { ...options, headers });
        if (response.status === 401) {
            // Token expired or invalid -- force back to login rather than silently failing.
            token = null;
            localStorage.removeItem('skyrefund_token');
            localStorage.removeItem('skyrefund_user');
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
 
function buildCalculationEditor(ticket) {
    const wrap = document.createElement('div');
    wrap.className = 'calculation-ticket';
    wrap.dataset.ticketId = ticket.id;

    const money = (name, value, opts = {}) => {
        const row = document.createElement('div');
        row.className = 'calc-row' + (opts.span2 ? ' span-2' : '');

        const label = document.createElement('label');
        label.textContent = opts.label || formatLabel(name.replace(/_/g, ' '));
        row.appendChild(label);

        const currency = document.createElement('div');
        currency.className = 'calc-currency' + (opts.fare ? ' fare' : '');
        if (opts.disabledByDefault) currency.classList.add('disabled');

        const prefix = document.createElement('span');
        prefix.className = 'prefix';
        prefix.textContent = ticket.currency || 'NGN';
        currency.appendChild(prefix);

        const input = document.createElement('input');
        input.type = 'number';
        input.step = '0.01';
        input.min = '0';
        input.className = `input-${name}`;
        input.value = value;
        if (opts.disabledByDefault) input.disabled = true;
        currency.appendChild(input);

        row.appendChild(currency);
        return { row, input, currencyWrap: currency };
    };

    const fare = money('fare_paid', ticket.fare_paid, { span2: true, fare: true, label: 'Fare paid' });
    wrap.appendChild(fare.row);

    const nuc = money('nuc', ticket.nuc ?? 0, { label: 'NUC' });
    wrap.appendChild(nuc.row);

    const govTax = money('government_tax_ng', ticket.government_tax_ng ?? 0, { label: 'Government tax (NG)' });
    wrap.appendChild(govTax.row);

    const secTax = money('security_tax_yq', ticket.security_tax_yq ?? 0, { label: 'Security tax (YQ)' });
    wrap.appendChild(secTax.row);

    const airportTax = money('airport_tax_qt', ticket.airport_tax_qt ?? 0, { label: 'Airport tax (QT)' });
    wrap.appendChild(airportTax.row);

    const insurance = money('insurance', ticket.insurance ?? 0, { span2: true, label: 'Insurance' });
    wrap.appendChild(insurance.row);

    // No-show toggle, styled as a switch, enables/disables the fee field
    const toggleRow = document.createElement('div');
    toggleRow.className = 'calc-row span-2';
    const toggleWrap = document.createElement('div');
    toggleWrap.className = 'calc-toggle-row';

    const toggleText = document.createElement('div');
    toggleText.className = 'calc-toggle-text';
    const toggleTitle = document.createElement('span');
    toggleTitle.textContent = 'No-show';
    const toggleSub = document.createElement('span');
    toggleSub.textContent = 'Applies the no-show fee to the deduction total';
    toggleText.append(toggleTitle, toggleSub);

    const switchLabel = document.createElement('label');
    switchLabel.className = 'calc-switch';
    const noShowInput = document.createElement('input');
    noShowInput.type = 'checkbox';
    noShowInput.className = 'input-is_no_show';
    noShowInput.checked = Boolean(ticket.is_no_show);
    const track = document.createElement('span');
    track.className = 'track';
    const thumb = document.createElement('span');
    thumb.className = 'thumb';
    track.appendChild(thumb);
    switchLabel.append(noShowInput, track);

    toggleWrap.append(toggleText, switchLabel);
    toggleRow.appendChild(toggleWrap);
    wrap.appendChild(toggleRow);

    const noShowFee = money('no_show_fee', ticket.no_show_fee ?? 0, {
        span2: true, label: 'No-show fee', disabledByDefault: !ticket.is_no_show,
    });
    wrap.appendChild(noShowFee.row);

    noShowInput.addEventListener('change', () => {
        noShowFee.currencyWrap.classList.toggle('disabled', !noShowInput.checked);
        noShowFee.input.disabled = !noShowInput.checked;
        recalcTicketSummary(wrap);
    });

    // Live summary strip
    const summary = document.createElement('div');
    summary.className = 'calc-summary';
    summary.innerHTML = `
        <div class="cell deduction">
            <div class="k">Total deduction</div>
            <div class="v" data-role="deduction">${formatMoney(0, ticket.currency)}</div>
        </div>
        <div class="cell refund">
            <div class="k">Refund due</div>
            <div class="v" data-role="refund">${formatMoney(0, ticket.currency)}</div>
        </div>
    `;
    wrap.appendChild(summary);

    [fare, nuc, govTax, secTax, airportTax, insurance, noShowFee].forEach(field => {
        field.input.addEventListener('input', () => recalcTicketSummary(wrap));
    });

    recalcTicketSummary(wrap);

    return wrap;
}

// Recomputes the total-deduction / refund-due summary for one ticket's
// calculation card as the officer types. Mirrors RefundTicket::recalculateDeduction()
// server-side — this is a UI preview only; the server value is authoritative.
function recalcTicketSummary(wrap) {
    const num = (name) => parseFloat(wrap.querySelector(`.input-${name}`)?.value) || 0;
    const isNoShow = wrap.querySelector('.input-is_no_show')?.checked;

    const deduction = num('nuc') + num('government_tax_ng') + num('security_tax_yq')
        + num('airport_tax_qt') + num('insurance') + (isNoShow ? num('no_show_fee') : 0);
    const refund = Math.max(0, num('fare_paid') - deduction);

    const currencyEl = wrap.querySelector('.calc-currency.fare .prefix');
    const currency = currencyEl ? currencyEl.textContent : 'NGN';

    const deductionEl = wrap.querySelector('[data-role="deduction"]');
    const refundEl = wrap.querySelector('[data-role="refund"]');
    if (deductionEl) deductionEl.textContent = formatMoney(deduction, currency);
    if (refundEl) refundEl.textContent = formatMoney(refund, currency);
}
async function populateAirlineSelect() {
    const select = document.getElementById('newUserAirline');
    const { response, data } = await api('/v1/admin/airlines');
    if (!response.ok || !data?.success) return;
    select.innerHTML = '<option value="">Select airline</option>' +
        data.data.map(a => `<option value="${a.id}">${escapeHtml(a.name)}</option>`).join('');
}
 
function populateNewUserSelects() {
    const roleSelect = document.getElementById('newUserRole');
    const deptSelect = document.getElementById('newUserDepartment');

    roleSelect.innerHTML = '<option value="">Select role</option>' +
        ROLE_OPTIONS.map(r => `<option value="${r}">${formatLabel(r)}</option>`).join('');

    deptSelect.innerHTML = '<option value="">Select department (optional)</option>' +
        DEPARTMENT_OPTIONS.map(d => `<option value="${d}">${formatLabel(d)}</option>`).join('');
}

// Single `createUser` implementation kept earlier in file; duplicates removed.
/**
 * Replace your existing login() function with this.
 */

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

        // Persist so a reload doesn't force a fresh login.
        localStorage.setItem('skyrefund_token', token);
        localStorage.setItem('skyrefund_user', JSON.stringify(data.user));

        setUserHeader(data.user);

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

        loadUsers();


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


/**
 * Replace your existing logout() function with this.
 */

async function logout(){
    if (token) {
        await api('/v1/admin/logout', { method: 'POST' });
    }
    token = null;
    currentAdminRole = null;
    localStorage.removeItem('skyrefund_token');
    localStorage.removeItem('skyrefund_user');
    document.getElementById('appContent').classList.add('hidden');
    document.getElementById('login').classList.remove('hidden');
    renderMessage('');
    setUserHeader(null);
}


/**
 * New function — add this anywhere in the file (near login/logout makes
 * sense). Restores a session from localStorage on page load, validating
 * against GET /v1/admin/me rather than trusting the cached copy blindly.
 */

async function restoreSession() {
    const savedToken = localStorage.getItem('skyrefund_token');
    if (!savedToken) return;

    token = savedToken;

    const { response, data } = await api('/v1/admin/me');

    if (!response.ok || !data?.user) {
        token = null;
        localStorage.removeItem('skyrefund_token');
        localStorage.removeItem('skyrefund_user');
        return;
    }

    currentAdminRole = (data.user.role || '').toLowerCase() || null;
    localStorage.setItem('skyrefund_user', JSON.stringify(data.user));
    setUserHeader(data.user);

    document.getElementById('login').classList.add('hidden');
    document.getElementById('appContent').classList.remove('hidden');

    showView('dashboard');
    loadDashboard();
    loadRefunds(1);
    applyRoleGating();
    loadUsers();
}


/**
 * Find your existing DOMContentLoaded listener at the bottom of the file
 * (it currently just calls loadAdminAirlines()) and add restoreSession()
 * to it, like this:
 */

document.addEventListener("DOMContentLoaded", function () {
    loadAdminAirlines();
    restoreSession();
});
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
    const userOpsAllowed = ['refund_officer', 'super_admin'].includes(currentAdminRole);
    const userOpsLink = document.querySelector('.nav a[data-view="users"]');
    if (userOpsLink) {
        userOpsLink.style.display = userOpsAllowed ? '' : 'none';
    }

    const addUserCard = document.getElementById('addUserCard');
    if (addUserCard) {
        addUserCard.style.display = currentAdminRole === 'super_admin' ? '' : 'none';
    }
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
const DEPARTMENT_OPTIONS = ['refund', 'commercial', 'audit', 'finance', 'treasury'];

async function loadUsers() {
    const table = document.getElementById('userTable');
    const { response, data } = await api('/v1/admin/users');

    if (!response.ok || !data?.success) {
        table.innerHTML = '<tr><th colspan="7" class="muted">Could not load users.</th></tr>';
        return;
    }

    const header = `
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Airline</th>
        <th>Role</th>
        <th>Department</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
`;
    const rows = data.data.map(buildUserRow).join('');

    table.innerHTML = header + (rows || '<tr><td colspan="7" class="muted">No users found.</td></tr>');

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
 
    const airlineName = user.airline?.name ? escapeHtml(user.airline.name) : '—';
 
    return `
        <tr data-user-id="${user.id}">
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${airlineName}</td>
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

async function loadAdminAirlines() {
    console.log("=== LOAD AIRLINES STARTED ===");

    const select = document.getElementById("newUserAirline");

    console.log("Airline select:", select);

    if (!select) {
        console.error("ERROR: #newUserAirline was not found!");
        return;
    }

    try {
        const response = await fetch(
            "http://127.0.0.1:8000/api/v1/airlines",
            {
                method: "GET",
                headers: {
                    "Accept": "application/json"
                }
            }
        );

        console.log("Airline API status:", response.status);

        const result = await response.json();

        console.log("Airline API response:", result);

        if (!response.ok) {
            throw new Error(result.message || "Failed to load airlines");
        }

        select.innerHTML = '<option value="">Select airline</option>';

        if (!result.data || !Array.isArray(result.data)) {
            console.error("Invalid airline response:", result);
            return;
        }

        result.data.forEach(airline => {

            console.log(
                "Adding airline:",
                airline.id,
                airline.name
            );

            const option = document.createElement("option");

            option.value = airline.id;
            option.textContent = airline.name;

            select.appendChild(option);
        });

        console.log(
            "Airlines loaded:",
            select.options.length - 1
        );

    } catch (error) {

        console.error("LOAD AIRLINES ERROR:", error);

        select.innerHTML =
            '<option value="">Unable to load airlines</option>';
    }
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
        detailBox.innerHTML = '<div class="card"><p class="muted">Enter a refund ID or reference to load.</p></div>';
        return;
    }

    // Tag this call so a slower, older request can't overwrite the DOM
    // after a newer one has already rendered.
    const requestId = ++refundDetailsRequestId;

    detailBox.innerHTML = '<div class="card"><p class="muted">Loading refund...</p></div>';

    const { response, data } = await api('/v1/admin/refunds/' + encodeURIComponent(id));

    // A newer load started while this one was still in flight — this
    // response is stale, discard it instead of rendering with it.
    if (requestId !== refundDetailsRequestId) {
        return;
    }

    if (!response.ok || !data) {
        detailBox.innerHTML = '<div class="card"><p class="error-text">Refund could not be loaded.</p></div>';
        return;
    }

    const refund = data.refund;

    // These two nodes get moved *into* detailBox's children each time it's
    // built (so their onclick/oninput handlers keep working). Rescue them
    // back out to the section root first, or clearing detailBox below would
    // delete them along with everything else.
    const detailsSection = document.getElementById('detailsView');
    const actionButtonsNode = document.getElementById('actionButtons');
    const notesTemplateNode = document.getElementById('notesPanelTemplate');
    if (detailsSection && actionButtonsNode) detailsSection.appendChild(actionButtonsNode);
    if (detailsSection && notesTemplateNode) detailsSection.appendChild(notesTemplateNode);

    detailBox.innerHTML = '';

    detailBox.appendChild(buildDetailHero(refund));

    const twoCol = document.createElement('div');
    twoCol.className = 'detail-two-col';
    twoCol.appendChild(buildSummarySection(refund));
    twoCol.appendChild(buildNotesPanel(refund));
    detailBox.appendChild(twoCol);

    if (refund.analyses?.length) {
        detailBox.appendChild(buildAiSection(refund.analyses[refund.analyses.length - 1]));
    }

    if (refund.tickets?.length) {
        detailBox.appendChild(buildTicketsSection(refund.tickets, refund.id, refund.current_status));
    }
    detailBox.appendChild(buildPaymentConfirmSection(refund));

    if (refund.attachments?.length) {
        detailBox.appendChild(buildAttachmentsSection(refund.attachments, refund.id));
    }

    applyRoleGating();
    applyStatusGating(refund);
    updateNoteCounter();
}

const STATUS_ACTIONS = {
    NEW_REQUEST: ['reject'],
    RETURNED_BY_COMMERCIAL: [ 'reject'],
    RETURNED_BY_AUDIT: ['reject'],
    RETURNED_BY_FINANCE: ['reject'],
    PENDING_COMMERCIAL: ['approve', 'return', 'reject'],
    PENDING_AUDIT: ['approve', 'return', 'reject'],
    PENDING_FINANCE: ['approve', 'return', 'reject'],
    PENDING_TREASURY: ['reject'],
    REFUND_COMPLETED: [],
    REJECTED: [],
    CANCELLED: [],
};

// Statuses where the officer's calculation form is the right thing to show
// (first submission, or correcting/resubmitting after any department sent
// it back) — everything else shows the top action bar instead.
const CALCULATION_EDITABLE_STATUSES = [
    'NEW_REQUEST',
    'RETURNED_BY_COMMERCIAL',
    'RETURNED_BY_AUDIT',
    'RETURNED_BY_FINANCE',
];

function applyStatusGating(refund) {
    const status = refund.current_status;
    const allowed = STATUS_ACTIONS[status] || [];

    // ---------------------------------------------------------
    // Action buttons
    // ---------------------------------------------------------
    document
        .querySelectorAll('#actionButtons button[data-action]')
        .forEach(btn => {
            const action = btn.dataset.action;

            btn.classList.toggle(
                'hidden',
                !allowed.includes(action)
            );
        });


    // ---------------------------------------------------------
    // Calculation permissions
    // ---------------------------------------------------------
    const editable = CALCULATION_EDITABLE_STATUSES.includes(status);


    // ---------------------------------------------------------
    // Calculation section
    //
    // IMPORTANT:
    // The calculation is ALWAYS visible.
    // Only the Refund Officer's statuses allow editing.
    // ---------------------------------------------------------
    document
        .querySelectorAll('.calculation-ticket')
        .forEach(el => {

            // Always show calculation
            el.classList.remove('hidden');

            // Add/remove readonly styling
            el.classList.toggle(
                'calc-readonly',
                !editable
            );


            // Enable/disable calculation inputs
            el.querySelectorAll('input, select, textarea')
                .forEach(input => {
                    input.disabled = !editable;
                });


            // -------------------------------------------------
            // No-show fee has its own conditional logic
            // -------------------------------------------------
            if (editable) {

                const noShowChecked =
                    el.querySelector('.input-is_no_show')?.checked;

                const feeInput =
                    el.querySelector('.input-no_show_fee');

                const feeWrap =
                    feeInput?.closest('.calc-currency');


                if (feeInput) {
                    feeInput.disabled = !noShowChecked;
                }


                if (feeWrap) {
                    feeWrap.classList.toggle(
                        'disabled',
                        !noShowChecked
                    );
                }
            }
        });


    // ---------------------------------------------------------
    // Save Draft / Submit Calculation controls
    //
    // These are only available when the Refund Officer
    // is allowed to edit the calculation.
    // ---------------------------------------------------------
    document
        .querySelectorAll('.calculation-controls')
        .forEach(el => {
            el.classList.toggle(
                'hidden',
                !editable
            );
        });


    // ---------------------------------------------------------
    // Treasury payment confirmation
    // ---------------------------------------------------------
    document
        .getElementById('paymentConfirmCard')
        ?.classList.toggle(
            'hidden',
            status !== 'PENDING_TREASURY'
        );
}
// Builds the reference/status/priority header row and reattaches the
// (single, template) action-button node so its onclick handlers keep working.
function buildDetailHero(refund) {
    const hero = document.createElement('div');
    hero.className = 'detail-hero';

    const titleWrap = document.createElement('div');

    const titleRow = document.createElement('div');
    titleRow.className = 'detail-hero-title';

    const h1 = document.createElement('h1');
    h1.textContent = refund.reference || `Refund #${refund.id}`;
    titleRow.appendChild(h1);

    const statusPill = document.createElement('span');
    statusPill.className = `pill ${statusPillClass(refund.current_status)}`;
    statusPill.textContent = formatLabel(refund.current_status);
    titleRow.appendChild(statusPill);

    if (refund.priority) {
        const priorityPill = document.createElement('span');
        priorityPill.className = 'pill priority-pill';
        priorityPill.textContent = formatLabel(refund.priority);
        titleRow.appendChild(priorityPill);
    }

    titleWrap.appendChild(titleRow);

    const sub = document.createElement('div');
    sub.className = 'detail-hero-sub';
    sub.textContent = `Department: ${formatLabel(refund.current_department)} · Passenger: ${refund.first_name || ''} ${refund.last_name || ''}`.trim();
    titleWrap.appendChild(sub);

    hero.appendChild(titleWrap);

    const actionButtons = document.getElementById('actionButtons');
    if (actionButtons) {
        actionButtons.style.display = '';
        hero.appendChild(actionButtons);
    }

    return hero;
}

function buildPaymentConfirmSection(refund) {
    const wrap = document.createElement('div');
    wrap.className = 'card payment-confirm-card';
    wrap.id = 'paymentConfirmCard';
    wrap.classList.toggle('hidden', refund.current_status !== 'PENDING_TREASURY');
 
    const title = document.createElement('h3');
    title.className = 'detail-card-title';
    title.textContent = 'Confirm payment';
    wrap.appendChild(title);
 
    const fieldRow = document.createElement('div');
    fieldRow.className = 'field-row';
 
    const refField = document.createElement('div');
    const refLabel = document.createElement('label');
    refLabel.textContent = 'Payment reference';
    const refInput = document.createElement('input');
    refInput.type = 'text';
    refInput.id = 'paymentReference';
    refInput.placeholder = 'e.g. bank transfer ref';
    refField.append(refLabel, refInput);
 
    const dateField = document.createElement('div');
    const dateLabel = document.createElement('label');
    dateLabel.textContent = 'Date paid';
    const dateInput = document.createElement('input');
    dateInput.type = 'date';
    dateInput.id = 'paymentPaidAt';
    dateInput.max = new Date().toISOString().split('T')[0];
    dateField.append(dateLabel, dateInput);
 
    fieldRow.append(refField, dateField);
    wrap.appendChild(fieldRow);
 
    const status = document.createElement('p');
    status.className = 'muted';
    status.id = 'paymentConfirmStatus';
    wrap.appendChild(status);
 
    const completeBtn = document.createElement('button');
    completeBtn.className = 'btn-primary';
    completeBtn.type = 'button';
    completeBtn.textContent = 'Mark as Completed';
    completeBtn.addEventListener('click', () => submitPaymentConfirmation(refund.id));
    wrap.appendChild(completeBtn);
 
    return wrap;
}
 
async function submitPaymentConfirmation(refundId) {
    const refInput = document.getElementById('paymentReference');
    const dateInput = document.getElementById('paymentPaidAt');
    const status = document.getElementById('paymentConfirmStatus');
 
    const paymentReference = refInput.value.trim();
    if (!paymentReference) {
        status.textContent = 'Enter a payment reference before completing.';
        status.className = 'error-text';
        return;
    }
 
    status.textContent = 'Saving...';
    status.className = 'muted';
 
    const payload = { payment_reference: paymentReference };
    if (dateInput.value) payload.paid_at = dateInput.value;
 
    const { response, data } = await api(`/v1/admin/refunds/${refundId}/complete`, {
        method: 'PATCH',
        body: JSON.stringify(payload),
    });
 
    if (response.ok) {
        status.textContent = 'Refund marked as completed.';
        status.className = 'muted';
        await loadRefundDetails();
        loadRefunds(1);
        loadDashboard();
    } else {
        status.textContent = data?.message || 'Could not complete refund.';
        status.className = 'error-text';
    }
}

// Builds the "Notes & Activity" card, reattaching the (single, template)
// note textarea / feedback nodes so existing onclick/oninput handlers keep working.
function buildNotesPanel(refund) {
    const card = document.createElement('div');
    card.className = 'card';

    const title = document.createElement('h3');
    title.className = 'detail-card-title';
    title.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/></svg> Notes & Activity';
    card.appendChild(title);

    const template = document.getElementById('notesPanelTemplate');
    if (template) {
        template.style.display = '';
        card.appendChild(template);
    }

    const divider = document.createElement('div');
    divider.className = 'activity-divider';
    card.appendChild(divider);

    const activityHeader = document.createElement('div');
    activityHeader.className = 'activity-header';
    activityHeader.textContent = 'Recent activity';
    card.appendChild(activityHeader);

    if (refund.status_logs?.length) {
        card.appendChild(buildHistorySection(refund.status_logs));
    } else {
        const empty = document.createElement('p');
        empty.className = 'muted';
        empty.textContent = 'No activity recorded yet.';
        card.appendChild(empty);
    }

    return card;
}

// Live character counter for the note textarea.
function updateNoteCounter() {
    const textarea = document.getElementById('actionNote');
    const counter = document.getElementById('noteCounter');
    if (!textarea || !counter) return;
    counter.textContent = `${textarea.value.length} / ${textarea.value.maxLength || 500}`;
}

// "Add Note" is a lightweight local staging confirmation: notes are actually
// persisted server-side when attached to a workflow action (approve/reject/
// return/complete) below, since the API has no standalone note-only endpoint.
function stageNote() {
    const textarea = document.getElementById('actionNote');
    const feedback = document.getElementById('actionFeedback');
    if (!textarea || !feedback) return;
    if (!textarea.value.trim()) {
        feedback.textContent = 'Write a note first, then it will be attached to your next workflow action.';
        feedback.className = 'muted';
        return;
    }
    feedback.textContent = 'Note staged — it will be attached when you Approve, Reject, Return, or Complete.';
    feedback.className = 'muted';
}

function makeSectionTitle(text) {
    const h = document.createElement('h4');
    h.className = 'detail-section-title';
    h.textContent = text;
    return h;
}

function buildSummarySection(refund) {
    const wrap = document.createElement('div');
    wrap.className = 'card';

    const title = document.createElement('h3');
    title.className = 'detail-card-title';
    title.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg> Summary';
    wrap.appendChild(title);

    const grid = document.createElement('div');
    grid.className = 'detail-grid';

    const rows = [
        ['Reference', refund.reference],
        ['Amount', formatMoney(sumTicketRefundAmount(refund.tickets), refund.tickets?.[0]?.currency), 'money'],
        ['Status', formatLabel(refund.current_status)],
        ['Booking ref', refund.tickets?.[0]?.booking_reference ?? '—'],
        ['Priority', formatLabel(refund.priority)],
        ['Flight', refund.tickets?.[0]?.flight_number ?? '—'],
        ['Department', formatLabel(refund.current_department)],
        ['Submitted date', formatDate(refund.created_at)],
        ['Passenger', `${refund.first_name || ''} ${refund.last_name || ''}`.trim()],
        ['Assigned to', refund.assignee?.name ?? 'Unassigned'],
        ['Email', refund.email],
        ['Phone', refund.phone],
        ['Airline', refund.airline?.name ?? '—'],
    ];

    for (const [label, value, modifier] of rows) {
        grid.appendChild(makeDetailRow(label, value, modifier));
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

function nextDepartmentLabel(currentStatus) {
    const map = {
        NEW_REQUEST: 'Commercial',
        RETURNED_BY_COMMERCIAL: 'Commercial',
        RETURNED_BY_AUDIT: 'Audit',
        RETURNED_BY_FINANCE: 'Finance',
    };
    return map[currentStatus] || 'Review';
}

function buildTicketsSection(tickets, refundId , currentStatus) {
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
        card.appendChild(buildCalculationEditor(ticket));

        if (ticket.remarks) {
            card.appendChild(makeDetailRow('Remarks', ticket.remarks));
        }

        wrap.appendChild(card);
    }

    // Global calculation controls (Save Draft / Submit)
    const controls = document.createElement('div');
    controls.className = 'calculation-controls';

    const noteLabel = document.createElement('label');
    noteLabel.textContent = 'Note (optional)';
    const noteInput = document.createElement('textarea');
    noteInput.id = `calcNote_${refundId}`;
    noteInput.rows = 2;

    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn-primary';
    saveBtn.type = 'button';
    saveBtn.textContent = 'Save Calculations (Draft)';
    saveBtn.addEventListener('click', () => sendCalculation(refundId, false));

    const submitBtn = document.createElement('button');
    submitBtn.className = 'btn-primary';
    submitBtn.type = 'button';
    submitBtn.style.marginLeft = '8px';
    submitBtn.textContent = `Submit to ${nextDepartmentLabel(currentStatus)}`;
    submitBtn.addEventListener('click', () => sendCalculation(refundId, true));

    controls.appendChild(noteLabel);
    controls.appendChild(noteInput);
    controls.id = `calcControls_${refundId}`;
    controls.appendChild(saveBtn);
    controls.appendChild(submitBtn);

    const uploadSection = document.createElement('div');
uploadSection.className = 'calculation-controls';
uploadSection.style.marginTop = '14px';
 
const uploadLabel = document.createElement('label');
uploadLabel.textContent = 'Add supporting documents';
uploadSection.appendChild(uploadLabel);
 
const uploadFileInput = document.createElement('input');
uploadFileInput.type = 'file';
uploadFileInput.id = `officerUploadFiles_${refundId}`;
uploadFileInput.multiple = true;
uploadFileInput.accept = '.jpg,.jpeg,.png,.pdf';
uploadFileInput.style.marginBottom = '10px';
uploadSection.appendChild(uploadFileInput);
 
const uploadTypeSelect = document.createElement('select');
uploadTypeSelect.id = `officerUploadType_${refundId}`;
uploadTypeSelect.style.marginLeft = '8px';
[
    ['other', 'Other'],
    ['signature', 'Signature'],
    ['passenger_id', 'Passenger ID'],
    ['account_holder_id', 'Account Holder ID'],
    ['authorization_letter', 'Authorization Letter'],
].forEach(([value, label]) => {
    const opt = document.createElement('option');
    opt.value = value;
    opt.textContent = label;
    uploadTypeSelect.appendChild(opt);
});
uploadSection.appendChild(uploadTypeSelect);
 
const uploadBtn = document.createElement('button');
uploadBtn.className = 'btn-ghost';
uploadBtn.type = 'button';
uploadBtn.style.marginLeft = '8px';
uploadBtn.textContent = 'Upload';
uploadBtn.addEventListener('click', () => uploadOfficerAttachments(refundId));
uploadSection.appendChild(uploadBtn);
 
const uploadStatus = document.createElement('span');
uploadStatus.className = 'muted';
uploadStatus.id = `officerUploadStatus_${refundId}`;
uploadStatus.style.marginLeft = '10px';
uploadSection.appendChild(uploadStatus);
 
wrap.appendChild(uploadSection);
 
 
/**
 * 2. New function — add this anywhere in admin.js (near sendCalculation()
 *    makes sense). Calls the existing POST /refunds/{id}/attachments
 *    endpoint, then refreshes the refund detail view so the new
 *    attachment shows up in the existing Attachments section.
 */
 
async function uploadOfficerAttachments(refundId) {
    const filesInput = document.getElementById(`officerUploadFiles_${refundId}`);
    const typeSelect = document.getElementById(`officerUploadType_${refundId}`);
    const status = document.getElementById(`officerUploadStatus_${refundId}`);
 
    if (!filesInput.files.length) {
        status.textContent = 'Choose at least one file.';
        return;
    }
 
    status.textContent = 'Uploading...';
 
    const formData = new FormData();
    Array.from(filesInput.files).forEach(file => {
        formData.append('attachments[]', file);
        formData.append('attachment_types[]', typeSelect.value);
    });
 
    /**
 * Replace the fetch() call inside uploadOfficerAttachments() — everything
 * else in that function stays the same, just the headers object gains
 * Accept: application/json.
 */

const headers = {
    'Accept': 'application/json',
};
if (token) headers['Authorization'] = 'Bearer ' + token;

const response = await fetch(`/api/v1/admin/refunds/${refundId}/attachments`, {
    method: 'POST',
    headers,
    body: formData,
});

    try {
        const response = await fetch(`/api/v1/admin/refunds/${refundId}/attachments`, {
            method: 'POST',
            headers,
            body: formData,
        });
 
        const data = await response.json().catch(() => null);
 
        if (response.ok && data?.success) {
            status.textContent = 'Uploaded.';
            filesInput.value = '';
            await loadRefundDetails();
        } else {
            status.textContent = data?.message || 'Upload failed.';
        }
    } catch (err) {
        console.error('Attachment upload failed:', err);
        status.textContent = 'Upload failed — network error.';
    }
}
 

    wrap.appendChild(controls);

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


async function sendCalculation(refundId, submit = false) {
    // Scope tickets to the current refund detail view
    const detailBox = document.getElementById('detailBox');
    const ticketEls = Array.from(detailBox.querySelectorAll('.calculation-ticket'))
        .filter(el => el.closest('.ticket-card'));

    const tickets = ticketEls.map(el => ({
        ticket_id: Number(el.dataset.ticketId),
        fare_paid: parseFloat(el.querySelector('.input-fare_paid').value),
        nuc: parseFloat(el.querySelector('.input-nuc').value),
        government_tax_ng: parseFloat(el.querySelector('.input-government_tax_ng').value),
        security_tax_yq: parseFloat(el.querySelector('.input-security_tax_yq').value),
        airport_tax_qt: parseFloat(el.querySelector('.input-airport_tax_qt').value),
        insurance: parseFloat(el.querySelector('.input-insurance').value),
        is_no_show: !!el.querySelector('.input-is_no_show').checked,
        no_show_fee: parseFloat(el.querySelector('.input-no_show_fee').value),
    }));

    // Client-side validation
    const errors = [];
    if (!tickets.length) errors.push('No tickets found to calculate.');
    tickets.forEach((t, i) => {
        const idx = i + 1;
        if (!Number.isInteger(t.ticket_id) || t.ticket_id <= 0) errors.push(`Ticket ${idx}: invalid ticket_id`);
        ['fare_paid','nuc','government_tax_ng','security_tax_yq','airport_tax_qt','insurance','no_show_fee']
            .forEach(k => {
                if (typeof t[k] !== 'number' || Number.isNaN(t[k]) || t[k] < 0) {
                    errors.push(`Ticket ${idx}: ${k} must be a number >= 0`);
                }
            });
        if (typeof t.is_no_show !== 'boolean') errors.push(`Ticket ${idx}: is_no_show must be boolean`);
    });

    if (errors.length) {
        alert(errors[0]);
        return;
    }

    const noteEl = document.getElementById(`calcNote_${refundId}`);
    const payload = { tickets, note: noteEl?.value || null };

    const path = `/v1/admin/refunds/${refundId}/tickets/calculation/${submit ? 'submit' : 'draft'}`;

    // Disable controls while request is in progress
    const controls = document.getElementById(`calcControls_${refundId}`);
    const btns = controls ? Array.from(controls.querySelectorAll('button')) : [];
    btns.forEach(b => b.disabled = true);

    try {
        const { response, data } = await api(path, {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        if (response.ok) {
            alert(data?.message || (submit ? 'Submitted.' : 'Saved draft.'));
            // Refresh refund details and refresh list
            await loadRefundDetails();
            loadRefunds(1);
        } else if (response.status === 422) {
        console.error('Validation errors', data);
        const message = data?.errors
        ? Object.values(data.errors).flat()[0]
        : (data?.message || 'Validation failed');
        alert(message);
        } else {
            alert(data?.message || 'Calculation error');
        }
    } finally {
        btns.forEach(b => b.disabled = false);
    }
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

const CHECK_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

function buildHistorySection(logs) {
    const list = document.createElement('div');
    list.className = 'history-list';

    // Most recent activity first, matching a typical activity feed.
    const ordered = [...logs].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

    for (const log of ordered) {
        const entry = document.createElement('div');
        entry.className = 'history-entry';

        const icon = document.createElement('div');
        icon.className = 'history-icon';
        icon.innerHTML = CHECK_ICON;
        entry.appendChild(icon);

        const body = document.createElement('div');
        body.className = 'history-body';

        const top = document.createElement('div');
        top.className = 'history-top';

        const line = document.createElement('div');
        line.className = 'history-line';
        line.textContent = `${formatLabel(log.old_status) || 'Start'} → ${formatLabel(log.new_status)}`;
        top.appendChild(line);

        const time = document.createElement('div');
        time.className = 'history-time';
        time.textContent = formatDate(log.created_at);
        top.appendChild(time);

        body.appendChild(top);

        const meta = document.createElement('div');
        meta.className = 'history-meta';
        meta.textContent = `by ${log.user?.name ?? 'System'}`;
        body.appendChild(meta);

        if (log.note) {
            const note = document.createElement('div');
            note.className = 'history-note';
            note.textContent = log.note;
            body.appendChild(note);
        }

        entry.appendChild(body);
        list.appendChild(entry);
    }

    return list;
}

function makeDetailRow(label, value, modifier) {
    const row = document.createElement('div');
    row.className = 'detail-row';

    const labelEl = document.createElement('span');
    labelEl.className = 'detail-label';
    labelEl.textContent = label;

    const valueEl = document.createElement('span');
    valueEl.className = modifier ? `detail-value ${modifier}` : 'detail-value';
    valueEl.textContent = (value === null || value === undefined || value === '') ? '—' : value;

    row.append(labelEl, valueEl);
    return row;
}

// Sums each ticket's refund_amount to show a single headline figure in the
// summary card. Returns null (rendered as "—") when there's nothing to total.
function sumTicketRefundAmount(tickets) {
    if (!tickets?.length) return null;
    const total = tickets.reduce((sum, t) => sum + (Number(t.refund_amount) || 0), 0);
    return total;
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
    document.addEventListener("DOMContentLoaded", function () {
    console.log("ADMIN JS LOADED");

    loadAdminAirlines();
});