-- ====================================================================
-- DATABASE MIGRATION FOR POINT OF SALE (POS) SYSTEM UPGRADE
-- FOR LAKE VICTORIA TILAPIA DEPOT
-- ====================================================================

USE lake_victoria_tilapia_depot;

-- --------------------------------------------------------------------
-- 1.1 Alter the `fish` table
-- --------------------------------------------------------------------
ALTER TABLE fish
ADD COLUMN size VARCHAR(20) DEFAULT 'Size 1' AFTER name,
ADD COLUMN type ENUM('raw', 'fried') DEFAULT 'raw' AFTER size,
ADD COLUMN cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price,
ADD COLUMN retail_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER cost_price,
ADD COLUMN wholesale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER retail_price,
ADD COLUMN stock_qty INT NOT NULL DEFAULT 0 AFTER stock_quantity,
ADD COLUMN low_stock_threshold INT DEFAULT 10 AFTER stock_qty,
ADD COLUMN unit ENUM('piece', 'kg', 'crate') DEFAULT 'piece' AFTER low_stock_threshold;

-- Migrate existing fish price data to retail_price and stock_quantity to stock_qty
UPDATE fish SET retail_price = price, wholesale_price = price * 0.90, stock_qty = stock_quantity;

-- --------------------------------------------------------------------
-- 1.2 Alter the `users` table
-- --------------------------------------------------------------------
ALTER TABLE users
ADD COLUMN customer_type ENUM('retail', 'wholesale') DEFAULT 'retail' AFTER role,
ADD COLUMN credit_limit DECIMAL(10,2) DEFAULT 0.00 AFTER customer_type,
ADD COLUMN outstanding_balance DECIMAL(10,2) DEFAULT 0.00 AFTER credit_limit,
ADD COLUMN two_factor_secret VARCHAR(64) NULL AFTER outstanding_balance,
ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER two_factor_secret,
ADD COLUMN two_factor_verified_at DATETIME NULL AFTER two_factor_enabled,
ADD COLUMN login_attempts INT DEFAULT 0 AFTER two_factor_verified_at,
ADD COLUMN locked_until DATETIME NULL AFTER login_attempts,
ADD COLUMN last_login DATETIME NULL AFTER locked_until,
ADD COLUMN last_login_ip VARCHAR(45) NULL AFTER last_login;

-- Ensure phone is VARCHAR(20)
ALTER TABLE users MODIFY COLUMN phone VARCHAR(20);

-- --------------------------------------------------------------------
-- 1.3 Create `pos_sales` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pos_sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_ref VARCHAR(20) UNIQUE NOT NULL,
  cashier_id INT NOT NULL,
  customer_id INT NULL,          -- NULL for anonymous walk-ins
  customer_name VARCHAR(100),    -- for walk-ins without account
  customer_phone VARCHAR(20),
  customer_type ENUM('retail','wholesale') DEFAULT 'retail',
  subtotal DECIMAL(10,2) NOT NULL,
  discount DECIMAL(10,2) DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  amount_tendered DECIMAL(10,2),
  change_given DECIMAL(10,2),
  payment_method ENUM('cash','mpesa','credit') NOT NULL,
  mpesa_ref VARCHAR(50),
  credit_recorded TINYINT(1) DEFAULT 0,
  status ENUM('completed','voided','refunded') DEFAULT 'completed',
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cashier_id) REFERENCES users(id),
  FOREIGN KEY (customer_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.4 Create `pos_sale_items` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pos_sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  fish_id INT NOT NULL,
  fish_name VARCHAR(100),
  size VARCHAR(20),
  type ENUM('raw','fried'),
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (sale_id) REFERENCES pos_sales(id) ON DELETE CASCADE,
  FOREIGN KEY (fish_id) REFERENCES fish(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.5 Create `debt_ledger` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS debt_ledger (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  sale_id INT NULL,
  type ENUM('debt','payment') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  balance_after DECIMAL(10,2) NOT NULL,
  notes TEXT,
  recorded_by INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sale_id) REFERENCES pos_sales(id) ON DELETE SET NULL,
  FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.6 Create `stock_deliveries` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stock_deliveries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  delivery_ref VARCHAR(20) UNIQUE NOT NULL,
  supplier_name VARCHAR(100) NOT NULL,
  supplier_phone VARCHAR(20),
  received_by INT NOT NULL,
  total_cost DECIMAL(10,2) DEFAULT 0.00,
  notes TEXT,
  delivery_date DATE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (received_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.7 Create `stock_delivery_items` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stock_delivery_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  delivery_id INT NOT NULL,
  fish_id INT NOT NULL,
  quantity_received INT NOT NULL,
  cost_per_unit DECIMAL(10,2) NOT NULL,
  line_cost DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (delivery_id) REFERENCES stock_deliveries(id) ON DELETE CASCADE,
  FOREIGN KEY (fish_id) REFERENCES fish(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.8 Create `frying_batches` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS frying_batches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_ref VARCHAR(20) UNIQUE NOT NULL,
  started_by INT NOT NULL,
  raw_fish_id INT NOT NULL,
  fried_fish_id INT NOT NULL,
  raw_quantity INT NOT NULL,
  fried_quantity_expected INT NOT NULL,
  fried_quantity_actual INT,
  oil_cost DECIMAL(10,2) DEFAULT 0.00,
  fuel_cost DECIMAL(10,2) DEFAULT 0.00,
  labor_cost DECIMAL(10,2) DEFAULT 0.00,
  total_frying_cost DECIMAL(10,2) DEFAULT 0.00,
  status ENUM('in_progress','completed','cancelled') DEFAULT 'in_progress',
  started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  notes TEXT,
  FOREIGN KEY (started_by) REFERENCES users(id),
  FOREIGN KEY (raw_fish_id) REFERENCES fish(id),
  FOREIGN KEY (fried_fish_id) REFERENCES fish(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.9 Create `wastage_log` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wastage_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fish_id INT NOT NULL,
  quantity INT NOT NULL,
  reason VARCHAR(255),
  estimated_loss DECIMAL(10,2),
  recorded_by INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (fish_id) REFERENCES fish(id),
  FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.10 Create `two_factor_codes` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS two_factor_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  code VARCHAR(10) NOT NULL,
  type ENUM('login','password_reset','email_change') DEFAULT 'login',
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.11 Create `audit_log` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  table_affected VARCHAR(50),
  record_id INT NULL,
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 1.12 Create `rate_limits` table
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  action_key VARCHAR(100) NOT NULL,
  attempts INT DEFAULT 1,
  first_attempt DATETIME NOT NULL,
  last_attempt DATETIME NOT NULL,
  UNIQUE KEY unique_ip_action (ip_address, action_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
