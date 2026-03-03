-- M-Pesa Payment Tracking Tables
USE lake_victoria_tilapia_depot;

-- Payment requests table (logs STK push requests)
CREATE TABLE IF NOT EXISTS payment_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    phone VARCHAR(15) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    merchant_request_id VARCHAR(100),
    checkout_request_id VARCHAR(100) UNIQUE,
    response_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_checkout_request (checkout_request_id),
    INDEX idx_order_id (order_id)
);

-- Payment transactions table (logs callback responses)
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) DEFAULT 0,
    mpesa_receipt VARCHAR(100),
    phone VARCHAR(15),
    transaction_date VARCHAR(20),
    status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    failure_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_mpesa_receipt (mpesa_receipt),
    INDEX idx_order_status (order_id, status)
);
