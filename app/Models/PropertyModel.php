<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Constants;
use App\Config\Database;
use Exception;
use PDO;

class PropertyModel
{
    public static function getAll(string $search = '', string $searchField = 'all', string $statusFilter = '', bool $isAdmin = true, string $userDepartment = '', bool $disposedOnly = false): array
    {
        $pdo = Database::getConnection();
        $query = "SELECT * FROM gso_property_inventory WHERE 1=1";
        $params = [];

        if (!$isAdmin) {
            $query .= ' AND department = :user_department';
            $params[':user_department'] = $userDepartment;
        }

        $query .= $disposedOnly ? " AND status = :disposed_status" : " AND status <> :disposed_status";
        $params[':disposed_status'] = 'Disposed';

        if ($search !== '') {
            if ($searchField === 'all') {
                $searchConditions = [];
                foreach (array_keys(Constants::INVENTORY_FIELDS) as $index => $field) {
                    $placeholder = ':search' . $index;
                    $searchConditions[] = "{$field} LIKE {$placeholder}";
                    $params[$placeholder] = "%{$search}%";
                }
                $query .= ' AND (' . implode(' OR ', $searchConditions) . ')';
            } elseif (array_key_exists($searchField, Constants::INVENTORY_FIELDS)) {
                $query .= " AND {$searchField} LIKE :search";
                $params[':search'] = "%{$search}%";
            }
        }

        if ($statusFilter !== '') {
            $query .= " AND status = :status";
            $params[':status'] = $statusFilter;
        }

        $query .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getStats(bool $isAdmin = true, string $userDepartment = ''): array
    {
        $pdo = Database::getConnection();
        $statsQuery = "SELECT 
            COUNT(*) as total_items,
            COALESCE(SUM(onhand_total_value), 0) as total_val,
            COALESCE(SUM(onhand_quantity), 0) as total_qty
        FROM gso_property_inventory";

        if (!$isAdmin) {
            $statsQuery .= ' WHERE department = :user_department';
            $statsStmt = $pdo->prepare($statsQuery);
            $statsStmt->execute([':user_department' => $userDepartment]);
        } else {
            $statsStmt = $pdo->query($statsQuery);
        }

        return $statsStmt->fetch() ?: ['total_items' => 0, 'total_val' => 0, 'total_qty' => 0];
    }

    public static function getVerificationDepartments(): array
    {
        $stmt = Database::getConnection()->query(
            "SELECT DISTINCT department
             FROM gso_property_inventory
             WHERE department IS NOT NULL AND TRIM(department) <> ''
             ORDER BY department"
        );

        return array_map(
            static fn(array $row): string => (string)$row['department'],
            $stmt->fetchAll()
        );
    }

    public static function getVerificationItems(bool $isAdmin, string $userDepartment, string $selectedDepartment = ''): array
    {
        $sql = "
            SELECT
                inventory.id,
                inventory.barcode,
                inventory.property_number,
                inventory.article_road_name,
                inventory.department,
                verification.verified_at,
                verification.verified_by,
                verification.scan_count
            FROM gso_property_inventory AS inventory
            LEFT JOIN inventory_verifications AS verification
                ON verification.record_id = inventory.id";
        $params = [];

        if (!$isAdmin) {
            $sql .= ' WHERE inventory.department = :user_department';
            $params[':user_department'] = $userDepartment;
        } elseif ($selectedDepartment !== '') {
            $sql .= ' WHERE inventory.department = :selected_department';
            $params[':selected_department'] = $selectedDepartment;
        }

        $sql .= ' ORDER BY inventory.id DESC';
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function verifyQrPayload(
        string $payload,
        string $username,
        bool $isAdmin,
        string $userDepartment,
        string $selectedDepartment = ''
    ): ?array
    {
        try {
            $qrData = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException('This QR code is not a recognized inventory label.', 0, $exception);
        }

        if (!is_array($qrData)) {
            throw new \InvalidArgumentException('This QR code is not a recognized inventory label.');
        }

        $rawBarcode = $qrData['barcode'] ?? null;
        $rawPropertyNumber = $qrData['property_number'] ?? null;
        if (!is_scalar($rawBarcode) || !is_scalar($rawPropertyNumber)) {
            throw new \InvalidArgumentException('This QR code does not contain a barcode and property number.');
        }

        $barcode = trim((string)$rawBarcode);
        $propertyNumber = trim((string)$rawPropertyNumber);
        if ($barcode === '' || $propertyNumber === '') {
            throw new \InvalidArgumentException('This QR code does not contain a barcode and property number.');
        }

        $pdo = Database::getConnection();
        $sql = 'SELECT id, barcode, property_number, article_road_name, department
                FROM gso_property_inventory
                WHERE barcode = :barcode AND property_number = :property_number';
        $params = [
            ':barcode' => $barcode,
            ':property_number' => $propertyNumber,
        ];

        if (!$isAdmin) {
            $sql .= ' AND department = :user_department';
            $params[':user_department'] = $userDepartment;
        } elseif ($selectedDepartment !== '') {
            $sql .= ' AND department = :selected_department';
            $params[':selected_department'] = $selectedDepartment;
        }

        $sql .= ' ORDER BY id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $matches = $stmt->fetchAll();

        if (count($matches) > 1) {
            throw new \RuntimeException('More than one inventory record matches this QR code. Ask an administrator to review the duplicate identifiers.');
        }
        if (!$matches) {
            return null;
        }

        $record = $matches[0];
        if (Database::$driver === 'mysql') {
            $verificationSql = '
                INSERT INTO inventory_verifications (record_id, verified_at, verified_by, scan_count)
                VALUES (:record_id, CURRENT_TIMESTAMP, :verified_by, 1)
                ON DUPLICATE KEY UPDATE
                    verified_at = CURRENT_TIMESTAMP,
                    verified_by = VALUES(verified_by),
                    scan_count = scan_count + 1';
        } else {
            $verificationSql = '
                INSERT INTO inventory_verifications (record_id, verified_at, verified_by, scan_count)
                VALUES (:record_id, CURRENT_TIMESTAMP, :verified_by, 1)
                ON CONFLICT(record_id) DO UPDATE SET
                    verified_at = CURRENT_TIMESTAMP,
                    verified_by = excluded.verified_by,
                    scan_count = inventory_verifications.scan_count + 1';
        }

        $verificationStmt = $pdo->prepare($verificationSql);
        $verificationStmt->execute([
            ':record_id' => $record['id'],
            ':verified_by' => $username,
        ]);

        $verifiedStmt = $pdo->prepare(
            'SELECT verified_at, verified_by, scan_count
             FROM inventory_verifications
             WHERE record_id = :record_id'
        );
        $verifiedStmt->execute([':record_id' => $record['id']]);
        $verification = $verifiedStmt->fetch();
        if (!$verification) {
            throw new \RuntimeException('The inventory verification could not be saved.');
        }

        return array_merge($record, $verification);
    }

    public static function find(int $id, bool $isAdmin = true, string $userDepartment = ''): ?array
    {
        $pdo = Database::getConnection();
        $sql = 'SELECT * FROM gso_property_inventory WHERE id = :id';
        $params = [':id' => $id];

        if (!$isAdmin) {
            $sql .= ' AND department = :user_department';
            $params[':user_department'] = $userDepartment;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public static function findByIds(array $ids, bool $isAdmin = true, string $userDepartment = ''): array
    {
        if (empty($ids)) {
            return [];
        }

        $pdo = Database::getConnection();
        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $placeholder = ':id' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }

        $sql = 'SELECT * FROM gso_property_inventory WHERE id IN (' . implode(', ', $placeholders) . ')';
        if (!$isAdmin) {
            $sql .= ' AND department = :user_department';
            $params[':user_department'] = $userDepartment;
        }
        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();

        $sql = <<<SQL
        INSERT INTO gso_property_inventory (
            barcode, property_number, article_road_name, description,
            onhand_quantity, unit, unit_cost, onhand_total_value,
            status, location, accountable, designation, department,
            previously_accountable, acquisition_date, date_surrendered,
            po_number, pr_number, invoice_number, are_number, air_number,
            remarks, supplier, source_of_fund, general_ledger
        ) VALUES (
            :barcode, :property_number, :article_road_name, :description,
            :onhand_quantity, :unit, :unit_cost, :onhand_total_value,
            :status, :location, :accountable, :designation, :department,
            :previously_accountable, :acquisition_date, :date_surrendered,
            :po_number, :pr_number, :invoice_number, :are_number, :air_number,
            :remarks, :supplier, :source_of_fund, :general_ledger
        )
        SQL;

        $isSqlite = Database::$driver === 'sqlite';
        if ($isSqlite) {
            $pdo->exec('BEGIN IMMEDIATE TRANSACTION');
        } else {
            $pdo->beginTransaction();
        }

        try {
            $barcodeQuery = 'SELECT barcode FROM gso_property_inventory';
            if (Database::$driver === 'mysql') {
                $barcodeQuery .= ' FOR UPDATE';
            }
            $existingBarcodes = $pdo->query($barcodeQuery)->fetchAll(PDO::FETCH_COLUMN);
            $highestSequence = 0;
            foreach ($existingBarcodes as $existingBarcode) {
                $existingBarcode = (string)$existingBarcode;
                if (strlen($existingBarcode) >= 7 && ctype_digit($existingBarcode)) {
                    $highestSequence = max($highestSequence, (int)$existingBarcode);
                }
            }
            $data['barcode'] = str_pad((string)($highestSequence + 1), 7, '0', STR_PAD_LEFT);

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':barcode'               => $data['barcode'],
                ':property_number'        => $data['property_number'],
                ':article_road_name'       => $data['article_road_name'],
                ':description'           => $data['description'],
                ':onhand_quantity'       => $data['onhand_quantity'],
                ':unit'                  => $data['unit'],
                ':unit_cost'              => $data['unit_cost'],
                ':onhand_total_value'     => $data['onhand_total_value'],
                ':status'                => $data['status'],
                ':location'              => $data['location'],
                ':accountable'           => $data['accountable'],
                ':designation'           => $data['designation'],
                ':department'            => $data['department'],
                ':previously_accountable' => $data['previously_accountable'],
                ':acquisition_date'       => $data['acquisition_date'],
                ':date_surrendered'       => $data['date_surrendered'],
                ':po_number'              => $data['po_number'],
                ':pr_number'              => $data['pr_number'],
                ':invoice_number'         => $data['invoice_number'],
                ':are_number'             => $data['are_number'],
                ':air_number'             => $data['air_number'],
                ':remarks'               => $data['remarks'],
                ':supplier'              => $data['supplier'],
                ':source_of_fund'         => $data['source_of_fund'],
                ':general_ledger'         => $data['general_ledger'],
            ]);

            $recordId = (int)$pdo->lastInsertId();
            $recordStmt = $pdo->prepare('SELECT * FROM gso_property_inventory WHERE id = ?');
            $recordStmt->execute([$recordId]);
            $record = $recordStmt->fetch();

            AuditLogModel::log('add', $recordId, ['record' => $record]);
            if ($isSqlite) {
                $pdo->exec('COMMIT');
            } else {
                $pdo->commit();
            }
            return $recordId;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                if ($isSqlite) {
                    $pdo->exec('ROLLBACK');
                } else {
                    $pdo->rollBack();
                }
            }
            throw $e;
        }
    }

