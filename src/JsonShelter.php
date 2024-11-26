<?php

class JsonShelter
{
  private string $baseDir;

  // Constructor to initialize the database directory
  public function __construct(string $baseDir)
  {
    // Validate if the provided base directory exists; create if it doesn't
    if (!is_dir($baseDir)) {
      mkdir($baseDir, 0777, true);
    }
    $this->baseDir = rtrim($baseDir, "/");
  }

  // Get the path of the JSON file for a specific table
  private function getFilePath(string $table): string
  {
    return "{$this->baseDir}/{$table}.json";
  }

  // Read data from a JSON file
  private function readFile(string $table): array
  {
    $filePath = $this->getFilePath($table);

    // Check if the file exists; return empty array if it does not
    if (!file_exists($filePath)) {
      return [];
    }

    try {
      $json = file_get_contents($filePath);
      return json_decode($json, true) ?? [];
    } catch (\Exception $e) {
      // Handle any errors during file reading and JSON decoding
      echo "Error reading file: " . $e->getMessage();
      return [];
    }
  }

  // Write data to a JSON file
  private function writeFile(string $table, array $data): void
  {
    $filePath = $this->getFilePath($table);
    try {
      file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    } catch (\Exception $e) {
      // Handle any errors during file writing
      echo "Error writing file: " . $e->getMessage();
    }
  }

  // Create a new record in the specified table
  public function create(string $table, array $record): void
  {
    // Validate the record data before processing
    if (empty($record)) {
      throw new InvalidArgumentException("Record cannot be empty");
    }

    $data = $this->readFile($table);
    $record["id"] = $this->generateId($data); // Generate a unique ID
    $data[] = $record;
    $this->writeFile($table, $data);
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
    $data = $this->readFile($table);
    foreach ($data as $key => $record) {
      if ($record["id"] === $id) {
        unset($data[$key]); // Remove the record from the array
        $this->writeFile($table, $data);
        return true; // Return true on successful deletion
      }
    }
    return false; // Return false if the record isn't found
  }

  // Generate a unique ID for a new record
  private function generateId(array $data): int
  {
    // Assuming ID is auto-incrementing; find the maximum ID and add one
    $maxId = 0;
    foreach ($data as $record) {
      if (isset($record["id"]) && $record["id"] > $maxId) {
        $maxId = $record["id"];
      }
    }
    return $maxId + 1; // Return the next available ID
  }
}
