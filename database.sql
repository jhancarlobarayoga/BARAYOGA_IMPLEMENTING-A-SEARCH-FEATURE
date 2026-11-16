CREATE DATABASE IF NOT EXISTS webdev_business_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE webdev_business_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Parent entity: one programmer can have many client records.
CREATE TABLE IF NOT EXISTS programmers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    specialization VARCHAR(120) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by INT UNSIGNED NOT NULL,
    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_updated_by INT UNSIGNED NOT NULL,
    CONSTRAINT fk_programmer_added_by FOREIGN KEY (added_by) REFERENCES users(id),
    CONSTRAINT fk_programmer_updated_by FOREIGN KEY (last_updated_by) REFERENCES users(id),
    INDEX idx_programmer_name (last_name, first_name),
    INDEX idx_programmer_email (email)
) ENGINE=InnoDB;

-- Child entity: each client is assigned to one parent programmer.
CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    programmer_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    company_name VARCHAR(150) NOT NULL,
    project_name VARCHAR(150) NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by INT UNSIGNED NOT NULL,
    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_updated_by INT UNSIGNED NOT NULL,
    CONSTRAINT fk_client_programmer FOREIGN KEY (programmer_id) REFERENCES programmers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_client_added_by FOREIGN KEY (added_by) REFERENCES users(id),
    CONSTRAINT fk_client_updated_by FOREIGN KEY (last_updated_by) REFERENCES users(id),
    INDEX idx_client_name (last_name, first_name),
    INDEX idx_client_company (company_name),
    INDEX idx_client_project (project_name)
) ENGINE=InnoDB;

-- Logs remain available after parent or child records are deleted.
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_name VARCHAR(50) NOT NULL,
    record_id BIGINT UNSIGNED DEFAULT NULL,
    action_name VARCHAR(30) NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    description VARCHAR(500) NOT NULL,
    action_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_time (action_time),
    INDEX idx_activity_entity (entity_name, record_id)
) ENGINE=InnoDB;
