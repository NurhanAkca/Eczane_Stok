-- Eczane Stok - Gecmis Veri ve Genis Kategori Seed Dosyasi
-- Tek seferde import ederek aylik analiz ekranini dolu hale getirir.
-- Not: Bu dosya tekrar calistirilabilir. Kendi ekledigi batch kayitlarini temizler.

USE eczane_stok;

SET @batch_tag := 'HIST_SEED_V1';

-- 0) Ayni seed tekrar kosarsa sadece bu batch'e ait hareketleri sil
DELETE FROM stock_movements
WHERE note LIKE CONCAT(@batch_tag, '%');

-- 1) Kullanici id'leri (yoksa herhangi aktif kullaniciya duser)
SET @admin_user_id := (
    SELECT id FROM users WHERE username = 'admin' LIMIT 1
);
SET @pharmacist_user_id := (
    SELECT id FROM users WHERE username = 'eczaci' LIMIT 1
);
SET @fallback_user_id := (
    SELECT id FROM users ORDER BY id ASC LIMIT 1
);
SET @admin_user_id := IFNULL(@admin_user_id, @fallback_user_id);
SET @pharmacist_user_id := IFNULL(@pharmacist_user_id, @fallback_user_id);

-- 2) Kategori ve ilac cesitlerini genislet (barcode unique oldugu icin upsert)
INSERT INTO medicines (name, barcode, category, stock, unit_price, expiry_date)
VALUES
('Parol Plus 500mg', '869100000001', 'Agri Kesici', 120, 62.50, DATE_ADD(CURDATE(), INTERVAL 360 DAY)),
('Dolorex Forte', '869100000002', 'Agri Kesici', 95, 88.00, DATE_ADD(CURDATE(), INTERVAL 420 DAY)),
('Majezik 100mg', '869100000003', 'Agri Kesici', 110, 79.90, DATE_ADD(CURDATE(), INTERVAL 390 DAY)),
('Arveles 25mg', '869100000004', 'Agri Kesici', 105, 91.75, DATE_ADD(CURDATE(), INTERVAL 300 DAY)),

('Amoklav 1000mg', '869100000005', 'Antibiyotik', 70, 236.00, DATE_ADD(CURDATE(), INTERVAL 220 DAY)),
('Cipro 500mg', '869100000006', 'Antibiyotik', 65, 204.50, DATE_ADD(CURDATE(), INTERVAL 280 DAY)),
('Sef 500mg', '869100000007', 'Antibiyotik', 80, 189.00, DATE_ADD(CURDATE(), INTERVAL 260 DAY)),
('Klarit 500mg', '869100000008', 'Antibiyotik', 60, 248.40, DATE_ADD(CURDATE(), INTERVAL 240 DAY)),

('Gripon Cold', '869100000009', 'Soguk Alg.', 130, 98.90, DATE_ADD(CURDATE(), INTERVAL 320 DAY)),
('Daymed Plus', '869100000010', 'Soguk Alg.', 115, 106.20, DATE_ADD(CURDATE(), INTERVAL 350 DAY)),
('Nasalon Sprey', '869100000011', 'Soguk Alg.', 90, 122.00, DATE_ADD(CURDATE(), INTERVAL 280 DAY)),
('Thermo Flu', '869100000012', 'Soguk Alg.', 125, 94.30, DATE_ADD(CURDATE(), INTERVAL 300 DAY)),

('Vitamin D3 2000IU', '869100000013', 'Vitamin', 140, 149.90, DATE_ADD(CURDATE(), INTERVAL 500 DAY)),
('Omega 3 1000mg', '869100000014', 'Vitamin', 100, 215.00, DATE_ADD(CURDATE(), INTERVAL 520 DAY)),
('Magnezyum B6', '869100000015', 'Vitamin', 120, 167.40, DATE_ADD(CURDATE(), INTERVAL 450 DAY)),
('Multivitamin A-Z', '869100000016', 'Vitamin', 135, 186.60, DATE_ADD(CURDATE(), INTERVAL 480 DAY)),

