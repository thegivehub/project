// standardize-user-data.js
// MongoDB migration script to standardize user data structure
// Run with: mongo givehub standardize-user-data.js

print("Starting user data standardization...");

// Count total users
const totalUsers = db.users.count();
print(`Total users in database: ${totalUsers}`);

// Check for duplicate emails
print("\nChecking for duplicate emails...");
const emailCounts = {};
const duplicateEmails = [];

db.users.find().forEach(user => {
  const email = user.email || 
                (user.personalInfo && user.personalInfo.email) || 
                (user.profile && user.profile.email);
  
  if (email) {
    if (!emailCounts[email]) {
      emailCounts[email] = 1;
    } else {
      emailCounts[email]++;
      if (emailCounts[email] === 2) { // Only add to the array once when count reaches 2
        duplicateEmails.push(email);
      }
    }
  }
});

if (duplicateEmails.length > 0) {
  print(`Found ${duplicateEmails.length} duplicate emails.`);
  print("Duplicate emails:");
  duplicateEmails.forEach(email => {
    const count = emailCounts[email];
    print(`- ${email} (${count} occurrences)`);
    
    // Show the duplicate users
    print("  Users with this email:");
    db.users.find({ $or: [
      { email: email },
      { "personalInfo.email": email },
      { "profile.email": email }
    ]}).forEach(user => {
      print(`  - ${user._id} (${user.username || "no username"})`);
    });
  });
  
  print("\nPlease handle duplicate emails before proceeding with standardization.");
  print("Options:");
  print("1. Run the script with the --force flag to ignore duplicates (will keep the existing email in personalInfo)");
  print("2. Manually fix the duplicate emails in the database first\n");
  
  // Check if running with --force flag
  const args = (typeof process !== 'undefined' && process.argv) ? process.argv.slice(2) : [];
  if (args.indexOf("--force") === -1) {
    print("Migration aborted. Run with --force to continue anyway.");
    quit();
  } else {
    print("Running with --force flag. Proceeding with standardization...");
  }
}

// Track counts for reporting
let updatedUsers = 0;
let alreadyStandardized = 0;
let missingData = 0;
let duplicateEmailsHandled = 0;

