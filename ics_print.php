<?php
declare(strict_types=1);

/**
 * ICS Print Entry Point -> PropertyController@printIcs
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\PropertyController;

$controller = new PropertyController();
$controller->printIcs();
