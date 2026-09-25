// ================= Timetable CRUD =================

const TIMETABLE_API = 'api/timetable_api.php';
let ttSearchTimer = null;

function formatTime(t) {
    if (!t) return '—';
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 === 0 ? 12 : hour % 12;
    return `${hour12}:${m} ${ampm}`;
}

async function loadTimetable(search = '') {
    const tbody = document.getElementById('timetableBody');
    tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px">Loading...</td></tr>`;

    try {
        const res = await fetch(`${TIMETABLE_API}?action=list&search=${encodeURIComponent(search)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Failed to load timetable');

        const rows = json.data;
        document.getElementById('resultCount').textContent = `${rows.length} entr${rows.length !== 1 ? 'ies' : 'y'}`;

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:30px">No timetable entries found.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td><b>${r.day_of_week}</b></td>
                <td>${formatTime(r.start_time)} – ${formatTime(r.end_time)}</td>
                <td>${r.course_name ? `${r.course_code} - ${r.course_name}` : '—'}</td>
                <td>${r.room || '—'}</td>
                <td>
                    <div class="row-actions">
                        <button title="Edit" onclick="editEntry(${r.id})"><i class="fa-solid fa-pen"></i></button>
                        <button title="Delete" class="delete" onclick="deleteEntry(${r.id})"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--red);padding:30px">${err.message}</td></tr>`;
    }
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Schedule';
    document.getElementById('timetableForm').reset();
    document.getElementById('ttId').value = '';
    openModal('timetableModal');
}

async function editEntry(id) {
    try {
        const res = await fetch(`${TIMETABLE_API}?action=get&id=${id}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const r = json.data;
        document.getElementById('modalTitle').textContent = 'Edit Schedule';
        document.getElementById('ttId').value = r.id;
        document.getElementById('course_id').value = r.course_id;
        document.getElementById('day_of_week').value = r.day_of_week;
        document.getElementById('room').value = r.room || '';
        document.getElementById('start_time').value = r.start_time.slice(0,5);
        document.getElementById('end_time').value = r.end_time.slice(0,5);

        openModal('timetableModal');
    } catch (err) {
        alert('Could not load entry: ' + err.message);
    }
}

async function deleteEntry(id) {
    if (!confirm('Delete this timetable entry? This cannot be undone.')) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const res = await fetch(TIMETABLE_API, { method: 'POST', body: formData });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        loadTimetable(document.getElementById('searchInput').value);
    } catch (err) {
        alert('Delete failed: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadTimetable();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(ttSearchTimer);
        ttSearchTimer = setTimeout(() => loadTimetable(e.target.value), 350);
    });

    document.getElementById('timetableForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('ttId').value;
        const formData = new FormData(e.target);
        formData.set('action', id ? 'update' : 'create');

        try {
            const res = await fetch(TIMETABLE_API, { method: 'POST', body: formData });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            closeModal('timetableModal');
            loadTimetable(document.getElementById('searchInput').value);
        } catch (err) {
            alert('Save failed: ' + err.message);
        }
    });
});
