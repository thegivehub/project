# User Data Standardization

This folder contains MongoDB scripts to standardize user data in the GiveHub database.

## The Problem

User data in the GiveHub database has inconsistent structure:
- Some users have personal information in a `personalInfo` object (correct)
- Some users have it in a `profile` object
- Some users have it at the top level (e.g., as `displayName`, `email`, etc.)

## The Solution

These scripts standardize all user records to have:
- A `personalInfo` object containing at minimum:
  - `firstName`
  - `lastName`
  - `email`

## Scripts

### 1. Backup Users Collection
```bash
mongo givehub backup-users.js
```
This creates a timestamped backup collection of the users collection before any changes.

### 2. Standardize User Data
```bash
mongo givehub standardize-user-data.js
```

Or, to force run even if duplicate emails are found:
```bash
mongo givehub standardize-user-data.js --force
```

This script:
- Finds users with inconsistent data structure
- Creates a `personalInfo` object for users who don't have one
- Moves data from `profile` or top-level fields to `personalInfo`
- Ensures `firstName`, `lastName`, and `email` are consistently stored in `personalInfo`
- Handles duplicate email addresses by adding a timestamp to make them unique

### 3. Verify Migration
```bash
mongo givehub verify-migration.js
```
This script verifies that all users now have a properly structured `personalInfo` object.

## Execution Order

1. First, back up the users collection
2. Run the standardization script
3. Verify the migration was successful

## Handling Duplicate Emails

The script takes two approaches to handling duplicate emails:

1. **Detection and Reporting**: When first run, the script will check for duplicate emails and report them
2. **Resolution with --force Flag**: When run with `--force`, for users with duplicate emails, the script will:
   - Keep the existing email if it's already in `personalInfo`
   - Otherwise, add a timestamp to the email to make it unique (e.g., `user@example.com` becomes `user-1714867401234@example.com`)

After running the migration, you should:
1. Review users with modified emails (the verification script will identify them)
2. Determine which accounts should be primary vs. duplicates
3. Consider merging or deactivating duplicate accounts
4. Notify affected users if their email addresses were modified

## Restoration (if needed)

If you need to restore from backup:

```javascript
// In MongoDB shell
// Replace with your actual backup collection name from step 1
const backupCollection = "users_backup_2024-05-04T12-34-56-789Z";

// Drop the current users collection
db.users.drop();

// Copy from backup to users
db[backupCollection].find().forEach(function(doc) {
  db.users.insert(doc);
});
```