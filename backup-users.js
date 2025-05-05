// backup-users.js
// MongoDB script to backup the users collection before making changes
// Run with: mongo givehub backup-users.js

print("Starting users collection backup...");

// Get current timestamp for backup collection name
const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const backupCollectionName = `users_backup_${timestamp}`;

// Create a backup collection
db.users.aggregate([{ $match: {} }]).forEach(function(doc) {
  db[backupCollectionName].insert(doc);
});

const originalCount = db.users.count();
const backupCount = db[backupCollectionName].count();

print(`Original collection: ${originalCount} documents`);
print(`Backup collection: ${backupCount} documents`);

if (originalCount === backupCount) {
  print(`Backup successful! Backup collection name: ${backupCollectionName}`);
} else {
  print("WARNING: Backup count doesn't match original count. Check data before proceeding with migration.");
}

// Create an index on the _id field to match the original collection
db[backupCollectionName].createIndex({ _id: 1 });

print("Backup complete.");