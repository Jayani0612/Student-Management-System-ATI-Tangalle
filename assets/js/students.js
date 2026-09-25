// ================= Students CRUD - Complete Working Version =================

// ===== CONFIGURATION =====
const API_URL = 'api/students_api.php';
let searchTimer = null;

// ===== WAIT FOR DOM TO LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Students JS loaded successfully!');
    console.log('🔍 Checking elements...');
    
    // Check if required elements exist
    const tbody = document.getElementById('studentsBody');
    const searchInput = document.getElementById('searchInput');
    const form = document.getElementById('studentForm');
    
    if (!tbody) {
        console.error('❌ studentsBody element not found!');
        return;
    }
    
    console.log('✅ studentsBody found');
    
    // Load initial data
    loadStudents();
    
    // ===== SEARCH =====
    if (searchInput) {
        console.log('✅ searchInput found');
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                loadStudents(e.target.value);
            }, 400);
        });
    } else {
        console.warn('⚠️ searchInput not found');
    }
    
    // ===== FORM SUBMISSION =====
    if (form) {
        console.log('✅ studentForm found');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            await handleFormSubmit(e);
        });
    } else {
        console.warn('⚠️ studentForm not found');
    }
    
    // ===== CLOSE MODAL ON ESCAPE =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('studentModal');
            if (modal && modal.classList.contains('active')) {
                closeModal('studentModal');
            }
        }
    });
    
    // ===== CLICK OUTSIDE MODAL TO CLOSE =====
    const modalOverlay = document.getElementById('studentModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal('studentModal');
            }
        });
    }
    
    // ===== AUTO-OPEN MODAL =====
    if (typeof AUTO_OPEN_ADD !== 'undefined' && AUTO_OPEN_ADD === true) {
        setTimeout(openAddModal, 500);
    }
    
    console.log('✅ All event listeners attached successfully!');
});

// ===== LOAD STUDENTS =====
async function loadStudents(search = '') {
    console.log('🔄 Loading students...');
    
    const tbody = document.getElementById('studentsBody');
    if (!tbody) {
        console.error('❌ studentsBody not found!');
        return;
    }
    
    // Show loading state
    tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align:center;color:var(--text-muted);padding:40px;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                Loading students...
            </td>
        </tr>
    `;

    try {
        const url = `${API_URL}?action=list&search=${encodeURIComponent(search)}`;
        console.log('📡 Fetching:', url);
        
        const response = await fetch(url);
        const json = await response.json();
        
        console.log('📊 Response:', json);
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to load students');
        }

        const students = json.data || [];
        const resultCount = document.getElementById('resultCount');
        if (resultCount) {
            resultCount.textContent = `${students.length} student${students.length !== 1 ? 's' : ''}`;
        }

        if (students.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--text-muted);padding:40px;">
                        <i class="fa-solid fa-user-graduate" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                        No students found. Click "Add Student" to register one.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        students.forEach(s => {
            const statusBadge = getStatusBadge(s.status);
            const admissionDate = formatDate(s.admission_date);
            const department = s.department_name || '—';
            const email = s.email || '—';
            
            html += `
                <tr>
                    <td><strong>${escapeHtml(s.student_no)}</strong></td>
                    <td>${escapeHtml(s.full_name)}</td>
                    <td>${escapeHtml(department)}</td>
                    <td>Year ${s.year_level || 1}</td>
                    <td>${escapeHtml(email)}</td>
                    <td>${statusBadge}</td>
                    <td>${admissionDate}</td>
                    <td>
                        <div class="row-actions">
                            <button title="Edit Student" onclick="editStudent(${s.id})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button title="Delete Student" class="delete" onclick="deleteStudent(${s.id}, '${escapeHtml(s.full_name).replace(/'/g, "\\'")}')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        console.log(`✅ Loaded ${students.length} students`);

    } catch (err) {
        console.error('❌ Error loading students:', err);
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align:center;color:var(--danger);padding:40px;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                    ${escapeHtml(err.message)}
                </td>
            </tr>
        `;
    }
}

// ===== GET STATUS BADGE =====
function getStatusBadge(status) {
    const map = {
        'Active': 'badge-success',
        'Inactive': 'badge-warning',
        'Graduated': 'badge-info'
    };
    const className = map[status] || 'badge-info';
    return `<span class="badge ${className}">${status || 'Active'}</span>`;
}

// ===== FORMAT DATE =====
function formatDate(dateStr) {
    if (!dateStr) return '—';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '—';
        return d.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    } catch (e) {
        return '—';
    }
}

// ===== ESCAPE HTML =====
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== OPEN ADD MODAL =====
function openAddModal() {
    console.log('📝 Opening Add Modal...');
    
    const modal = document.getElementById('studentModal');
    if (!modal) {
        console.error('❌ studentModal not found!');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Add New Student';
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
    
    // Set default values
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('admission_date').value = today;
    document.getElementById('year_level').value = '1';
    document.getElementById('status').value = 'Active';
    
    // Auto-generate student number
    document.getElementById('student_no').value = generateStudentNumber();
    
    // Clear any previous error states
    document.querySelectorAll('#studentForm .form-control').forEach(el => {
        el.classList.remove('error');
    });
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('full_name').focus();
    }, 200);
}

// ===== CLOSE MODAL =====
function closeModal(modalId) {
    console.log('📕 Closing modal:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// ===== EDIT STUDENT =====
async function editStudent(id) {
    console.log('✏️ Editing student ID:', id);
    
    if (!id) {
        showError('Invalid student ID');
        return;
    }

    try {
        const url = `${API_URL}?action=get&id=${id}`;
        console.log('📡 Fetching:', url);
        
        const response = await fetch(url);
        const json = await response.json();
        
        console.log('📊 Response:', json);
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to load student details');
        }

        const s = json.data;
        
        document.getElementById('modalTitle').textContent = 'Edit Student';
        document.getElementById('studentId').value = s.id;
        document.getElementById('student_no').value = s.student_no || '';
        document.getElementById('full_name').value = s.full_name || '';
        document.getElementById('email').value = s.email || '';
        document.getElementById('phone').value = s.phone || '';
        document.getElementById('department_id').value = s.department_id || '';
        document.getElementById('year_level').value = s.year_level || 1;
        document.getElementById('status').value = s.status || 'Active';
        document.getElementById('admission_date').value = s.admission_date || '';

        // Clear any previous error states
        document.querySelectorAll('#studentForm .form-control').forEach(el => {
            el.classList.remove('error');
        });

        const modal = document.getElementById('studentModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        console.log('✅ Student data loaded for editing');

    } catch (err) {
        console.error('❌ Error editing student:', err);
        showError('Could not load student: ' + err.message);
    }
}

// ===== DELETE STUDENT =====
async function deleteStudent(id, name) {
    console.log('🗑️ Deleting student:', id, name);
    
    if (!id) {
        showError('Invalid student ID');
        return;
    }

    // Custom confirm dialog
    if (!confirm(`Are you sure you want to delete student "${name}"?\n\nThis action cannot be undone.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    try {
        console.log('📡 Deleting...');
        
        const response = await fetch(API_URL, { 
            method: 'POST', 
            body: formData 
        });
        
        const json = await response.json();
        console.log('📊 Response:', json);
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to delete student');
        }

        showSuccess(`Student "${name}" deleted successfully`);
        
        // Reload the list
        const searchInput = document.getElementById('searchInput');
        loadStudents(searchInput ? searchInput.value : '');

    } catch (err) {
        console.error('❌ Error deleting student:', err);
        showError('Delete failed: ' + err.message);
    }
}