('Tansil 8mg', '869100000017', 'Tansiyon', 88, 173.00, DATE_ADD(CURDATE(), INTERVAL 400 DAY)),
('CardioFix 5mg', '869100000018', 'Tansiyon', 76, 210.50, DATE_ADD(CURDATE(), INTERVAL 380 DAY)),
('Hipoten 50mg', '869100000019', 'Tansiyon', 82, 164.75, DATE_ADD(CURDATE(), INTERVAL 420 DAY)),
('Presson 10mg', '869100000020', 'Tansiyon', 79, 198.20, DATE_ADD(CURDATE(), INTERVAL 390 DAY)),

('Glifor 1000mg', '869100000021', 'Diyabet', 92, 224.00, DATE_ADD(CURDATE(), INTERVAL 430 DAY)),
('InsuCare Pen', '869100000022', 'Diyabet', 66, 312.80, DATE_ADD(CURDATE(), INTERVAL 300 DAY)),
('DiaControl 50mg', '869100000023', 'Diyabet', 74, 241.60, DATE_ADD(CURDATE(), INTERVAL 410 DAY)),
('SugarLess 850mg', '869100000024', 'Diyabet', 85, 205.90, DATE_ADD(CURDATE(), INTERVAL 440 DAY)),

('RefluStop 20mg', '869100000025', 'Sindirim', 108, 118.00, DATE_ADD(CURDATE(), INTERVAL 360 DAY)),
('Midepan 40mg', '869100000026', 'Sindirim', 99, 132.50, DATE_ADD(CURDATE(), INTERVAL 340 DAY)),
('Probio Plus', '869100000027', 'Sindirim', 90, 156.00, DATE_ADD(CURDATE(), INTERVAL 420 DAY)),
('Enzimax', '869100000028', 'Sindirim', 87, 145.70, DATE_ADD(CURDATE(), INTERVAL 390 DAY)),

('Alergon 10mg', '869100000029', 'Alerji', 114, 109.50, DATE_ADD(CURDATE(), INTERVAL 410 DAY)),
('Histafin Surup', '869100000030', 'Alerji', 96, 93.20, DATE_ADD(CURDATE(), INTERVAL 320 DAY)),
('Rinitol Sprey', '869100000031', 'Alerji', 89, 137.80, DATE_ADD(CURDATE(), INTERVAL 370 DAY)),
('Antihist 5mg', '869100000032', 'Alerji', 102, 121.40, DATE_ADD(CURDATE(), INTERVAL 400 DAY)),

('BabyVit D', '869100000033', 'Cocuk', 118, 85.50, DATE_ADD(CURDATE(), INTERVAL 500 DAY)),
('KidsCough Surup', '869100000034', 'Cocuk', 104, 97.30, DATE_ADD(CURDATE(), INTERVAL 330 DAY)),
('MiniParol Surup', '869100000035', 'Cocuk', 112, 74.90, DATE_ADD(CURDATE(), INTERVAL 360 DAY)),
('KidsProbio', '869100000036', 'Cocuk', 94, 128.00, DATE_ADD(CURDATE(), INTERVAL 430 DAY)),

('Dermacare Krem', '869100000037', 'Cilt', 92, 139.00, DATE_ADD(CURDATE(), INTERVAL 420 DAY)),
('Akne Jel', '869100000038', 'Cilt', 88, 151.75, DATE_ADD(CURDATE(), INTERVAL 380 DAY)),
('Yanik Merhemi', '869100000039', 'Cilt', 86, 116.90, DATE_ADD(CURDATE(), INTERVAL 340 DAY)),
('Nemlendirici Losyon', '869100000040', 'Cilt', 95, 172.40, DATE_ADD(CURDATE(), INTERVAL 460 DAY))
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    unit_price = VALUES(unit_price),
    expiry_date = VALUES(expiry_date);

