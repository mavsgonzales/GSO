<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Constants;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\PropertyModel;
use Exception;
use RuntimeException;
use App\Services\ExcelInventoryImporter;

class PropertyController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $isAdmin = Auth::isAdmin();
        $userDepartment = Auth::department();

        $message = null;
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['inventory_flash'])) {
            $flash = $_SESSION['inventory_flash'];
            unset($_SESSION['inventory_flash']);
            $message = (string)($flash['message'] ?? '');
            $messageType = (string)($flash['type'] ?? 'success');
        }

        // Handle POST submissions (Create, Import, Edit, Delete)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$isAdmin) {
                http_response_code(403);
                exit('This account has view-only access.');
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                try {
                    $barcode         = trim((string)($_POST['barcode'] ?? ''));
                    $propertyNumber  = trim((string)($_POST['property_number'] ?? ''));
                    $articleRoadName = trim((string)($_POST['article_road_name'] ?? ''));
                    $onhandQty       = (float)($_POST['onhand_quantity'] ?? 0);
                    $unitCost        = (float)($_POST['unit_cost'] ?? 0);
                    $department      = $isAdmin ? trim((string)($_POST['department'] ?? '')) : $userDepartment;

                    if ($barcode === '' && $propertyNumber === '' && $articleRoadName === '') {
                        throw new Exception("Please provide at least a Barcode, Property Number, or Article/Road Name.");
                    }

                    if (!$isAdmin && $userDepartment === '') {
                        throw new Exception('Your account has no department assigned. Contact an administrator.');
                    }

                    $recordData = [
                        'barcode'               => $barcode,
                        'property_number'        => $propertyNumber,
                        'article_road_name'       => $articleRoadName,
                        'description'           => trim((string)($_POST['description'] ?? '')),
                        'onhand_quantity'       => $onhandQty,
                        'unit'                  => trim((string)($_POST['unit'] ?? 'unit')),
                        'unit_cost'              => $unitCost,
                        'onhand_total_value'     => $onhandQty * $unitCost,
                        'status'                => trim((string)($_POST['status'] ?? 'Serviceable')),
                        'location'              => trim((string)($_POST['location'] ?? '')),
                        'accountable'           => trim((string)($_POST['accountable'] ?? '')),
                        'designation'           => trim((string)($_POST['designation'] ?? '')),
                        'department'            => $department,
                        'previously_accountable' => trim((string)($_POST['previously_accountable'] ?? '')),
                        'acquisition_date'       => !empty($_POST['acquisition_date']) ? trim((string)$_POST['acquisition_date']) : null,
                        'date_surrendered'       => !empty($_POST['date_surrendered']) ? trim((string)$_POST['date_surrendered']) : null,
                        'po_number'              => trim((string)($_POST['po_number'] ?? '')),
                        'pr_number'              => trim((string)($_POST['pr_number'] ?? '')),
                        'invoice_number'         => trim((string)($_POST['invoice_number'] ?? '')),
                        'are_number'             => trim((string)($_POST['are_number'] ?? '')),
                        'air_number'             => trim((string)($_POST['air_number'] ?? '')),
                        'remarks'               => trim((string)($_POST['remarks'] ?? '')),
                        'supplier'              => trim((string)($_POST['supplier'] ?? '')),
                        'source_of_fund'         => trim((string)($_POST['source_of_fund'] ?? '')),
                        'general_ledger'         => trim((string)($_POST['general_ledger'] ?? '')),
                    ];

                    PropertyModel::create($recordData);
                    $message = "Record [{$propertyNumber}] saved successfully!";
                    $messageType = "success";
                } catch (Exception $e) {
                    $message = "Error saving item: " . $e->getMessage();
                    $messageType = "danger";
                }
            } elseif ($action === 'import_excel') {
                try {
                    $upload = $_FILES['ics_excel'] ?? null;
                    if (!is_array($upload) || !isset($upload['error'], $upload['tmp_name'], $upload['name'])) {
                        throw new RuntimeException('Choose an .xlsx Excel file to import.');
                    }
                    if ($upload['error'] !== UPLOAD_ERR_OK) {
                        $uploadErrors = [
                            UPLOAD_ERR_INI_SIZE => 'The Excel file exceeds the server upload limit.',
                            UPLOAD_ERR_FORM_SIZE => 'The Excel file exceeds the upload limit.',
                            UPLOAD_ERR_PARTIAL => 'The Excel file upload was incomplete. Please try again.',
                            UPLOAD_ERR_NO_FILE => 'Choose an .xlsx Excel file to import.',
                            UPLOAD_ERR_NO_TMP_DIR => 'The server upload folder is unavailable.',
                            UPLOAD_ERR_CANT_WRITE => 'The uploaded Excel file could not be saved by the server.',
                            UPLOAD_ERR_EXTENSION => 'A server extension stopped the Excel file upload.',
                        ];
                        throw new RuntimeException($uploadErrors[$upload['error']] ?? 'The Excel file upload failed.');
                    }
                    if (strtolower(pathinfo((string)$upload['name'], PATHINFO_EXTENSION)) !== 'xlsx'
                        || !is_uploaded_file((string)$upload['tmp_name'])) {
                        throw new RuntimeException('Upload a valid .xlsx Excel workbook.');
                    }

                    $records = (new ExcelInventoryImporter())->import((string)$upload['tmp_name']);
                    $importedCount = 0;
                    $rowErrors = [];
                    foreach ($records as $record) {
                        try {
                            PropertyModel::create($record['data']);
                            $importedCount++;
                        } catch (Exception $e) {
                            $rowErrors[] = 'Row ' . $record['row'] . ': ' . $e->getMessage();
                        }
                    }

                    if ($rowErrors) {
                        $message = 'Imported ' . $importedCount . ' of ' . count($records) . ' rows. '
                            . implode(' ', array_slice($rowErrors, 0, 5));
                        if (count($rowErrors) > 5) {
                            $message .= ' ' . (count($rowErrors) - 5) . ' additional row errors.';
                        }
                        $messageType = 'danger';
                    } else {
                        $message = 'Successfully imported ' . $importedCount . ' inventory item'
                            . ($importedCount === 1 ? '' : 's') . '.';
                        $messageType = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'Excel import failed: ' . $e->getMessage();
                    $messageType = 'danger';
                }
            } elseif ($action === 'edit') {
                try {
                    $id = (int)($_POST['record_id'] ?? 0);
                    $barcode         = trim((string)($_POST['barcode'] ?? ''));
                    $propertyNumber  = trim((string)($_POST['property_number'] ?? ''));
                    $articleRoadName = trim((string)($_POST['article_road_name'] ?? ''));
                    $onhandQty       = (float)($_POST['onhand_quantity'] ?? 0);
                    $unitCost        = (float)($_POST['unit_cost'] ?? 0);
                    $department      = $isAdmin ? trim((string)($_POST['department'] ?? '')) : $userDepartment;

                    if ($barcode === '' && $propertyNumber === '' && $articleRoadName === '') {
                        throw new Exception('Please provide at least a Barcode, Property Number, or Article/Road Name.');
                    }

                    $after = [
                        'barcode'               => $barcode,
                        'property_number'        => $propertyNumber,
                        'article_road_name'       => $articleRoadName,
                        'description'           => trim((string)($_POST['description'] ?? '')),
                        'onhand_quantity'       => $onhandQty,
                        'unit'                  => trim((string)($_POST['unit'] ?? 'unit')),
                        'unit_cost'              => $unitCost,
                        'onhand_total_value'     => $onhandQty * $unitCost,
                        'status'                => trim((string)($_POST['status'] ?? 'Serviceable')),
                        'location'              => trim((string)($_POST['location'] ?? '')),
                        'accountable'           => trim((string)($_POST['accountable'] ?? '')),
                        'designation'           => trim((string)($_POST['designation'] ?? '')),
                        'department'            => $department,
                        'previously_accountable' => trim((string)($_POST['previously_accountable'] ?? '')),
                        'acquisition_date'       => !empty($_POST['acquisition_date']) ? trim((string)$_POST['acquisition_date']) : null,
                        'date_surrendered'       => !empty($_POST['date_surrendered']) ? trim((string)$_POST['date_surrendered']) : null,
                        'po_number'              => trim((string)($_POST['po_number'] ?? '')),
                        'pr_number'              => trim((string)($_POST['pr_number'] ?? '')),
                        'invoice_number'         => trim((string)($_POST['invoice_number'] ?? '')),
                        'are_number'             => trim((string)($_POST['are_number'] ?? '')),
                        'air_number'             => trim((string)($_POST['air_number'] ?? '')),
                        'remarks'               => trim((string)($_POST['remarks'] ?? '')),
                        'supplier'              => trim((string)($_POST['supplier'] ?? '')),
                        'source_of_fund'         => trim((string)($_POST['source_of_fund'] ?? '')),
                        'general_ledger'         => trim((string)($_POST['general_ledger'] ?? '')),
                    ];

                    PropertyModel::update($id, $after, $isAdmin, $userDepartment);
                    $message = "Record [{$after['property_number']}] updated successfully!";
                    $messageType = "success";
                } catch (Exception $e) {
                    $message = "Error updating item: " . $e->getMessage();
                    $messageType = "danger";
                }
            } elseif ($action === 'delete') {
                try {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id > 0) {
                        PropertyModel::delete($id, $isAdmin, $userDepartment);
                        $message = "Record deleted successfully.";
                        $messageType = "info";
                    }
                } catch (Exception $e) {
                    $message = "Error deleting: " . $e->getMessage();
                    $messageType = "danger";
                }
            }

            if (in_array($action, ['create', 'import_excel', 'edit', 'delete'], true)) {
                if ($message !== null) {
                    $_SESSION['inventory_flash'] = [
                        'message' => $message,
                        'type' => $messageType,
                    ];
                }

                $redirectParams = $_GET;
                if (($_POST['return_view'] ?? '') === 'disposed') {
                    $redirectParams['view'] = 'disposed';
                }
                if ($action === 'edit' && isset($after) && ($after['status'] ?? '') === 'Disposed') {
                    $redirectParams['view'] = 'disposed';
                    unset($redirectParams['status']);
                }

                $query = http_build_query($redirectParams);
                header('Location: index.php' . ($query !== '' ? '?' . $query : ''), true, 303);
                exit;
            }
        }

        // Search & Filters
        $search = trim((string)($_GET['search'] ?? ''));
        $searchField = (string)($_GET['search_field'] ?? 'all');
        if ($searchField !== 'all' && !array_key_exists($searchField, Constants::INVENTORY_FIELDS)) {
            $searchField = 'all';
        }
        $statusFilter = trim((string)($_GET['status'] ?? ''));
        $disposedOnly = (string)($_GET['view'] ?? '') === 'disposed';
        if ($disposedOnly) {
            $statusFilter = '';
        }

        $items = PropertyModel::getAll($search, $searchField, $statusFilter, $isAdmin, $userDepartment, $disposedOnly);
        $stats = PropertyModel::getStats($isAdmin, $userDepartment);

        $departmentChoices = $isAdmin ? Constants::DEPARTMENT_CHOICES : [$userDepartment];

        $this->render('inventory/index', [
            'isAdmin'                 => $isAdmin,
            'userDepartment'          => $userDepartment,
            'message'                 => $message,
            'messageType'             => $messageType,
            'search'                  => $search,
            'searchField'             => $searchField,
            'statusFilter'            => $statusFilter,
            'disposedOnly'            => $disposedOnly,
            'items'                   => $items,
            'stats'                   => $stats,
            'inventoryFields'         => Constants::INVENTORY_FIELDS,
            'statusChoices'           => Constants::STATUS_CHOICES,
            'unitChoices'             => Constants::UNIT_CHOICES,
            'departmentChoices'       => $departmentChoices,
            'sourceOfFundChoices'     => Constants::SOURCE_OF_FUND_CHOICES,
            'generalLedgerChoices'    => Constants::GENERAL_LEDGER_CHOICES,
        ]);
    }

    public function suggestions(): void
    {
        Auth::requireLogin();

        $term = trim((string)($_GET['q'] ?? ''));
        $field = trim((string)($_GET['field'] ?? ''));

        if ($term === '' && !in_array($field, ['accountable', 'supplier'], true)) {
            $this->json([]);
        }

        $isAdmin = Auth::isAdmin();
        $userDepartment = Auth::department();

        $results = PropertyModel::getSuggestions($term, $field, $isAdmin, $userDepartment);
        $this->json($results);
    }

    public function printIcs(): void
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $error = null;
        $items = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $error = 'Submit selected inventory items from the dashboard to generate an ICS.';
        } elseif (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
            http_response_code(400);
            $error = 'Select at least one inventory item before printing.';
        } else {
            $ids = [];
            foreach ($_POST['ids'] as $submittedId) {
                if (!is_string($submittedId) && !is_int($submittedId)) {
                    http_response_code(400);
                    $error = 'The selected inventory item list is invalid.';
                    break;
                }
                $idString = (string)$submittedId;
                if (!ctype_digit($idString) || (int)$idString < 1) {
                    http_response_code(400);
                    $error = 'The selected inventory item list is invalid.';
                    break;
                }
                $ids[] = (int)$idString;
            }

            $ids = array_values(array_unique($ids));
            if (!$error && !$ids) {
                http_response_code(400);
                $error = 'Select at least one inventory item before printing.';
            }

            if (!$error) {
                $items = PropertyModel::findByIds($ids, Auth::isAdmin(), Auth::department());
                if (count($items) !== count($ids)) {
                    http_response_code(404);
                    $error = 'One or more selected inventory items could not be found. Refresh the dashboard and try again.';
                }
            }
        }

        $this->render('inventory/ics_print', [
            'error' => $error,
            'items' => $items,
        ]);
    }
}
