<?php
declare(strict_types=1);

/**
 * GSO Property & Inventory Management System
 * Entry Point -> PropertyController@index
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\PropertyController;

$controller = new PropertyController();
$controller->index();
