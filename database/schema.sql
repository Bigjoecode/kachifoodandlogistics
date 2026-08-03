-- Kachi Food & Logistics — schema
-- Safe to re-run: drops and recreates every table.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_events;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS logistics_events;
DROP TABLE IF EXISTS logistics_bookings;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS schema_migrations;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)  NOT NULL,
    email         VARCHAR(160)  NOT NULL UNIQUE,
    phone         VARCHAR(30)   NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
    company       VARCHAR(160)  NULL,
    address       VARCHAR(255)  NULL,
    city          VARCHAR(80)   NULL,
    state         VARCHAR(80)   NULL,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    slug        VARCHAR(140) NOT NULL UNIQUE,
    description VARCHAR(400) NULL,
    icon        VARCHAR(16)  NULL,
    sort_order  INT          NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products carry a retail price and an optional wholesale price that applies
-- once the line quantity reaches wholesale_min_qty.
CREATE TABLE products (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    category_id       INT           NULL,
    name              VARCHAR(160)  NOT NULL,
    slug              VARCHAR(180)  NOT NULL UNIQUE,
    sku               VARCHAR(60)   NULL UNIQUE,
    summary           VARCHAR(400)  NULL,
    description       TEXT          NULL,
    origin            VARCHAR(120)  NULL,
    unit              VARCHAR(40)   NOT NULL DEFAULT 'bag',
    retail_price      DECIMAL(12,2) NOT NULL DEFAULT 0,
    wholesale_price   DECIMAL(12,2) NULL,
    wholesale_min_qty INT           NOT NULL DEFAULT 10,
    sale_price        DECIMAL(12,2) NULL,
    min_order         INT           NOT NULL DEFAULT 1,
    stock_qty         INT           NOT NULL DEFAULT 0,
    image             VARCHAR(255)  NULL,
    is_featured       TINYINT(1)    NOT NULL DEFAULT 0,
    is_active         TINYINT(1)    NOT NULL DEFAULT 1,
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_products_active (is_active),
    INDEX idx_products_featured (is_featured),
    FULLTEXT KEY ft_products (name, summary, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    reference         VARCHAR(32)   NOT NULL UNIQUE,
    user_id           INT           NULL,
    type              ENUM('order','quote') NOT NULL DEFAULT 'order',
    status            ENUM('pending','quoted','confirmed','processing','dispatched','in_transit','delivered','cancelled')
                      NOT NULL DEFAULT 'pending',
    customer_name     VARCHAR(120)  NOT NULL,
    email             VARCHAR(160)  NOT NULL,
    phone             VARCHAR(30)   NOT NULL,
    company           VARCHAR(160)  NULL,
    delivery_address  VARCHAR(255)  NOT NULL,
    city              VARCHAR(80)   NOT NULL,
    state             VARCHAR(80)   NOT NULL,
    delivery_date     DATE          NULL,
    delivery_window   VARCHAR(40)   NULL,
    logistics_service VARCHAR(60)   NULL,
    notes             TEXT          NULL,
    subtotal          DECIMAL(12,2) NOT NULL DEFAULT 0,
    delivery_fee      DECIMAL(12,2) NOT NULL DEFAULT 0,
    total             DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method    ENUM('transfer','cash','terms') NOT NULL DEFAULT 'transfer',
    payment_status    ENUM('unpaid','part_paid','paid') NOT NULL DEFAULT 'unpaid',
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_orders_status (status),
    INDEX idx_orders_type (type),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id     INT           NOT NULL,
    product_id   INT           NULL,
    product_name VARCHAR(160)  NOT NULL,
    unit         VARCHAR(40)   NOT NULL,
    unit_price   DECIMAL(12,2) NOT NULL DEFAULT 0,
    quantity     INT           NOT NULL DEFAULT 1,
    line_total   DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_events (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT          NOT NULL,
    status     VARCHAR(40)  NOT NULL,
    note       VARCHAR(400) NULL,
    location   VARCHAR(120) NULL,
    created_by VARCHAR(120) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_events_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_events_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logistics bookings are independent of food orders: truck and van hire,
-- relocations, interstate runs. Customers can book without buying anything.
CREATE TABLE logistics_bookings (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    reference           VARCHAR(32)   NOT NULL UNIQUE,
    user_id             INT           NULL,
    status              ENUM('pending','quoted','confirmed','assigned','in_transit','completed','cancelled')
                        NOT NULL DEFAULT 'pending',
    customer_name       VARCHAR(120)  NOT NULL,
    email               VARCHAR(160)  NOT NULL,
    phone               VARCHAR(30)   NOT NULL,
    company             VARCHAR(160)  NULL,
    service_type        VARCHAR(60)   NOT NULL,
    vehicle_type        VARCHAR(60)   NOT NULL,
    pickup_address      VARCHAR(255)  NOT NULL,
    pickup_city         VARCHAR(80)   NOT NULL,
    destination_address VARCHAR(255)  NOT NULL,
    destination_city    VARCHAR(80)   NOT NULL,
    pickup_date         DATE          NULL,
    pickup_time         VARCHAR(40)   NULL,
    distance_band       VARCHAR(60)   NULL,
    weight_kg           INT           NOT NULL DEFAULT 0,
    urgency             VARCHAR(40)   NOT NULL DEFAULT 'Standard',
    needs_labour        TINYINT(1)    NOT NULL DEFAULT 0,
    description         TEXT          NULL,
    instructions        TEXT          NULL,
    estimated_price     DECIMAL(12,2) NOT NULL DEFAULT 0,
    quoted_price        DECIMAL(12,2) NULL,
    driver_name         VARCHAR(120)  NULL,
    vehicle_reg         VARCHAR(40)   NULL,
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_bookings_status (status),
    INDEX idx_bookings_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE logistics_events (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT          NOT NULL,
    status     VARCHAR(40)  NOT NULL,
    note       VARCHAR(400) NULL,
    location   VARCHAR(120) NULL,
    created_by VARCHAR(120) NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logevents_booking FOREIGN KEY (booking_id) REFERENCES logistics_bookings(id) ON DELETE CASCADE,
    INDEX idx_logevents_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(160) NOT NULL,
    phone      VARCHAR(30)  NULL,
    subject    VARCHAR(180) NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    setting_key   VARCHAR(80)  NOT NULL PRIMARY KEY,
    setting_value TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schema_migrations (
    migration VARCHAR(190) PRIMARY KEY,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
