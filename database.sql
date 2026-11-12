CREATE DATABASE IF NOT EXISTS specmatrix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE specmatrix_db;

DROP TABLE IF EXISTS saved_comparisons;
DROP TABLE IF EXISTS devices;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_username_unique (username),
    UNIQUE KEY users_email_unique (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE devices (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    brand VARCHAR(60) NOT NULL,
    category VARCHAR(30) NOT NULL,
    processor VARCHAR(120) NOT NULL,
    ram_gb INT UNSIGNED NOT NULL,
    storage_gb INT UNSIGNED NOT NULL,
    gpu VARCHAR(120) NOT NULL DEFAULT '',
    battery_mah INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY devices_category_index (category),
    KEY devices_brand_index (brand)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE saved_comparisons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    device_1_id INT UNSIGNED NOT NULL,
    device_2_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY comparisons_user_index (user_id),
    CONSTRAINT comparisons_user_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT comparisons_device_one_foreign FOREIGN KEY (device_1_id) REFERENCES devices (id) ON DELETE CASCADE,
    CONSTRAINT comparisons_device_two_foreign FOREIGN KEY (device_2_id) REFERENCES devices (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@specmatrix.com', '$2y$10$JeIV6MF5Tkg2LeVMFh74VuGFjxlu05QFj03XTOUmYoEsQ34J3Gx0u', 'admin');

INSERT INTO devices (name, brand, category, processor, ram_gb, storage_gb, gpu, battery_mah, price, image_path) VALUES
('MacBook Air 13 M3', 'Apple', 'laptop', 'Apple M3 (8-core CPU)', 16, 512, 'Apple 10-core GPU', 5300, 1099.00, ''),
('MacBook Pro 14 M3 Pro', 'Apple', 'laptop', 'Apple M3 Pro (11-core CPU)', 18, 512, 'Apple 18-core GPU', 6300, 1999.00, ''),
('Dell XPS 15 9530', 'Dell', 'laptop', 'Intel Core i7-13700H', 16, 1024, 'NVIDIA GeForce RTX 4060 8GB', 6300, 1899.00, ''),
('ThinkPad X1 Carbon Gen 11', 'Lenovo', 'laptop', 'Intel Core i7-1365U', 16, 512, 'Intel Iris Xe Graphics', 3700, 1650.00, ''),
('HP Spectre x360 14', 'HP', 'laptop', 'Intel Core Ultra 7 155H', 16, 1024, 'Intel Arc Graphics', 6800, 1299.00, ''),
('ROG Zephyrus G14 2024', 'ASUS', 'laptop', 'AMD Ryzen 9 8945HS', 32, 1024, 'NVIDIA GeForce RTX 4070 8GB', 7300, 1599.00, ''),
('Swift Go 14', 'Acer', 'laptop', 'Intel Core i5-13500H', 16, 512, 'Intel Iris Xe Graphics', 4200, 749.00, ''),
('Katana 15 B13V', 'MSI', 'laptop', 'Intel Core i7-13620H', 16, 512, 'NVIDIA GeForce RTX 4050 6GB', 5300, 1099.00, ''),
('IdeaPad Slim 5 Gen 8', 'Lenovo', 'laptop', 'AMD Ryzen 7 7730U', 16, 512, 'AMD Radeon Graphics', 5700, 649.00, ''),
('Victus 16', 'HP', 'laptop', 'Intel Core i5-13500H', 16, 512, 'NVIDIA GeForce RTX 3050 6GB', 4700, 799.00, ''),
('iPhone 15 Pro', 'Apple', 'phone', 'Apple A17 Pro', 8, 256, 'Apple 6-core GPU', 3274, 999.00, ''),
('iPhone 15', 'Apple', 'phone', 'Apple A16 Bionic', 6, 128, 'Apple 5-core GPU', 3349, 799.00, ''),
('Galaxy S24 Ultra', 'Samsung', 'phone', 'Snapdragon 8 Gen 3 for Galaxy', 12, 512, 'Adreno 750', 5000, 1299.00, ''),
('Galaxy S24', 'Samsung', 'phone', 'Exynos 2400', 8, 256, 'Xclipse 940', 4000, 799.00, ''),
('Galaxy A55', 'Samsung', 'phone', 'Exynos 1480', 8, 128, 'Xclipse 530', 5000, 449.00, ''),
('Pixel 8 Pro', 'Google', 'phone', 'Google Tensor G3', 12, 128, 'Mali-G715 MC10', 5050, 899.00, ''),
('Pixel 8a', 'Google', 'phone', 'Google Tensor G3', 8, 128, 'Mali-G715 MC9', 4492, 499.00, ''),
('OnePlus 12', 'OnePlus', 'phone', 'Snapdragon 8 Gen 3', 16, 256, 'Adreno 750', 5400, 799.00, ''),
('Xiaomi 14', 'Xiaomi', 'phone', 'Snapdragon 8 Gen 3', 12, 512, 'Adreno 750', 4610, 999.00, ''),
('Phone (2a)', 'Nothing', 'phone', 'Dimensity 7200 Pro', 12, 256, 'Mali-G610 MC4', 5000, 349.00, ''),
('iPad Pro 11 M4', 'Apple', 'tablet', 'Apple M4 (9-core CPU)', 8, 256, 'Apple 10-core GPU', 8160, 999.00, ''),
('iPad Air 11 M2', 'Apple', 'tablet', 'Apple M2 (8-core CPU)', 8, 128, 'Apple 9-core GPU', 7600, 599.00, ''),
('Galaxy Tab S9', 'Samsung', 'tablet', 'Snapdragon 8 Gen 2 for Galaxy', 8, 128, 'Adreno 740', 8400, 799.00, ''),
('Galaxy Tab A9 Plus', 'Samsung', 'tablet', 'Snapdragon 695', 8, 128, 'Adreno 619', 7040, 219.00, ''),
('Tab P12', 'Lenovo', 'tablet', 'MediaTek Dimensity 7050', 8, 128, 'Mali-G68 MC4', 10200, 349.00, ''),
('Bravia XR A80L 55', 'Sony', 'androidtv', 'Cognitive Processor XR', 6, 32, 'XR Triluminos Pro', 0, 1499.00, ''),
('55QN90C Neo QLED', 'Samsung', 'androidtv', 'Quantum Neural Processor 4K', 8, 64, 'Quantum Matrix Technology', 0, 1499.00, ''),
('OLED evo C3 55', 'LG', 'androidtv', 'Alpha 9 AI Processor 4K Gen 6', 8, 32, 'OLED evo 4K panel', 0, 1299.00, ''),
('Smart TV X Pro 55', 'Xiaomi', 'androidtv', 'Quad-core Cortex-A55', 4, 32, 'Adreno 610', 0, 599.00, ''),
('55C645 QLED', 'TCL', 'androidtv', 'Quad-core Cortex-A55', 4, 32, 'Mali-G52 MC1', 0, 449.00, '');
