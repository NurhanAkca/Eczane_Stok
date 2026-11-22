-- Eczane Stok Sistemi - Aylik Satis ve Fiyat Analizi Eklentisi
-- Bu dosyayi mevcut kurulu veritabaninda calistirin.
-- Hedef DB: eczane_stok

USE eczane_stok;

-- 1) Hareket kaydina islem anindaki birim fiyati ekle (tekrar calistirmaya uygun)
SET @has_unit_price := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'stock_movements'
      AND COLUMN_NAME = 'unit_price'
);
SET @sql_add_unit_price := IF(
    @has_unit_price = 0,
    'ALTER TABLE stock_movements ADD COLUMN unit_price DECIMAL(10,2) NULL AFTER quantity',
    'SELECT "unit_price kolonu zaten var"'
);
PREPARE stmt FROM @sql_add_unit_price;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Mevcut kayitlar icin geriye donuk fiyat doldurma
--    OUT hareketlerinde mevcut ilac fiyatini baz alir.
UPDATE stock_movements sm
JOIN medicines m ON m.id = sm.medicine_id
SET sm.unit_price = m.unit_price
WHERE sm.movement_type = 'OUT' AND sm.unit_price IS NULL;

-- IN hareketlerinde fiyat bilgisi analizde kullanilmadigi icin 0 atanir.
UPDATE stock_movements
SET unit_price = 0
WHERE movement_type = 'IN' AND unit_price IS NULL;

-- 3) Bundan sonra yeni eklenen kayitlarda fiyatin otomatik gelmesi icin trigger
DROP TRIGGER IF EXISTS trg_stock_movements_set_unit_price;
DELIMITER $$
CREATE TRIGGER trg_stock_movements_set_unit_price
BEFORE INSERT ON stock_movements
FOR EACH ROW
BEGIN
    DECLARE current_price DECIMAL(10,2) DEFAULT 0;

    IF NEW.unit_price IS NULL THEN
        IF NEW.movement_type = 'OUT' THEN
            SELECT unit_price INTO current_price
            FROM medicines
            WHERE id = NEW.medicine_id
            LIMIT 1;

            SET NEW.unit_price = IFNULL(current_price, 0);
        ELSE
            SET NEW.unit_price = 0;
        END IF;
    END IF;
END$$
DELIMITER ;

-- 4) Performans icin indeksler (tekrar calistirmaya uygun)
SET @has_idx_date_type := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'stock_movements'
      AND INDEX_NAME = 'idx_stock_movements_date_type'
);
SET @sql_add_idx_date_type := IF(
    @has_idx_date_type = 0,
    'ALTER TABLE stock_movements ADD INDEX idx_stock_movements_date_type (movement_date, movement_type)',
    'SELECT "idx_stock_movements_date_type zaten var"'
);
PREPARE stmt FROM @sql_add_idx_date_type;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_medicine_date := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'stock_movements'
      AND INDEX_NAME = 'idx_stock_movements_medicine_date'
);
SET @sql_add_idx_medicine_date := IF(
    @has_idx_medicine_date = 0,
    'ALTER TABLE stock_movements ADD INDEX idx_stock_movements_medicine_date (medicine_id, movement_date)',
    'SELECT "idx_stock_movements_medicine_date zaten var"'
);
PREPARE stmt FROM @sql_add_idx_medicine_date;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5) Aylik satis ozeti (toplam adet ve ciro)
DROP VIEW IF EXISTS vw_monthly_sales_summary;
CREATE VIEW vw_monthly_sales_summary AS
SELECT
    DATE_FORMAT(sm.movement_date, '%Y-%m') AS report_month,
    SUM(sm.quantity) AS total_units_sold,
    ROUND(SUM(sm.quantity * sm.unit_price), 2) AS total_revenue,
    ROUND(AVG(sm.unit_price), 2) AS avg_sale_price
FROM stock_movements sm
WHERE sm.movement_type = 'OUT'
GROUP BY DATE_FORMAT(sm.movement_date, '%Y-%m');

-- 6) Ilac bazli aylik satis detayi
DROP VIEW IF EXISTS vw_monthly_medicine_sales;
CREATE VIEW vw_monthly_medicine_sales AS
SELECT
    DATE_FORMAT(sm.movement_date, '%Y-%m') AS report_month,
    m.id AS medicine_id,
    m.name AS medicine_name,
    m.category,
    SUM(sm.quantity) AS units_sold,
    ROUND(AVG(sm.unit_price), 2) AS avg_unit_price,
    ROUND(SUM(sm.quantity * sm.unit_price), 2) AS revenue
FROM stock_movements sm
JOIN medicines m ON m.id = sm.medicine_id
WHERE sm.movement_type = 'OUT'
GROUP BY DATE_FORMAT(sm.movement_date, '%Y-%m'), m.id, m.name, m.category;

-- 7) Aylik fiyat analizi (kategori bazli min/ort/max satis fiyati)
DROP VIEW IF EXISTS vw_monthly_price_analysis;
CREATE VIEW vw_monthly_price_analysis AS
SELECT
    DATE_FORMAT(sm.movement_date, '%Y-%m') AS report_month,
    m.category,
    ROUND(MIN(sm.unit_price), 2) AS min_price,
    ROUND(AVG(sm.unit_price), 2) AS avg_price,
    ROUND(MAX(sm.unit_price), 2) AS max_price
FROM stock_movements sm
JOIN medicines m ON m.id = sm.medicine_id
WHERE sm.movement_type = 'OUT'
GROUP BY DATE_FORMAT(sm.movement_date, '%Y-%m'), m.category;

-- 8) Ornek kullanim sorgulari
-- Tum aylarin satis ozeti:
-- SELECT * FROM vw_monthly_sales_summary;
--
-- Belirli bir ayin ilac bazli satislari:
-- SELECT * FROM vw_monthly_medicine_sales WHERE report_month = '2026-04';
--
-- Kategori bazli fiyat analizi:
-- SELECT * FROM vw_monthly_price_analysis;
