# JsonShelter Usage Guide

## Introduction

The `JsonShelter` class provides a simple file-based JSON database solution with encryption capabilities. This guide covers the usage of `JsonShelter`, `Model`, and `JsonEncryptor` classes, along with examples for various operations.

## JsonShelter Class

The `JsonShelter` class offers CRUD operations and query capabilities for a file-based JSON database with encryption.

### Initialization

First, initialize the `JsonShelter` class with a base directory for the database, a secret key, and an initialization vector for encryption.

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Almhdy\JsonShelter\JsonShelter;

// Create a logger instance (using Monolog for example)
$logger = new Logger('json_shelter');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// Initialize JsonShelter
$jsonShelter = new JsonShelter('path/to/db', 'your_secret_key', 'your_secret_iv', $logger);
```

### Inserting Data

You can insert data into a table using the `insertInto` method.

```php
// Insert a new record into the 'users' table
$jsonShelter->insertInto('users', ['name' => 'John Doe', 'email' => 'john@example.com']);
```

### Reading Data

To read all records from a table:

```php
// Read all records from the 'users' table
$users = $jsonShelter->readAll('users');
print_r($users);
```

To read a specific record by its ID:

```php
// Read a specific record from the 'users' table by ID
$user = $jsonShelter->read('users', 1);
print_r($user);
```

### Updating Data

To update a record:

```php
// Update a record in the 'users' table
$jsonShelter->updateRecord('users', 1, ['email' => 'john.doe@example.com']);
```

### Deleting Data

To delete a record:

```php
// Delete a record from the 'users' table
$jsonShelter->deleteRecord('users', 1);
```

### Querying Data

You can filter records using the `where` method:

```php
// Get users with the name 'John Doe'
$users = $jsonShelter->where('users', ['name' => 'John Doe']);
print_r($users);
```

You can also search within a specific field:

```php
// Search for users with 'example.com' in their email
$users = $jsonShelter->search('users', 'email', 'example.com');
print_r($users);
```

### Ordering and Limiting Data

Ordering data:

```php
// Order users by name in descending order
$users = $jsonShelter->orderBy('users', 'name', 'desc');
print_r($users);
```

Limiting data:

```php
// Get the first 10 users, skipping the first 5 records
$users = $jsonShelter->limit('users', 10, 5);
print_r($users);
```

## Model Class

The `Model` class provides an ORM-like interface to interact with the `JsonShelter` class.

### Initialization

First, initialize a `Model` for a specific table:

```php
use Almhdy\JsonShelter\Model;

// Initialize a Model for the 'users' table
$userModel = new Model($jsonShelter, 'users');
```

### Inserting Data

Inserting data using the `Model`:

```php
// Insert a new record
$userModel->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
```

### Reading Data

Reading all records:

```php
// Read all records
$users = $userModel->all();
print_r($users);
```

Reading a specific record by ID:

```php
// Read a specific record by ID
$user = $userModel->find(1);
print_r($user);
```

### Updating Data

Updating a record:

```php
// Update a record
$userModel->update(1, ['email' => 'jane.doe@example.com']);
```

### Deleting Data

Deleting a record:

```php
// Delete a record
$userModel->delete(1);
```

### Querying Data

Filtering records:

```php
// Get users with the name 'Jane Doe'
$users = $userModel->where(['name' => 'Jane Doe']);
print_r($users);
```

Searching within a field:

```php
// Search for users with 'example.com' in their email
$users = $userModel->search('email', 'example.com');
print_r($users);
```

Ordering and limiting data:

```php
// Order users by name in ascending order
$orderedUsers = $userModel->orderBy('name', 'asc');
print_r($orderedUsers);

// Limit the number of users returned
$limitedUsers = $userModel->limit(10, 0);
print_r($limitedUsers);
```

## JsonEncryptor Class

The `JsonEncryptor` class handles encryption and decryption of data.

### Initialization

Initialize the `JsonEncryptor` class with a secret key and initialization vector:

```php
use Almhdy\JsonShelter\JsonEncryptor;

// Initialize JsonEncryptor
$encryptor = new JsonEncryptor('your_secret_key', 'your_secret_iv');
```

### Encrypting Data

Encrypting data:

```php
$data = ['name' => 'Jane Doe', 'email' => 'jane@example.com'];
$encryptedData = $encryptor->encrypt($data);
echo $encryptedData;
```

### Decrypting Data

Decrypting data:

```php
$decryptedData = $encryptor->decrypt($encryptedData);
print_r($decryptedData);
```

### Handling Large Datasets

Encrypting a large file using streams:

```php
$inputStream = fopen('path/to/large_file.json', 'rb');
$outputStream = fopen('path/to/encrypted_file.enc', 'wb');
$encryptor->encryptStream($inputStream, $outputStream);
fclose($inputStream);
fclose($outputStream);
```

Decrypting a large file using streams:

```php
$inputStream = fopen('path/to/encrypted_file.enc', 'rb');
$outputStream = fopen('path/to/decrypted_file.json', 'wb');
$encryptor->decryptStream($inputStream, $outputStream);
fclose($inputStream);
fclose($outputStream);
```

## Summary

- The `JsonShelter` class provides CRUD operations and query capabilities for a file-based JSON database with encryption.
- The `Model` class offers an ORM-like interface to interact with `JsonShelter`.
- The `JsonEncryptor` class handles encryption and decryption of data, including large datasets using streams.

These examples cover a wide range of use cases and provide a deep understanding of how to use the classes in various scenarios.