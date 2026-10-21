<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    // Configuration - Set to 'sqlite' or 'mysql'
    public static string $driver = 'sqlite';

    // MySQL Settings (used if driver is changed to 'mysql')
    public static string $host = '127.0.0.1';
    public static string $port = '3306';
    public static string $db   = 'gso_inventory';
    public static string $user = 'root';
    public static string $pass = '';

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        try {
            if (self::$driver === 'mysql') {
                $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db . ";charset=utf8mb4";
                self::$pdo = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } else {
                $dbPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'gso_inventory.sqlite';
                self::$pdo = new PDO("sqlite:{$dbPath}", null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$pdo->exec("PRAGMA journal_mode = WAL;");
            }
        } catch (PDOException $e) {
            die("<h3>Database Connection Error:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p>");
        }

        return self::$pdo;
    }

    public static function init(): void
    {
        $pdo = self::getConnection();

        if (self::$driver === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS gso_property_inventory (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    barcode TEXT,
                    property_number TEXT,
                    article_road_name TEXT,
                    description TEXT,
                    onhand_quantity REAL DEFAULT 0,
                    unit TEXT,
                    unit_cost REAL DEFAULT 0,
                    onhand_total_value REAL DEFAULT 0,
                    status TEXT DEFAULT 'Serviceable',
                    location TEXT,
                    accountable TEXT,
                    designation TEXT,
                    department TEXT,
                    previously_accountable TEXT,
                    acquisition_date TEXT,
                    date_surrendered TEXT,
                    po_number TEXT,
                    pr_number TEXT,
                    invoice_number TEXT,
                    are_number TEXT,
                    air_number TEXT,
                    remarks TEXT,
                    supplier TEXT,
                    source_of_fund TEXT,
                    general_ledger TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    username VARCHAR(50) PRIMARY KEY,
                    password_hash VARCHAR(255) NOT NULL,
                    role VARCHAR(20) NOT NULL DEFAULT 'user',
                    department VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS inventory_audit_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    action TEXT NOT NULL,
                    record_id INTEGER,
                    details TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS inventory_verifications (
                    record_id INTEGER PRIMARY KEY,
                    verified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    verified_by VARCHAR(50) NOT NULL,
                    scan_count INTEGER NOT NULL DEFAULT 1
                );
            ");

            $userColumns = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_ASSOC), 'name');
            if (!in_array('role', $userColumns, true)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'");
            }
            if (!in_array('department', $userColumns, true)) {
                $pdo->exec('ALTER TABLE users ADD COLUMN department VARCHAR(255) NULL');
            }
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `gso_property_inventory` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `barcode` VARCHAR(50) NULL,
                    `property_number` VARCHAR(50) NULL,
                    `article_road_name` VARCHAR(255) NULL,
                    `description` TEXT NULL,
                    `onhand_quantity` DECIMAL(12, 2) DEFAULT 0.00,
                    `unit` VARCHAR(50) NULL,
                    `unit_cost` DECIMAL(15, 2) DEFAULT 0.00,
                    `onhand_total_value` DECIMAL(15, 2) DEFAULT 0.00,
                    `status` VARCHAR(50) DEFAULT 'Serviceable',
                    `location` VARCHAR(255) NULL,
                    `accountable` VARCHAR(255) NULL,
                    `designation` VARCHAR(255) NULL,
                    `department` VARCHAR(255) NULL,
                    `previously_accountable` VARCHAR(255) NULL,
                    `acquisition_date` DATE NULL,
                    `date_surrendered` DATE NULL,
                    `po_number` VARCHAR(100) NULL,
                    `pr_number` VARCHAR(100) NULL,
                    `invoice_number` VARCHAR(100) NULL,
                    `are_number` VARCHAR(100) NULL,
                    `air_number` VARCHAR(100) NULL,
                    `remarks` TEXT NULL,
                    `supplier` VARCHAR(255) NULL,
                    `source_of_fund` VARCHAR(100) NULL,
                    `general_ledger` VARCHAR(100) NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX (`barcode`),
                    INDEX (`property_number`),
                    INDEX (`status`),
                    INDEX (`department`),
                    INDEX (`accountable`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                    `username` VARCHAR(50) PRIMARY KEY,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
                    `department` VARCHAR(255),
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `inventory_audit_logs` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(50) NOT NULL,
                    `action` VARCHAR(20) NOT NULL,
                    `record_id` BIGINT NULL,
                    `details` LONGTEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (`created_at`),
                    INDEX (`record_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `inventory_verifications` (
                    `record_id` BIGINT UNSIGNED PRIMARY KEY,
                    `verified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `verified_by` VARCHAR(50) NOT NULL,
                    `scan_count` BIGINT UNSIGNED NOT NULL DEFAULT 1
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $userColumns = array_column($pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_ASSOC), 'Field');
            if (!in_array('role', $userColumns, true)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'");
            }
            if (!in_array('department', $userColumns, true)) {
                $pdo->exec('ALTER TABLE users ADD COLUMN department VARCHAR(255) NULL');
            }
        }
    }
}
