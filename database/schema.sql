-- Procurement Management System Database Schema
-- Run this script to create all necessary tables

-- Create database
CREATE DATABASE IF NOT EXISTS procurement_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE procurement_system;

-- Users table for authentication and role management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    department VARCHAR(100),
    role ENUM('admin', 'budget_officer', 'procurement_officer', 'auditor', 'department_head', 'finance_officer') DEFAULT 'department_head',
    status ENUM('active', 'inactive') DEFAULT 'active',
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procurements table - main procurement records
CREATE TABLE procurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    department VARCHAR(100) NOT NULL,
    created_by INT NOT NULL,
    status ENUM('draft', 'in_progress', 'completed', 'cancelled', 'on_hold') DEFAULT 'draft',
    current_stage ENUM('budget_formulation', 'budget_review', 'procurement_planning', 'contract_execution', 'disbursement', 'monitoring', 'audit') DEFAULT 'budget_formulation',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    estimated_value DECIMAL(15, 2),
    actual_value DECIMAL(15, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_reference (reference_number),
    INDEX idx_status (status),
    INDEX idx_stage (current_stage),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Budget formulation stage
CREATE TABLE budget_formulation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    budget_amount DECIMAL(15, 2) NOT NULL,
    category ENUM('CAPEX', 'OPEX', 'Infrastructure', 'Supplies') NOT NULL,
    funding_source ENUM('national_budget', 'local_budget', 'special_fund', 'grant') NOT NULL,
    cost_breakdown TEXT NOT NULL,
    justification TEXT NOT NULL,
    submitted_by INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Budget review stage
CREATE TABLE budget_review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    review_date DATE NOT NULL,
    approval_status ENUM('approved', 'approved_with_modifications', 'pending_revision', 'rejected') NOT NULL,
    approved_amount DECIMAL(15, 2),
    review_comments TEXT NOT NULL,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Procurement planning stage
CREATE TABLE procurement_planning (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    procurement_method ENUM('public_bidding', 'limited_source', 'direct_contracting', 'shopping', 'negotiated') NOT NULL,
    timeline VARCHAR(255) NOT NULL,
    technical_specs TEXT NOT NULL,
    evaluation_criteria TEXT NOT NULL,
    bid_documents TEXT NOT NULL,
    planned_by INT NOT NULL,
    planned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (planned_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contract execution stage
CREATE TABLE contract_execution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    winning_bidder VARCHAR(255) NOT NULL,
    contract_amount DECIMAL(15, 2) NOT NULL,
    contract_date DATE NOT NULL,
    contract_duration VARCHAR(100) NOT NULL,
    deliverables TEXT NOT NULL,
    payment_terms TEXT NOT NULL,
    contract_file_url VARCHAR(255),
    executed_by INT NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (executed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Disbursement stage
CREATE TABLE disbursements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    payment_number VARCHAR(50) NOT NULL,
    payment_amount DECIMAL(15, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('bank_transfer', 'check', 'lddap', 'cash') NOT NULL,
    invoice_number VARCHAR(100) NOT NULL,
    payment_notes TEXT NOT NULL,
    processed_by INT NOT NULL,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Monitoring stage
CREATE TABLE monitoring_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    monitoring_date DATE NOT NULL,
    progress_percentage TINYINT NOT NULL CHECK (progress_percentage >= 0 AND progress_percentage <= 100),
    deliverables_status TEXT NOT NULL,
    issues_identified TEXT NOT NULL,
    recommendations TEXT NOT NULL,
    monitored_by INT NOT NULL,
    monitored_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (monitored_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit stage
CREATE TABLE audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    auditor_id INT NOT NULL,
    audit_date DATE NOT NULL,
    compliance_status ENUM('fully_compliant', 'mostly_compliant', 'partially_compliant', 'non_compliant') NOT NULL,
    audit_findings TEXT NOT NULL,
    recommendations TEXT NOT NULL,
    overall_rating ENUM('excellent', 'satisfactory', 'needs_improvement', 'unsatisfactory') NOT NULL,
    audited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (auditor_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log for audit trail
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id),
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments/notes system
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    user_id INT NOT NULL,
    stage VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents/attachments
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    stage VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_procurement (procurement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    procurement_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (procurement_id) REFERENCES procurements(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123 - change this in production!)
INSERT INTO users (username, email, password_hash, full_name, department, role) VALUES
('admin', 'admin@procurement.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'IT Department', 'admin'),
('budget_officer', 'budget@procurement.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budget Officer', 'Finance', 'budget_officer'),
('procurement', 'procurement@procurement.gov', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Procurement Officer', 'Procurement', 'procurement_officer');

-- Views for reporting
CREATE VIEW procurement_summary AS
SELECT 
    p.id,
    p.reference_number,
    p.title,
    p.department,
    p.status,
    p.current_stage,
    p.priority,
    p.estimated_value,
    p.actual_value,
    u.full_name as created_by_name,
    p.created_at,
    p.updated_at,
    DATEDIFF(CURRENT_DATE, p.created_at) as days_open
FROM procurements p
JOIN users u ON p.created_by = u.id;

CREATE VIEW stage_statistics AS
SELECT 
    current_stage,
    COUNT(*) as count,
    AVG(DATEDIFF(CURRENT_DATE, created_at)) as avg_days_in_stage
FROM procurements
WHERE status = 'in_progress'
GROUP BY current_stage;

-- Indexes for performance
CREATE INDEX idx_proc_status_stage ON procurements(status, current_stage);
CREATE INDEX idx_proc_department ON procurements(department);
CREATE INDEX idx_activity_date ON activity_logs(created_at DESC);
