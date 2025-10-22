-- Update schema for Lake Victoria Tilapia Depot
-- Add quantity_kg field to support weight-based ordering

USE lake_victoria_tilapia_depot;

-- Add quantity_kg column to cart table (in addition to quantity for units)
ALTER TABLE cart 
ADD COLUMN quantity_kg DECIMAL(10,2) DEFAULT 1.00 AFTER quantity;

-- Add quantity_kg column to order_items table  
ALTER TABLE order_items 
ADD COLUMN quantity_kg DECIMAL(10,2) DEFAULT 1.00 AFTER quantity;

-- Update fish table description to clarify that price is per kg
-- (No schema change needed, just update data)
UPDATE fish SET description = CONCAT(description, ' (Price per KG)') 
WHERE description NOT LIKE '%(Price per KG)%';

-- Update orders table to include 'completed' status if not already present
ALTER TABLE orders 
MODIFY COLUMN status ENUM('pending', 'confirmed', 'processing', 'completed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending';
