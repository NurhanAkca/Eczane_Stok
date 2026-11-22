-- Web Tabanli Eczane Stok Takip Sistemi SQL Kurulum Dosyasi
-- 1) phpMyAdmin'den calistirin veya mysql komut satirindan import edin.

CREATE DATABASE IF NOT EXISTS eczane_stok CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eczane_stok;

DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS medicines;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pharmacist') NOT NULL DEFAULT 'pharmacist',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    barcode VARCHAR(60) NOT NULL UNIQUE,
    category VARCHAR(80) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    movement_type ENUM('IN', 'OUT') NOT NULL,
    quantity INT NOT NULL,
    note VARCHAR(255) NULL,
    user_id INT NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Varsayilan kullanicilar
-- admin / Admin123*
-- eczaci / Eczaci123*
INSERT INTO users (full_name, username, password_hash, role, is_active) VALUES
('Sistem Yoneticisi', 'admin', 'Admin123*', 'admin', 1),
('Ornek Eczaci', 'eczaci', 'Eczaci123*', 'pharmacist', 1);

-- Ornek ilaclar
INSERT INTO medicines (name, barcode, category, stock, unit_price, expiry_date) VALUES
('Parol 500mg', '869000000001', 'Agri Kesici', 120, 45.50, DATE_ADD(CURDATE(), INTERVAL 300 DAY)),
('Augmentin 1000mg', '869000000002', 'Antibiyotik', 22, 215.00, DATE_ADD(CURDATE(), INTERVAL 90 DAY)),
('Aferin Tablet', '869000000003', 'Soguk Alg.', 14, 95.75, DATE_ADD(CURDATE(), INTERVAL 40 DAY)),
('Vitamin C 1000mg', '869000000004', 'Vitamin', 9, 110.00, DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
('Nurofen Cold', '869000000005', 'Soguk Alg.', 35, 130.00, DATE_ADD(CURDATE(), INTERVAL 180 DAY));

-- Ornek stok hareketleri
INSERT INTO stock_movements (medicine_id, movement_type, quantity, note, user_id) VALUES
(1, 'IN', 100, 'Acilis stogu', 1),
(1, 'IN', 20, 'Ek alim', 1),
(2, 'IN', 22, 'Acilis stogu', 1),
(3, 'IN', 14, 'Acilis stogu', 2),
(4, 'IN', 9, 'Acilis stogu', 2),
(5, 'IN', 35, 'Acilis stogu', 1),
(1, 'OUT', 8, 'Recete satisi', 2),
(3, 'OUT', 4, 'Tezgah satisi', 2);
