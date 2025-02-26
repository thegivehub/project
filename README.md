# Deliverable Tracker System

This task management system allows you to track progress for the three tranches of deliverables for the GiveHub project.

## Setup Instructions

1. Create a MySQL database named `givehub`
2. Update the `config.php` file with your database credentials
3. Run the `seed_db.php` script to populate your database
4. Access the application through your web server

## File Structure

- `config.php` - Database connection settings
- `handle_tasks.php` - API endpoint to handle task operations
- `seed_db.php` - Script to populate the database with initial task data
- `index.html` - Main interface for viewing and managing tasks

## Database Schema

The system uses a `tasks` table with the following structure:

```sql
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  `subcategory` varchar(255) DEFAULT NULL,
  `task_name` varchar(255) DEFAULT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tranche` varchar(25) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
)
```

## Features

1. View tasks organized by tranches (MVP, Testnet, Mainnet)
2. Filter tasks by category
3. Mark tasks as complete/incomplete
4. Track progress with visual indicators
5. View summary statistics for each category

## API Endpoints

The `handle_tasks.php` file provides the following functionality:

### GET /handle_tasks.php
Returns a list of all tasks in the database.

### POST /handle_tasks.php
Accepts the following actions:

- `update` - Update the completion status of a task
  - Parameters:
    - `task_id` - ID of the task to update
    - `completed` - New completion status (0 or 1)

- `get_progress` - Get summary progress statistics
  - Returns an array of category data with completion counts

## Implementation Notes

- The system uses vanilla JavaScript without external dependencies
- Task updates are performed asynchronously with fetch API
- Data is refreshed without requiring page reloads
- The interface is responsive and works on mobile devices

## Security Notes

Before deploying to production, ensure you:

1. Validate all inputs to prevent SQL injection
2. Implement proper authentication for the task update functionality
3. Add CSRF protection for form submissions
4. Consider adding rate limiting to prevent abuse

## Customization

You can customize the system by:

1. Modifying the CSS in the HTML file
2. Adding new tranches in the database and updating the UI
3. Extending the API with additional functionality
4. Adding user authentication and role-based permissions
