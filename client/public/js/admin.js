/**
 * admin.js
 * JavaScript for the admin panel of Glo-CED India.
 * Fetches data from the server routes and populates tables.
 */

const API_BASE = '/server/routes';

document.addEventListener('DOMContentLoaded', () => {

    // --- Dashboard Stats ---
    const totalUsers       = document.getElementById('totalUsers');
    const totalSubmissions = document.getElementById('totalSubmissions');
    const totalPrograms    = document.getElementById('totalPrograms');

    if (totalUsers || totalSubmissions) {
        Promise.all([
            fetch(`${API_BASE}/users.php`).then(r => r.json()),
            fetch(`${API_BASE}/submissions.php`).then(r => r.json()),
        ]).then(([users, submissions]) => {
            if (totalUsers)       totalUsers.textContent       = users.length       ?? '—';
            if (totalSubmissions) totalSubmissions.textContent = submissions.length ?? '—';
            if (totalPrograms)    totalPrograms.textContent    = '—';
        }).catch(() => { /* silently fail on stats */ });
    }

    // --- Users Table ---
    const usersTableBody = document.querySelector('#usersTable tbody');
    if (usersTableBody) {
        fetch(`${API_BASE}/users.php`)
            .then(r => r.json())
            .then(users => {
                usersTableBody.innerHTML = users.map(u => `
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.username}</td>
                        <td>${u.email}</td>
                        <td>${u.role}</td>
                        <td>${u.created_at}</td>
                        <td>
                            <button onclick="deleteUser(${u.id})" class="btn-danger">Delete</button>
                        </td>
                    </tr>`).join('');
            });
    }

    // --- Submissions Table ---
    const submissionsTableBody = document.querySelector('#submissionsTable tbody');
    if (submissionsTableBody) {
        fetch(`${API_BASE}/submissions.php`)
            .then(r => r.json())
            .then(subs => {
                submissionsTableBody.innerHTML = subs.map(s => `
                    <tr>
                        <td>${s.id}</td>
                        <td>${s.name}</td>
                        <td>${s.email}</td>
                        <td>${s.message.substring(0, 60)}…</td>
                        <td>${s.created_at}</td>
                        <td>
                            <button onclick="deleteSubmission(${s.id})" class="btn-danger">Delete</button>
                        </td>
                    </tr>`).join('');
            });
    }
});

async function deleteUser(id) {
    if (!confirm('Delete this user?')) return;
    await fetch(`/server/routes/users.php?id=${id}`, { method: 'DELETE' });
    location.reload();
}

async function deleteSubmission(id) {
    if (!confirm('Delete this submission?')) return;
    await fetch(`/server/routes/submissions.php?id=${id}`, { method: 'DELETE' });
    location.reload();
}
