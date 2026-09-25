// ================= Transport CRUD =================

const TRANSPORT_API = 'api/transport_api.php';
let transportSearchTimer = null;

// ===== HELPER FUNCTIONS =====

function transportBadge(status) {
    const map = { 
        Active: 'badge-success', 
        Inactive: 'badge-danger' 
    };
    return `<span class="badge ${map[status] || 'badge-info'}">${status}</span>`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 
                 type === 'warning' ? 'fa-triangle-exclamation' : 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${escapeHtml(message)}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 4000);
}

function showSuccess(message) {
    showToast(message, 'success');
}

function showError(message) {
    showToast(message, 'error');
}

// ===== LOAD ROUTES =====

async function loadRoutes(search = '') {
    const tbody = document.getElementById('transportBody');
    if (!tbody) return;
    
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">
        <i class="fa-solid fa-spinner fa-spin"></i> Loading...
    </td></tr>`;

    try {
        const res = await fetch(`${TRANSPORT_API}?action=list&search=${encodeURIComponent(search)}`);
        const json = await res.json();
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to load routes');
        }

        const rows = json.data;
        const resultCount = document.getElementById('resultCount');
        if (resultCount) {
            resultCount.textContent = `${rows.length} route${rows.length !== 1 ? 's' : ''}`;
        }

        if (rows.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:30px">
                <i class="fa-solid fa-bus" style="font-size:24px;display:block;margin-bottom:10px;opacity:0.3"></i>
                No routes found. Click "Add Route" to create one.
            </td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(r => `
            <tr>
                <td><strong>${escapeHtml(r.route_name)}</strong></td>
                <td>${escapeHtml(r.vehicle_no || '—')}</td>
                <td>${escapeHtml(r.driver_name || '—')}</td>
                <td>${r.capacity}</td>
                <td>${transportBadge(r.status)}</td>
                <td>
                    <div class="row-actions">
                        <button title="Edit Route" onclick="editRoute(${r.id})">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button title="Delete Route" class="delete" onclick="deleteRoute(${r.id}, '${escapeHtml(r.route_name).replace(/'/g, "\\'")}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--danger);padding:30px">
            <i class="fa-solid fa-circle-exclamation" style="font-size:24px;display:block;margin-bottom:10px;"></i>
            ${escapeHtml(err.message)}
        </td></tr>`;
    }
}

// ===== MODAL FUNCTIONS =====

function openAddModal() {
    const modal = document.getElementById('transportModal');
    if (!modal) return;
    
    document.getElementById('modalTitle').textContent = 'Add New Route';
    document.getElementById('transportForm').reset();
    document.getElementById('routeId').value = '';
    document.getElementById('capacity').value = 40;
    document.getElementById('status').value = 'Active';
    
    // Clear any previous error states
    document.querySelectorAll('.form-control').forEach(el => {
        el.classList.remove('error');
    });
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('route_name').focus();
    }, 100);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

async function editRoute(id) {
    if (!id) {
        showError('Invalid route ID');
        return;
    }

    try {
        const res = await fetch(`${TRANSPORT_API}?action=get&id=${id}`);
        const json = await res.json();
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to load route details');
        }

        const r = json.data;
        
        document.getElementById('modalTitle').textContent = 'Edit Route';
        document.getElementById('routeId').value = r.id;
        document.getElementById('route_name').value = r.route_name || '';
        document.getElementById('vehicle_no').value = r.vehicle_no || '';
        document.getElementById('driver_name').value = r.driver_name || '';
        document.getElementById('capacity').value = r.capacity || 40;
        document.getElementById('status').value = r.status || 'Active';
        document.getElementById('stops').value = r.stops || '';

        // Clear any previous error states
        document.querySelectorAll('.form-control').forEach(el => {
            el.classList.remove('error');
        });

        const modal = document.getElementById('transportModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

    } catch (err) {
        showError('Could not load route: ' + err.message);
    }
}

// ===== DELETE ROUTE =====

async function deleteRoute(id, name) {
    if (!id) {
        showError('Invalid route ID');
        return;
    }

    // Create custom confirm dialog
    if (!confirm(`Are you sure you want to delete the route "${name}"?\n\nThis action cannot be undone.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        const res = await fetch(TRANSPORT_API, { 
            method: 'POST', 
            body: formData 
        });
        
        const json = await res.json();
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to delete route');
        }

        showSuccess(`Route "${name}" deleted successfully`);
        
        // Reload the list
        const searchInput = document.getElementById('searchInput');
        loadRoutes(searchInput ? searchInput.value : '');

    } catch (err) {
        showError('Delete failed: ' + err.message);
    }
}

