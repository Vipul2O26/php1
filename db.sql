-- =========================
-- DATABASE
-- =========================
CREATE DATABASE IF NOT EXISTS training;
USE training;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    register_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ACCOUNTS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS accounts (
    acc_id INT AUTO_INCREMENT PRIMARY KEY,
    balance DECIMAL(10,2) NOT NULL,
    user_id INT NOT NULL,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =========================
-- PRODUCTS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(50),
    product_detail VARCHAR(255),
    product_price DECIMAL(10,2),
    product_image VARCHAR(255),
    create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =========================
-- CART TABLE
-- =========================
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    qty INT NOT NULL,
    user_id INT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =========================
-- ORDER TABLE
-- =========================
CREATE TABLE IF NOT EXISTS product_order (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_qty INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    order_status VARCHAR(50),
    product_id INT,
    user_id INT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =========================
-- TRANSACTIONS TABLE
-- =========================
CREATE TABLE IF NOT EXISTS transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type VARCHAR(50),
    transaction_amount DECIMAL(10,2),
    acc_id INT,
    user_id INT,
    order_id INT,
    update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (acc_id) REFERENCES accounts(acc_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (order_id) REFERENCES product_order(order_id)
);

-- =========================
-- SAMPLE DATA
-- =========================

INSERT INTO users (name,email,password)
VALUES
('John','john@gmail.com','123'),
('TestUser','testuser@gmail.com','123');

INSERT INTO accounts (balance,user_id)
VALUES
(50000,1),
(100000,2);

INSERT INTO products (product_name,product_detail,product_price,product_image,user_id)
VALUES
('Samsung Mobile','5000mah fast charging',12000,'mobile.jpg',1);

-- =========================
-- TRIGGER 1
-- Deduct Balance Only If Order Placed
-- =========================

DELIMITER $$

CREATE TRIGGER deduct_balance_before_order
BEFORE INSERT ON product_order
FOR EACH ROW
BEGIN

IF NEW.order_status = 'order placed' THEN

    UPDATE accounts
    SET balance = balance - NEW.total_amount
    WHERE user_id = NEW.user_id;

END IF;

END$$

DELIMITER ;

-- =========================
-- TRIGGER 2
-- Insert Transaction Only If Order Placed
-- =========================

DELIMITER $$

CREATE TRIGGER insert_transaction_after_order
AFTER INSERT ON product_order
FOR EACH ROW
BEGIN

DECLARE accid INT;

IF NEW.order_status = 'order placed' THEN

    SELECT acc_id INTO accid
    FROM accounts
    WHERE user_id = NEW.user_id
    LIMIT 1;

    INSERT INTO transactions
    (
        transaction_type,
        transaction_amount,
        acc_id,
        user_id,
        order_id
    )
    VALUES
    (
        'debit',
        NEW.total_amount,
        accid,
        NEW.user_id,
        NEW.order_id
    );

END IF;

END$$

DELIMITER ;