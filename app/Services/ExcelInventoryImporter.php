<?php
declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use RuntimeException;
use SimpleXMLElement;

class ExcelInventoryImporter
{
    private const MAX_FILE_BYTES = 10_485_760;
    private const MAX_ENTRY_BYTES = 20_971_520;
    private const MAX_ROWS = 2000;

    private const HEADER_FIELDS = [
        'barcode' => ['barcode'],
        'property_number' => ['propertynumber'],
        'article_road_name' => ['articleroadname', 'articleorroadname'],
        'description' => ['description'],
        'onhand_quantity' => ['onhandquantity', 'onhandqty', 'quantity'],
        'unit' => ['unit'],
        'unit_cost' => ['unitcost'],
        'onhand_total_value' => ['totalcost', 'onhandtotalvalue', 'totalvalue'],
        'status' => ['status'],
        'location' => ['location'],
        'accountable' => ['accountable'],
        'designation' => ['designation'],
        'department' => ['department'],
        'previously_accountable' => ['previouslyaccountable'],
        'acquisition_date' => ['acquisitiondate'],
        'date_surrendered' => ['datesurrendered'],
        'po_number' => ['ponumber'],
        'pr_number' => ['prnumber'],
        'invoice_number' => ['invoicenumber'],
        'are_number' => ['arenumber', 'icsnumber', 'arenumbericsno'],
        'air_number' => ['airnumber'],
        'remarks' => ['remarks'],
        'supplier' => ['supplier'],
        'source_of_fund' => ['sourceoffund'],
        'general_ledger' => ['generalledger', 'generalledgeraccount'],
    ];

    public function import(string $filePath): array
    {
        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize < 1 || $fileSize > self::MAX_FILE_BYTES) {
            throw new RuntimeException('The Excel file is empty or exceeds the 10 MB limit.');
        }

        $archive = file_get_contents($filePath);
        if ($archive === false) {
            throw new RuntimeException('The uploaded Excel file could not be read.');
        }

        $entries = $this->readZipDirectory($archive);
        $sharedStrings = $this->readSharedStrings($archive, $entries);
        $worksheetPath = $this->findFirstWorksheet($archive, $entries);
        $sheet = $this->parseXml($this->readZipEntry($archive, $entries, $worksheetPath), 'worksheet');
        $rows = $this->readWorksheetRows($sheet, $sharedStrings);

        if (!$rows) {
            throw new RuntimeException('The Excel worksheet has no data.');
        }

        $headerRowNumber = array_key_first($rows);
        $headers = $this->mapHeaders($rows[$headerRowNumber]);
        $records = [];
        foreach ($rows as $rowNumber => $cells) {
            if ($rowNumber <= $headerRowNumber || $this->rowIsEmpty($cells)) {
                continue;
            }
            if (count($records) >= self::MAX_ROWS) {
                throw new RuntimeException('The Excel file exceeds the maximum of ' . self::MAX_ROWS . ' inventory rows.');
            }
            $records[] = [
                'row' => $rowNumber,
                'data' => $this->mapRecord($cells, $headers, $rowNumber),
            ];
        }

        if (!$records) {
            throw new RuntimeException('The Excel worksheet contains no inventory rows.');
        }

