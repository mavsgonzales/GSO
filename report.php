<?php
declare(strict_types=1);

/**
 * Report Generation Entry Point -> ReportController@index
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\ReportController;

$controller = new ReportController();
$controller->index();
