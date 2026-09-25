// Courses Management - Complete JavaScript File

// Load all courses when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCourses();
});

// Load courses from server
function loadCourses() {
    const searchTerm = document.getElementById('searchInput')?.value || '';
    
    fetch('api/courses.php?action=get&search=' + encodeURIComponent(searchTerm))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCourses(data.data);
                document.getElementById('resultCount').textContent = data.total + ' courses';
            } else {
                showError('Failed to load courses');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Network error occurred');
        });
}

// Render courses in table
function renderCourses(courses) {
    const tbody = document.getElementById('coursesBody');
    
    if (!courses || courses.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:30px">No courses found</td></tr>';
        return;
    }
    
    let html = '';
    courses.forEach(course => {
        const statusBadge = course.status === 'Active' 
            ? '<span class="badge badge-success">Active</span>' 
            : '<span class="badge badge-danger">Inactive</span>';
        
        html += `
            <tr>
                <td><strong>${escapeHtml(course.course_code)}</strong></td>
                <td>${escapeHtml(course.course_name)}</td>
                <td>${escapeHtml(course.department_name || 'Unassigned')}</td>
                <td>${course.credits}</td>
                <td>${escapeHtml(course.teacher_name || 'Unassigned')}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon" onclick="editCourse(${course.id})" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn-icon" onclick="deleteCourse(${course.id})" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        loadCourses();
    }
});

// Optional: Auto-search with debounce
let searchTimeout;
document.getElementById('searchInput')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadCourses();
    }, 500);
});

// Open Add Course Modal
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Course';
    document.getElementById('courseId').value = '';
    document.getElementById('courseForm').reset();
    document.getElementById('course_code').value = '';
    document.getElementById('course_name').value = '';
    document.getElementById('credits').value = '3';
    document.getElementById('status').value = 'Active';
    openModal('courseModal');
}

// Edit Course - Load data into modal
function editCourse(id) {
    fetch(`api/courses.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const course = data.data;
                document.getElementById('modalTitle').textContent = 'Edit Course';
                document.getElementById('courseId').value = course.id;
                document.getElementById('course_code').value = course.course_code;
                document.getElementById('course_name').value = course.course_name;
                document.getElementById('department_id').value = course.department_id || '';
                document.getElementById('credits').value = course.credits;
                document.getElementById('teacher_id').value = course.teacher_id || '';
                document.getElementById('status').value = course.status || 'Active';
                openModal('courseModal');
            } else {
                showError('Failed to load course details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Network error occurred');
        });
}

// Delete Course with confirmation
function deleteCourse(id) {
    if (!confirm('Are you sure you want to delete this course?')) {
        return;
    }
    
    fetch('api/courses.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Course deleted successfully');
            loadCourses();
        } else {
            showError(data.message || 'Failed to delete course');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Network error occurred');
    });
}

// Handle form submission (Add/Edit)
document.getElementById('courseForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('courseId').value;
    const action = id ? 'update' : 'add';
    
    // Basic validation
    const courseCode = document.getElementById('course_code').value.trim();
    const courseName = document.getElementById('course_name').value.trim();
    
    if (!courseCode || !courseName) {
        showError('Course code and name are required');
        return;
    }
    
    const data = {
        action: action,
        id: id,
        course_code: courseCode,
        course_name: courseName,
        department_id: document.getElementById('department_id').value || null,
        credits: document.getElementById('credits').value || 3,
        teacher_id: document.getElementById('teacher_id').value || null,
        status: document.getElementById('status').value || 'Active'
    };
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    fetch('api/courses.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            showSuccess(action === 'add' ? 'Course added successfully' : 'Course updated successfully');
            closeModal('courseModal');
            loadCourses();
        } else {
            showError(data.message || 'Failed to save course');
        }
    })
    .catch(error => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        console.error('Error:', error);
        showError('Network error occurred');
    });
});

// Modal helper functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeModal(e.target.id);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal-overlay.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show success message (toast)
function showSuccess(message) {
    showToast(message, 'success');
}

// Show error message (toast)
function showError(message) {
    showToast(message, 'error');
}

// Toast notification system
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fa-solid ${icon}"></i>
        <span>${escapeHtml(message)}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:20px">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 4000);
}

// Initialize with loading state
console.log('Courses JS loaded successfully');