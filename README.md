# EduSphere – Student Management System

Frontend: HTML, CSS, JavaScript (vanilla, no build step)
Backend: PHP + MySQL (mysqli)

## What's included

- **Login system** with PHP sessions (`login.php`, `logout.php`, session guard on every page)
- **Dashboard** (`dashboard.php`) — stat cards pull **live counts** from MySQL (students, teachers,
  active courses); charts (Student Growth, Attendance Analytics) use sample data via Chart.js;
  Upcoming Events and Recent Activities are pulled live from the database.
- **11 fully working modules**, each with search + Add/Edit/Delete through AJAX + prepared
  MySQL statements, exactly like the Students module:
  - **Students** — `students.php` + `api/students_api.php`
  - **Teachers** — `teachers.php` + `api/teachers_api.php`
  - **Courses** — `courses.php` + `api/courses_api.php`
  - **Attendance** — `attendance.php` + `api/attendance_api.php`
  - **Finance** — `finance.php` + `api/finance_api.php` (invoices/payments)
  - **Library** — `library.php` + `api/library_api.php` (book catalog)
  - **Hostel** — `hostel.php` + `api/hostel_api.php` (rooms)
  - **Transport** — `transport.php` + `api/transport_api.php` (routes)
  - **Timetable** — `timetable.php` + `api/timetable_api.php` (linked to Courses)
- **Reports, Messages, Settings, Support** — still scaffolded placeholders (layout only).

## Setup (XAMPP / WAMP / LAMP) — fresh install

1. Copy the `edusphere` folder into your server's web root
   (e.g. `htdocs/edusphere` for XAMPP, or `www/edusphere` for WAMP).
2. Start Apache + MySQL.
3. Open **phpMyAdmin** and import `database.sql` (this creates the `edusphere` database and
   ALL tables/sample data needed for every module above).
4. Open `config/db.php` and update `DB_USER` / `DB_PASS` if your MySQL isn't the
   default XAMPP `root` with no password.
5. In your browser, visit `http://localhost/edusphere/setup.php` **once**.
   This creates the default admin login:
   - Username: `admin`
   - Password: `admin123`
6. **Delete `setup.php`** after running it (security).
7. Go to `http://localhost/edusphere/login.php` and sign in.

## Already have the database set up from before?

If you imported `database.sql` previously (before Finance/Library/Hostel/Transport/Timetable
existed), just import **`database_updates.sql`** in phpMyAdmin — it only adds the new tables
(`payments`, `books`, `hostel_rooms`, `transport_routes`, `timetable`) with `CREATE TABLE IF NOT
EXISTS`, so it's safe to run even if some already exist. Then replace all the project files with
this new copy.

## Folder structure

```
edusphere/
├── api/
│   └── students_api.php     # AJAX endpoint: list/get/create/update/delete
├── assets/
│   ├── css/style.css        # All styling
│   └── js/                  # main.js, dashboard.js, students.js
├── config/
│   └── db.php               # MySQL connection settings
├── includes/
│   ├── header.php           # Top bar (search, notifications, user menu)
│   ├── sidebar.php           # Left navigation
│   └── session_check.php    # Login guard, included at top of every protected page
├── dashboard.php
├── students.php              # Full CRUD module
├── teachers.php / courses.php / attendance.php / finance.php /
│   library.php / hostel.php / transport.php / timetable.php /
│   reports.php / messages.php / settings.php / support.php   (scaffolded)
├── login.php / logout.php / index.php
├── setup.php                 # One-time admin account creator (delete after use)
└── database.sql              # Full schema + sample data
```

## Extending a placeholder module (e.g. Teachers)

Follow the same pattern as Students:

1. Add an `api/teachers_api.php` with `list` / `get` / `create` / `update` / `delete` actions
   (copy `students_api.php` and change the table/fields).
2. In `teachers.php`, replace the placeholder panel with a `data-table` + search box + modal,
   copying the markup from `students.php`.
3. Create `assets/js/teachers.js` copying `students.js`, changing the API URL and field names.

## Security notes

- Passwords are hashed with PHP's `password_hash()` / verified with `password_verify()`.
- All database queries use prepared statements (protects against SQL injection).
- Delete `setup.php` once the admin account is created.
- Change the default `admin123` password after first login (add a "change password"
  form to `settings.php` when you extend it).
