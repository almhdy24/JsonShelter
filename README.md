
# 🗄️ JsonShelter - Your Friendly PHP JSON Manager

Welcome to JsonShelter! This is a nifty PHP library designed to help you store and manage your JSON data with ease. Let's dive into the installation and usage instructions! 🚀

## Installation ⚙️

You can bring JsonShelter into your project using one of the methods below:

### Method 1: Install via Composer 🎵

Use Composer to include the package seamlessly in your project:

```bash
composer require almhdy/json-shelter
```

### Method 2: Clone the Repository 🥳

Prefer to have a local copy? Clone the repository directly from GitHub:

```bash
git clone https://github.com/almhdy24/JsonShelter.git
```

### Method 3: Download as Archive 📦

You can also download a release archive from GitHub. Simply visit the [releases page](https://github.com/almhdy24/JsonShelter/releases) and grab the latest version as a ZIP file. Extract it to your project directory.

## Usage 📖

Using JsonShelter in your project is easy-peasy! Include the class by using Composer or directly. Let’s walk through it!

### Option 1: Using Composer 🥇

If you used Composer, just include the autoload file and get started! Here’s an example:

```php
// Include Composer's autoload file
require 'vendor/autoload.php';

// Use the JsonShelter namespace
use Almhdy\JsonShelter.JsonShelter;

// Create a new JsonShelter instance
$baseDir = "myDatabase"; // Base directory path
$secretKey = "your_secret_key"; // Your secret key
$secretIv = "your_secret_iv"; // Your secret IV

$logger = new Logger('json_shelter');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

$db = new JsonShelter($baseDir, $secretKey, $secretIv, $logger);
```

### Option 2: Directly Including the File 🌟

If you cloned the repo or downloaded the archive, include the `JsonShelter.php` file directly. Adjust the path as needed!

```php
// Include the JsonShelter class file
require 'path/to/JsonShelter.php';  // Set the correct path

// Use the JsonShelter namespace
use Almhdy.JsonShelter.JsonShelter;

// Create a new JsonShelter instance
$baseDir = "myDatabase"; // Base directory path
$secretKey = "your_secret_key"; // Your secret key
$secretIv = "your_secret_iv"; // Your secret IV

$logger = new Logger('json_shelter');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

$db = new JsonShelter($baseDir, $secretKey, $secretIv, $logger);
```

### Encryption 🛡️

You can easily enable or disable encryption for your records. This is a handy feature to keep your data secure!

- **Enable Encryption:**

```php
$db->enableEncryption();
```

- **Disable Encryption:**

```php
$db->disableEncryption();
```

### Record Operations 📋

You can perform CRUD operations (Create, Read, Update, Delete) with the following methods:

- **Create a record:** ✍️

```php
// Create a new record in 'myTable'
$db->create('myTable', ['name' => 'John', 'age' => 30]);
```

- **Read a record:** 🔍

```php
// Read a record from 'myTable' by ID
$record = $db->read('myTable', 1); // Replace 1 with the record ID
print_r($record); // Display the retrieved record
```

- **Update a record:** 🔄

```php
// Update a record in 'myTable' by ID
$db->update('myTable', 1, ['age' => 31]); // Increment age
```

- **Delete a record:** ❌

```php
// Delete a record from 'myTable' by ID
$db->delete('myTable', 1); // Replace 1 with the record ID
```

## Model Class

The `Model` class provides an ORM-like interface to interact with the `JsonShelter` class.

### Initialization

First, initialize a `Model` for a specific table:

```php
use Almhdy.JsonShelter.Model;

// Initialize a Model for the 'users' table
$userModel = new Model($db, 'users');
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
use Almhdy.JsonShelter.JsonEncryptor;

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

## Additional Methods Overview

Our database manager 🌈, now affectionately called "db," comes packed with handy extra methods 🎉 that make managing JSON files and checking their directory status a breeze! Let’s explore them.

### Check Directory Status

Before diving into the JSON realm, make sure your directory is in tip-top shape! This method checks if it’s readable and writable:

```php
$status = $db->checkDirectoryStatus();
print_r($status);
```

### Get Size and Permissions of JSON Files

Curious about your JSON files? This method gives you the lowdown on their size and permissions:

```php
$directoryInfo = $db->getJsonFilesInfo();
print_r($directoryInfo);
```

### Set Best Permissions for JSON Files

Let’s tighten up security! This method automatically sets the best permissions for your JSON files:

```php
$permissionResults = $db->setBestPermissionsForJsonFiles();
print_r($permissionResults);
```

## Conclusion 🎉

And that's a wrap! 🎊 You've now unlocked the power of JsonShelter, making it super simple to integrate into your PHP applications! 🚀 With just a few easy steps, you're ready to harness the power of file-based JSON data management, complete with encryption and ORM-like capabilities.

Remember, every line of code brings you closer to your goals. Embrace the journey and happy coding! 😄💻✨