// ===== GENERATE STUDENT NUMBER =====
function generateStudentNumber() {
    const year = new Date().getFullYear();
    const random = Math.floor(Math.random() * 9000 + 1000);
    return `STU-${year}-${random}`;
}

// ===== HANDLE FORM SUBMIT =====
async function handleFormSubmit(e) {
    console.log('📝 Form submitted');
    
    const id = document.getElementById('studentId').value;
    const studentNo = document.getElementById('student_no').value.trim();
    const fullName = document.getElementById('full_name').value.trim();
    
    // Validate
    if (!studentNo) {
        showError('Student number is required');
        document.getElementById('student_no').focus();
        document.getElementById('student_no').classList.add('error');
        return;
    }
    
    if (!fullName) {
        showError('Full name is required');
        document.getElementById('full_name').focus();
        document.getElementById('full_name').classList.add('error');
        return;
    }

    // Create FormData
    const formData = new FormData(e.target);
    formData.set('action', id ? 'update' : 'create');

    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;

    try {
        console.log('📡 Saving student...');
        
        const response = await fetch(API_URL, { 
            method: 'POST', 
            body: formData 
        });
        
        const json = await response.json();
        console.log('📊 Response:', json);
        
        if (!json.success) {
            throw new Error(json.message || 'Failed to save student');
        }

        showSuccess(id ? 'Student updated successfully' : 'Student added successfully');
        
        // Close modal
        closeModal('studentModal');
        
        // Reload the list
        const searchInput = document.getElementById('searchInput');
        loadStudents(searchInput ? searchInput.value : '');

    } catch (err) {
        console.error('❌ Error saving student:', err);
        showError('Save failed: ' + err.message);
    } finally {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// ===== TOAST NOTIFICATIONS =====
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

// ===== GLOBAL EXPOSURE =====
window.openAddModal = openAddModal;
window.closeModal = closeModal;
window.editStudent = editStudent;
window.deleteStudent = deleteStudent;
window.loadStudents = loadStudents;
window.showSuccess = showSuccess;
window.showError = showError;
window.generateStudentNumber = generateStudentNumber;

// ===== CONSOLE LOG =====
console.log('✅ Students JS ready!');
console.log('📌 Available functions:');
console.log('  - openAddModal()');
console.log('  - closeModal(id)');
console.log('  - editStudent(id)');
console.log('  - deleteStudent(id, name)');
console.log('  - loadStudents(search)');
console.log('  - showSuccess(message)');
console.log('  - showError(message)');
console.log('⌨️ Keyboard shortcuts:');
console.log('  - Escape: Close Modal');