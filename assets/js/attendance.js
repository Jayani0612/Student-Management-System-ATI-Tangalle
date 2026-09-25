// ================= Attendance CRUD =================

const ATTENDANCE_API = 'api/attendance_api.php';
let attendanceSearchTimer = null;

function attendanceBadge(status) {
    const map = { Present: 'badge-green', Absent: 'badge-red', Late: 'badge-amber' };
    return `<span class="badge ${map[status] || 'badge-blue'}">${status}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function loadAttendance(search = '') {
    const tbody = document.getElementById('attendanceBody');
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px">Loading...</td></tr>`;

    try {
        const res = await fetch(`${ATTENDANCE_API}?action=list&search=${encodeURIComponent(search)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Failed to load attendance');

        const rows = json.data;
        document.getElementById('resultCount').textContent = `${rows.length} record${rows.length !== 1 ? 's' : ''}`;

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px">No attendance records found.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td><b>${r.student_no}</b></td>
                <td>${r.student_name}</td>
                <td>${formatDate(r.attendance_date)}</td>
                <td>${attendanceBadge(r.status)}</td>
                <td>
                    <div class="row-actions">
                        <button title="Edit" onclick="editAttendance(${r.id})"><i class="fa-solid fa-pen"></i></button>
                        <button title="Delete" class="delete" onclick="deleteAttendance(${r.id})"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--red);padding:30px">${err.message}</td></tr>`;
    }
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Mark Attendance';
    document.getElementById('attendanceForm').reset();
    document.getElementById('attendanceId').value = '';
    document.getElementById('attendance_date').value = new Date().toISOString().split('T')[0];
    openModal('attendanceModal');
}

async function editAttendance(id) {
    try {
        const res = await fetch(`${ATTENDANCE_API}?action=get&id=${id}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const r = json.data;
        document.getElementById('modalTitle').textContent = 'Edit Attendance';
        document.getElementById('attendanceId').value = r.id;
        document.getElementById('student_id').value = r.student_id;
        document.getElementById('attendance_date').value = r.attendance_date;
        document.getElementById('status').value = r.status;

        openModal('attendanceModal');
    } catch (err) {
        alert('Could not load record: ' + err.message);
    }
}

async function deleteAttendance(id) {
    if (!confirm('Delete this attendance record? This cannot be undone.')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const res = await fetch(ATTENDANCE_API, { method: 'POST', body: formData });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        loadAttendance(document.getElementById('searchInput').value);
    } catch (err) {
        alert('Delete failed: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadAttendance();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(attendanceSearchTimer);
        attendanceSearchTimer = setTimeout(() => loadAttendance(e.target.value), 350);
    });

    document.getElementById('attendanceForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('attendanceId').value;
        const formData = new FormData(e.target);
        formData.set('action', id ? 'update' : 'create');

        try {
            const res = await fetch(ATTENDANCE_API, { method: 'POST', body: formData });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            closeModal('attendanceModal');
            loadAttendance(document.getElementById('searchInput').value);
        } catch (err) {
            alert('Save failed: ' + err.message);
        }
    });
});
