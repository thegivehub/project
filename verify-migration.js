// verify-migration.js
// MongoDB script to verify the user standardization was successful
// Run with: mongo givehub verify-migration.js

print("Verifying user data standardization...");

// Count total users
const totalUsers = db.users.count();
print(`Total users in database: ${totalUsers}`);

// Count users with personalInfo object
const usersWithPersonalInfo = db.users.count({ 'personalInfo': { $exists: true } });
print(`Users with personalInfo object: ${usersWithPersonalInfo}`);

// Count users with firstName, lastName, and email in personalInfo
const usersWithCompleteInfo = db.users.count({
  'personalInfo.firstName': { $exists: true },
  'personalInfo.lastName': { $exists: true },
  'personalInfo.email': { $exists: true }
});
print(`Users with complete personalInfo: ${usersWithCompleteInfo}`);

// Find users still missing personalInfo
const missingPersonalInfo = db.users.count({ 'personalInfo': { $exists: false } });
if (missingPersonalInfo > 0) {
  print(`WARNING: ${missingPersonalInfo} users still missing personalInfo object`);
  print("Sample of users missing personalInfo:");
  db.users.find({ 'personalInfo': { $exists: false } }).limit(3).forEach(user => {
    print(JSON.stringify(user._id));
  });
} else {
  print("All users have personalInfo object!");
}

// Find users with incomplete personalInfo
const incompletePersonalInfo = db.users.count({
  'personalInfo': { $exists: true },
  $or: [
    { 'personalInfo.firstName': { $exists: false } },
    { 'personalInfo.lastName': { $exists: false } },
    { 'personalInfo.email': { $exists: false } }
  ]
});

if (incompletePersonalInfo > 0) {
  print(`WARNING: ${incompletePersonalInfo} users have incomplete personalInfo`);
  print("Sample of users with incomplete personalInfo:");
  db.users.find({
    'personalInfo': { $exists: true },
    $or: [
      { 'personalInfo.firstName': { $exists: false } },
      { 'personalInfo.lastName': { $exists: false } },
      { 'personalInfo.email': { $exists: false } }
    ]
  }).limit(3).forEach(user => {
    print(JSON.stringify({
      _id: user._id,
      firstName: user.personalInfo.firstName,
      lastName: user.personalInfo.lastName,
      email: user.personalInfo.email
    }));
  });
} else {
  print("All users have complete personalInfo!");
}

// Check for users with modified emails (those containing a timestamp)
const modifiedEmails = db.users.count({
  'personalInfo.email': { $regex: /\-\d+@/ }
});

if (modifiedEmails > 0) {
  print(`\nFound ${modifiedEmails} users with modified emails (due to duplicates)`);
  print("Sample of users with modified emails:");
  db.users.find({
    'personalInfo.email': { $regex: /\-\d+@/ }
  }).limit(5).forEach(user => {
    print(`- ${user._id}: ${user.personalInfo.email} (${user.username || "no username"})`);
  });
  
  print("\nYou should review these users to determine which accounts are duplicates");
  print("and which ones should be treated as primary accounts.");
}

print("\nVerification complete!");