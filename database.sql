CREATE DATABASE IF NOT EXISTS office_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE office_management;

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    department VARCHAR(60) NOT NULL,
    role_title VARCHAR(80) NOT NULL,
    status ENUM('Active', 'On Leave', 'Remote') DEFAULT 'Active',
    salary DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    joined_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO employees (full_name, email, department, role_title, status, salary, joined_date) VALUES
('Ananya Sen', 'ananya.sen@company.com', 'Engineering', 'Software Developer', 'Active', 65000.00, '2025-02-10'),
('Rahul Verma', 'rahul.verma@company.com', 'Human Resources', 'HR Manager', 'Remote', 58000.00, '2024-08-15'),
('Priya Das', 'priya.das@company.com', 'Finance', 'Accountant', 'On Leave', 52000.00, '2025-01-05');
