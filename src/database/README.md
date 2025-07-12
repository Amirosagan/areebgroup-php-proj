# Database Setup

This directory contains the SQL schema files for the User Management System.

## Files

- `schema.sql` - Contains the CREATE TABLE statements for Users and Posts tables

## Usage

### Option 1: Using phpMyAdmin
1. Open phpMyAdmin in your browser
2. Select your database
3. Go to the "SQL" tab
4. Copy and paste the contents of `schema.sql`
5. Click "Go" to execute

### Option 2: Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name < src/database/schema.sql
```

### Option 3: Using PHP Script
Create a setup script to run the SQL:
```php
<?php
require_once '../config/database.php';
$config = require '../config.php';
$database = new Database($config);

$sql = file_get_contents(__DIR__ . '/schema.sql');
$database->getConnection()->exec($sql);
echo "Database tables created successfully!";
?>
```

## Database Structure

### Users Table
- `id` (INT, Auto Increment, Primary Key)
- `Name` (VARCHAR(255), Not Null)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### Posts Table
- `id` (INT, Auto Increment, Primary Key)
- `Title` (VARCHAR(255), Not Null)
- `content` (TEXT)
- `ImgUrl` (VARCHAR(500))
- `User_id` (INT, Foreign Key to Users.id)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

## Notes

- The SQL includes `IF NOT EXISTS` clauses to prevent errors if tables already exist
- Foreign key constraint ensures data integrity between Users and Posts
- Indexes are created for better query performance
- Sample data is included for testing purposes 