// Process all users
db.users.find().forEach(user => {
  let needsUpdate = false;
  let personalInfo = user.personalInfo || {};
  
  // If user doesn't have personalInfo object
  if (!user.personalInfo) {
    needsUpdate = true;
    print(`User ${user._id} missing personalInfo object`);
    
    // Check if profile object exists
    if (user.profile) {
      // Extract data from profile
      if (user.profile.firstName !== undefined) {
        personalInfo.firstName = user.profile.firstName;
      }
      if (user.profile.lastName !== undefined) {
        personalInfo.lastName = user.profile.lastName;
      }
    }
    
    // Check for top-level firstName/lastName
    if (user.firstName !== undefined) {
      personalInfo.firstName = user.firstName;
    }
    if (user.lastName !== undefined) {
      personalInfo.lastName = user.lastName;
    }
    
    // Check displayName and try to parse
    if (user.displayName && (!personalInfo.firstName || !personalInfo.lastName)) {
      const nameParts = user.displayName.trim().split(/\s+/);
      if (nameParts.length >= 2) {
        // Assume first and last name
        if (!personalInfo.firstName) {
          personalInfo.firstName = nameParts[0];
        }
        if (!personalInfo.lastName) {
          personalInfo.lastName = nameParts.slice(1).join(' ');
        }
      } else if (nameParts.length === 1 && !personalInfo.firstName) {
        // Just set as firstName if no other name info
        personalInfo.firstName = nameParts[0];
        personalInfo.lastName = personalInfo.lastName || '';
      }
    }
    
    // Check for top-level email
    if (user.email !== undefined) {
      personalInfo.email = user.email;
    }
  } else {
    // personalInfo exists, but ensure it has all required fields
    if (!user.personalInfo.firstName || !user.personalInfo.lastName || !user.personalInfo.email) {
      needsUpdate = true;
      
      // Copy existing values
      personalInfo = { ...user.personalInfo };
      
      // Check if any missing fields exist in profile or top-level
      if (!personalInfo.firstName) {
        if (user.profile && user.profile.firstName) {
          personalInfo.firstName = user.profile.firstName;
        } else if (user.firstName) {
          personalInfo.firstName = user.firstName;
        } else if (user.displayName) {
          const nameParts = user.displayName.trim().split(/\s+/);
          if (nameParts.length >= 1) {
            personalInfo.firstName = nameParts[0];
          }
        }
      }
      
      if (!personalInfo.lastName) {
        if (user.profile && user.profile.lastName) {
          personalInfo.lastName = user.profile.lastName;
        } else if (user.lastName) {
          personalInfo.lastName = user.lastName;
        } else if (user.displayName) {
          const nameParts = user.displayName.trim().split(/\s+/);
          if (nameParts.length >= 2) {
            personalInfo.lastName = nameParts.slice(1).join(' ');
          }
        }
      }
      
      if (!personalInfo.email && user.email) {
        personalInfo.email = user.email;
      }
    }
  }
  
  // Check for duplicate email issues
  const userEmail = personalInfo.email || user.email || '';
  let isDuplicateEmail = duplicateEmails.includes(userEmail);
  
  // If we're dealing with a duplicate email
  if (isDuplicateEmail) {
    // If user already has personalInfo.email, keep it
    if (user.personalInfo && user.personalInfo.email) {
      personalInfo.email = user.personalInfo.email;
    } 
    // Otherwise use existing top-level email if available
    else if (user.email) {
      personalInfo.email = user.email;
    }
    // If we still don't have an email, create a unique one
    if (!personalInfo.email) {
      personalInfo.email = userEmail || `user-${user._id}@placeholder.com`;
    }
    duplicateEmailsHandled++;
  }
  
  // Finally, check if we have the minimum required fields
  if ((!personalInfo.firstName || !personalInfo.lastName || !personalInfo.email) && needsUpdate) {
    print(`Warning: User ${user._id} is missing required data after standardization`);
    // Set placeholders for required fields to ensure consistency
    personalInfo.firstName = personalInfo.firstName || '';
    personalInfo.lastName = personalInfo.lastName || '';
    personalInfo.email = personalInfo.email || (user.email || `user-${user._id}@placeholder.com`);
    missingData++;
  }
  
  // Update user if changes are needed
  if (needsUpdate) {
    try {
      db.users.updateOne(
        { _id: user._id },
        { $set: { personalInfo: personalInfo } }
      );
      updatedUsers++;
      print(`Updated user ${user._id}`);
    } catch (err) {
      print(`Error updating user ${user._id}: ${err.message}`);
      
      // Handle duplicate key errors
      if (err.message.includes("duplicate key")) {
        print(`Duplicate key error for user ${user._id}. Adding unique suffix to email.`);
        // Add a unique identifier to the email
        const timestamp = new Date().getTime();
        personalInfo.email = personalInfo.email.replace('@', `-${timestamp}@`);
        
        // Try again with the modified email
        try {
          db.users.updateOne(
            { _id: user._id },
            { $set: { personalInfo: personalInfo } }
          );
          updatedUsers++;
          print(`Updated user ${user._id} with modified email`);
        } catch (retryErr) {
          print(`Failed to update user ${user._id} after retry: ${retryErr.message}`);
        }
      }
    }
  } else {
    alreadyStandardized++;
  }
});

// Print summary
print("\nMigration complete!");
print(`Total users processed: ${totalUsers}`);
print(`Users already standardized: ${alreadyStandardized}`);
print(`Users updated: ${updatedUsers}`);
print(`Users with missing data: ${missingData}`);
print(`Duplicate emails handled: ${duplicateEmailsHandled}`);

// If we handled duplicate emails, suggest next steps
if (duplicateEmailsHandled > 0) {
  print("\nSome users had duplicate emails which were handled automatically.");
  print("You may want to:");
  print("1. Review these users to confirm which should be the primary account");
  print("2. Merge or deactivate duplicate accounts as appropriate");
  print("3. Notify affected users if they need to update their email");
}