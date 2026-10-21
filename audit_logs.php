<?php
declare(strict_types=1);

/**
 * Audit Logs Entry Point -> AuditLogController@index
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\AuditLogController;

$controller = new AuditLogController();
$controller->index();
