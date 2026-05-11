CREATE DATABASE IF NOT EXISTS sme_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sme_platform;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    role_id INT NULL,
    role ENUM('admin','manager','employee') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role_id (role_id),
    CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS role_id INT NULL;
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_role_id (role_id);
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    assigned_to INT NOT NULL,
    assigned_by INT NULL,
    required_skill_id INT NULL,
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    status ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
    deadline DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tasks_assigned_to (assigned_to),
    INDEX idx_tasks_assigned_by (assigned_by),
    INDEX idx_tasks_required_skill (required_skill_id),
    INDEX idx_tasks_status (status),
    INDEX idx_tasks_deadline (deadline),
    CONSTRAINT fk_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS required_skill_id INT NULL;

CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS employee_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner','intermediate','expert') NOT NULL DEFAULT 'beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_employee_skill (user_id, skill_id),
    INDEX idx_employee_skills_user (user_id),
    INDEX idx_employee_skills_skill (skill_id),
    CONSTRAINT fk_employee_skills_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_employee_skills_skill FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating TINYINT NOT NULL,
    feedback TEXT NULL,
    review_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_performance_user (user_id),
    INDEX idx_performance_reviewer (reviewer_id),
    CONSTRAINT fk_performance_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_performance_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_performance_rating CHECK (rating BETWEEN 1 AND 5)
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_is_read (is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS task_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_comments_task (task_id),
    INDEX idx_task_comments_user (user_id),
    CONSTRAINT fk_task_comments_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS task_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_attachments_task (task_id),
    CONSTRAINT fk_task_attachments_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_attachments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(120) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_logs_user (user_id),
    INDEX idx_activity_logs_created_at (created_at),
    CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS task_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NULL,
    action VARCHAR(120) NOT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_task_history_task (task_id),
    INDEX idx_task_history_user (user_id),
    CONSTRAINT fk_task_history_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_task_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS user_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_department (user_id, department_id),
    INDEX idx_user_departments_user (user_id),
    INDEX idx_user_departments_department (department_id),
    CONSTRAINT fk_user_departments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_departments_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO roles (role_name) VALUES
('admin'),
('manager'),
('employee')
ON DUPLICATE KEY UPDATE role_name = VALUES(role_name);

INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@sme.local', '$2y$10$t266ySp0nbYUPwpNvfPxveAIVNIgWNR1q71dCqPeooxnx1xk.sSf2', 'admin'),
('Manager User', 'manager@sme.local', '$2y$10$t266ySp0nbYUPwpNvfPxveAIVNIgWNR1q71dCqPeooxnx1xk.sSf2', 'manager'),
('Employee User', 'employee@sme.local', '$2y$10$t266ySp0nbYUPwpNvfPxveAIVNIgWNR1q71dCqPeooxnx1xk.sSf2', 'employee')
ON DUPLICATE KEY UPDATE email = VALUES(email);

UPDATE users u
INNER JOIN roles r ON r.role_name = u.role
SET u.role_id = r.id
WHERE u.role_id IS NULL;
