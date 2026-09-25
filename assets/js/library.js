// ================= Library CRUD =================

const LIBRARY_API = 'api/library_api.php';
let librarySearchTimer = null;

async function loadBooks(search = '') {
    const tbody = document.getElementById('libraryBody');
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">Loading...</td></tr>`;

    try {
        const res = await fetch(`${LIBRARY_API}?action=list&search=${encodeURIComponent(search)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Failed to load books');

        const rows = json.data;
        document.getElementById('resultCount').textContent = `${rows.length} book${rows.length !== 1 ? 's' : ''}`;

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">No books found.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(b => `
            <tr>
                <td><b>${b.title}</b></td>
                <td>${b.author || '—'}</td>
                <td>${b.category || '—'}</td>
                <td>${b.isbn || '—'}</td>
                <td>${b.available_copies} / ${b.total_copies}</td>
                <td>
                    <div class="row-actions">
                        <button title="Edit" onclick="editBook(${b.id})"><i class="fa-solid fa-pen"></i></button>
                        <button title="Delete" class="delete" onclick="deleteBook(${b.id}, '${b.title.replace(/'/g, "\\'")}')"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--red);padding:30px">${err.message}</td></tr>`;
    }
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Book';
    document.getElementById('libraryForm').reset();
    document.getElementById('bookId').value = '';
    document.getElementById('total_copies').value = 1;
    document.getElementById('available_copies').value = 1;
    openModal('libraryModal');
}

async function editBook(id) {
    try {
        const res = await fetch(`${LIBRARY_API}?action=get&id=${id}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const b = json.data;
        document.getElementById('modalTitle').textContent = 'Edit Book';
        document.getElementById('bookId').value = b.id;
        document.getElementById('title').value = b.title;
        document.getElementById('author').value = b.author || '';
        document.getElementById('category').value = b.category || '';
        document.getElementById('isbn').value = b.isbn || '';
        document.getElementById('total_copies').value = b.total_copies;
        document.getElementById('available_copies').value = b.available_copies;

        openModal('libraryModal');
    } catch (err) {
        alert('Could not load book: ' + err.message);
    }
}

async function deleteBook(id, title) {
    if (!confirm(`Delete book "${title}"? This cannot be undone.`)) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const res = await fetch(LIBRARY_API, { method: 'POST', body: formData });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        loadBooks(document.getElementById('searchInput').value);
    } catch (err) {
        alert('Delete failed: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadBooks();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(librarySearchTimer);
        librarySearchTimer = setTimeout(() => loadBooks(e.target.value), 350);
    });

    document.getElementById('libraryForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('bookId').value;
        const formData = new FormData(e.target);
        formData.set('action', id ? 'update' : 'create');

        try {
            const res = await fetch(LIBRARY_API, { method: 'POST', body: formData });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            closeModal('libraryModal');
            loadBooks(document.getElementById('searchInput').value);
        } catch (err) {
            alert('Save failed: ' + err.message);
        }
    });
});