// ===== FORM SUBMISSION =====

document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadRoutes();

    // ===== SEARCH =====
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(transportSearchTimer);
            transportSearchTimer = setTimeout(() => {
                loadRoutes(e.target.value);
            }, 350);
        });

        // Search on Enter key
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(transportSearchTimer);
                loadRoutes(this.value);
            }
        });
    }

    // ===== FORM SUBMISSION =====
    const form = document.getElementById('transportForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Get form data
            const id = document.getElementById('routeId').value;
            const routeName = document.getElementById('route_name').value.trim();
            
            // Validate
            if (!routeName) {
                showError('Route name is required');
                document.getElementById('route_name').focus();
                document.getElementById('route_name').classList.add('error');
                return;
            }

            // Create FormData
            const formData = new FormData(this);
            formData.set('action', id ? 'update' : 'create');

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;

            try {
                const res = await fetch(TRANSPORT_API, { 
                    method: 'POST', 
                    body: formData 
                });
                
                const json = await res.json();
                
                if (!json.success) {
                    throw new Error(json.message || 'Failed to save route');
                }

                // Success
                showSuccess(id ? 'Route updated successfully' : 'Route added successfully');
                
                // Close modal
                closeModal('transportModal');
                
                // Reload the list
                const searchInput = document.getElementById('searchInput');
                loadRoutes(searchInput ? searchInput.value : '');

            } catch (err) {
                showError('Save failed: ' + err.message);
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // ===== CLOSE MODAL ON OVERLAY CLICK =====
    const modalOverlay = document.getElementById('transportModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('transportModal');
            }
        });
    }

    // ===== CLOSE MODAL ON ESCAPE KEY =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal-overlay.active');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });

    // ===== ADD KEYBOARD SHORTCUTS =====
    document.addEventListener('keydown', function(e) {
        // Ctrl + N or Cmd + N to open Add Modal
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            openAddModal();
        }
        
        // Ctrl + F or Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });
});

// ===== EXPOSE FUNCTIONS GLOBALLY =====
window.openAddModal = openAddModal;
window.closeModal = closeModal;
window.editRoute = editRoute;
window.deleteRoute = deleteRoute;
window.loadRoutes = loadRoutes;
window.showSuccess = showSuccess;
window.showError = showError;

// ===== TOAST CLOSE HANDLER =====
document.addEventListener('click', function(e) {
    if (e.target.closest('.toast-close')) {
        const toast = e.target.closest('.toast-notification');
        if (toast) {
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(50px)';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }
});

// ===== CONSOLE LOG FOR DEBUGGING =====
console.log('✅ Transport JS loaded successfully!');
console.log('📌 Available functions:');
console.log('  - openAddModal()');
console.log('  - closeModal(id)');
console.log('  - editRoute(id)');
console.log('  - deleteRoute(id, name)');
console.log('  - loadRoutes(search)');
console.log('  - showSuccess(message)');
console.log('  - showError(message)');
console.log('⌨️ Keyboard shortcuts:');
console.log('  - Ctrl+N / Cmd+N: Open Add Modal');
console.log('  - Ctrl+F / Cmd+F: Focus Search');
console.log('  - Escape: Close Modal');