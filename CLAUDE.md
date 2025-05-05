# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build and Run Commands
- Setup database: `mysql -u [username] -p < init.sql`
- Seed database: `php seed_db.php`
- Run locally: Use PHP's built-in server `php -S localhost:8000`

## Code Style Guidelines
- PHP: 4-space indentation, PSR-12 compliant where possible
- JavaScript: 4-space indentation, semicolons required
- HTML/CSS: 4-space indentation, consistent class naming
- SQL: Uppercase SQL keywords, lowercase table/column names

## Naming Conventions
- PHP functions: camelCase
- Database columns: snake_case
- CSS classes: kebab-case
- JS variables: camelCase

## Error Handling
- Use try/catch blocks for PHP database operations
- Validate all user inputs before processing
- Return JSON responses with success/error status for API endpoints

## Security Notes
- Never commit config.php with credentials
- Always use prepared statements for database queries
- Sanitize HTML output with htmlspecialchars()
- Follow CSRF protection best practices