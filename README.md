# JsonShelter

**JsonShelter** is a lightweight JSON database library for PHP that simplifies data management with easy CRUD (Create, Read, Update, Delete) operations. Ideal for prototypes and small applications, this library provides a simple file-based storage solution.

## Features

- 📁 File-based JSON storage
- ✅ Easy CRUD operations
- 🔄 Unique record identification
- 📥 Simple setup and usage

## Installation

You can include the class in your project by cloning the repository or downloading the `JsonShelter.php` file.

```bash
git clone https://github.com/almhdy24/JsonShelter.git
```

## Usage

1. **Include the class:**

   ```php
   require 'JsonShelter.php';
   ```

2. **Create a new instance:**

   ```php
   $db = new JsonShelter('/path/to/your/database');
   ```

3. **CRUD Operations:**

   - **Create a record:**

     ```php
     $db->create('myTable', ['name' => 'John', 'age' => 30]);
     ```

   - **Read a record:**

     ```php
     $record = $db->read('myTable', 1); // Replace 1 with the record ID
     ```

   - **Update a record:**

     ```php
     $db->update('myTable', 1, ['age' => 31]); // Increment age
     ```

   - **Delete a record:**

     ```php
     $db->delete('myTable', 1); // Replace 1 with the record ID
     ```

## Methods

- `create(string $table, array $record): void`
- `read(string $table, int $id): ?array`
- `update(string $table, int $id, array $newData): bool`
- `delete(string $table, int $id): bool`

## Contributing

Contributions are welcome! If you have suggestions or improvements, feel free to open an issue or submit a pull request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

Thanks to everyone who contributes to making **JsonShelter** better!