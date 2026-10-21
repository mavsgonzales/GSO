<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PropertyModel;
use InvalidArgumentException;
use RuntimeException;

class InventoryCheckController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $isAdmin = Auth::isAdmin();
        $userDepartment = Auth::department();
        $departmentChoices = $isAdmin ? PropertyModel::getVerificationDepartments() : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payload = $_POST['payload'] ?? null;
            if (!is_string($payload) || trim($payload) === '' || strlen($payload) > 65536) {
                $this->json([
                    'success' => false,
                    'message' => 'Scan a valid inventory QR code and try again.',
                ], 400);
            }

            $selectedDepartment = '';
            if ($isAdmin) {
                $submittedDepartment = $_POST['department'] ?? '';
                if (!is_string($submittedDepartment)) {
                    $this->json([
                        'success' => false,
                        'message' => 'Choose a valid department filter.',
                    ], 400);
                }
                $selectedDepartment = trim($submittedDepartment);
                if ($selectedDepartment !== '' && !in_array($selectedDepartment, $departmentChoices, true)) {
                    $this->json([
                        'success' => false,
                        'message' => 'Choose a valid department filter.',
                    ], 400);
                }
            }

            try {
                $item = PropertyModel::verifyQrPayload(
                    $payload,
                    Auth::username(),
                    $isAdmin,
                    $userDepartment,
                    $selectedDepartment
                );
            } catch (InvalidArgumentException | RuntimeException $exception) {
                $this->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }

            if ($item === null) {
                $this->json([
                    'success' => false,
                    'message' => 'No inventory item matching this QR code was found in your accessible records.',
                ], 404);
            }

            $this->json([
                'success' => true,
                'message' => 'Item found and marked as physically verified.',
                'item' => $item,
            ]);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Allow: GET, POST');
            $this->json([
                'success' => false,
                'message' => 'This request method is not supported.',
            ], 405);
        }

        $selectedDepartment = '';
        if ($isAdmin) {
            $requestedDepartment = $_GET['department'] ?? '';
            if (is_string($requestedDepartment)) {
                $requestedDepartment = trim($requestedDepartment);
                if ($requestedDepartment === '' || in_array($requestedDepartment, $departmentChoices, true)) {
                    $selectedDepartment = $requestedDepartment;
                }
            }
        }

        $items = PropertyModel::getVerificationItems($isAdmin, $userDepartment, $selectedDepartment);
        $verifiedCount = count(array_filter(
            $items,
            static fn(array $item): bool => !empty($item['verified_at'])
        ));

        $this->render('inventory/check', [
            'items' => $items,
            'verifiedCount' => $verifiedCount,
            'isAdmin' => $isAdmin,
            'userDepartment' => $userDepartment,
            'departmentChoices' => $departmentChoices,
            'selectedDepartment' => $selectedDepartment,
        ]);
    }
}
