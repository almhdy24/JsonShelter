<?php
namespace Almhdy\JsonShelter;

use Psr\Log\LoggerInterface;

class JsonShelter
{
    private string $baseDir = "db";
    private JsonEncryptor $encryptor;
    private bool $isEncryptionEnabled;
    private LoggerInterface $logger;

    public function __construct(
        ?string $baseDir,
        string $secretKey,
        string $secretIv,
        LoggerInterface $logger
    ) {
        if ($baseDir === null) {
            $baseDir = $this->baseDir;
        }

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $this->baseDir = rtrim($baseDir, "/");
        $this->encryptor = new JsonEncryptor($secretKey, $secretIv);
        $this->isEncryptionEnabled = true;
        $this->logger = $logger;
    }

    public function enableEncryption(): void
    {
        $this->isEncryptionEnabled = true;
    }

    public function disableEncryption(): void
    {
        $this->isEncryptionEnabled = false;
    }

    private function getFilePath(string $table): string
    {
        return "{$this->baseDir}/{$table}.json";
    }

    private function readFile(string $table): array
    {
        $filePath = $this->getFilePath($table);

        if (!file_exists($filePath)) {
            return [];
        }

        try {
            $json = file_get_contents($filePath);
            $encryptedData = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON decode error: " . json_last_error_msg());
            }

            if (isset($encryptedData["content"])) {
                if ($this->isEncryptionEnabled) {
                    if (is_string($encryptedData["content"])) {
                        $decryptedData = $this->encryptor->decrypt($encryptedData["content"]);
                        return is_array($decryptedData) ? $decryptedData : [];
                    } else {
                        throw new \Exception("Expected string for decryption.");
                    }
                } else {
                    return is_array($encryptedData["content"]) ? $encryptedData["content"] : [];
                }
            } else {
                throw new \Exception("Invalid file structure: 'content' key not found.");
            }
        } catch (\Exception $e) {
            $this->logger->error("Error reading file: " . $e->getMessage());
            return [];
        }
    }

    private function writeFile(string $table, array $data): void
    {
        $filePath = $this->getFilePath($table);

        if (!is_array($data)) {
            throw new \InvalidArgumentException("Data must be an array.");
        }

        try {
            $contentData = $this->isEncryptionEnabled
                ? $this->encryptor->encrypt($data)
                : $data;

            $content = ["content" => $contentData];

            if (file_put_contents($filePath, json_encode($content, JSON_PRETTY_PRINT)) === false) {
                throw new \Exception("Failed to write data to file.");
            }
        } catch (\Exception $e) {
            $this->logger->error("Error writing file '{$filePath}': " . $e->getMessage());
        }
    }

    public function create(string $table, array $record): void
    {
        if (empty($record)) {
            throw new \InvalidArgumentException("Record cannot be empty");
        }

        $data = $this->readFile($table);
        $record["id"] = $this->generateId($data);
        $data[] = $record;
        $this->writeFile($table, $data);
    }

    public function insertInto(string $table, array $record): void
    {
        $this->create($table, $record);
    }

    public function readAll(string $table): array
    {
        return $this->readFile($table);
    }

    public function read(string $table, int $id): ?array
    {
        $data = $this->readFile($table);
        foreach ($data as $record) {
            if ($record["id"] === $id) {
                return $record;
            }
        }
        return null;
    }

    public function readAllBatched(string $table, int $batchSize): array
    {
        $data = $this->readFile($table);
        $batches = [];
        for ($i = 0; $i < count($data); $i += $batchSize) {
            $batches[] = array_slice($data, $i, $batchSize);
        }
        return $batches;
    }

    public function update(string $table, int $id, array $newData): bool
    {
        if (empty($newData)) {
            throw new InvalidArgumentException("New data cannot be empty");
        }

        $data = $this->readFile($table);
        foreach ($data as &$record) {
            if ($record["id"] === $id) {
                $record = array_merge($record, $newData);
                $this->writeFile($table, $data);
                return true;
            }
        }
        return false;
    }

    public function updateRecord(string $table, int $id, array $newData): bool
    {
        return $this->update($table, $id, $newData);
    }

    public function delete(string $table, int $id): bool
    {
        if (empty($table) || !is_string($table)) {
            throw new \InvalidArgumentException("Invalid table name provided.");
        }

        try {
            $data = $this->readFile($table);
            $filteredData = array_filter($data, function ($record) use ($id) {
                return $record["id"] !== $id;
            });

            if (count($data) === count($filteredData)) {
                $this->logger->error("Record with ID {$id} not found in table '{$table}'.");
                return false;
            }

            $this->writeFile($table, array_values($filteredData));
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Error deleting record from table '{$table}': " . $e->getMessage());
            return false;
        }
    }

    public function deleteRecord(string $table, int $id): bool
    {
        return $this->delete($table, $id);
    }

    public function where(string $table, array $conditions): array
    {
        if (empty($table) || !is_string($table)) {
            throw new \InvalidArgumentException("Invalid table name provided.");
        }

        $data = $this->readFile($table);
        $results = [];

        foreach ($data as $record) {
            $matches = true;
            foreach ($conditions as $key => $value) {
                if (!array_key_exists($key, $record) || $record[$key] !== $value) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                $results[] = $record;
            }
        }

        return $results;
    }

    public function search(string $table, string $field, string $keyword): array
    {
        if (empty($table) || !is_string($table)) {
            throw new \InvalidArgumentException("Invalid table name provided.");
        }

        if (empty($field) || !is_string($field)) {
            throw new \InvalidArgumentException("Invalid field name provided.");
        }

        $data = $this->readFile($table);
        $results = [];

        foreach ($data as $record) {
            if (array_key_exists($field, $record) && stripos($record[$field], $keyword) !== false) {
                $results[] = $record;
            }
        }

        return $results;
    }

    private function generateId(array $data): int
    {
        if (empty($data)) {
            return 1;
        }

        $ids = array_column($data, "id");
        $maxId = max($ids);

        return $maxId + 1;
    }

    public function orderBy(string $table, string $field, string $direction = 'asc'): array
    {
        $data = $this->readAll($table);
        usort($data, function ($a, $b) use ($field, $direction) {
            if ($direction === 'asc') {
                return $a[$field] <=> $b[$field];
            } else {
                return $b[$field] <=> $a[$field];
            }
        });
        return $data;
    }

    public function limit(string $table, int $limit, int $offset = 0): array
    {
        $data = $this->readAll($table);
        return array_slice($data, $offset, $limit);
    }

    public function hasOne(string $relatedTable, string $foreignKey, string $localKey): array
    {
        $relatedData = $this->where($relatedTable, [$foreignKey => $localKey]);
        return $relatedData;
    }

    public function hasMany(string $relatedTable, string $foreignKey, string $localKey): array
    {
        return $this->where($relatedTable, [$foreignKey => $localKey]);
    }

    public function belongsTo(string $relatedTable, string $foreignKey, string $ownerKey): ?array
    {
        $relatedData = $this->where($relatedTable, [$ownerKey => $foreignKey]);
        return $relatedData[0] ?? null;
    }

    private function logError(string $message): void
    {
        $this->logger->error($message);
    }

    public function getJsonFilesInfo(): array
    {
        if (!is_dir($this->baseDir)) {
            return ["error" => "Directory does not exist."];
        }

        if (!is_readable($this->baseDir)) {
            return ["error" => "Directory is not readable."];
        }

        $filesInfo = [];
        $files = glob($this->baseDir . "/*.json");

        if (empty($files)) {
            return ["message" => "No JSON files found in the directory."];
        }

        foreach ($files as $file) {
            $fileSize = filesize($file);
            $permissions = fileperms($file);
            $filesInfo[$file] = [
                "size" => $fileSize,
                "permissions" => $this->getPermissionsString($permissions),
            ];
        }

        return $filesInfo;
    }

    public function setBestPermissionsForJsonFiles(): array
    {
        if (!is_dir($this->baseDir)) {
            return ["error" => "Directory does not exist."];
        }

        if (!is_writable($this->baseDir)) {
            return ["error" => "Directory is not writable."];
        }

        $files = glob($this->baseDir . "/*.json");
        $results = [];
        $bestPermissions = 0664;

        if (empty($files)) {
            return ["message" => "No JSON files found to update permissions."];
        }

        foreach ($files as $file) {
            $result = chmod($file, $bestPermissions);

            if (!$result) {
                $results[$file] = "Failed to update permissions.";
            } else {
                $results[$file] = "Permissions updated successfully.";
            }
        }

        return $results;
    }

    public function checkDirectoryStatus(): array
    {
        $status = [];

        if (!is_dir($this->baseDir)) {
            $status["error"] = "Directory does not exist.";
        } else {
            $status["readable"] = is_readable($this->baseDir);
            $status["writable"] = is_writable($this->baseDir);
            $status["message"] = "Directory exists.";
        }

        return $status;
    }

    private function getPermissionsString($permissions): string
    {
        $info = "";

        $info .= $permissions & 0x1000 ? "p" : "-";
        $info .= $permissions & 0x2000 ? "c" : "-";
        $info .= $permissions & 0x4000 ? "d" : "-";
        $info .= $permissions & 0x6000 ? "b" : "-";
        $info .= $permissions & 0x100 ? "r" : "-";
        $info .= $permissions & 0x80 ? "w" : "-";
        $info .= $permissions & 0x40 ? "x" : "-";
        $info .= $permissions & 0x20 ? "r" : "-";
        $info .= $permissions & 0x10 ? "w" : "-";
        $info .= $permissions & 0x8 ? "x" : "-";
        $info .= $permissions & 0x4 ? "r" : "-";
        $info .= $permissions & 0x2 ? "w" : "-";
        $info .= $permissions & 0x1 ? "x" : "-";

        return $info;
    }
}