        return $records;
    }

    private function readZipDirectory(string $archive): array
    {
        $endOffset = strrpos($archive, "PK\x05\x06");
        if ($endOffset === false || strlen($archive) - $endOffset < 22) {
            throw new RuntimeException('The uploaded file is not a valid .xlsx workbook.');
        }

        $end = unpack('vdisk/vcentralDisk/ventriesOnDisk/ventriesTotal/VcentralSize/VcentralOffset/vcommentLength', substr($archive, $endOffset + 4, 18));
        if ($end === false || $end['entriesTotal'] < 1 || $end['entriesTotal'] > 5000) {
            throw new RuntimeException('The Excel workbook archive directory is invalid.');
        }

        $entries = [];
        $position = (int)$end['centralOffset'];
        for ($index = 0; $index < $end['entriesTotal']; $index++) {
            if (substr($archive, $position, 4) !== "PK\x01\x02" || strlen($archive) - $position < 46) {
                throw new RuntimeException('The Excel workbook contains an invalid archive entry.');
            }
            $entry = unpack(
                'vversionMade/vversionNeeded/vflags/vmethod/vmodifiedTime/vmodifiedDate/Vcrc/VcompressedSize/VuncompressedSize/vnameLength/vextraLength/vcommentLength/vdiskStart/vinternalAttributes/VexternalAttributes/VlocalOffset',
                substr($archive, $position + 4, 42)
            );
            $nameLength = (int)$entry['nameLength'];
            $extraLength = (int)$entry['extraLength'];
            $commentLength = (int)$entry['commentLength'];
            $name = substr($archive, $position + 46, $nameLength);

            if ($entry['compressedSize'] === 0xFFFFFFFF || $entry['uncompressedSize'] === 0xFFFFFFFF || $entry['localOffset'] === 0xFFFFFFFF) {
                throw new RuntimeException('ZIP64 Excel workbooks are not supported.');
            }
            if ($entry['uncompressedSize'] > self::MAX_ENTRY_BYTES) {
                throw new RuntimeException('An Excel workbook component exceeds the allowed size.');
            }

            $entries[$name] = [
                'flags' => (int)$entry['flags'],
                'method' => (int)$entry['method'],
                'compressedSize' => (int)$entry['compressedSize'],
                'uncompressedSize' => (int)$entry['uncompressedSize'],
                'localOffset' => (int)$entry['localOffset'],
            ];
            $position += 46 + $nameLength + $extraLength + $commentLength;
        }

        return $entries;
    }

    private function readZipEntry(string $archive, array $entries, string $path): string
    {
        if (!isset($entries[$path])) {
            throw new RuntimeException('The Excel workbook is missing required component: ' . $path);
        }
        $entry = $entries[$path];
        if (($entry['flags'] & 1) !== 0) {
            throw new RuntimeException('Password-protected Excel workbooks are not supported.');
        }

        $offset = $entry['localOffset'];
        if (substr($archive, $offset, 4) !== "PK\x03\x04" || strlen($archive) - $offset < 30) {
            throw new RuntimeException('An Excel workbook component has an invalid header.');
        }
        $header = unpack('vversion/vflags/vmethod/vmodifiedTime/vmodifiedDate/Vcrc/VcompressedSize/VuncompressedSize/vnameLength/vextraLength', substr($archive, $offset + 4, 26));
        if ($header === false || (int)$header['method'] !== $entry['method']) {
            throw new RuntimeException('An Excel workbook component has inconsistent compression data.');
        }

        $dataOffset = $offset + 30 + (int)$header['nameLength'] + (int)$header['extraLength'];
        $compressed = substr($archive, $dataOffset, $entry['compressedSize']);
        if (strlen($compressed) !== $entry['compressedSize']) {
            throw new RuntimeException('An Excel workbook component is truncated.');
        }

        if ($entry['method'] === 0) {
            $content = $compressed;
        } elseif ($entry['method'] === 8) {
            $content = gzinflate($compressed, self::MAX_ENTRY_BYTES);
        } else {
            throw new RuntimeException('The Excel workbook uses an unsupported compression method.');
        }

        if ($content === false || strlen($content) !== $entry['uncompressedSize']) {
            throw new RuntimeException('An Excel workbook component could not be decompressed.');
        }

        return $content;
    }

    private function parseXml(string $content, string $part): SimpleXMLElement
    {
        $xml = simplexml_load_string($content, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOBLANKS);
        if ($xml === false) {
            throw new RuntimeException('The Excel workbook ' . $part . ' XML is invalid.');
        }
        return $xml;
    }

    private function readSharedStrings(string $archive, array $entries): array
    {
        if (!isset($entries['xl/sharedStrings.xml'])) {
            return [];
        }

        $xml = $this->parseXml($this->readZipEntry($archive, $entries, 'xl/sharedStrings.xml'), 'shared strings');
        $strings = [];
        foreach ($xml->xpath('//*[local-name()="si"]') ?: [] as $item) {
            $parts = $item->xpath('.//*[local-name()="t"]') ?: [];
            $strings[] = implode('', array_map(static fn(SimpleXMLElement $part): string => (string)$part, $parts));
        }
        return $strings;
    }

    private function findFirstWorksheet(string $archive, array $entries): string
    {
        $workbookPath = 'xl/workbook.xml';
        $relationshipsPath = 'xl/_rels/workbook.xml.rels';
        $workbook = $this->parseXml($this->readZipEntry($archive, $entries, $workbookPath), 'workbook');
        $relationships = $this->parseXml($this->readZipEntry($archive, $entries, $relationshipsPath), 'relationships');
        $sheets = $workbook->xpath('//*[local-name()="sheet"]') ?: [];
        if (!$sheets) {
            throw new RuntimeException('The Excel workbook does not contain a worksheet.');
        }

        $relationshipId = (string)$sheets[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
        foreach ($relationships->xpath('//*[local-name()="Relationship"]') ?: [] as $relationship) {
            $attributes = $relationship->attributes();
            if ((string)$attributes['Id'] !== $relationshipId) {
                continue;
            }
            if ((string)$attributes['TargetMode'] === 'External') {
                throw new RuntimeException('External worksheets are not supported.');
            }
            $target = (string)$attributes['Target'];
            $path = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/' . $target;
            return $this->normalizeZipPath($path);
        }

        throw new RuntimeException('The first worksheet reference is missing from the workbook.');
    }

    private function normalizeZipPath(string $path): string
    {
        $parts = [];
        foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
            } else {
                $parts[] = $part;
            }
        }
        return implode('/', $parts);
    }

    private function readWorksheetRows(SimpleXMLElement $sheet, array $sharedStrings): array
    {
        $rows = [];
        foreach ($sheet->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
            $rowAttributes = $row->attributes();
            $rowNumber = (int)($rowAttributes['r'] ?? 0);
            if ($rowNumber < 1) {
                continue;
            }

            $cells = [];
            foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                $attributes = $cell->attributes();
                $reference = (string)($attributes['r'] ?? '');
                $column = $this->columnIndex($reference);
                if ($column < 0) {
                    continue;
                }
                $type = (string)($attributes['t'] ?? '');
                $valueNodes = $cell->xpath('./*[local-name()="v"]') ?: [];
                $value = $valueNodes ? (string)$valueNodes[0] : '';

                if ($type === 's' && $value !== '') {
                    $value = $sharedStrings[(int)$value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $textNodes = $cell->xpath('.//*[local-name()="is"]//*[local-name()="t"]') ?: [];
                    $value = implode('', array_map(static fn(SimpleXMLElement $text): string => (string)$text, $textNodes));
                } elseif ($type === 'b') {
                    $value = $value === '1' ? 'TRUE' : 'FALSE';
                }
                $cells[$column] = $value;
            }
            ksort($cells);
            $rows[$rowNumber] = $cells;
        }
        ksort($rows);
        return $rows;
    }

    private function columnIndex(string $cellReference): int
    {
        if (!preg_match('/^([A-Z]+)/i', $cellReference, $matches)) {
            return -1;
        }
        $index = 0;
        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $index = ($index * 26) + ord($letter) - 64;
        }
        return $index - 1;
    }

    private function mapHeaders(array $headerCells): array
    {
        $aliases = [];
        foreach (self::HEADER_FIELDS as $field => $names) {
            foreach ($names as $name) {
                $aliases[$name] = $field;
            }
        }

        $headers = [];
        foreach ($headerCells as $column => $header) {
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', trim((string)$header)));
            if (isset($aliases[$normalized])) {
                $field = $aliases[$normalized];
                if (isset($headers[$field])) {
                    throw new RuntimeException('The Excel header row contains duplicate columns for ' . str_replace('_', ' ', $field) . '.');
                }
                $headers[$field] = $column;
            }
        }

        foreach (['property_number', 'article_road_name', 'onhand_quantity', 'unit', 'unit_cost'] as $requiredField) {
            if (!isset($headers[$requiredField])) {
                throw new RuntimeException('The Excel header row is missing the required column: ' . strtoupper(str_replace('_', ' ', $requiredField)) . '.');
            }
        }
        return $headers;
    }

    private function mapRecord(array $cells, array $headers, int $rowNumber): array
    {
        $value = static function (string $field) use ($cells, $headers): string {
            return isset($headers[$field]) ? trim((string)($cells[$headers[$field]] ?? '')) : '';
        };

        $propertyNumber = $value('property_number');
        $articleRoadName = $value('article_road_name');
        if ($propertyNumber === '' && $articleRoadName === '') {
            throw new RuntimeException('Excel row ' . $rowNumber . ' must include a property number or article/road name.');
        }

        $quantity = $this->parseNumber($value('onhand_quantity'), 'quantity', $rowNumber);
        $unitCost = $this->parseNumber($value('unit_cost'), 'unit cost', $rowNumber);
        if ($quantity < 0 || $unitCost < 0) {
            throw new RuntimeException('Excel row ' . $rowNumber . ' cannot have a negative quantity or unit cost.');
        }

        return [
            'barcode' => '',
            'property_number' => $propertyNumber,
            'article_road_name' => $articleRoadName,
            'description' => $value('description'),
            'onhand_quantity' => $quantity,
            'unit' => $value('unit'),
            'unit_cost' => $unitCost,
            'onhand_total_value' => $quantity * $unitCost,
            'status' => $value('status') !== '' ? $value('status') : 'Serviceable',
            'location' => $value('location'),
            'accountable' => $value('accountable'),
            'designation' => $value('designation'),
            'department' => $value('department'),
            'previously_accountable' => $value('previously_accountable'),
            'acquisition_date' => $this->parseDate($value('acquisition_date'), 'acquisition date', $rowNumber),
            'date_surrendered' => $this->parseDate($value('date_surrendered'), 'date surrendered', $rowNumber),
            'po_number' => $value('po_number'),
            'pr_number' => $value('pr_number'),
            'invoice_number' => $value('invoice_number'),
            'are_number' => $value('are_number'),
            'air_number' => $value('air_number'),
            'remarks' => $value('remarks'),
            'supplier' => $value('supplier'),
            'source_of_fund' => $value('source_of_fund'),
            'general_ledger' => $value('general_ledger'),
        ];
    }

    private function parseNumber(string $value, string $label, int $rowNumber): float
    {
        $normalized = str_replace([',', '₱', 'PHP', ' '], '', strtoupper($value));
        if ($normalized === '' || !is_numeric($normalized)) {
            throw new RuntimeException('Excel row ' . $rowNumber . ' has an invalid ' . $label . '.');
        }

        $number = (float)$normalized;
        if (!is_finite($number)) {
            throw new RuntimeException('Excel row ' . $rowNumber . ' has an invalid ' . $label . '.');
        }
        return $number;
    }

    private function parseDate(string $value, string $label, int $rowNumber): ?string
    {
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $serial = (float)$value;
            if ($serial >= 1 && $serial <= 2_958_465) {
                $date = (new DateTimeImmutable('1899-12-30'))->modify('+' . (int)$serial . ' days');
                return $date->format('Y-m-d');
            }
        }

        foreach (['!Y-m-d', '!n/j/Y', '!m/d/Y', '!d/m/Y', '!Y/m/d'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }

        throw new RuntimeException('Excel row ' . $rowNumber . ' has an invalid ' . $label . '.');
    }

    private function rowIsEmpty(array $cells): bool
    {
        foreach ($cells as $value) {
            if (trim((string)$value) !== '') {
                return false;
            }
        }
        return true;
    }
}
