<?php
declare(strict_types=1);

/**
 * Search Suggestions API Entry Point -> PropertyController@suggestions
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\PropertyController;

$controller = new PropertyController();
$controller->suggestions();
