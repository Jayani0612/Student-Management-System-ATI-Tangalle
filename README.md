<div align="center">

# 🎓 EduSphere - Student Management System
### 🏫 Advanced Technological Institute (ATI) - Tangalle

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A comprehensive web-based Student Management System developed using PHP, HTML, and MySQL to manage all aspects of an educational institute.

</div>

---

## 📌 Project Overview
EduSphere is a full-featured management system designed for the Advanced Technological Institute (ATI) - Tangalle. It streamlines daily operations including student admissions, teacher management, course scheduling, attendance, finance, library, hostel, and transport.

## ✨ Key Features

<table>
  <tr>
    <td width="50%">
      <h3>👨‍🎓 Academic Management</h3>
      <ul>
        <li><b>Students:</b> Add, update, and manage student records.</li>
        <li><b>Teachers:</b> Manage teacher profiles, subjects, and departments.</li>
        <li><b>Courses:</b> Manage the course catalog, credits, and instructors.</li>
        <li><b>Attendance:</b> Track daily student attendance (Present, Absent, Late).</li>
        <li><b>Timetable:</b> Manage the weekly class schedule.</li>
        <li><b>Exams:</b> Schedule and manage examinations.</li>
      </ul>
    </td>
    <td width="50%">
      <h3>🏢 Administration</h3>
      <ul>
        <li><b>Finance:</b> Manage fee invoices and payment statuses.</li>
        <li><b>Library:</b> Manage the book catalog and copy availability.</li>
        <li><b>Hostel:</b> Manage hostel rooms and occupancy.</li>
        <li><b>Transport:</b> Manage transport routes, vehicles, and drivers.</li>
        <li><b>Events:</b> Manage academic and campus events.</li>
        <li><b>Reports:</b> Generate and export system data.</li>
      </ul>
    </td>
  </tr>
</table>

## 🛠️ Technologies Used

*   **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Font Awesome
*   **Backend:** PHP (with `mysqli` prepared statements)
*   **Database:** MySQL
*   **Server:** XAMPP / WAMP / LAMP
*   **Charting:** Chart.js (for dashboard visualizations)

## 🚀 How to Install & Run

Follow these steps to run the project on your local machine:

1.  **Install a local server:** Download and install [XAMPP](https://www.apachefriends.org/) or WAMP.
2.  **Move the project:** Copy the `edusphere` folder into your server's web root directory (e.g., `C:\xampp\htdocs\edusphere`).
3.  **Start the server:** Open the XAMPP Control Panel and start **Apache** and **MySQL**.
4.  **Import the database:** 
    *   Open your browser and go to `http://localhost/phpmyadmin`.
    *   Create a new database named `edusphere`.
    *   Import the `database.sql` file from the project folder.
    *   *(If you already have an old database, import `database_updates.sql` instead to add the new tables).*
5.  **Configure the connection:** Open `config/db.php` and update `DB_USER` and `DB_PASS` if your MySQL credentials are different from the default XAMPP settings (`root` / no password).
6.  **Create the admin account:** Visit `http://localhost/edusphere/setup.php` in your browser. This will create the default admin account.
7.  **Log in:** Go to `http://localhost/edusphere/login.php` and use the following credentials:
    *   **Username:** `admin`
    *   **Password:** `admin123`
8.  **Security:** **Delete `setup.php`** from your project folder after creating the admin account.

## 📂 Folder Structure

```text
edusphere/
├── api/                     # AJAX endpoints for CRUD operations
│   └── ...
├── assets/                  # CSS, JS, and images
│   ├── css/
│   └── js/
├── config/                  # Database connection
│   └── db.php
├── includes/                # Reusable components
│   ├── header.php
│   ├── sidebar.php
│   └── session_check.php
├── dashboard.php            # Main dashboard
├── students.php             # Student management module
├── teachers.php             # Teacher management module
├── courses.php              # Course management module
├── attendance.php           # Attendance tracking module
├── finance.php              # Finance & payment module
├── library.php              # Library management module
├── hostel.php               # Hostel management module
├── transport.php            # Transport management module
├── timetable.php            # Timetable module
├── events.php               # Events module
├── exams.php                # Exams module
├── reports.php              # Reports module
├── login.php                # Login page
├── logout.php               # Logout script
├── index.php                # Redirects to login/dashboard
├── setup.php                # One-time admin account creator (delete after use)
└── database.sql             # Full database schema & sample data
