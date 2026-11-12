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
('XPS 15 9530', 'Dell', 'laptop', 'Intel Core i7-13700H', 16, 1024, 'NVIDIA GeForce RTX 4060 8GB', 6300, 1899.00, ''),
('ThinkPad X1 Carbon Gen 11', 'Lenovo', 'laptop', 'Intel Core i7-1365U', 16, 512, 'Intel Iris Xe Graphics', 3700, 1650.00, ''),
('iPhone 15 Pro', 'Apple', 'phone', 'Apple A17 Pro', 8, 256, 'Apple 6-core GPU', 3274, 999.00, ''),
('Galaxy S24 Ultra', 'Samsung', 'phone', 'Snapdragon 8 Gen 3 for Galaxy', 12, 512, 'Adreno 750', 5000, 1299.00, ''),
('Pixel 8 Pro', 'Google', 'phone', 'Google Tensor G3', 12, 128, 'Mali-G715 MC10', 5050, 899.00, '');