-- 3) Son 18 ay icin gecmis satis/alim hareketleri uret
DROP TEMPORARY TABLE IF EXISTS tmp_seed_months;
CREATE TEMPORARY TABLE tmp_seed_months (
    month_start DATE NOT NULL PRIMARY KEY
);

INSERT INTO tmp_seed_months (month_start)
SELECT DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL n MONTH), '%Y-%m-01')
FROM (
    SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
    UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17
) AS nums;

SET @first_month := (
    SELECT MIN(month_start) FROM tmp_seed_months
);

-- 3.a) Her ilac icin baslangic toplu alim (ilk ay)
INSERT INTO stock_movements (medicine_id, movement_type, quantity, unit_price, note, user_id, movement_date)
SELECT
    m.id AS medicine_id,
    'IN' AS movement_type,
    140 + (m.id % 45) AS quantity,
    0 AS unit_price,
    CONCAT(@batch_tag, ' | initial_in') AS note,
    @admin_user_id AS user_id,
    DATE_ADD(@first_month, INTERVAL (m.id % 7) DAY) AS movement_date
FROM medicines m;

-- 3.b) Her ay ilac bazli satis (OUT) kayitlari
INSERT INTO stock_movements (medicine_id, movement_type, quantity, unit_price, note, user_id, movement_date)
SELECT
    m.id AS medicine_id,
    'OUT' AS movement_type,
    6 + ((m.id * 3 + MONTH(t.month_start)) % 18) AS quantity,
    ROUND(
        m.unit_price * (
            1 + (
                ((YEAR(t.month_start) * 12 + MONTH(t.month_start)) % 7) * 0.015
            )
        ),
        2
    ) AS unit_price,
    CONCAT(@batch_tag, ' | monthly_out') AS note,
    IF((m.id % 2) = 0, @pharmacist_user_id, @admin_user_id) AS user_id,
    DATE_ADD(
        DATE_ADD(t.month_start, INTERVAL ((m.id + MONTH(t.month_start)) % 20) DAY),
        INTERVAL ((m.id * 7) % 9) HOUR
    ) AS movement_date
FROM medicines m
CROSS JOIN tmp_seed_months t;

-- 3.c) Ceyreklik stok tamamlama alimlari (IN)
INSERT INTO stock_movements (medicine_id, movement_type, quantity, unit_price, note, user_id, movement_date)
SELECT
    m.id AS medicine_id,
    'IN' AS movement_type,
    30 + ((m.id + MONTH(t.month_start)) % 26) AS quantity,
    0 AS unit_price,
    CONCAT(@batch_tag, ' | quarterly_in') AS note,
    @admin_user_id AS user_id,
    DATE_ADD(t.month_start, INTERVAL 24 DAY) AS movement_date
FROM medicines m
CROSS JOIN tmp_seed_months t
WHERE MONTH(t.month_start) IN (1, 4, 7, 10);

DROP TEMPORARY TABLE IF EXISTS tmp_seed_months;

-- 4) Analizde hiz icin istege bagli index kontrolu (yoksa ekler)
SET @has_idx_note := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'stock_movements'
      AND INDEX_NAME = 'idx_stock_movements_note'
);
SET @sql_add_idx_note := IF(
    @has_idx_note = 0,
    'ALTER TABLE stock_movements ADD INDEX idx_stock_movements_note (note)',
    'SELECT "idx_stock_movements_note zaten var"'
);
PREPARE stmt_note FROM @sql_add_idx_note;
EXECUTE stmt_note;
DEALLOCATE PREPARE stmt_note;

-- 5) Kontrol sorgulari
-- SELECT DATE_FORMAT(movement_date, '%Y-%m') AS ay, COUNT(*) AS kayit
-- FROM stock_movements
-- WHERE note LIKE CONCAT(@batch_tag, '%')
-- GROUP BY DATE_FORMAT(movement_date, '%Y-%m')
-- ORDER BY ay DESC;
--
-- SELECT category, COUNT(*) AS ilac_sayisi
-- FROM medicines
-- GROUP BY category
-- ORDER BY ilac_sayisi DESC, category ASC;
