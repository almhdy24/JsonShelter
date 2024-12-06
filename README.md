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

You can also download a release archive from GitHub. Simply visit the [releases page](https://github.com/almhdy24/JsonShelter/releases) and grab the latest version as a ZIP file. Extract it to your project directory afterward.

## Usage 📖

Using JsonShelter in your project is easy-peasy! Include the class by using Composer or directly. Let’s walk through it!

### Option 1: Using Composer 🥇

If you used Composer, just include the autoload file and get started! Here’s an example:

```php
// Include Composer's autoload file
require 'vendor/autoload.php';

// Use the JsonShelter namespace
use Almhdy\JsonShelter\JsonShelter;

// Create a new JsonShelter instance
$baseDir = "myDatabase"; // Base directory path
$secretKey = "your_secret_key"; // Your secret key
$secretIv = "your_secret_iv"; // Your secret IV

$db = new JsonShelter($baseDir, $secretKey, $secretIv);
```

### Option 2: Directly Including the File 🌟

If you cloned the repo or downloaded the archive, include the `JsonShelter.php` file directly. Adjust the path as needed!

```php
// Include the JsonShelter class file
require 'path/to/JsonShelter.php';  // Set the correct path

// Use the JsonShelter namespace
use Almhdy\JsonShelter\JsonShelter;

// Create a new JsonShelter instance
$baseDir = "myDatabase"; // Base directory path
$secretKey = "your_secret_key"; // Your secret key
$secretIv = "your_secret_iv"; // Your secret IV

$db = new JsonShelter($baseDir, $secretKey, $secretIv);
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

## 🌟 Additional Methods Overview

Our database manager 🌈, now affectionately called "db," comes packed with handy extra methods 🎉 that make managing JSON files and checking their directory status a breeze! Let’s explore these features with a touch of flair. 💼✨

## 1. 📂 Check Directory Status

Before diving into the JSON realm, make sure your directory is in tip-top shape! This method checks if it’s readable and writable:

```php
$status = $db->checkDirectoryStatus();
print_r($status);
```

### Purpose 🕵️‍♂️

- **Readability**: Ensures you can easily read files from the directory. 📖
- **Writability**: Confirms that you can create or modify files without a hitch! ✍️

### Expected Output 🎉

You’ll receive an associative array detailing the directory's read and write capabilities! This is a developer's best friend for troubleshooting permission issues! 🛠️

---

## 2. 🗄️ Get Size and Permissions of JSON Files

Curious about your JSON files? This method gives you the lowdown on their size and permissions:

```php
$directoryInfo = $db->getJsonFilesInfo();
print_r($directoryInfo);
```

### Purpose 📊

- **File Size**: See how much space your JSON files are taking up! 🚀
- **File Permissions**: Check the permissions for each file—crucial for security! 🔒

### Expected Output 🥳

You’ll get an array where each JSON file is highlighted, showcasing its size and permissions. It’s like a report card for your files! 📝

---

## 3. 🛡️ Set Best Permissions for JSON Files

Let’s tighten up security! This method automatically sets the best permissions for your JSON files:

```php
$permissionResults = $db->setBestPermissionsForJsonFiles();
print_r($permissionResults);
```

### Purpose 🔐

- **Security Boost**: Ensures your JSON files have just the right permissions, keeping unwanted eyes away! 👀
- **Standardization**: Applies uniform permission settings across all files—cohesion is key! 🔗

### Expected Output 🎊

You’ll receive an array reflecting the results of all the nifty permission adjustments made. Verify that everything’s shipshape and ready to go! ⚓️

---

With these vibrant methods 💖, managing your JSON files becomes not just easy, but enjoyable! Embrace the elegance of efficient code and let db do the heavy lifting, making your application shine like a star! 🌟✨

## Conclusion 🎉

And that's a wrap! 🎊 You've now unlocked the power of JsonShelter, making it super simple to integrate into your PHP applications! 🚀 With just a few easy steps, you're ready to harness the full potential of JSON management.

Remember, every line of code brings you closer to your goals. Embrace the journey and happy coding! 😄💻✨ 

