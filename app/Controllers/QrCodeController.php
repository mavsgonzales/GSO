<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Constants;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\PropertyModel;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRCodeDataException;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCode\QROptions;
use JsonException;

class QrCodeController extends Controller
{
    public function printSelected(): void
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $error = null;
        $items = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $error = 'Submit selected inventory items from the dashboard to generate QR codes.';
        } elseif (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
            http_response_code(400);
            $error = 'Select at least one inventory item before generating QR codes.';
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
                $error = 'Select at least one inventory item before generating QR codes.';
            }

            if (!$error) {
                $items = PropertyModel::findByIds($ids, true);
                if (count($items) !== count($ids)) {
                    http_response_code(404);
                    $error = 'One or more selected inventory items could not be found. Refresh the dashboard and try again.';
                }
            }
        }

        if (!$error) {
            $options = new QROptions();
            $options->outputType = QROutputInterface::MARKUP_SVG;
            $options->eccLevel = EccLevel::L;

            foreach ($items as &$item) {
                $qrCode = new QRCode($options);
                $fields = [];
                foreach (Constants::INVENTORY_FIELDS as $field => $label) {
                    $fields[$field] = $item[$field] ?? '';
                }
                $fields['onhand_total_value'] =
                    (float)($item['onhand_quantity'] ?? 0) * (float)($item['unit_cost'] ?? 0);

                try {
                    $payload = json_encode(
                        $fields,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                    );
                } catch (JsonException $exception) {
                    http_response_code(422);
                    $error = 'The inventory fields for a selected item contain text that cannot be encoded. Correct the record and try again.';
                    break;
                }

                try {
                    $image = $qrCode->render($payload);
                } catch (QRCodeDataException $exception) {
                    http_response_code(422);
                    $propertyNumber = (string)($item['property_number'] ?? 'selected item');
                    $error = 'The full inventory details for ' . $propertyNumber
                        . ' exceed the QR code size limit. Shorten the record description or remarks and try again.';
                    break;
                } catch (QRCodeException $exception) {
                    http_response_code(422);
                    $error = 'The QR code could not be generated for a selected inventory item: '
                        . $exception->getMessage();
                    break;
                }

                if (!is_string($image)) {
                    throw new \RuntimeException('The QR code library returned an invalid image.');
                }

                $item['qr_image'] = $image;
            }
            unset($item);
        }

        $this->render('inventory/qr_print', [
            'error' => $error,
            'items' => $items,
            'fields' => Constants::INVENTORY_FIELDS,
        ]);
    }
}
