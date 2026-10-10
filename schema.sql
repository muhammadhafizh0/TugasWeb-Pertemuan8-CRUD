-- Tugas Rutin 8 - CRUD Inventaris
-- Import: mysql -u root -p < schema.sql  (atau lewat phpMyAdmin > Import)

CREATE DATABASE IF NOT EXISTS inventaris_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventaris_db;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS categories;

-- Normalisasi 3NF: kategori & supplier dipisah dari produk
CREATE TABLE categories (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE suppliers (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(100) NOT NULL,
  phone VARCHAR(20)  NULL
) ENGINE=InnoDB;

CREATE TABLE products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)  NOT NULL,
  category_id INT           NOT NULL,
  supplier_id INT           NOT NULL,
  price       DECIMAL(12,2) NOT NULL CHECK (price >= 0),
  stock       INT           NOT NULL DEFAULT 0 CHECK (stock >= 0),
  created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id)
    REFERENCES suppliers(id)  ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Bonus: log aktivitas (dipakai oleh transaction pada delete)
CREATE TABLE activity_logs (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  action     VARCHAR(20)  NOT NULL,
  detail     VARCHAR(255) NOT NULL,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed data (minimal 5 per tabel)
INSERT INTO categories (name) VALUES
 ('Aksesoris'), ('Laptop'), ('Monitor'), ('Penyimpanan'), ('Jaringan');

INSERT INTO suppliers (name, phone) VALUES
 ('PT Medan Teknologi', '061-4521000'),
 ('CV Sumut Digital',   '0812-6000-1111'),
 ('Toko Komputer Maju', '0813-7000-2222'),
 ('PT Nusantara Elektronik', '061-7788990'),
 ('UD Karya Mandiri',   '0852-1000-3333');

INSERT INTO products (name, category_id, supplier_id, price, stock) VALUES
 ('Laptop ASUS Vivobook 14', 2, 1, 9500000, 10),
 ('Mouse Wireless Logitech', 1, 2, 150000, 20),
 ('Keyboard Mechanical',     1, 3, 450000, 15),
 ('Monitor LED 24 inch',     3, 4, 1800000, 8),
 ('SSD NVMe 512GB',          4, 1, 750000, 25),
 ('Router WiFi 6',           5, 5, 650000, 12);

INSERT INTO activity_logs (action, detail) VALUES
 ('SEED', 'Data awal dimuat dari schema.sql');
