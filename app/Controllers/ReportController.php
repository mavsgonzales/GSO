<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PropertyModel;

class ReportController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $isAdmin = Auth::isAdmin();
        $userDepartment = Auth::department();

        $filters = [
            'department' => 'Department',
            'accountable' => 'Accountable',
            'general_ledger' => 'General Ledger Account (GLA)',
        ];

        $fields = [
            'property_number' => 'Property Number',
            'article_road_name' => 'Article / Road Name',
            'onhand_quantity' => 'On-hand Qty',
            'unit' => 'Unit',
            'unit_cost' => 'Unit Cost',
            'onhand_total_value' => 'Total Value',
            'status' => 'Status',
            'location' => 'Location',
            'accountable' => 'Accountable',
            'department' => 'Department',
            'general_ledger' => 'General Ledger',
        ];

        $selectedFilters = [];
        foreach ($filters as $field => $label) {
            $value = $_GET[$field] ?? '';
            $selectedFilters[$field] = is_string($value) ? trim($value) : '';
        }

        $options = [];
        foreach ($filters as $field => $label) {
            $options[$field] = PropertyModel::getDistinctFieldValues($field, $isAdmin, $userDepartment);
        }

        $items = [];
        $totalAmount = 0.0;
        $totalQuantity = 0.0;
        $reportGenerated = ($_GET['generate'] ?? '') === '1';

        if ($reportGenerated) {
            $items = PropertyModel::getReportItems($selectedFilters, $isAdmin, $userDepartment);
            $totalAmount = array_sum(array_map(
                static fn(array $item): float => (float)($item['onhand_quantity'] ?? 0) * (float)($item['unit_cost'] ?? 0),
                $items
            ));
            $totalQuantity = array_sum(array_map(
                static fn(array $item): float => (float)($item['onhand_quantity'] ?? 0),
                $items
            ));
        }

        $this->render('reports/index', [
            'filters'          => $filters,
            'fields'           => $fields,
            'selectedFilters'  => $selectedFilters,
            'options'          => $options,
            'items'            => $items,
            'totalAmount'      => $totalAmount,
            'totalQuantity'    => $totalQuantity,
            'reportGenerated'  => $reportGenerated,
            'isAdmin'          => $isAdmin,
        ]);
    }
}
