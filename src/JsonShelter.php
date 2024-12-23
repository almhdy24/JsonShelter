<?php
namespace Almhdy\JsonShelter;
use Almhdy\JsonShelter\JsonEncryptor;

class JsonShelter
{
  private string $baseDir = "db";
  private JsonEncryptor $encryptor;
  private bool $isEncryptionEnabled;

  // Constructor to initialize the database directory and encryption
  public function __construct(
    ?string $baseDir,
    string $secretKey,
    string $secretIv
  ) {
    // Validate if the provided base directory exists; create if it doesn't
    if ($baseDir === null) {
      $baseDir = $this->baseDir; // Use default if null
    }

    if (!is_dir($baseDir)) {
      mkdir($baseDir, 0777, true);
    }

    $this->baseDir = rtrim($baseDir, "/");
    $this->encryptor = new JsonEncryptor($secretKey, $secretIv);
    $this->isEncryptionEnabled = true; // Default to enabled
  }

  public function enableEncryption(): void
  {
    $this->isEncryptionEnabled = true;
  }

  public function disableEncryption(): void
  {
    $this->isEncryptionEnabled = false;
  }
  // Get the path of the JSON file for a specific table
  private function getFilePath(string $table): string
  {
    return "{$this->baseDir}/{$table}.json";
  }

  // Read data from an encrypted JSON file
  private function readFile(string $table): array
  {
    $filePath = $this->getFilePath($table);

    if (!file_exists($filePath)) {
      return []; // Return an empty array if file does not exist
    }

    try {
      $json = file_get_contents($filePath);
      $encryptedData = json_decode($json, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("JSON decode error: " . json_last_error_msg());
      }

      if (isset($encryptedData["content"])) {
        // Check if encryption is enabled
        if ($this->isEncryptionEnabled) {
          // Expected 'content' to be an encrypted string
          if (is_string($encryptedData["content"])) {
            // Decrypt and ensure it returns an array
            $decryptedData = $this->encryptor->decrypt(
              $encryptedData["content"]
            );
            return is_array($decryptedData) ? $decryptedData : [];
          } else {
            throw new \Exception("Expected string for decryption.");
          }
        } else {
          // If encryption is disabled, return raw content, ensure it's an array
          return is_array($encryptedData["content"])
            ? $encryptedData["content"]
            : [];
        }
      } else {
        throw new \Exception(
          "Invalid file structure: 'content' key not found."
        );
      }
    } catch (\Exception $e) {
      $this->logError("Error reading file: " . $e->getMessage());
      return []; // Return an empty array on error
    }
  }

  // Write data to an encrypted JSON file
  private function writeFile(string $table, array $data): void
  {
    $filePath = $this->getFilePath($table);

    // Validate data is an array
    if (!is_array($data)) {
      throw new \InvalidArgumentException("Data must be an array.");
    }

    try {
      // Decide whether to encrypt data based on the current flag
      $contentData = $this->isEncryptionEnabled
        ? $this->encryptor->encrypt($data)
        : $data; // Use raw data if encryption is disabled

      // Structure the data with 'content' key
      $content = ["content" => $contentData];

      // Write the content to the file
      if (
        file_put_contents(
          $filePath,
          json_encode($content, JSON_PRETTY_PRINT)
        ) === false
      ) {
        throw new \Exception("Failed to write data to file.");
      }
    } catch (\Exception $e) {
      // Handle errors during file writing
      $this->logError("Error writing file '{$filePath}': " . $e->getMessage());
    }
  }
  // Create a new record in the specified table
  public function create(string $table, array $record): void
  {
    // Validate the record data before processing
    if (empty($record)) {
      throw new \InvalidArgumentException("Record cannot be empty");
    }

    $data = $this->readFile($table);
    $record["id"] = $this->generateId($data); // Generate a unique ID
    $data[] = $record;
    $this->writeFile($table, $data);
  }
  // Read all records from the specified table
  public function readAll(string $table): array
  {
    return $this->readFile($table); // Read and return all records from the file
  }
  // Read a record from the specified table using its ID
  public function read(string $table, int $id): ?array
  {
    $data = $this->readFile($table);
    foreach ($data as $record) {
      if ($record["id"] === $id) {
        return $record; // Return the found record
      }
    }
    return null; // Return null if no record is found
  }
  // Read all records from the specified table in batches
  public function readAllBatched(string $table, int $batchSize): array
  {
    $data = $this->readFile($table); // Read all records from the file
    $batches = []; // Initialize an array to hold the batches

    // Split the data into batches
    for ($i = 0; $i < count($data); $i += $batchSize) {
      $batches[] = array_slice($data, $i, $batchSize); // Create a batch
    }

    return $batches; // Return the array of batches
  }
  // Update an existing record in the specified table
  public function update(string $table, int $id, array $newData): bool
  {
    if (empty($newData)) {
      throw new InvalidArgumentException("New data cannot be empty");
    }

    $data = $this->readFile($table);
    foreach ($data as &$record) {
      if ($record["id"] === $id) {
        $record = array_merge($record, $newData); // Update with new data
        $this->writeFile($table, $data);
        return true; // Return true on successful update
      }
    }
    return false; // Return false if the record isn't found
  }

