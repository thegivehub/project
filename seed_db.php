<?php
// seed_db.php - Updated to include assignee field
require_once("config.php");

// Check if table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'tasks'");
if ($checkTable->num_rows > 0) {
    // Check if assignee_id column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM tasks LIKE 'assignee_id'");
    if ($checkColumn->num_rows === 0) {
        // Add the assignee_id column if it doesn't exist
        $alterTable = "ALTER TABLE `tasks` ADD COLUMN `assignee_id` int(11) DEFAULT NULL AFTER `completed`";
        if ($conn->query($alterTable) === TRUE) {
            echo "Table 'tasks' modified successfully, added assignee_id column.\n";
        } else {
            echo "Error modifying table: " . $conn->error . "\n";
            exit;
        }
    } else {
        echo "Table 'tasks' already exists with assignee_id column. Skipping seed process.\n";
        exit;
    }
} else {
    // Create table with assignee_id column
    $createTable = "CREATE TABLE `tasks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `category` varchar(255) DEFAULT NULL,
      `subcategory` varchar(255) DEFAULT NULL,
      `task_name` varchar(255) DEFAULT NULL,
      `completed` tinyint(1) DEFAULT 0,
      `assignee_id` int(11) DEFAULT NULL,
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      `tranche` varchar(25) NOT NULL DEFAULT '1',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($createTable) === TRUE) {
        echo "Table 'tasks' created successfully with assignee_id column.\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
        exit;
    }
}

