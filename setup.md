# GiveHub Deliverable Tracker Setup Guide

This guide will help you set up the GiveHub Deliverable Tracker system, allowing you to track tasks across the three tranches (MVP, Testnet, and Mainnet) of your project.

## System Requirements

- PHP 7.4+ with MySQLi extension
- MySQL or MariaDB database
- Web server (Apache, Nginx, etc.)

## File Structure

Create the following files in your web server directory:

1. `config.php` - Database connection settings
2. `handle_tasks.php` - API for task management
3. `seed_db.php` - Database initialization script
4. `index.php` - Main user interface

## Setup Steps

### 1. Create Database

First, create a MySQL database for the application:

```sql
CREATE DATABASE givehub;
```

### 2. Configure Database Connection

Edit the `config.php` file and update with your database credentials:

```php
<?php
$servername = "localhost";
$username = "yourusername"; // Replace with your MySQL username
$password = "yourpassword"; // Replace with your MySQL password
$dbname = "givehub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

### 3. Initialize the Database

Run the seed script to create the tasks table and populate it with tasks:

```
php seed_db.php
```

You should see output indicating successful table creation and task insertion.

### 4. Access the Application

Open your web browser and navigate to the location where you placed the files:

```
http://yourserver/path/to/index.php
```

## Using the Tracker

- **View Tasks**: Tasks are organized by tranche (MVP, Testnet, Mainnet) and category
- **Track Progress**: View progress bars for each category
- **Update Tasks**: Click checkboxes to mark tasks as complete or incomplete
- **Filter Content**: Click on the tabs to switch between tranches

## File Descriptions

### config.php

This file contains the database connection settings. Make sure to update with your actual credentials.

### handle_tasks.php

This file handles all API interactions:
- GET requests return all tasks
- POST requests with action=update update task status
- POST requests with action=get_progress return progress statistics

### seed_db.php

This script creates the tasks table and populates it with the initial data. It includes:
- 80 tasks for Tranche 1 (MVP)
- 32 tasks for Tranche 2 (Testnet)
- 40 tasks for Tranche 3 (Mainnet)

### index.php

The main user interface file that displays the task tracker dashboard.

## Troubleshooting

1. **Database Connection Issues**
   - Verify your database credentials in config.php
   - Ensure MySQL service is running
   - Check that your PHP has MySQLi enabled

2. **Task Updates Not Saving**
   - Verify the connection between frontend and handle_tasks.php
   - Check PHP error logs for any issues
   - Ensure the tasks table has correct permissions

3. **Display Issues**
   - Clear your browser cache
   - Make sure JavaScript is enabled
   - Try a different browser to isolate the issue

## Customization

You can customize the system by:

1. Adding more tasks to the seed_db.php file
2. Modifying the CSS in index.php to match your branding
3. Extending functionality by adding user authentication

## Security Considerations

Before using in production:
1. Add user authentication
2. Validate all inputs
3. Add CSRF protection
4. Use prepared statements (already implemented)
5. Implement proper error handling

## Maintenance

- Regularly backup your database
- Update tasks as your project evolves
- Consider adding more features like task editing or task dependencies
