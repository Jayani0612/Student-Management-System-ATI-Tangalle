console.log('✅ teachers.js loaded!');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Page loaded!');
    loadTeachers();
});

function loadTeachers() {
    console.log('🔄 Loading teachers...');
    
    fetch('get_teachers.php')
        .then(response => response.json())
        .then(data => {
            console.log('📊 Data:', data);
            if (Array.isArray(data)) {
                displayTeachers(data);
            } else {
                showError('Invalid data format');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showError(error.message);
        });
}

function displayTeachers(teachers) {
    const tbody = document.getElementById('teachersBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (!teachers || teachers.length === 0) {
        tbody.innerHTML = <tr><td colspan="7" style="text-align:center;padding:30px;">No teachers found</td></tr>;
        return;
    }
    
    teachers.forEach(teacher => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${teacher.full_name || '-'}</td>
            <td>${teacher.subject || '-'}</td>
            <td>${teacher.department_name || '-'}</td>
            <td>${teacher.email || '-'}</td>
            <td>${teacher.phone || '-'}</td>
            <td>${teacher.joined_date || '-'}</td>
            <td>
                <button onclick="editTeacher(${teacher.id})">✏️</button>
                <button onclick="deleteTeacher(${teacher.id})">🗑️</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showError(message) {
    const tbody = document.getElementById('teachersBody');
    if (tbody) {
        tbody.innerHTML = <tr><td colspan="7" style="text-align:center;color:red;padding:30px;">❌ ${message}</td></tr>;
    }
}

function openAddModal() {
    document.getElementById('teacherModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function editTeacher(id) {
    alert('Edit: ' + id);
}

function deleteTeacher(id) {
    if (confirm('Delete?')) {
        alert('Deleted: ' + id);
    }
}s
<?php
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'edusphere';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    echo json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]);
    exit;
}

$sql = "SELECT * FROM teachers";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    exit;
}

$teachers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $teachers[] = $row;
}

// JSON Array එකක් හැටියට Send කරන්න
echo json_encode($teachers);

$conn->close();
?>