// Define tasks for all tranches with assignee_id (1 for Chris, 2 for Kevin)
// Tranche 1 - MVP
$tranche1Tasks = [
    // Frontend Engineering - Setup & Configuration (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Setup & Configuration', 'Initialize project structure with global app object', 1, 1, '1'],
    ['Frontend Engineering', 'Setup & Configuration', 'Set up build process and development environment', 1, 1, '1'],
    ['Frontend Engineering', 'Setup & Configuration', 'Configure JWT handling utilities', 1, 1, '1'],
    ['Frontend Engineering', 'Setup & Configuration', 'Implement local storage management', 1, 1, '1'],
    
    // Frontend Engineering - Registration Flow (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Registration Flow', 'Create registration form with validation', 1, 1, '1'],
    ['Frontend Engineering', 'Registration Flow', 'Implement real-time field validation', 1, 1, '1'],
    ['Frontend Engineering', 'Registration Flow', 'Add Google OAuth integration', 1, 1, '1'],
    ['Frontend Engineering', 'Registration Flow', 'Build email verification UI', 1, 1, '1'],
    ['Frontend Engineering', 'Registration Flow', 'Create success/error handling states', 1, 1, '1'],
    
    // Frontend Engineering - Login System (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Login System', 'Build login form with validation', 1, 1, '1'],
    ['Frontend Engineering', 'Login System', 'Implement session management', 1, 1, '1'],
    ['Frontend Engineering', 'Login System', 'Add "Remember Me" functionality', 1, 1, '1'],
    ['Frontend Engineering', 'Login System', 'Create password reset flow', 1, 1, '1'],
    ['Frontend Engineering', 'Login System', 'Add login error handling', 1, 1, '1'],
    
    // Frontend Engineering - Profile Management (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Profile Management', 'Create profile editor interface', 1, 1, '1'],
    ['Frontend Engineering', 'Profile Management', 'Implement avatar upload/cropping', 1, 1, '1'],
    ['Frontend Engineering', 'Profile Management', 'Add profile completion indicator', 0, 1, '1'],
    ['Frontend Engineering', 'Profile Management', 'Build contact information editor', 1, 1, '1'],
    ['Frontend Engineering', 'Profile Management', 'Create profile validation system', 0, 1, '1'],
    
    // Frontend Engineering - Basic Structure (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Basic Structure', 'Create campaign form layout', 1, 1, '1'],
    ['Frontend Engineering', 'Basic Structure', 'Implement form validation system', 1, 1, '1'],
    ['Frontend Engineering', 'Basic Structure', 'Add auto-save functionality', 0, 1, '1'],
    ['Frontend Engineering', 'Basic Structure', 'Build draft/preview toggle', 0, 1, '1'],
    
    // Frontend Engineering - Media Management (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Media Management', 'Create image upload interface', 1, 1, '1'],
    ['Frontend Engineering', 'Media Management', 'Implement upload progress indicators', 1, 1, '1'],
    ['Frontend Engineering', 'Media Management', 'Build media gallery management', 0, 1, '1'],
    ['Frontend Engineering', 'Media Management', 'Add drag-and-drop support', 1, 1, '1'],
    ['Frontend Engineering', 'Media Management', 'Implement image optimization', 1, 1, '1'],
    
    // Frontend Engineering - Campaign Preview (Assigned to Chris - ID 1)
    ['Frontend Engineering', 'Campaign Preview', 'Create preview mode toggle', 0, 1, '1'],
    ['Frontend Engineering', 'Campaign Preview', 'Build mobile/desktop preview', 0, 1, '1'],
    ['Frontend Engineering', 'Campaign Preview', 'Implement social share preview', 0, 1, '1'],
    ['Frontend Engineering', 'Campaign Preview', 'Add SEO preview functionality', 0, 1, '1'],
    
    // Backend Engineering - Basic Setup (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'Basic Setup', 'Initialize Express.js project', 1, 2, '1'],
    ['Backend Engineering', 'Basic Setup', 'Set up middleware architecture', 1, 2, '1'],
    ['Backend Engineering', 'Basic Setup', 'Configure error handling', 1, 2, '1'],
    ['Backend Engineering', 'Basic Setup', 'Implement logging system', 1, 2, '1'],
    
    // Backend Engineering - API Endpoints (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'API Endpoints', 'Create user management endpoints', 1, 2, '1'],
    ['Backend Engineering', 'API Endpoints', 'Build campaign management routes', 1, 2, '1'],
    ['Backend Engineering', 'API Endpoints', 'Implement donation processing', 0, 2, '1'],
    ['Backend Engineering', 'API Endpoints', 'Add media handling endpoints', 1, 2, '1'],
    
    // Backend Engineering - Data Validation (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'Data Validation', 'Implement input sanitization', 1, 2, '1'],
    ['Backend Engineering', 'Data Validation', 'Create schema validation', 1, 2, '1'],
    ['Backend Engineering', 'Data Validation', 'Add request/response logging', 1, 2, '1'],
    ['Backend Engineering', 'Data Validation', 'Build error handling system', 1, 2, '1'],
    
    // Backend Engineering - Collection Setup (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'Collection Setup', 'Design and implement user schema', 1, 2, '1'],
    ['Backend Engineering', 'Collection Setup', 'Create campaign schema', 1, 2, '1'],
    ['Backend Engineering', 'Collection Setup', 'Build transaction schema', 1, 2, '1'],
    ['Backend Engineering', 'Collection Setup', 'Add milestone schema', 1, 2, '1'],
    
    // Backend Engineering - Optimization (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'Optimization', 'Configure database indexes', 1, 2, '1'],
    ['Backend Engineering', 'Optimization', 'Implement query optimization', 1, 2, '1'],
    ['Backend Engineering', 'Optimization', 'Set up data migration system', 1, 2, '1'],
    ['Backend Engineering', 'Optimization', 'Add data validation rules', 1, 2, '1'],
    
    // Backend Engineering - Basic Verification (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'Basic Verification', 'Set up document upload system', 0, 2, '1'],
    ['Backend Engineering', 'Basic Verification', 'Implement face verification', 0, 2, '1'],
    ['Backend Engineering', 'Basic Verification', 'Add address validation', 0, 2, '1'],
    ['Backend Engineering', 'Basic Verification', 'Create verification tracking', 0, 2, '1'],
    
    // Backend Engineering - Jumio Integration (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'Jumio Integration', 'Configure Jumio API client', 0, 2, '1'],
    ['Backend Engineering', 'Jumio Integration', 'Implement webhook handling', 0, 2, '1'],
    ['Backend Engineering', 'Jumio Integration', 'Add result processing', 0, 2, '1'],
    ['Backend Engineering', 'Jumio Integration', 'Create retry mechanism', 0, 2, '1'],
    
    // Backend Engineering - JWT Implementation (Assigned to Kevin - ID 2)
    ['Backend Engineering', 'JWT Implementation', 'Set up token generation', 1, 2, '1'],
    ['Backend Engineering', 'JWT Implementation', 'Implement token validation', 1, 2, '1'],
    ['Backend Engineering', 'JWT Implementation', 'Create refresh token system', 1, 2, '1'],
    ['Backend Engineering', 'JWT Implementation', 'Add token revocation', 1, 2, '1'],
    
    // Blockchain Engineering - Wallet Setup (Assigned to Kevin - ID 2)
    ['Blockchain Engineering', 'Wallet Setup', 'Implement key pair generation', 1, 2, '1'],
    ['Blockchain Engineering', 'Wallet Setup', 'Add testnet account funding', 1, 2, '1'],
    ['Blockchain Engineering', 'Wallet Setup', 'Create balance management', 1, 2, '1'],
    ['Blockchain Engineering', 'Wallet Setup', 'Build error handling', 1, 2, '1'],
    
    // Blockchain Engineering - Transaction Handling (Assigned to Kevin - ID 2)
    ['Blockchain Engineering', 'Transaction Handling', 'Implement transaction building', 0, 2, '1'],
    ['Blockchain Engineering', 'Transaction Handling', 'Add signature collection', 0, 2, '1'],
    ['Blockchain Engineering', 'Transaction Handling', 'Create status tracking', 0, 2, '1'],
    ['Blockchain Engineering', 'Transaction Handling', 'Implement fee management', 0, 2, '1'],
    
    // Testing & Documentation (Shared between both)
    ['Testing & Documentation', 'Testing Setup', 'Configure testing environment', 1, 1, '1'],
    ['Testing & Documentation', 'Testing Setup', 'Create unit test suite', 1, 2, '1'],
    ['Testing & Documentation', 'Testing Setup', 'Implement integration tests', 0, 2, '1'],
    ['Testing & Documentation', 'Testing Setup', 'Set up CI pipeline', 1, 1, '1'],
    ['Testing & Documentation', 'Documentation', 'Create API documentation', 1, 2, '1'],
    ['Testing & Documentation', 'Documentation', 'Write setup instructions', 1, 1, '1'],
    ['Testing & Documentation', 'Documentation', 'Document deployment process', 1, 2, '1'],
    ['Testing & Documentation', 'Documentation', 'Create user guides', 0, 1, '1']
];

