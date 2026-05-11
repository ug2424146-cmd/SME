-- Student Performance Management System Database Schema
-- MySQL Database

-- Create database
CREATE DATABASE IF NOT EXISTS student_system;
USE student_system;

-- Users table (for all roles)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    date_of_birth DATE,
    enrollment_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    teacher_id VARCHAR(20) UNIQUE NOT NULL,
    department VARCHAR(100),
    hire_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Subjects table
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    credits INT DEFAULT 3,
    description TEXT
);

-- Marks table
CREATE TABLE marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    exam_type VARCHAR(50) NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL,
    semester VARCHAR(20),
    exam_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE(student_id, subject_id, exam_type, semester)
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE(student_id, subject_id, date)
);

-- AI Feedback table
CREATE TABLE ai_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    risk_level ENUM('LOW', 'MEDIUM', 'HIGH') NOT NULL,
    advice TEXT NOT NULL,
    attendance_score DECIMAL(5,2),
    marks_score DECIMAL(5,2),
    gpa_score DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_marks_student ON marks(student_id);
CREATE INDEX idx_marks_subject ON marks(subject_id);
CREATE INDEX idx_attendance_student ON attendance(student_id);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Insert sample data

-- Insert Admin user
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@school.edu', '$2b$12$YourHashedPasswordHere', 'admin');

-- Insert Teachers
INSERT INTO users (name, email, password, role) VALUES
('John Smith', 'john.smith@school.edu', '$2b$12$YourHashedPasswordHere', 'teacher'),
('Jane Doe', 'jane.doe@school.edu', '$2b$12$YourHashedPasswordHere', 'teacher');

INSERT INTO teachers (user_id, teacher_id, department) VALUES
(2, 'T001', 'Computer Science'),
(3, 'T002', 'Mathematics');

-- Insert Students
INSERT INTO users (name, email, password, role) VALUES
('Alice Johnson', 'alice.johnson@student.edu', '$2b$12$YourHashedPasswordHere', 'student'),
('Bob Wilson', 'bob.wilson@student.edu', '$2b$12$YourHashedPasswordHere', 'student'),
('Charlie Brown', 'charlie.brown@student.edu', '$2b$12$YourHashedPasswordHere', 'student');

INSERT INTO students (user_id, student_id, date_of_birth) VALUES
(4, 'S001', '2002-05-15'),
(5, 'S002', '2002-08-20'),
(6, 'S003', '2002-03-10');

-- Insert Subjects
INSERT INTO subjects (name, code, credits, description) VALUES
('Introduction to Programming', 'CS101', 3, 'Fundamentals of programming'),
('Mathematics I', 'MATH101', 4, 'Calculus and algebra'),
('Database Systems', 'CS201', 3, 'Database design and SQL'),
('Web Development', 'CS202', 3, 'HTML, CSS, JavaScript'),
('Data Structures', 'CS301', 4, 'Algorithms and data structures');

-- Insert Marks
INSERT INTO marks (student_id, subject_id, teacher_id, exam_type, marks_obtained, max_marks, semester, exam_date) VALUES
(1, 1, 1, 'Midterm', 85.0, 100.0, 'Fall 2024', '2024-10-15'),
(1, 2, 2, 'Midterm', 78.0, 100.0, 'Fall 2024', '2024-10-16'),
(1, 3, 1, 'Midterm', 92.0, 100.0, 'Fall 2024', '2024-10-17'),
(2, 1, 1, 'Midterm', 65.0, 100.0, 'Fall 2024', '2024-10-15'),
(2, 2, 2, 'Midterm', 58.0, 100.0, 'Fall 2024', '2024-10-16'),
(2, 3, 1, 'Midterm', 72.0, 100.0, 'Fall 2024', '2024-10-17'),
(3, 1, 1, 'Midterm', 45.0, 100.0, 'Fall 2024', '2024-10-15'),
(3, 2, 2, 'Midterm', 52.0, 100.0, 'Fall 2024', '2024-10-16'),
(3, 3, 1, 'Midterm', 38.0, 100.0, 'Fall 2024', '2024-10-17');

-- Insert Attendance
INSERT INTO attendance (student_id, subject_id, date, status) VALUES
(1, 1, '2024-09-01', 'present'),
(1, 1, '2024-09-02', 'present'),
(1, 1, '2024-09-03', 'present'),
(1, 2, '2024-09-01', 'present'),
(1, 2, '2024-09-02', 'present'),
(1, 2, '2024-09-03', 'late'),
(2, 1, '2024-09-01', 'present'),
(2, 1, '2024-09-02', 'absent'),
(2, 1, '2024-09-03', 'present'),
(2, 2, '2024-09-01', 'present'),
(2, 2, '2024-09-02', 'present'),
(2, 2, '2024-09-03', 'absent'),
(3, 1, '2024-09-01', 'absent'),
(3, 1, '2024-09-02', 'absent'),
(3, 1, '2024-09-03', 'present'),
(3, 2, '2024-09-01', 'absent'),
(3, 2, '2024-09-02', 'present'),
(3, 2, '2024-09-03', 'absent');

-- Insert AI Feedback
INSERT INTO ai_feedback (student_id, risk_level, advice, attendance_score, marks_score, gpa_score) VALUES
(1, 'LOW', 'Excellent performance. Keep up the good work!', 90.0, 85.0, 3.5),
(2, 'MEDIUM', 'Focus on improving attendance and study regularly.', 70.0, 65.0, 2.8),
(3, 'HIGH', 'Critical: Improve attendance immediately and seek academic support.', 45.0, 45.0, 1.9);
