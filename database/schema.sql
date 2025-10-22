-- Create database
CREATE DATABASE lake_victoria_tilapia_depot;
USE lake_victoria_tilapia_depot;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Fish table
CREATE TABLE fish (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(50),
    stock_quantity INT DEFAULT 0,
    weight_range VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    mpesa_receipt VARCHAR(100),
    shipping_address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    fish_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (fish_id) REFERENCES fish(id)
);

-- Cart table
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    fish_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (fish_id) REFERENCES fish(id),
    UNIQUE KEY unique_cart_item (customer_id, fish_id)
);

-- Insert sample data
INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES
('admin', 'admin@tilapiadepot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+254700000000', 'Nairobi, Kenya', 'admin'),
('staff1', 'staff@tilapiadepot.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Staff', '+254711111111', 'Kisumu, Kenya', 'staff'),
('customer1', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Customer', '+254722222222', 'Mombasa, Kenya', 'customer');

INSERT INTO fish (name, description, price, category, stock_quantity, weight_range) VALUES
('Fresh Tilapia', 'Freshly caught Lake Victoria Tilapia, perfect for grilling', 450.00, 'Fresh', 100, '300-500g'),
('Smoked Tilapia', 'Traditional smoked tilapia with rich flavor', 600.00, 'Smoked', 50, '250-400g'),
('Tilapia Fillets', 'Boneless tilapia fillets, ready to cook', 550.00, 'Fillets', 75, '200-300g'),
('Large Tilapia', 'Premium large tilapia for special occasions', 700.00, 'Fresh', 30, '500-800g'),
('Tilapia Powder', 'Dried and powdered tilapia for soups and sauces', 300.00, 'Processed', 40, '100g packets');