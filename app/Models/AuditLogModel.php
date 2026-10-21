<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class AuditLogModel
{
    public static function log(string $action, ?int $recordId, array $details, ?string $username = null): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO inventory_audit_logs (username, action, record_id, details)
            VALUES (:username, :action, :record_id, :details)
        ");
        $stmt->execute([
            ':username' => $username ?? ($_SESSION['username'] ?? 'unknown'),
            ':action' => $action,
            ':record_id' => $recordId,
            ':details' => json_encode($details, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public static function getRecent(int $limit = 500): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT username, action, record_id, details, created_at
            FROM inventory_audit_logs
            ORDER BY id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function formatRecordLabel(array $record): string
    {
        $propertyNumber = trim((string)($record['property_number'] ?? ''));
        $article = trim((string)($record['article_road_name'] ?? ''));
        $barcode = trim((string)($record['barcode'] ?? ''));
        $label = array_filter([$propertyNumber, $article, $barcode], static fn(string $value): bool => $value !== '');
        return $label ? implode(' · ', $label) : 'Inventory record';
    }

    public static function formatChangedFields(array $before, array $after): string
    {
        $changed = [];
        $labels = ['are_number' => 'ICS Number'];
        foreach ($after as $field => $value) {
            if ($field === 'id' || $field === 'created_at' || $field === 'updated_at') {
                continue;
            }
            if (($before[$field] ?? null) != $value) {
                $changed[] = $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
            }
        }
        return $changed ? 'Changed: ' . implode(', ', $changed) : 'Record saved without field changes.';
    }
}
