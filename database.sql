-- ============================================================
-- EduSphere - Student Management System
-- Database Schema + Sample Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS edusphere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edusphere;

-- ------------------------------------------------------------
-- Users (login / authentication)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'Admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- NOTE: run setup.php once in your browser after import to create
-- the default admin account with a properly hashed password.

-- ------------------------------------------------------------
-- Departments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO departments (name) VALUES
('Engineering'), ('Arts & Science'), ('Business'), ('Medicine'), ('Law');

-- ------------------------------------------------------------
-- Students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    department_id INT,
    year_level INT DEFAULT 1,
    status ENUM('Active','Inactive','Graduated') DEFAULT 'Active',
    admission_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

INSERT INTO students (student_no, full_name, email, phone, department_id, year_level, status, admission_date) VALUES
('STU-1001', 'Alex Johnson', 'alex.johnson@example.com', '0771234567', 1, 2, 'Active', '2024-01-15'),
('STU-1002', 'Nadeesha Perera', 'nadeesha.p@example.com', '0772345678', 2, 1, 'Active', '2025-02-10'),
('STU-1003', 'Kasun Fernando', 'kasun.f@example.com', '0773456789', 1, 3, 'Active', '2023-06-20'),
('STU-1004', 'Ishara Silva', 'ishara.s@example.com', '0774567890', 3, 2, 'Active', '2024-03-05'),
('STU-1005', 'Ravindu Bandara', 'ravindu.b@example.com', '0775678901', 4, 4, 'Active', '2022-09-01'),
('STU-1006', 'Sithara Jayasuriya', 'sithara.j@example.com', '0776789012', 2, 1, 'Inactive', '2025-01-12');

-- ------------------------------------------------------------
-- Teachers
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    department_id INT,
    subject VARCHAR(100),
    joined_date DATE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

INSERT INTO teachers (full_name, email, phone, department_id, subject, joined_date) VALUES
('Dr. Mahesh Gunawardena', 'mahesh.g@example.com', '0711234567', 1, 'Software Engineering', '2019-08-01'),
('Ms. Chamari Weerasinghe', 'chamari.w@example.com', '0712345678', 2, 'English Literature', '2020-01-15'),
('Mr. Dinesh Rathnayake', 'dinesh.r@example.com', '0713456789', 3, 'Accounting', '2018-05-10');

-- ------------------------------------------------------------
-- Courses
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(150) NOT NULL,
    department_id INT,
    credits INT DEFAULT 3,
    teacher_id INT,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

INSERT INTO courses (course_code, course_name, department_id, credits, teacher_id, status) VALUES
('CS201', 'Data Structures & Algorithms', 1, 4, 1, 'Active'),
('EN105', 'English Literature I', 2, 3, 2, 'Active'),
('BUS110', 'Principles of Accounting', 3, 3, 3, 'Active');

-- ------------------------------------------------------------
-- Attendance
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present','Absent','Late') DEFAULT 'Present',
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Events (Upcoming Events widget)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    time_label VARCHAR(50),
    location VARCHAR(100)
);

INSERT INTO events (title, event_date, time_label, location) VALUES
('Faculty Meeting', '2026-10-24', '10:00 AM - 12:00 PM', 'Conference Room A'),
('Semester Exams Start', '2026-10-26', 'All Day Event', 'Main Campus'),
('EduSphere Tech Expo', '2026-10-28', '9:00 AM', 'Main Auditorium');

-- ------------------------------------------------------------
-- Activity Log (Recent Activities widget)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_type VARCHAR(50),
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO activity_log (activity_type, description) VALUES
('student', 'New Student registered: Alex Johnson'),
('payment', 'Payment Received: #INV-4921 ($850)');

-- ------------------------------------------------------------
-- Finance / Payments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    invoice_no VARCHAR(30) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    status ENUM('Paid','Pending','Overdue') DEFAULT 'Pending',
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
);

INSERT INTO payments (student_id, invoice_no, amount, payment_date, status, notes) VALUES
(1, 'INV-4921', 850.00, '2026-07-01', 'Paid', 'Semester tuition fee'),
(2, 'INV-4922', 850.00, NULL, 'Pending', 'Semester tuition fee'),
(3, 'INV-4900', 300.00, '2026-05-15', 'Paid', 'Hostel fee');

-- ------------------------------------------------------------
-- Library - Books
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100),
    isbn VARCHAR(30),
    category VARCHAR(60),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO books (title, author, isbn, category, total_copies, available_copies) VALUES
('Introduction to Algorithms', 'Cormen, Leiserson, Rivest', '978-0262033848', 'Computer Science', 5, 3),
('Clean Code', 'Robert C. Martin', '978-0132350884', 'Computer Science', 4, 4),
('Principles of Economics', 'N. Gregory Mankiw', '978-1305585126', 'Business', 3, 1);

-- ------------------------------------------------------------
-- Hostel - Rooms
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS hostel_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_no VARCHAR(20) NOT NULL UNIQUE,
    block VARCHAR(50),
    room_type ENUM('Single','Shared','Dormitory') DEFAULT 'Shared',
    capacity INT DEFAULT 2,
    occupied INT DEFAULT 0,
    status ENUM('Available','Full','Maintenance') DEFAULT 'Available'
);

INSERT INTO hostel_rooms (room_no, block, room_type, capacity, occupied, status) VALUES
('A-101', 'Block A', 'Shared', 2, 2, 'Full'),
('A-102', 'Block A', 'Shared', 2, 1, 'Available'),
('B-201', 'Block B', 'Single', 1, 0, 'Available');

-- ------------------------------------------------------------
-- Transport - Routes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transport_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(100) NOT NULL,
    vehicle_no VARCHAR(20),
    driver_name VARCHAR(100),
    capacity INT DEFAULT 40,
    stops VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active'
);

INSERT INTO transport_routes (route_name, vehicle_no, driver_name, capacity, stops, status) VALUES
('Route 1 - City Center', 'WP-NA-1234', 'Sunil Perera', 40, 'Town Hall, Main Street, Campus Gate', 'Active'),
('Route 2 - Negombo Road', 'WP-NB-5678', 'Kamal Silva', 35, 'Negombo Bus Stand, Beach Road, Campus Gate', 'Active');

-- ------------------------------------------------------------
-- Timetable
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(50),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

INSERT INTO timetable (course_id, day_of_week, start_time, end_time, room) VALUES
(1, 'Monday', '09:00:00', '11:00:00', 'Lab 1'),
(2, 'Tuesday', '10:00:00', '11:30:00', 'Room 204'),
(3, 'Wednesday', '13:00:00', '15:00:00', 'Room 105');