// Tranche 2 tasks with assignees
$tranche2Tasks = [
    // Frontend Engineering tasks assigned to Chris (ID 1)
    ['Frontend Engineering', 'Digital Nomad Portal', 'Create verification interface', 0, 1, '2'],
    ['Frontend Engineering', 'Digital Nomad Portal', 'Implement document upload management', 0, 1, '2'],
    ['Frontend Engineering', 'Digital Nomad Portal', 'Build progress tracking system', 0, 1, '2'],
    ['Frontend Engineering', 'Digital Nomad Portal', 'Add real-time status updates', 0, 1, '2'],
    
    ['Frontend Engineering', 'Milestone Tracking', 'Design milestone creation interface', 0, 1, '2'],
    ['Frontend Engineering', 'Milestone Tracking', 'Implement budget allocation tools', 0, 1, '2'],
    ['Frontend Engineering', 'Milestone Tracking', 'Create timeline visualization', 0, 1, '2'],
    ['Frontend Engineering', 'Milestone Tracking', 'Add progress tracking features', 0, 1, '2'],
    
    ['Frontend Engineering', 'Impact Metrics', 'Build metrics visualization components', 0, 1, '2'],
    ['Frontend Engineering', 'Impact Metrics', 'Create reporting interface', 0, 1, '2'],
    ['Frontend Engineering', 'Impact Metrics', 'Implement data analysis tools', 0, 1, '2'],
    ['Frontend Engineering', 'Impact Metrics', 'Add trend analysis features', 0, 1, '2'],
    
    // Backend Engineering tasks assigned to Kevin (ID 2)
    ['Backend Engineering', 'Verification System', 'Implement multi-step verification process', 0, 2, '2'],
    ['Backend Engineering', 'Verification System', 'Create document processing pipeline', 0, 2, '2'],
    ['Backend Engineering', 'Verification System', 'Build advanced notification system', 0, 2, '2'],
    ['Backend Engineering', 'Verification System', 'Add approval workflows', 0, 2, '2'],
    
    ['Backend Engineering', 'KYC/AML Processing', 'Enhance identity verification', 0, 2, '2'],
    ['Backend Engineering', 'KYC/AML Processing', 'Implement transaction monitoring', 0, 2, '2'],
    ['Backend Engineering', 'KYC/AML Processing', 'Create compliance reporting', 0, 2, '2'],
    ['Backend Engineering', 'KYC/AML Processing', 'Add risk scoring system', 0, 2, '2'],
    
    ['Backend Engineering', 'Impact Analytics', 'Build metrics processing engine', 0, 2, '2'],
    ['Backend Engineering', 'Impact Analytics', 'Implement data integration services', 0, 2, '2'],
    ['Backend Engineering', 'Impact Analytics', 'Create reporting system', 0, 2, '2'],
    ['Backend Engineering', 'Impact Analytics', 'Add custom calculations', 0, 2, '2'],
    
    // Blockchain Engineering tasks assigned to Kevin (ID 2)
    ['Blockchain Engineering', 'Smart Contracts', 'Develop campaign contract', 0, 2, '2'],
    ['Blockchain Engineering', 'Smart Contracts', 'Create milestone contract', 0, 2, '2'],
    ['Blockchain Engineering', 'Smart Contracts', 'Build verification contract', 0, 2, '2'],
    ['Blockchain Engineering', 'Smart Contracts', 'Implement multi-signature support', 0, 2, '2'],
    
    ['Blockchain Engineering', 'Testing & Security', 'Create comprehensive test suite', 0, 2, '2'],
    ['Blockchain Engineering', 'Testing & Security', 'Implement security controls', 0, 2, '2'],
    ['Blockchain Engineering', 'Testing & Security', 'Add contract documentation', 0, 2, '2'],
    ['Blockchain Engineering', 'Testing & Security', 'Optimize gas usage', 0, 2, '2']
];