  // Delete a record from the specified table using its ID
  public function delete(string $table, int $id): bool
  {
    // Validate the table name
    if (empty($table) || !is_string($table)) {
      throw new \InvalidArgumentException("Invalid table name provided.");
    }

    // Read existing data from file
    try {
      $data = $this->readFile($table);

      // Find and remove the record with the specified ID
      $filteredData = array_filter($data, function ($record) use ($id) {
        return $record["id"] !== $id; // Keep records that do not match the ID
      });

      // Check if a record was removed
      if (count($data) === count($filteredData)) {
        // Record not found, return false
        $this->logError("Record with ID {$id} not found in table '{$table}'.");
        return false;
      }

      // Write the updated data back to the file
      $this->writeFile($table, array_values($filteredData)); // Reset keys to be sequential
      return true; // Return true on successful deletion
    } catch (\Exception $e) {
      // Handle potential file reading or writing errors
      $this->logError(
        "Error deleting record from table '{$table}': " . $e->getMessage()
      );
      return false;
    }
  }

// Read records from the specified table that match the given conditions
public function where(string $table, array $conditions): array
{
    // Validate the table name
    if (empty($table) || !is_string($table)) {
        throw new \InvalidArgumentException("Invalid table name provided.");
    }

    // Read all records from the specified table
    $data = $this->readFile($table);
    $results = []; // Initialize an array to hold matching records

    // Iterate through each record and check if it matches the conditions
    foreach ($data as $record) {
        $matches = true;
        foreach ($conditions as $key => $value) {
            // Check if the record has the key and if its value matches the condition
            if (!array_key_exists($key, $record) || $record[$key] !== $value) {
                $matches = false;
                break; // Exit the inner loop if a condition is not met
            }
        }
        // If all conditions are met, add the record to the results
        if ($matches) {
            $results[] = $record;
        }
    }

    return $results; // Return all records that matched the conditions
}
  // Generate a unique ID for a new record
  private function generateId(array $data): int
  {
    // Guard clause for empty data
    if (empty($data)) {
      return 1; // Start from 1 if there are no records
    }

    // Extract IDs and find the maximum ID value
    $ids = array_column($data, "id"); // Retrieve all 'id' values from the records
    $maxId = max($ids); // Find the maximum ID value

    return $maxId + 1; // Return the next available ID
  }
  // A simple method to log errors
  private function logError(string $message): void
  {
    // file logging errors
    error_log($message);
  }
  // Get the size and permissions of all JSON files in the base directory
  public function getJsonFilesInfo(): array
  {
    if (!is_dir($this->baseDir)) {
      return ["error" => "Directory does not exist."];
    }

    if (!is_readable($this->baseDir)) {
      return ["error" => "Directory is not readable."];
    }

    $filesInfo = [];
    $files = glob($this->baseDir . "/*.json"); // Get all JSON files in the baseDir

    if (empty($files)) {
      return ["message" => "No JSON files found in the directory."];
    }

    foreach ($files as $file) {
      $fileSize = filesize($file); // Get file size
      $permissions = fileperms($file); // Get permissions
      $filesInfo[$file] = [
        "size" => $fileSize,
        "permissions" => $this->getPermissionsString($permissions),
      ];
    }

    return $filesInfo; // Return an array with size and permissions for each file
  }

  // Set best permissions for all JSON files in the base directory
  public function setBestPermissionsForJsonFiles(): array
  {
    if (!is_dir($this->baseDir)) {
      return ["error" => "Directory does not exist."];
    }

    if (!is_writable($this->baseDir)) {
      return ["error" => "Directory is not writable."];
    }

    $files = glob($this->baseDir . "/*.json"); // Get all JSON files in the baseDir
    $results = [];
    $bestPermissions = 0664; // Set best permissions (rw-rw-r--)

    if (empty($files)) {
      return ["message" => "No JSON files found to update permissions."];
    }

    foreach ($files as $file) {
      $result = chmod($file, $bestPermissions);

      if (!$result) {
        $results[$file] =
          "Failed to update permissions due to insufficient permissions or other errors.";
      } else {
        $results[$file] = "Permissions updated successfully.";
      }
    }

    return $results; // Return results for each file
  }

  // Check if the base directory is readable and writable
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

    return $status; // Return directory status
  }

  // Helper function to convert permissions to a readable string
  private function getPermissionsString($permissions): string
  {
    $info = "";

    // Convert to human-readable form
    $info .= $permissions & 0x1000 ? "p" : "-"; // FIFO pipe
    $info .= $permissions & 0x2000 ? "c" : "-"; // Character special
    $info .= $permissions & 0x4000 ? "d" : "-"; // Directory
    $info .= $permissions & 0x6000 ? "b" : "-"; // Block special
    $info .= $permissions & 0x100 ? "r" : "-"; // Owner read
    $info .= $permissions & 0x80 ? "w" : "-"; // Owner write
    $info .= $permissions & 0x40 ? "x" : "-"; // Owner execute
    $info .= $permissions & 0x20 ? "r" : "-"; // Group read
    $info .= $permissions & 0x10 ? "w" : "-"; // Group write
    $info .= $permissions & 0x8 ? "x" : "-"; // Group execute
    $info .= $permissions & 0x4 ? "r" : "-"; // Other read
    $info .= $permissions & 0x2 ? "w" : "-"; // Other write
    $info .= $permissions & 0x1 ? "x" : "-"; // Other execute

    return $info; // Return permissions string
  }
}
