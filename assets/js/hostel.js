// ================= Hostel CRUD =================

const HOSTEL_API = 'api/hostel_api.php';
let hostelSearchTimer = null;

function hostelBadge(status) {
    const map = { Available: 'badge-green', Full: 'badge-red', Maintenance: 'badge-amber' };
    return `<span class="badge ${map[status] || 'badge-blue'}">${status}</span>`;
}

async function loadRooms(search = '') {
    const tbody = document.getElementById('hostelBody');
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">Loading...</td></tr>`;

    try {
        const res = await fetch(`${HOSTEL_API}?action=list&search=${encodeURIComponent(search)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Failed to load rooms');

        const rows = json.data;
        document.getElementById('resultCount').textContent = `${rows.length} room${rows.length !== 1 ? 's' : ''}`;

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">No rooms found.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td><b>${r.room_no}</b></td>
                <td>${r.block || '—'}</td>
                <td>${r.room_type}</td>
                <td>${r.occupied} / ${r.capacity}</td>
                <td>${hostelBadge(r.status)}</td>
                <td>
                    <div class="row-actions">
                        <button title="Edit" onclick="editRoom(${r.id})"><i class="fa-solid fa-pen"></i></button>
                        <button title="Delete" class="delete" onclick="deleteRoom(${r.id}, '${r.room_no.replace(/'/g, "\\'")}')"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--red);padding:30px">${err.message}</td></tr>`;
    }
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Room';
    document.getElementById('hostelForm').reset();
    document.getElementById('roomId').value = '';
    document.getElementById('capacity').value = 2;
    document.getElementById('occupied').value = 0;
    openModal('hostelModal');
}

async function editRoom(id) {
    try {
        const res = await fetch(`${HOSTEL_API}?action=get&id=${id}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const r = json.data;
        document.getElementById('modalTitle').textContent = 'Edit Room';
        document.getElementById('roomId').value = r.id;
        document.getElementById('room_no').value = r.room_no;
        document.getElementById('block').value = r.block || '';
        document.getElementById('room_type').value = r.room_type;
        document.getElementById('status').value = r.status;
        document.getElementById('capacity').value = r.capacity;
        document.getElementById('occupied').value = r.occupied;

        openModal('hostelModal');
    } catch (err) {
        alert('Could not load room: ' + err.message);
    }
}

async function deleteRoom(id, roomNo) {
    if (!confirm(`Delete room "${roomNo}"? This cannot be undone.`)) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const res = await fetch(HOSTEL_API, { method: 'POST', body: formData });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        loadRooms(document.getElementById('searchInput').value);
    } catch (err) {
        alert('Delete failed: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadRooms();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(hostelSearchTimer);
        hostelSearchTimer = setTimeout(() => loadRooms(e.target.value), 350);
    });

    document.getElementById('hostelForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('roomId').value;
        const formData = new FormData(e.target);
        formData.set('action', id ? 'update' : 'create');

        try {
            const res = await fetch(HOSTEL_API, { method: 'POST', body: formData });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            closeModal('hostelModal');
            loadRooms(document.getElementById('searchInput').value);
        } catch (err) {
            alert('Save failed: ' + err.message);
        }
    });
});
