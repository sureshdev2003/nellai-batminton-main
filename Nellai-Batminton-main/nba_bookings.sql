-- NBA Badminton Academy Booking System Database Setup
-- Database: nba_bookings

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS nba_bookings;
USE nba_bookings;

-- Create slots table
CREATE TABLE IF NOT EXISTS slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    court_number INT NOT NULL,
    time_slot VARCHAR(10) NOT NULL,
    max_members INT DEFAULT 6,
    available_members INT DEFAULT 6,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_court_time (court_number, time_slot)
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    aadhaar VARCHAR(12) NOT NULL,
    booking_type ENUM('daily', 'monthly') NOT NULL,
    court_slot INT NOT NULL,
    time_slot VARCHAR(10) NOT NULL,
    members_count INT DEFAULT 1,
    start_date DATE,
    start_month VARCHAR(7),
    duration INT,
    payment_method ENUM('online', 'offline') NOT NULL,
    payment_screenshot VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@nba.com')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample slots for all courts and time slots
INSERT INTO slots (court_number, time_slot, max_members, available_members) VALUES
-- Court 1
(1, '06:00', 6, 6), (1, '07:00', 6, 6), (1, '08:00', 6, 6), (1, '09:00', 6, 6),
(1, '10:00', 6, 6), (1, '11:00', 6, 6), (1, '12:00', 6, 6), (1, '13:00', 6, 6),
(1, '14:00', 6, 6), (1, '15:00', 6, 6), (1, '16:00', 6, 6), (1, '17:00', 6, 6),
(1, '18:00', 6, 6), (1, '19:00', 6, 6), (1, '20:00', 6, 6), (1, '21:00', 6, 6),

-- Court 2
(2, '06:00', 6, 6), (2, '07:00', 6, 6), (2, '08:00', 6, 6), (2, '09:00', 6, 6),
(2, '10:00', 6, 6), (2, '11:00', 6, 6), (2, '12:00', 6, 6), (2, '13:00', 6, 6),
(2, '14:00', 6, 6), (2, '15:00', 6, 6), (2, '16:00', 6, 6), (2, '17:00', 6, 6),
(2, '18:00', 6, 6), (2, '19:00', 6, 6), (2, '20:00', 6, 6), (2, '21:00', 6, 6),

-- Court 3
(3, '06:00', 6, 6), (3, '07:00', 6, 6), (3, '08:00', 6, 6), (3, '09:00', 6, 6),
(3, '10:00', 6, 6), (3, '11:00', 6, 6), (3, '12:00', 6, 6), (3, '13:00', 6, 6),
(3, '14:00', 6, 6), (3, '15:00', 6, 6), (3, '16:00', 6, 6), (3, '17:00', 6, 6),
(3, '18:00', 6, 6), (3, '19:00', 6, 6), (3, '20:00', 6, 6), (3, '21:00', 6, 6),

-- Court 4
(4, '06:00', 6, 6), (4, '07:00', 6, 6), (4, '08:00', 6, 6), (4, '09:00', 6, 6),
(4, '10:00', 6, 6), (4, '11:00', 6, 6), (4, '12:00', 6, 6), (4, '13:00', 6, 6),
(4, '14:00', 6, 6), (4, '15:00', 6, 6), (4, '16:00', 6, 6), (4, '17:00', 6, 6),
(4, '18:00', 6, 6), (4, '19:00', 6, 6), (4, '20:00', 6, 6), (4, '21:00', 6, 6),

-- Court 5
(5, '06:00', 6, 6), (5, '07:00', 6, 6), (5, '08:00', 6, 6), (5, '09:00', 6, 6),
(5, '10:00', 6, 6), (5, '11:00', 6, 6), (5, '12:00', 6, 6), (5, '13:00', 6, 6),
(5, '14:00', 6, 6), (5, '15:00', 6, 6), (5, '16:00', 6, 6), (5, '17:00', 6, 6),
(5, '18:00', 6, 6), (5, '19:00', 6, 6), (5, '20:00', 6, 6), (5, '21:00', 6, 6),

-- Court 6
(6, '06:00', 6, 6), (6, '07:00', 6, 6), (6, '08:00', 6, 6), (6, '09:00', 6, 6),
(6, '10:00', 6, 6), (6, '11:00', 6, 6), (6, '12:00', 6, 6), (6, '13:00', 6, 6),
(6, '14:00', 6, 6), (6, '15:00', 6, 6), (6, '16:00', 6, 6), (6, '17:00', 6, 6),
(6, '18:00', 6, 6), (6, '19:00', 6, 6), (6, '20:00', 6, 6), (6, '21:00', 6, 6),

-- Court 7
(7, '06:00', 6, 6), (7, '07:00', 6, 6), (7, '08:00', 6, 6), (7, '09:00', 6, 6),
(7, '10:00', 6, 6), (7, '11:00', 6, 6), (7, '12:00', 6, 6), (7, '13:00', 6, 6),
(7, '14:00', 6, 6), (7, '15:00', 6, 6), (7, '16:00', 6, 6), (7, '17:00', 6, 6),
(7, '18:00', 6, 6), (7, '19:00', 6, 6), (7, '20:00', 6, 6), (7, '21:00', 6, 6),

-- Court 8
(8, '06:00', 6, 6), (8, '07:00', 6, 6), (8, '08:00', 6, 6), (8, '09:00', 6, 6),
(8, '10:00', 6, 6), (8, '11:00', 6, 6), (8, '12:00', 6, 6), (8, '13:00', 6, 6),
(8, '14:00', 6, 6), (8, '15:00', 6, 6), (8, '16:00', 6, 6), (8, '17:00', 6, 6),
(8, '18:00', 6, 6), (8, '19:00', 6, 6), (8, '20:00', 6, 6), (8, '21:00', 6, 6)
ON DUPLICATE KEY UPDATE available_members = VALUES(available_members);