// Tranche 3 tasks with assignees
$tranche3Tasks = [
    // Frontend Engineering tasks assigned to Chris (ID 1)
    ['Frontend Engineering', 'Mobile Optimization', 'Enhance responsive design', 0, 1, '3'],
    ['Frontend Engineering', 'Mobile Optimization', 'Implement progressive web app features', 0, 1, '3'],
    ['Frontend Engineering', 'Mobile Optimization', 'Add offline functionality', 0, 1, '3'],
    ['Frontend Engineering', 'Mobile Optimization', 'Create mobile payment flow', 0, 1, '3'],
    
    ['Frontend Engineering', 'Multi-language Support', 'Build translation system', 0, 1, '3'],
    ['Frontend Engineering', 'Multi-language Support', 'Implement currency formatting', 0, 1, '3'],
    ['Frontend Engineering', 'Multi-language Support', 'Add content management for translations', 0, 1, '3'],
    ['Frontend Engineering', 'Multi-language Support', 'Create RTL support', 0, 1, '3'],
    
    ['Frontend Engineering', 'Payment Flow', 'Optimize donation interface', 0, 1, '3'],
    ['Frontend Engineering', 'Payment Flow', 'Create recurring payment setup', 0, 1, '3'],
    ['Frontend Engineering', 'Payment Flow', 'Implement transaction tracking', 0, 1, '3'],
    ['Frontend Engineering', 'Payment Flow', 'Add analytics integration', 0, 1, '3'],
    
    // Backend Engineering tasks assigned to Kevin (ID 2)
    ['Backend Engineering', 'Performance', 'Optimize database queries', 0, 2, '3'],
    ['Backend Engineering', 'Performance', 'Implement caching system', 0, 2, '3'],
    ['Backend Engineering', 'Performance', 'Add load testing', 0, 2, '3'],
    ['Backend Engineering', 'Performance', 'Create performance monitoring', 0, 2, '3'],
    
    ['Backend Engineering', 'Security', 'Implement security hardening', 0, 2, '3'],
    ['Backend Engineering', 'Security', 'Enhance access control system', 0, 2, '3'],
    ['Backend Engineering', 'Security', 'Add protection systems', 0, 2, '3'],
    ['Backend Engineering', 'Security', 'Create security monitoring', 0, 2, '3'],
    
    ['Backend Engineering', 'Documentation', 'Create comprehensive API docs', 0, 2, '3'],
    ['Backend Engineering', 'Documentation', 'Document system architecture', 0, 2, '3'],
    ['Backend Engineering', 'Documentation', 'Build developer resources', 0, 2, '3'],
    ['Backend Engineering', 'Documentation', 'Add integration guides', 0, 2, '3'],
    
    // Blockchain Engineering & DevOps tasks assigned to Kevin (ID 2)
    ['Blockchain Engineering', 'Mainnet', 'Perform contract migration', 0, 2, '3'],
    ['Blockchain Engineering', 'Mainnet', 'Implement security verification', 0, 2, '3'],
    ['Blockchain Engineering', 'Mainnet', 'Create production integration', 0, 2, '3'],
    ['Blockchain Engineering', 'Mainnet', 'Add monitoring system', 0, 2, '3'],
    
    ['DevOps', 'Infrastructure', 'Configure server environment', 0, 2, '3'],
    ['DevOps', 'Infrastructure', 'Implement monitoring systems', 0, 2, '3'],
    ['DevOps', 'Infrastructure', 'Create backup systems', 0, 2, '3'],
    ['DevOps', 'Infrastructure', 'Set up auto-scaling', 0, 2, '3'],
    
    ['DevOps', 'CI/CD', 'Implement deployment automation', 0, 2, '3'],
    ['DevOps', 'CI/CD', 'Create environment management', 0, 2, '3'],
    ['DevOps', 'CI/CD', 'Add testing automation', 0, 2, '3'],
    ['DevOps', 'CI/CD', 'Implement rollback procedures', 0, 2, '3'],
    
    // Quality Assurance tasks (shared)
    ['Quality Assurance', 'Testing', 'Perform system testing', 0, 1, '3'],
    ['Quality Assurance', 'Testing', 'Run cross-browser testing', 0, 1, '3'],
    ['Quality Assurance', 'Testing', 'Execute performance validation', 0, 2, '3'],
    ['Quality Assurance', 'Testing', 'Create test reports', 0, 1, '3']
];

// Prepare SQL statement for insertion
$stmt = $conn->prepare("INSERT INTO tasks (category, subcategory, task_name, completed, assignee_id, tranche) VALUES (?, ?, ?, ?, ?, ?)");

// Insert all task data
$allTasks = array_merge($tranche1Tasks, $tranche2Tasks, $tranche3Tasks);

$insertCount = 0;
foreach ($allTasks as $task) {
    $stmt->bind_param("sssiii", $task[0], $task[1], $task[2], $task[3], $task[4], $task[5]);
    if ($stmt->execute()) {
        $insertCount++;
    } else {
        echo "Error inserting task: " . $stmt->error . "\n";
    }
}

echo "Successfully inserted $insertCount tasks.\n";
$conn->close();
?>