    public static function update(int $id, array $data, bool $isAdmin = true, string $userDepartment = ''): bool
    {
        $pdo = Database::getConnection();

        $pdo->beginTransaction();
        try {
            $before = self::find($id, $isAdmin, $userDepartment);
            if (!$before) {
                throw new Exception('The inventory record could not be found.');
            }

            $updateSql = "
                UPDATE gso_property_inventory SET
                    barcode = :barcode,
                    property_number = :property_number,
                    article_road_name = :article_road_name,
                    description = :description,
                    onhand_quantity = :onhand_quantity,
                    unit = :unit,
                    unit_cost = :unit_cost,
                    onhand_total_value = :onhand_total_value,
                    status = :status,
                    location = :location,
                    accountable = :accountable,
                    designation = :designation,
                    department = :department,
                    previously_accountable = :previously_accountable,
                    acquisition_date = :acquisition_date,
                    date_surrendered = :date_surrendered,
                    po_number = :po_number,
                    pr_number = :pr_number,
                    invoice_number = :invoice_number,
                    are_number = :are_number,
                    air_number = :air_number,
                    remarks = :remarks,
                    supplier = :supplier,
                    source_of_fund = :source_of_fund,
                    general_ledger = :general_ledger
                WHERE id = :id
            ";
            if (!$isAdmin) {
                $updateSql .= " AND department = :user_department";
            }

            $updateParams = [
                ':id'                     => $id,
                ':barcode'               => $data['barcode'],
                ':property_number'        => $data['property_number'],
                ':article_road_name'       => $data['article_road_name'],
                ':description'           => $data['description'],
                ':onhand_quantity'       => $data['onhand_quantity'],
                ':unit'                  => $data['unit'],
                ':unit_cost'              => $data['unit_cost'],
                ':onhand_total_value'     => $data['onhand_total_value'],
                ':status'                => $data['status'],
                ':location'              => $data['location'],
                ':accountable'           => $data['accountable'],
                ':designation'           => $data['designation'],
                ':department'            => $data['department'],
                ':previously_accountable' => $data['previously_accountable'],
                ':acquisition_date'       => $data['acquisition_date'],
                ':date_surrendered'       => $data['date_surrendered'],
                ':po_number'              => $data['po_number'],
                ':pr_number'              => $data['pr_number'],
                ':invoice_number'         => $data['invoice_number'],
                ':are_number'             => $data['are_number'],
                ':air_number'             => $data['air_number'],
                ':remarks'               => $data['remarks'],
                ':supplier'              => $data['supplier'],
                ':source_of_fund'         => $data['source_of_fund'],
                ':general_ledger'         => $data['general_ledger'],
            ];
            if (!$isAdmin) {
                $updateParams[':user_department'] = $userDepartment;
            }

            $stmt = $pdo->prepare($updateSql);
            $stmt->execute($updateParams);

            $data['id'] = $id;
            AuditLogModel::log('edit', $id, ['before' => $before, 'after' => $data]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function delete(int $id, bool $isAdmin = true, string $userDepartment = ''): bool
    {
        $pdo = Database::getConnection();

        $pdo->beginTransaction();
        try {
            $record = self::find($id, $isAdmin, $userDepartment);
            if (!$record) {
                throw new Exception('The inventory record could not be found.');
            }

            $deleteSql = 'DELETE FROM gso_property_inventory WHERE id = :id';
            $deleteParams = [':id' => $id];
            if (!$isAdmin) {
                $deleteSql .= ' AND department = :user_department';
                $deleteParams[':user_department'] = $userDepartment;
            }

            $delStmt = $pdo->prepare($deleteSql);
            $delStmt->execute($deleteParams);

            AuditLogModel::log('delete', $id, ['record' => $record]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function getDistinctFieldValues(string $field, bool $isAdmin = true, string $userDepartment = ''): array
    {
        $allowed = array_keys(Constants::INVENTORY_FIELDS);
        if (!in_array($field, $allowed, true)) {
            return [];
        }

        $pdo = Database::getConnection();
        $sql = "SELECT DISTINCT {$field} FROM gso_property_inventory WHERE {$field} IS NOT NULL AND TRIM({$field}) <> ''";
        if (!$isAdmin) {
            $sql .= ' AND department = :user_department';
            $stmt = $pdo->prepare($sql . " ORDER BY {$field}");
            $stmt->execute([':user_department' => $userDepartment]);
        } else {
            $stmt = $pdo->query($sql . " ORDER BY {$field}");
        }

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getSuggestions(string $term, string $field = 'all', bool $isAdmin = true, string $userDepartment = ''): array
    {
        $pdo = Database::getConnection();
        $fields = array_keys(Constants::INVENTORY_FIELDS);

        if ($field !== '' && $field !== 'all') {
            if (!in_array($field, $fields, true)) {
                return [];
            }
            $targetFields = [$field];
        } else {
            $targetFields = $fields;
        }

        $departmentCondition = $isAdmin ? '' : ' AND department = :user_department';
        $queries = [];
        $params = $isAdmin ? [] : [':user_department' => $userDepartment];

        foreach ($targetFields as $index => $targetField) {
            $paramName = ':term' . $index;
            $params[$paramName] = '%' . $term . '%';
            $queries[] = "
                SELECT {$targetField} AS suggestion
                FROM gso_property_inventory
                WHERE {$targetField} IS NOT NULL
                  AND TRIM({$targetField}) <> ''
                  AND {$targetField} LIKE {$paramName}
                  {$departmentCondition}
            ";
        }

        $sql = implode(' UNION ', $queries) . ' ORDER BY suggestion ASC LIMIT 8';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getReportItems(array $selectedFilters, bool $isAdmin = true, string $userDepartment = ''): array
    {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        if (!$isAdmin) {
            $conditions[] = 'department = :user_department';
            $params[':user_department'] = $userDepartment;
        }

        foreach ($selectedFilters as $field => $value) {
            if ($value !== '' && array_key_exists($field, Constants::INVENTORY_FIELDS)) {
                $placeholder = ':' . $field;
                $conditions[] = "{$field} LIKE {$placeholder}";
                $params[$placeholder] = '%' . $value . '%';
            }
        }

        $sql = 'SELECT * FROM gso_property_inventory';
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
