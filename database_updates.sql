-- ============================================================
-- EduSphere - Database Update
-- Run this on your EXISTING 'edusphere' database in phpMyAdmin
-- (adds tables needed for Finance, Library, Hostel, Transport, Timetable)
-- The 'attendance' table already exists from database.sql
-- ============================================================

USE edusphere;

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
