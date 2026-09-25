// ================= Finance CRUD =================

const FINANCE_API = 'api/finance_api.php';
let financeSearchTimer = null;

function financeBadge(status) {
    const map = { Paid: 'badge-green', Pending: 'badge-amber', Overdue: 'badge-red' };
    return `<span class="badge ${map[status] || 'badge-blue'}">${status}</span>`;
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function loadFinance(search = '') {
    const tbody = document.getElementById('financeBody');
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">Loading...</td></tr>`;

    try {
        const res = await fetch(`${FINANCE_API}?action=list&search=${encodeURIComponent(search)}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Failed to load payments');

        const rows = json.data;
        document.getElementById('resultCount').textContent = `${rows.length} payment${rows.length !== 1 ? 's' : ''}`;

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">No payments found.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td><b>${r.invoice_no}</b></td>
                <td>${r.student_name ? r.student_name : '—'}</td>
                <td>$${Number(r.amount).toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                <td>${formatDate(r.payment_date)}</td>
                <td>${financeBadge(r.status)}</td>
                <td>
                    <div class="row-actions">
                        <button title="Edit" onclick="editFinance(${r.id})"><i class="fa-solid fa-pen"></i></button>
                        <button title="Delete" class="delete" onclick="deleteFinance(${r.id}, '${r.invoice_no.replace(/'/g, "\\'")}')"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--red);padding:30px">${err.message}</td></tr>`;
    }
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Payment';
    document.getElementById('financeForm').reset();
    document.getElementById('financeId').value = '';
    openModal('financeModal');
}

async function editFinance(id) {
    try {
        const res = await fetch(`${FINANCE_API}?action=get&id=${id}`);
        const json = await res.json();
        if (!json.success) throw new Error(json.message);

        const r = json.data;
        document.getElementById('modalTitle').textContent = 'Edit Payment';
        document.getElementById('financeId').value = r.id;
        document.getElementById('invoice_no').value = r.invoice_no;
        document.getElementById('amount').value = r.amount;
        document.getElementById('student_id').value = r.student_id || '';
        document.getElementById('payment_date').value = r.payment_date || '';
        document.getElementById('status').value = r.status;
        document.getElementById('notes').value = r.notes || '';

        openModal('financeModal');
    } catch (err) {
        alert('Could not load payment: ' + err.message);
    }
}

async function deleteFinance(id, invoice) {
    if (!confirm(`Delete payment "${invoice}"? This cannot be undone.`)) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const res = await fetch(FINANCE_API, { method: 'POST', body: formData });
        const json = await res.json();
        if (!json.success) throw new Error(json.message);
        loadFinance(document.getElementById('searchInput').value);
    } catch (err) {
        alert('Delete failed: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadFinance();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(financeSearchTimer);
        financeSearchTimer = setTimeout(() => loadFinance(e.target.value), 350);
    });

    document.getElementById('financeForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('financeId').value;
        const formData = new FormData(e.target);
        formData.set('action', id ? 'update' : 'create');

        try {
            const res = await fetch(FINANCE_API, { method: 'POST', body: formData });
            const json = await res.json();
            if (!json.success) throw new Error(json.message);

            closeModal('financeModal');
            loadFinance(document.getElementById('searchInput').value);
        } catch (err) {
            alert('Save failed: ' + err.message);
        }
    });
});
