<?php
declare(strict_types=1);

/**
 * Backward-compatibility wrapper for Database connection & helper functions.
 * Delegates to App\Config\Database and App\Models\AuditLogModel.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Config\Database;
use App\Models\AuditLogModel;

function getDatabaseConnection(): PDO
{
    return Database::getConnection();
}

function initDatabase(): void
{
    Database::init();
}

function writeInventoryAuditLog(PDO $pdo, string $action, ?int $recordId, array $details): void
{
    AuditLogModel::log($action, $recordId, $details);
}
