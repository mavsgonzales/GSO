-- =====================================================================
-- GSO (General Services Office) Property & Inventory Management Table
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.3+
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `gso_inventory` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `gso_inventory`;

DROP TABLE IF EXISTS `gso_property_inventory`;

CREATE TABLE `gso_property_inventory` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Identification & Tracking
    `barcode` VARCHAR(50) NOT NULL COMMENT 'Barcode tracking number',
    `property_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Official GSO property tag number',
    `article_road_name` VARCHAR(255) NOT NULL COMMENT 'Article title or Road/Infrastructure name',
    `description` TEXT NULL COMMENT 'Detailed item specifications, model, serial no.',
    
    -- Quantities & Financial Valuation
    `onhand_quantity` DECIMAL(12, 2) NOT NULL DEFAULT 1.00 COMMENT 'Current verified quantity on hand',
    `unit` VARCHAR(50) NOT NULL DEFAULT 'unit' COMMENT 'Unit of measure (e.g. pc, unit, set, lot, meters)',
    `unit_cost` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 COMMENT 'Acquisition or unit cost in PHP',
    `onhand_total_value` DECIMAL(15, 2) GENERATED ALWAYS AS (`onhand_quantity` * `unit_cost`) STORED COMMENT 'Auto-computed total value on hand',
    
    -- Physical Status & Custodianship
    `status` ENUM('Serviceable', 'Unserviceable', 'Under Repair', 'Surrendered', 'Disposed') NOT NULL DEFAULT 'Serviceable' COMMENT 'Physical condition status',
    `location` VARCHAR(255) NOT NULL COMMENT 'Physical location, office room, or road section',
    `accountable` VARCHAR(255) NOT NULL COMMENT 'Name of currently accountable officer / employee',
    `designation` VARCHAR(255) NULL COMMENT 'Designation or job title of accountable person',
    `department` VARCHAR(255) NOT NULL COMMENT 'Department, Division, or Office assigned to',
    `previously_accountable` VARCHAR(255) NULL COMMENT 'Name of previous custodian if transferred',
    
    -- Dates & Lifecycle
    `acquisition_date` DATE NOT NULL COMMENT 'Date asset was acquired / received',
    `date_surrendered` DATE NULL DEFAULT NULL COMMENT 'Date returned / surrendered to GSO if any',
    
    -- Procurement & Accounting References
    `po_number` VARCHAR(100) NULL COMMENT 'Purchase Order Number',
    `pr_number` VARCHAR(100) NULL COMMENT 'Purchase Request Number',
    `invoice_number` VARCHAR(100) NULL COMMENT 'Sales Invoice Number',
    `are_number` VARCHAR(100) NULL COMMENT 'Inventory Custodian Slip (ICS) No.',
    `air_number` VARCHAR(100) NULL COMMENT 'Acceptance and Inspection Report (AIR) No.',
    `supplier` VARCHAR(255) NULL COMMENT 'Supplier / Vendor / Contractor company name',
    `source_of_fund` VARCHAR(100) NULL COMMENT 'Source of Fund (e.g. General Fund, SEF, 20% Dev Fund)',
    `general_ledger` VARCHAR(100) NULL COMMENT 'General Ledger Account Title / Code (e.g. 1-07-05-020)',
    `remarks` TEXT NULL COMMENT 'Inspection notes, maintenance history, or remarks',
    
    -- System Audit Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes for fast search & reporting
    INDEX `idx_barcode` (`barcode`),
    INDEX `idx_status` (`status`),
    INDEX `idx_department` (`department`),
    INDEX `idx_accountable` (`accountable`),
    INDEX `idx_are_number` (`are_number`),
    INDEX `idx_po_number` (`po_number`),
    INDEX `idx_acquisition_date` (`acquisition_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
