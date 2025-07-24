<?php
// index.php - Updated with assignee functionality
require_once("config.php");
require_once("lib/Parsedown.php");
$Parsedown = new Parsedown();
$trancheStats = [];

// Load developers data
$developersJson = file_get_contents('developers.json');
$developers = json_decode($developersJson, true);
$developersMap = [];
foreach ($developers as $dev) {
    $developersMap[$dev['id']] = $dev;
}

// Get tranche stats
for ($i = 1; $i <= 3; $i++) {
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(completed) as completed
            FROM tasks 
            WHERE tranche = '$i'";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $trancheStats[$i] = [
            'total' => $row['total'],
            'completed' => $row['completed'],
            'percentage' => $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100) : 0
        ];
    }
}

// Get overall stats
$sql = "SELECT 
            COUNT(*) as total,
            SUM(completed) as completed
        FROM tasks";

$overallStats = ['total' => 0, 'completed' => 0, 'percentage' => 0];
$result = $conn->query($sql);
if ($result && $row = $result->fetch_assoc()) {
    $overallStats = [
        'total' => $row['total'],
        'completed' => $row['completed'],
        'percentage' => $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100) : 0
    ];
}

// Get assignee stats
$assigneeStats = [];
$sql = "SELECT 
        assignee_id, 
        COUNT(*) as total_tasks, 
        SUM(completed) as completed_tasks
    FROM tasks 
    WHERE assignee_id IS NOT NULL 
    GROUP BY assignee_id";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $assigneeId = $row['assignee_id'];
        $assigneeStats[$assigneeId] = [
            'total' => $row['total_tasks'],
            'completed' => $row['completed_tasks'],
            'percentage' => $row['total_tasks'] > 0 ? round(($row['completed_tasks'] / $row['total_tasks']) * 100) : 0
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiveHub Deliverable Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Light mode variables */
            --primary: #2563eb;
            --primary-light: #dbeafe;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --background: #f9fafb;
            --card-bg: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --text-muted: #6b7280;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --border-color: #e5e7eb;
        }

        .dark {
            /* Dark mode variables */
            --primary: #3b82f6;
            --primary-light: #1e3a8a;
            --success: #10b981;
            --success-light: #064e3b;
            --warning: #f59e0b;
            --warning-light: #78350f;
            --danger: #ef4444;
            --danger-light: #7f1d1d;
            --background: #111827;
            --card-bg: #1f2937;
            --text-primary: #f9fafb;
            --text-secondary: #e5e7eb;
            --text-muted: #9ca3af;
            --gray-100: #374151;
            --gray-200: #4b5563;
            --gray-300: #6b7280;
            --gray-600: #d1d5db;
            --gray-700: #e5e7eb;
            --gray-800: #f3f4f6;
            --border-color: #374151;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Lexend', sans-serif;
            background-color: var(--background);
            color: var(--text-primary);
            line-height: 1.5;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Theme toggle */
        .theme-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .theme-toggle svg {
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.05);
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-primary);
            text-align: center;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            color: var(--text-secondary);
        }

        .tab.active {
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .progress-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .progress-card h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .progress-bar {
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .category-section {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .category-header {
            padding: 1rem 1.5rem;
            background: var(--gray-100);
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-primary);
            transition: background-color 0.3s ease;
        }

        .category-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-progress-bar {
            height: 8px;
            width: 100px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }

        .task-list {
            padding: 0.5rem 0;
        }
        
        .subcategory-container {
            margin-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .subcategory-container:last-child {
            border-bottom: none;
        }
        
        .subcategory-header {
            padding: 0.75rem 1.5rem;
            background: var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .subcategory-title {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .subcategory-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .subcategory-tasks {
            background: var(--card-bg);
        }

        .task-item {
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: var(--card-bg);
        }

        .task-item:last-child {
            border-bottom: none;
        }

        .task-checkbox {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-300);
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            flex-shrink: 0;
            margin-top: 0.25rem;
            background-color: var(--card-bg);
        }

        .task-checkbox:checked {
            background: var(--primary);
            border-color: var(--primary);
        }

        .task-checkbox:checked::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 14px;
            font-weight: bold;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .task-info {
            flex: 1;
        }

        .task-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .task-subcategory {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .task-assignee {
            display: flex;
            align-items: center;
            margin-left: auto;
            position: relative;
        }
        
        .assignee-selector {
            padding: 0.35rem 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--card-bg);
            color: var(--text-primary);
            font-size: 0.875rem;
            cursor: pointer;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }
        
        .assignee-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .assignee-badge img {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 0.25rem;
        }

        .summary-card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .summary-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .summary-stat {
            padding: 1rem;
            background: var(--gray-100);
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--primary);
        }

        .summary-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        .dark .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--primary);
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .error {
            background: var(--danger-light);
            color: var(--danger);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .hidden {
            display: none;
        }

        .tranche-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .tranche-1 {
            background: var(--primary-light);
            color: var(--primary);
        }

        .tranche-2 {
            background: var(--warning-light);
            color: var(--warning);
        }

        .tranche-3 {
            background: var(--success-light);
            color: var(--success);
        }
        
        .assignee-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .assignee-card {
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .assignee-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .assignee-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--text-primary);
            margin-right: 1rem;
        }
        
        .assignee-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .assignee-info h3 {
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }
        
        .assignee-info p {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        /* Notes functionality styles */
        .task-notes-container {
            margin-top: 0.5rem;
            position: relative;
            display: flex;
            align-items: flex-start;
        }

        .task-notes {
            flex: 1;
            font-size: 0.875rem;
            color: var(--text-secondary);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            min-height: 1.5rem;
            margin-right: 0.5rem;
        }

        .task-notes:empty::before {
            content: "Add notes (supports Markdown)...";
            color: var(--text-muted);
            font-style: italic;
        }

        .task-notes[contenteditable="true"] {
            background-color: var(--gray-100);
            border: 1px solid var(--border-color);
            outline: none;
            padding: 0.25rem 0.5rem;
        }
        
        /* Markdown styles */
        .markdown-content {
            overflow-wrap: break-word;
        }
        
        .markdown-content h1, 
        .markdown-content h2, 
        .markdown-content h3, 
        .markdown-content h4, 
        .markdown-content h5, 
        .markdown-content h6 {
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
            line-height: 1.25;
        }
        
        .markdown-content h1 { font-size: 1.25rem; }
        .markdown-content h2 { font-size: 1.15rem; }
        .markdown-content h3 { font-size: 1.05rem; }
        .markdown-content h4, .markdown-content h5, .markdown-content h6 { font-size: 1rem; }
        
        .markdown-content p {
            margin-bottom: 0.5rem;
        }
        
        .markdown-content ul, .markdown-content ol {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
        }
        
        .markdown-content code {
            background-color: var(--gray-100);
            border-radius: 3px;
            padding: 0.2em 0.4em;
            font-size: 0.85em;
            font-family: monospace;
        }
        
        .markdown-content pre {
            background-color: var(--gray-100);
            border-radius: 3px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            overflow-x: auto;
        }
        
        .markdown-content pre code {
            background-color: transparent;
            padding: 0;
            font-size: 0.85em;
        }
        
        .markdown-content blockquote {
            border-left: 3px solid var(--gray-300);
            padding-left: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .markdown-content a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .markdown-content a:hover {
            text-decoration: underline;
        }
        
        .markdown-content img {
            max-width: 100%;
            height: auto;
        }
        
        .markdown-content table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .markdown-content th, .markdown-content td {
            border: 1px solid var(--border-color);
            padding: 0.25rem 0.5rem;
        }
        
        .markdown-content th {
            background-color: var(--gray-100);
        }

        .task-notes-edit {
            opacity: 0;
            transition: opacity 0.2s;
            cursor: pointer;
            color: var(--text-secondary);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }

        .task-item:hover .task-notes-edit {
            opacity: 1;
        }

        .task-notes-edit:hover {
            background-color: var(--gray-200);
            color: var(--primary);
        }

        .task-notes-edit.editing {
            opacity: 1;
            color: var(--primary);
        }
        /* Markdown tooltip */
        .markdown-help-tooltip {
            position: absolute;
            right: 30px;
            top: 0;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
            width: 18px;
            height: 18px;
            background-color: var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: var(--gray-600);
        }
        
        .task-notes-container:hover .markdown-help-tooltip {
            opacity: 0.7;
        }
        
        .markdown-help-tooltip:hover {
            opacity: 1 !important;
        }
        
        .markdown-help-popup {
            position: absolute;
            right: 30px;
            top: 25px;
            width: 260px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 12px;
            font-size: 0.8rem;
            z-index: 1000;
            display: none;
        }
        
        .markdown-help-popup.visible {
            display: block;
        }
        
        .markdown-help-popup h4 {
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .markdown-help-popup code {
            background-color: var(--gray-100);
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .markdown-help-popup ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .markdown-help-popup li {
            margin-bottom: 4px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .progress-container {
                grid-template-columns: 1fr;
            }
            
            .category-header,
            .subcategory-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .subcategory-progress {
                width: 100%;
            }
            
            .tabs {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .task-item {
                flex-wrap: wrap;
            }
            
            .task-assignee {
                margin-left: 0;
                margin-top: 0.5rem;
                width: 100%;
            }
            
            .assignee-selector {
                width: 100%;
            }

            .theme-toggle {
                top: 0.5rem;
                right: 0.5rem;
            }
        }
/* Filter Controls Styles - Add to your CSS section */
.filter-controls {
    background: var(--card-bg);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.filter-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.filter-label {
    font-weight: 500;
    font-size: 0.9rem;
    color: var(--text-primary);
}

/* Toggle Switch */
.filter-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.filter-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--gray-300);
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: var(--primary);
}

input:focus + .slider {
    box-shadow: 0 0 1px var(--primary);
}

input:checked + .slider:before {
    transform: translateX(20px);
}

.slider.round {
    border-radius: 24px;
}

.slider.round:before {
    border-radius: 50%;
}

/* Assignee Filters */
.assignee-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.assignee-filter-btn {
    background: var(--gray-100);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.assignee-filter-btn:hover {
    background: var(--gray-200);
}

.assignee-filter-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Task visibility based on filters */
.task-item.completed-task.hide-completed {
    display: none;
}

.task-item.filtered-by-assignee {
    display: none;
}

/* Empty category message styling */
.empty-category-message {
    padding: 1.5rem;
    text-align: center;
    color: var(--text-muted);
    font-style: italic;
    border-top: 1px solid var(--border-color);
}

.empty-subcategory-message {
    padding: 1rem;
    text-align: center;
    color: var(--text-muted);
    font-style: italic;
    font-size: 0.9rem;
}

/* Add a light pulse animation to newly filtered items */
@keyframes filterPulse {
    0% { background-color: var(--primary-light); }
    100% { background-color: transparent; }
}

.task-item.filter-changed {
    animation: filterPulse 1s ease-out;
}

/* Tabs container with export buttons */
.tabs-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.tabs {
    display: flex;
    border-bottom: none;
    margin-bottom: 0;
}

.export-buttons {
    display: flex;
    gap: 0.5rem;
    padding: 0.5rem 0;
}

.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: var(--card-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.export-btn:hover {
    background-color: var(--gray-100);
    border-color: var(--gray-300);
}

.export-btn svg {
    width: 16px;
    height: 16px;
}

/* Print specific styles */
@media print {
    .theme-toggle,
    .filter-controls,
    .export-buttons,
    .task-notes-edit,
    .markdown-help-tooltip,
    .assignee-selector {
        display: none !important;
    }
    
    body {
        background-color: white;
        color: black;
    }
    
    .container {
        max-width: 100%;
        padding: 0;
    }
    
    .progress-card,
    .category-section,
    .summary-card,
    .assignee-card {
        box-shadow: none;
        border: 1px solid #ddd;
        break-inside: avoid;
    }
    
    .task-item {
        break-inside: avoid;
    }
}

/* Add these to your existing media queries */
@media (max-width: 768px) {
    .filter-controls {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .filter-section {
        width: 100%;
    }

    .assignee-filters {
        width: 100%;
    }
    
    .tabs-container {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .tabs {
        width: 100%;
    }
    
    .export-buttons {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
}
    </style>
</head>
<body>
    <div class="theme-toggle" id="themeToggle" title="Toggle dark mode">
        <!-- Sun icon for light mode -->
        <svg xmlns="http://www.w3.org/2000/svg" id="lightIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
        <!-- Moon icon for dark mode -->
        <svg xmlns="http://www.w3.org/2000/svg" id="darkIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </div>

    <div class="container">
        <h1>GiveHub Deliverable Tracker</h1>
        
        <div id="errorDisplay" class="error hidden"></div>
        <div id="loadingDisplay" class="loading">
            <div class="spinner"></div>
        </div>
        
        <div id="contentContainer">
            <div class="summary-card">
                <h2>Overall Progress</h2>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="summary-value"><?php echo $overallStats['total']; ?></div>
                        <div class="summary-label">Total Tasks</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-value"><?php echo $overallStats['completed']; ?></div>
                        <div class="summary-label">Completed</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-value"><?php echo $overallStats['percentage']; ?>%</div>
                        <div class="summary-label">Completion Rate</div>
                    </div>
                </div>
            </div>
            <div class="filter-controls">
                <div class="filter-section">
                    <label class="filter-switch">
                        <input type="checkbox" id="hideCompleted">
                        <span class="slider round"></span>
                    </label>
                    <span class="filter-label">Hide Completed Tasks</span>
                </div>
                
                <div class="filter-section">
                    <div class="filter-label">Filter by Assignee:</div>
                    <div class="assignee-filters" id="assigneeFilters">
                        <button class="assignee-filter-btn active" data-assignee="all">All</button>
                        <!-- Developer filter buttons will be added dynamically -->
                    </div>
                </div>
            </div>
            <div class="assignee-stats" id="assigneeStats">
                <?php foreach ($developers as $developer): ?>
                    <?php 
                    $devId = $developer['id'];
                    $devStats = isset($assigneeStats[$devId]) ? $assigneeStats[$devId] : ['total' => 0, 'completed' => 0, 'percentage' => 0];
                    ?>
                    <div class="assignee-card">
                        <div class="assignee-header">
                            <div class="assignee-avatar">
                                <?php if (!empty($developer['avatar'])): ?>
                                    <img src="img/profilepics/<?php echo htmlspecialchars($developer['avatar']); ?>" alt="<?php echo htmlspecialchars($developer['name']); ?>">
                                <?php else: ?>
                                    <?php echo substr($developer['name'], 0, 2); ?>
                                <?php endif; ?>
                            </div>
                            <div class="assignee-info">
                                <h3><?php echo htmlspecialchars($developer['name']); ?></h3>
                                <p><?php echo htmlspecialchars($developer['role']); ?></p>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $devStats['percentage']; ?>%"></div>
                        </div>
                        <div class="progress-stats">
                            <div><?php echo $devStats['completed']; ?>/<?php echo $devStats['total']; ?> tasks</div>
                            <div><?php echo $devStats['percentage']; ?>%</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="tabs-container">
                <div class="tabs">
                    <div class="tab active" data-tranche="1">
                        Tranche 1 - MVP
                        <span class="tranche-badge tranche-1"><?php echo $trancheStats[1]['percentage']; ?>%</span>
                    </div>
                    <div class="tab" data-tranche="2">
                        Tranche 2 - Testnet
                        <span class="tranche-badge tranche-2"><?php echo $trancheStats[2]['percentage']; ?>%</span>
                    </div>
                    <div class="tab" data-tranche="3">
                        Tranche 3 - Mainnet
                        <span class="tranche-badge tranche-3"><?php echo $trancheStats[3]['percentage']; ?>%</span>
                    </div>
                </div>
                <div class="export-buttons">
                    <button onclick="exportTranche('markdown')" class="export-btn" title="Open Markdown version in new window">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Markdown
                    </button>
                    <button onclick="exportTranche('print')" class="export-btn" title="Open printable version in new window">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                        Print View
                    </button>
                </div>
            </div>
            
            <div class="progress-container" id="progressContainer">
                <!-- Progress cards will be inserted here -->
            </div>
            
            <div class="tab-content active" data-tranche="1" id="taskList1">
                <!-- Tasks for Tranche 1 will be inserted here -->
            </div>
            
            <div class="tab-content" data-tranche="2" id="taskList2">
                <!-- Tasks for Tranche 2 will be inserted here -->
            </div>
            
            <div class="tab-content" data-tranche="3" id="taskList3">
                <!-- Tasks for Tranche 3 will be inserted here -->
            </div>
        </div>
    </div>

<script>
    // Export tranche to markdown or print view
    function exportTranche(type) {
        var activeTab = document.querySelector('.tab.active');
        var trancheId = activeTab ? activeTab.getAttribute('data-tranche') : '1';
        window.open('export.php?type=' + type + '&tranche=' + trancheId, '_blank');
    }

    // Markdown to HTML conversion function
    function markdownToHTML(markdown) {
        if (!markdown) return '';
        return markdown;
    }
    
    // Theme toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('themeToggle');
        const lightIcon = document.getElementById('lightIcon');
        const darkIcon = document.getElementById('darkIcon');
        const htmlElement = document.documentElement;
        
        // Check for saved theme preference or respect OS preference
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            enableDarkMode();
        } else {
            enableLightMode();
        }
        
        // Toggle theme when button is clicked
        themeToggle.addEventListener('click', function() {
            if (htmlElement.classList.contains('dark')) {
                enableLightMode();
            } else {
                enableDarkMode();
            }
        });
        
        function enableDarkMode() {
            htmlElement.classList.add('dark');
            lightIcon.style.display = 'none';
            darkIcon.style.display = 'block';
            localStorage.setItem('theme', 'dark');
        }
        
        function enableLightMode() {
            htmlElement.classList.remove('dark');
            lightIcon.style.display = 'block';
            darkIcon.style.display = 'none';
            localStorage.setItem('theme', 'light');
        }
    });

    function setupNotesEditing() {
        // Find all edit icons
        const editIcons = document.querySelectorAll('.task-notes-edit');

        editIcons.forEach(editIcon => {
            editIcon.addEventListener('click', function(evt) {
                const notesContainer = this.closest('.task-notes-container');
                const notesElement = notesContainer.querySelector('.task-notes');
                evt.preventDefault();
                evt.stopPropagation();
                if (notesElement.getAttribute('contenteditable') === 'true') {
                    // Already in edit mode, so save
                    saveNotes(notesElement);
                } else {
                    // Enter edit mode
                    startEditing(notesElement, this);
                }
                return false;
            });
        });
        
        // Setup Markdown help tooltips
        const helpTooltips = document.querySelectorAll('.markdown-help-tooltip');
        helpTooltips.forEach(tooltip => {
            tooltip.addEventListener('click', function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                
                // Toggle popup visibility
                const popup = this.nextElementSibling;
                popup.classList.toggle('visible');
                
                // Close when clicking outside
                const closePopup = function(e) {
                    if (!popup.contains(e.target) && e.target !== tooltip) {
                        popup.classList.remove('visible');
                        document.removeEventListener('click', closePopup);
                    }
                };
                
                document.addEventListener('click', closePopup);
                return false;
            });
        });
    }

    function startEditing(notesElement, editIcon) {
        // Get original markdown content if available, otherwise use current content
        const markdownContent = notesElement.getAttribute('data-markdown');
        const originalContent = markdownContent ? decodeURIComponent(markdownContent) : notesElement.innerText;
        
        // Store original HTML content for cancel
        notesElement.dataset.originalContent = notesElement.innerHTML;
        
        // Set the editable content to the markdown version
        notesElement.innerText = originalContent;

        // Make editable and focus
        notesElement.setAttribute('contenteditable', 'true');
        notesElement.focus();

        // Select all text if not empty
        if (notesElement.textContent.trim() !== '') {
            selectElementContents(notesElement);
        }

        // Change icon to save icon
        editIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>';
        editIcon.classList.add('editing');

        // Handle key events (Enter to save, Escape to cancel)
        notesElement.addEventListener('keydown', handleNotesKeydown);

        // Handle click outside to save
        document.addEventListener('click', handleClickOutside);
    }

    function saveNotes(notesElement) {
        // Remove editing state
        notesElement.removeAttribute('contenteditable');

        const taskId = notesElement.closest('.task-item').querySelector('.task-checkbox').dataset.id;
        const markdownContent = notesElement.innerText.trim();
        
        // Update notes in database
        updateTaskNotes(taskId, markdownContent);
        
        // Store markdown content in data attribute for future edits
        notesElement.setAttribute('data-markdown', encodeURIComponent(markdownContent));
        
        // Temporarily show loading state
        notesElement.innerHTML = '<em>Updating...</em>';
        
        // Refresh the page after saving to show rendered markdown
        // A better solution would be to use AJAX to fetch the rendered HTML, but this is simpler
        setTimeout(() => {
            window.location.reload();
        }, 300);

        // Reset edit icon
        const editIcon = notesElement.parentElement.querySelector('.task-notes-edit');
        editIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        editIcon.classList.remove('editing');

        // Remove event listeners
        notesElement.removeEventListener('keydown', handleNotesKeydown);
        document.removeEventListener('click', handleClickOutside);
    }

    function cancelEditing(notesElement) {
        // Restore original content
        notesElement.innerHTML = notesElement.dataset.originalContent;
        notesElement.removeAttribute('contenteditable');

        // Reset edit icon
        const editIcon = notesElement.parentElement.querySelector('.task-notes-edit');
        editIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        editIcon.classList.remove('editing');

        // Remove event listeners
        notesElement.removeEventListener('keydown', handleNotesKeydown);
        document.removeEventListener('click', handleClickOutside);
    }

    function handleNotesKeydown(event) {
        const notesElement = event.target;

        // Enter key saves (unless Shift is held)
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            saveNotes(notesElement);
        }

        // Escape key cancels
        if (event.key === 'Escape') {
            cancelEditing(notesElement);
        }
    }

    function handleClickOutside(event) {
        const activeNotesElements = document.querySelectorAll('.task-notes[contenteditable="true"]');

        activeNotesElements.forEach(notesElement => {
            // Check if the click was inside this notes element
            if (!notesElement.contains(event.target) &&
                !notesElement.parentElement.querySelector('.task-notes-edit').contains(event.target)) {
                saveNotes(notesElement);
            }
        });
    }

    function selectElementContents(element) {
        const range = document.createRange();
        range.selectNodeContents(element);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function updateTaskNotes(taskId, notes) {
        const formData = new FormData();
        formData.append('action', 'update_notes');
        formData.append('task_id', taskId);
        formData.append('notes', notes);

        fetch('handle_tasks.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showError('Failed to update task notes. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error updating task notes:', error);
            showError('Failed to update task notes. Please try again.');
        });
    }
        
    document.addEventListener('DOMContentLoaded', function() {
        const tasks = [];
        const categories = {};
        const progressContainer = document.getElementById('progressContainer');
        const contentContainer = document.getElementById('contentContainer');
        const loadingDisplay = document.getElementById('loadingDisplay');
        const errorDisplay = document.getElementById('errorDisplay');
        
        const developers = <?php echo json_encode($developers); ?>;
        window.developers = developers;

        // Tab switching
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tranche = tab.getAttribute('data-tranche');
                
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Update active content
                tabContents.forEach(content => {
                    if (content.getAttribute('data-tranche') === tranche) {
                        content.classList.add('active');
                    } else {
                        content.classList.remove('active');
                    }
                });
                
                // Update progress cards
                updateProgressCards(tranche);
            });
        });
        
        fetchTasks()
            .catch(error => {
                console.error('Error initializing app:', error);
                showError('Failed to initialize application. Please try again.');
            });

        function fetchTasks() {
            showLoading(true);
            
            return fetch('handle_tasks.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && Array.isArray(data)) {
                        // Process the tasks data
                        processTasks(data);
                        showLoading(false);
                    } else {
                        throw new Error('Invalid data format received from server');
                    }
                })
                .catch(error => {
                    console.error('Error fetching tasks:', error);
                    showError('Failed to load tasks. Please try again later.');
                    showLoading(false);
                });
            }
            
            function getAssigneeName(assigneeId) {
                if (!assigneeId) return 'Unassigned';
                const developer = developers.find(d => d.id == assigneeId);
                return developer ? developer.name : 'Unknown';
            }
            
            function processTasks(taskData) {
                // Clear existing data
                tasks.length = 0;
                Object.keys(categories).forEach(key => delete categories[key]);
                
                // Process new data
                taskData.forEach(task => {
                    tasks.push(task);
                    
                    // Organize by category
                    if (!categories[task.category]) {
                        categories[task.category] = {
                            total: 0,
                            completed: 0,
                            subcategories: {}
                        };
                    }
                    
                    categories[task.category].total++;
                    if (task.completed == 1) {
                        categories[task.category].completed++;
                    }
                    
                    // Organize by subcategory
                    if (!categories[task.category].subcategories[task.subcategory]) {
                        categories[task.category].subcategories[task.subcategory] = {
                            total: 0,
                            completed: 0,
                            tasks: []
                        };
                    }
                    
                    categories[task.category].subcategories[task.subcategory].total++;
                    if (task.completed == 1) {
                        categories[task.category].subcategories[task.subcategory].completed++;
                    }
                    
                    categories[task.category].subcategories[task.subcategory].tasks.push(task);
                });
                
                // Render the UI
                renderTasks();
                updateProgressCards('1'); // Start with Tranche 1
                initializeFilters();
            }
            
            function renderTasks() {
                // Clear existing content
                document.getElementById('taskList1').innerHTML = '';
                document.getElementById('taskList2').innerHTML = '';
                document.getElementById('taskList3').innerHTML = '';
                
                // Group tasks by tranche
                const trancheGroups = {
                    '1': {},
                    '2': {},
                    '3': {}
                };
                
                // Organize tasks by tranche and category
                tasks.forEach(task => {
                    const tranche = task.tranche;
                    const category = task.category;
                    
                    if (!trancheGroups[tranche][category]) {
                        trancheGroups[tranche][category] = {
                            total: 0,
                            completed: 0,
                            subcategories: {}
                        };
                    }
                    
                    trancheGroups[tranche][category].total++;
                    if (task.completed == 1) {
                        trancheGroups[tranche][category].completed++;
                    }
                    
                    const subcategory = task.subcategory;
                    if (!trancheGroups[tranche][category].subcategories[subcategory]) {
                        trancheGroups[tranche][category].subcategories[subcategory] = {
                            total: 0,
                            completed: 0,
                            tasks: []
                        };
                    }
                    
                    trancheGroups[tranche][category].subcategories[subcategory].total++;
                    if (task.completed == 1) {
                        trancheGroups[tranche][category].subcategories[subcategory].completed++;
                    }
                    
                    trancheGroups[tranche][category].subcategories[subcategory].tasks.push(task);
                });
                
                // Render each tranche
                Object.keys(trancheGroups).forEach(tranche => {
                    const trancheElement = document.getElementById(`taskList${tranche}`);
                    const trancheCategories = trancheGroups[tranche];
                    
                    // Sort categories alphabetically
                    const sortedCategories = Object.keys(trancheCategories).sort();
                    
                    sortedCategories.forEach(category => {
                        const categoryData = trancheCategories[category];
                        const completionPercent = categoryData.total > 0 
                            ? Math.round((categoryData.completed / categoryData.total) * 100) 
                            : 0;
                        
                        const categorySection = document.createElement('div');
                        categorySection.className = 'category-section';
                        
                        // Category header
                        const categoryHeader = document.createElement('div');
                        categoryHeader.className = 'category-header';
                        categoryHeader.innerHTML = `
                            <div>${category}</div>
                            <div class="category-progress">
                                <div class="category-progress-bar">
                                    <div class="progress-fill" style="width: ${completionPercent}%"></div>
                                </div>
                                <div>${categoryData.completed}/${categoryData.total} (${completionPercent}%)</div>
                            </div>
                        `;
                        categorySection.appendChild(categoryHeader);
                        
                        // Task list container
                        const taskList = document.createElement('div');
                        taskList.className = 'task-list';
                        
                        // Sort subcategories alphabetically
                        const sortedSubcategories = Object.keys(categoryData.subcategories).sort();
                        
                        sortedSubcategories.forEach(subcategory => {
                            const subcategoryData = categoryData.subcategories[subcategory];
                            
                            // Create subcategory container
                            const subcategoryContainer = document.createElement('div');
                            subcategoryContainer.className = 'subcategory-container';
                            
                            // Create subcategory header
                            const subcategoryHeader = document.createElement('div');
                            subcategoryHeader.className = 'subcategory-header';
                            
                            // Calculate subcategory completion percentage
                            const subcategoryCompletionPercent = subcategoryData.total > 0 
                                ? Math.round((subcategoryData.completed / subcategoryData.total) * 100) 
                                : 0;
                                
                            subcategoryHeader.innerHTML = `
                                <div class="subcategory-title">${subcategory}</div>
                                <div class="subcategory-progress">
                                    <div class="category-progress-bar">
                                        <div class="progress-fill" style="width: ${subcategoryCompletionPercent}%"></div>
                                    </div>
                                    <div>${subcategoryData.completed}/${subcategoryData.total} (${subcategoryCompletionPercent}%)</div>
                                </div>
                            `;
                            
                            subcategoryContainer.appendChild(subcategoryHeader);
                            
                            // Create tasks container for this subcategory
                            const subcategoryTasks = document.createElement('div');
                            subcategoryTasks.className = 'subcategory-tasks';
                            
                            // Sort tasks by ID
                            const sortedTasks = subcategoryData.tasks.sort((a, b) => a.id - b.id);
                            
                            sortedTasks.forEach(task => {
                                const taskItem = document.createElement('div');
                                taskItem.className = `task-item ${task.completed == 1 ? 'completed-task' : ''}`;
                                
                                // Create assignee dropdown options
                                let assigneeOptions = '';
                                developers.forEach(dev => {
                                    const selected = task.assignee_id == dev.id ? 'selected' : '';
                                    assigneeOptions += `<option value="${dev.id}" ${selected}>${dev.name}</option>`;
                                });
                                
                                // Create task item HTML
                                taskItem.innerHTML = `
                                    <input type="checkbox" class="task-checkbox" data-id="${task.id}" ${task.completed == 1 ? 'checked' : ''}>
                                    <div class="task-info">
                                        <div class="task-name">${task.task_name}</div>
                                        <div class="task-notes-container">
                                            <div class="task-notes markdown-content" data-task-id="${task.id}" data-markdown="${task.notes_markdown ? encodeURIComponent(task.notes_markdown) : ''}" title="Supports Markdown formatting">${task.notes || ''}</div>
                                            <div class="markdown-help-tooltip" title="Markdown Help">?</div>
                                            <div class="markdown-help-popup">
                                                <h4>Markdown Formatting</h4>
                                                <ul>
                                                    <li><code># Heading</code> - Headings</li>
                                                    <li><code>**Bold**</code> - <strong>Bold text</strong></li>
                                                    <li><code>*Italic*</code> - <em>Italic text</em></li>
                                                    <li><code>[Link](url)</code> - <a href="#">Link</a></li>
                                                    <li><code>- Item</code> - List item</li>
                                                    <li><code>1. Item</code> - Numbered list</li>
                                                    <li><code>\`\`\`code\`\`\`</code> - Code block</li>
                                                </ul>
                                            </div>
                                            <div class="task-notes-edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="task-assignee">
                                        <select class="assignee-selector" data-id="${task.id}">
                                            <option value="">Unassigned</option>
                                            ${assigneeOptions}
                                        </select>
                                    </div>
                                `;
                               
                                subcategoryTasks.appendChild(taskItem);
                                
                                // Add event listener for checkbox
                                const checkbox = taskItem.querySelector('.task-checkbox');
                                checkbox.addEventListener('change', function() {
                                    updateTaskStatus(task.id, this.checked);
                                    
                                    // Update completed class on parent
                                    if (this.checked) {
                                        taskItem.classList.add('completed-task');
                                    } else {
                                        taskItem.classList.remove('completed-task');
                                    }
                                    
                                    // Reapply filters
                                    applyFilters();
                               });
                                
                                // Add event listener for assignee dropdown
                                const assigneeSelector = taskItem.querySelector('.assignee-selector');
                                assigneeSelector.addEventListener('change', function() {
                                    updateTaskAssignee(task.id, this.value);
                                    
                                    // Reapply filters to update visibility if needed
                                    setTimeout(() => {
                                        applyFilters();
                                    }, 100);
                               });
                            });
                            
                            // Add tasks to subcategory container
                            subcategoryContainer.appendChild(subcategoryTasks);
                            
                            // Add subcategory container to task list
                            taskList.appendChild(subcategoryContainer);
                        });
                        
                        categorySection.appendChild(taskList);
                        trancheElement.appendChild(categorySection);
                    });
                });
                setupNotesEditing();            
            }
            
            function updateTaskStatus(taskId, completed) {
                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('task_id', taskId);
                formData.append('completed', completed ? 1 : 0);
                
                fetch('handle_tasks.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update local data
                        const task = tasks.find(t => t.id == taskId);
                        if (task) {
                            task.completed = completed ? 1 : 0;
                            
                            // Update category data
                            const category = task.category;
                            const subcategory = task.subcategory;
                            
                            if (completed) {
                                categories[category].completed++;
                                categories[category].subcategories[subcategory].completed++;
                            } else {
                                categories[category].completed--;
                                categories[category].subcategories[subcategory].completed--;
                            }
                            
                            // Update UI
                            updateCategoryProgress();
                            updateProgressCards(document.querySelector('.tab.active').getAttribute('data-tranche'));
                            
                            // Reload the page to refresh server-side calculated stats
                            // You could implement a more elegant solution with AJAX
                            // setTimeout(() => { window.location.reload(); }, 500);
                        }
                    } else {
                        // Revert checkbox state on error
                        const checkbox = document.querySelector(`.task-checkbox[data-id="${taskId}"]`);
                        if (checkbox) {
                            checkbox.checked = !completed;
                        }
                        
                        showError('Failed to update task status. Please try again.');
                    }
                })
                .catch(error => {
                   console.error('Error updating task status:', error);
                    
                    // Revert checkbox state on error
                    const checkbox = document.querySelector(`.task-checkbox[data-id="${taskId}"]`);
                    if (checkbox) {
                        checkbox.checked = !completed;
                    }
                    
                    showError('Failed to update task status. Please try again.');
                });
            }
            
            function updateTaskAssignee(taskId, assigneeId) {
                const formData = new FormData();
                formData.append('action', 'update_assignee');
                formData.append('task_id', taskId);
                formData.append('assignee_id', assigneeId);
                
                fetch('handle_tasks.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update local data
                        const task = tasks.find(t => t.id == taskId);
                        if (task) {
                            task.assignee_id = assigneeId;
                            // Reload the page to refresh server-side calculated stats
                            // setTimeout(() => { window.location.reload(); }, 500);
                        }
                    } else {
                        // Revert select state on error
                        const assigneeSelector = document.querySelector(`.assignee-selector[data-id="${taskId}"]`);
                        if (assigneeSelector) {
                            assigneeSelector.value = task.assignee_id || '';
                        }
                        
                        showError('Failed to update task assignee. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error updating task assignee:', error);
                    
                    // Revert select state on error
                    const assigneeSelector = document.querySelector(`.assignee-selector[data-id="${taskId}"]`);
                    if (assigneeSelector) {
                        assigneeSelector.value = task.assignee_id || '';
                    }
                    
                    showError('Failed to update task assignee. Please try again.');
                });
            }
            
            function updateProgressCards(tranche) {
                progressContainer.innerHTML = '';
                
                // Filter tasks by tranche
                const trancheTasks = tasks.filter(task => task.tranche == tranche);
                
                // Group by category
                const trancheCategories = {};
                trancheTasks.forEach(task => {
                    if (!trancheCategories[task.category]) {
                        trancheCategories[task.category] = {
                            total: 0,
                            completed: 0
                        };
                    }
                    
                    trancheCategories[task.category].total++;
                    if (task.completed == 1) {
                        trancheCategories[task.category].completed++;
                    }
                });
                
                // Create progress cards
                Object.keys(trancheCategories).sort().forEach(category => {
                    const data = trancheCategories[category];
                    const percent = data.total > 0 ? Math.round((data.completed / data.total) * 100) : 0;
                    
                    const card = document.createElement('div');
                    card.className = 'progress-card';
                    card.innerHTML = `
                        <h3>${category}</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${percent}%"></div>
                        </div>
                        <div class="progress-stats">
                            <div>${data.completed}/${data.total} tasks</div>
                            <div>${percent}%</div>
                        </div>
                    `;
                    
                    progressContainer.appendChild(card);
                });
            }
            
            function updateCategoryProgress() {
                document.querySelectorAll('.category-section').forEach(section => {
                    const categoryHeader = section.querySelector('.category-header');
                    const categoryName = categoryHeader.querySelector('div:first-child').textContent;
                    const progressElement = categoryHeader.querySelector('.category-progress div:last-child');
                    const progressBar = categoryHeader.querySelector('.progress-fill');
                    
                    // Find the relevant category in the current tranche
                    const activeTrancheId = document.querySelector('.tab.active').getAttribute('data-tranche');
                    const trancheTasks = tasks.filter(task => task.tranche == activeTrancheId && task.category == categoryName);
                    
                    if (trancheTasks.length > 0) {
                        const total = trancheTasks.length;
                        const completed = trancheTasks.filter(t => t.completed == 1).length;
                        const percent = Math.round((completed / total) * 100);
                        
                        progressElement.textContent = `${completed}/${total} (${percent}%)`;
                        progressBar.style.width = `${percent}%`;
                    }
                });
            }
            
            function showLoading(show) {
                if (show) {
                    loadingDisplay.classList.remove('hidden');
                    contentContainer.classList.add('hidden');
                    errorDisplay.classList.add('hidden');
                } else {
                    loadingDisplay.classList.add('hidden');
                    contentContainer.classList.remove('hidden');
                }
            }
            
            function showError(message) {
                errorDisplay.textContent = message;
                errorDisplay.classList.remove('hidden');
                setTimeout(() => {
                    errorDisplay.classList.add('hidden');
                }, 5000);
            }
        });

// Task filtering functions
function initializeFilters() {
    // Create assignee filter buttons
    createAssigneeFilters();
    
    // Setup hide completed toggle
    const hideCompletedToggle = document.getElementById('hideCompleted');
    if (hideCompletedToggle) {
        hideCompletedToggle.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Apply initial filters
    applyFilters();
}

function createAssigneeFilters() {
    const assigneeFiltersContainer = document.getElementById('assigneeFilters');
    if (!assigneeFiltersContainer) return;
    
    // Get developers from existing data
    const developers = window.developers || [];
    
    // Add a button for each developer
    developers.forEach(dev => {
        const button = document.createElement('button');
        button.className = 'assignee-filter-btn';
        button.setAttribute('data-assignee', dev.id);
        button.textContent = dev.name;
        
        button.addEventListener('click', function() {
            // Toggle active state
            document.querySelectorAll('.assignee-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Apply filters
            applyFilters();
        });
        
        assigneeFiltersContainer.appendChild(button);
    });
    
    // Add an "Unassigned" button
    const unassignedButton = document.createElement('button');
    unassignedButton.className = 'assignee-filter-btn';
    unassignedButton.setAttribute('data-assignee', 'unassigned');
    unassignedButton.textContent = 'Unassigned';
    
    unassignedButton.addEventListener('click', function() {
        // Toggle active state
        document.querySelectorAll('.assignee-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');
        
        // Apply filters
        applyFilters();
    });
    
    assigneeFiltersContainer.appendChild(unassignedButton);
}

function applyFilters() {
    // Get current filter states
    const hideCompleted = document.getElementById('hideCompleted').checked;
    const activeAssigneeButton = document.querySelector('.assignee-filter-btn.active');
    const selectedAssignee = activeAssigneeButton ? activeAssigneeButton.getAttribute('data-assignee') : 'all';
    
    // Apply to all task items
    document.querySelectorAll('.task-item').forEach(taskItem => {
        // Reset classes first
        taskItem.classList.remove('hide-completed', 'filtered-by-assignee');
        
        // Check if task is completed
        const isCompleted = taskItem.querySelector('.task-checkbox').checked;
        
        // Add completed class if needed
        if (isCompleted) {
            taskItem.classList.add('completed-task');
            
            // Hide if the filter is active
            if (hideCompleted) {
                taskItem.classList.add('hide-completed');
            }
        } else {
            taskItem.classList.remove('completed-task');
        }
        
        // Apply assignee filter
        if (selectedAssignee !== 'all') {
            const assigneeSelector = taskItem.querySelector('.assignee-selector');
            const taskAssigneeId = assigneeSelector.value;
            
            if (selectedAssignee === 'unassigned' && taskAssigneeId !== '') {
                taskItem.classList.add('filtered-by-assignee');
            } else if (selectedAssignee !== 'unassigned' && taskAssigneeId !== selectedAssignee) {
                taskItem.classList.add('filtered-by-assignee');
            }
        }
    });
    
    // Update empty category display
    updateEmptyCategoryDisplay();
}

function updateEmptyCategoryDisplay() {
    // Check each category section for visible tasks
    document.querySelectorAll('.category-section').forEach(section => {
        const hasVisibleTasks = Array.from(section.querySelectorAll('.task-item')).some(task => {
            return !task.classList.contains('hide-completed') && !task.classList.contains('filtered-by-assignee');
        });
        
        if (!hasVisibleTasks) {
            if (!section.querySelector('.empty-category-message')) {
                const taskList = section.querySelector('.task-list');
                const emptyMessage = document.createElement('div');
                emptyMessage.className = 'empty-category-message';
                emptyMessage.textContent = 'No tasks match the current filters';
                taskList.appendChild(emptyMessage);
            }
        } else {
            const emptyMessage = section.querySelector('.empty-category-message');
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    });
    
    // Check each subcategory container for visible tasks
    document.querySelectorAll('.subcategory-container').forEach(container => {
        const hasVisibleTasks = Array.from(container.querySelectorAll('.task-item')).some(task => {
            return !task.classList.contains('hide-completed') && !task.classList.contains('filtered-by-assignee');
        });
        
        if (!hasVisibleTasks) {
            if (!container.querySelector('.empty-subcategory-message')) {
                const tasksContainer = container.querySelector('.subcategory-tasks');
                const emptyMessage = document.createElement('div');
                emptyMessage.className = 'empty-subcategory-message';
                emptyMessage.textContent = 'No tasks match the current filters';
                tasksContainer.appendChild(emptyMessage);
            }
        } else {
            const emptyMessage = container.querySelector('.empty-subcategory-message');
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    });
}

// Export button functionality
document.addEventListener('DOMContentLoaded', function() {
    const exportMarkdownBtn = document.getElementById('exportMarkdown');
    const exportPrintableBtn = document.getElementById('exportPrintable');
    
    if (exportMarkdownBtn) {
        exportMarkdownBtn.addEventListener('click', function() {
            generateExport('markdown');
        });
    }
    
    if (exportPrintableBtn) {
        exportPrintableBtn.addEventListener('click', function() {
            generateExport('print');
        });
    }
    
    function generateExport(type) {
        // Get current active tranche
        const activeTab = document.querySelector('.tab.active');
        const trancheId = activeTab ? activeTab.getAttribute('data-tranche') : '1';
        const trancheName = activeTab ? activeTab.textContent.trim().split('%')[0].trim() : 'Tranche 1 - MVP';
        
        // Collect all the task data
        const markdownContent = generateMarkdownContent(trancheId, trancheName);
        
        // Open new window with the content
        const newWindow = window.open('', '_blank');
        const closeScript = '</' + 'script>';
        if (type === 'markdown') {
            // Plain markdown format
            newWindow.document.write(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>${trancheName} - Markdown Export</title>
                    <style>
                        body {
                            font-family: monospace;
                            white-space: pre-wrap;
                            padding: 20px;
                            max-width: 1000px;
                            margin: 0 auto;
                        }
                        .toolbar {
                            position: fixed;
                            top: 10px;
                            right: 10px;
                            background: white;
                            border: 1px solid #ccc;
                            border-radius: 4px;
                            padding: 8px 12px;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        }
                        .copy-btn {
                            background: #2563eb;
                            color: white;
                            border: none;
                            border-radius: 4px;
                            padding: 4px 8px;
                            cursor: pointer;
                        }
                        .copy-btn:hover {
                            background: #1d4ed8;
                        }
                        .copy-feedback {
                            display: none;
                            margin-left: 8px;
                            color: #059669;
                            font-size: 14px;
                        }
                    </style>
                </head>
                <body>
                    <div class="toolbar">
                        <button class="copy-btn" onclick="copyContent()">Copy Markdown</button>
                        <span class="copy-feedback" id="copyFeedback">Copied!</span>
                    </div>
                    ${markdownContent}
                    <script>
                        function copyContent() {
                            const content = document.querySelector('body').innerText.replace(/Copy Markdown\\s+Copied!/g, '');
                            navigator.clipboard.writeText(content)
                                .then(() => {
                                    const feedback = document.getElementById('copyFeedback');
                                    feedback.style.display = 'inline';
                                    setTimeout(() => {
                                        feedback.style.display = 'none';
                                    }, 2000);
                                })
                                .catch(err => {
                                    console.error('Could not copy text: ', err);
                                    alert('Failed to copy. Please try selecting all text manually.');
                                });
                        }
                    ${closeScript}
                </body>
                </html>
            `);
        } else {
            // Printable version with styling
            newWindow.document.write(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>${trancheName} - Print View</title>
                    <link rel="stylesheet" href="lib/pandoc.css">
                    <style>
                        @media print {
                            .no-print {
                                display: none !important;
                            }
                        }
                        .toolbar {
                            position: fixed;
                            top: 10px;
                            right: 10px;
                            background: white;
                            border: 1px solid #ccc;
                            border-radius: 4px;
                            padding: 8px 12px;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        }
                        .print-btn {
                            background: #2563eb;
                            color: white;
                            border: none;
                            border-radius: 4px;
                            padding: 4px 8px;
                            cursor: pointer;
                        }
                        .print-btn:hover {
                            background: #1d4ed8;
                        }
                        h1, h2, h3, h4 {
                            page-break-after: avoid;
                        }
                        li {
                            page-break-inside: avoid;
                        }
                    </style>
                </head>
                <body>
                    <div class="toolbar no-print">
                        <button class="print-btn" onclick="window.print()">Print</button>
                    </div>
                    <div class="markdown-content">
                        ${convertMarkdownToHTML(markdownContent)}
                    </div>
                </body>
                </html>
            `);
        }
        
        newWindow.document.close();
    }
    
    function generateMarkdownContent(trancheId, trancheName) {
        // Start with the tranche name as the top heading
        let content = `# ${trancheName}\n\n`;
        
        // Get all categories in this tranche
        const trancheElement = document.querySelector(`.tab-content[data-tranche="${trancheId}"]`);
        const categories = trancheElement.querySelectorAll('.category-section');
        
        categories.forEach(category => {
            // Add category heading
            const categoryName = category.querySelector('.category-header div:first-child').textContent.trim();
            content += `## ${categoryName}\n\n`;
            
            // Get subcategories
            const subcategories = category.querySelectorAll('.subcategory-container');
            
            subcategories.forEach(subcategory => {
                // Add subcategory heading
                const subcategoryName = subcategory.querySelector('.subcategory-title').textContent.trim();
                content += `### ${subcategoryName}\n\n`;
                
                // Get tasks
                const tasks = subcategory.querySelectorAll('.task-item');
                
                tasks.forEach(task => {
                    // Get task info
                    const taskName = task.querySelector('.task-name').textContent.trim();
                    const isCompleted = task.querySelector('.task-checkbox').checked;
                    
                    // Get assignee if any
                    const assigneeSelect = task.querySelector('.assignee-selector');
                    let assigneeText = '';
                    if (assigneeSelect && assigneeSelect.value) {
                        const assigneeOption = assigneeSelect.options[assigneeSelect.selectedIndex];
                        assigneeText = ''; // ` (Assigned to: ${assigneeOption.textContent.trim()})`;
                    }
                    
                    // Get notes if any
                    const notesElement = task.querySelector('.task-notes');
                    let notesMarkdown = '';
                    if (notesElement && notesElement.getAttribute('data-markdown')) {
                        const markdown = decodeURIComponent(notesElement.getAttribute('data-markdown'));
                        if (markdown.trim()) {
                            notesMarkdown = '\n   ' + markdown.replace(/\n/g, '\n   ');
                        }
                    }
                    
                    // Add task as a checkbox list item
                    content += `- [${isCompleted ? 'x' : ' '}] ${taskName}${assigneeText}${notesMarkdown}\n`;
                });
                
                content += '\n';
            });
        });
        
        return content;
    }
    
    function convertMarkdownToHTML(markdown) {
        // Very basic markdown to HTML conversion
        // Headers
        let html = markdown
            .replace(/^# (.+)$/gm, '<h1>$1</h1>')
            .replace(/^## (.+)$/gm, '<h2>$1</h2>')
            .replace(/^### (.+)$/gm, '<h3>$1</h3>')
            .replace(/^#### (.+)$/gm, '<h4>$1</h4>');
        
        // Task lists
        html = html.replace(/- \[x\] (.+?)$/gm, '<li><input type="checkbox" checked disabled> $1</li>');
        html = html.replace(/- \[ \] (.+?)$/gm, '<li><input type="checkbox" disabled> $1</li>');
        
        // Wrap lists in <ul>
        html = html.replace(/<li>(.+?)<\/li>\n(?!<li>)/gs, '<ul>\n<li>$1</li>\n</ul>\n');
        
        // Consecutive list items
        html = html.replace(/<\/ul>\n<ul>/g, '');
        
        // Bold and italic
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
        
        // Links
        html = html.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2">$1</a>');
        
        // Paragraphs
        html = html.replace(/^(?!<[a-z]).+$/gm, '<p>$&</p>');
        
        // Fix multiple consecutive paragraphs
        html = html.replace(/<\/p>\n<p>/g, '</p><p>');
        
        return html;
    }
});

</script>
</body>
</html> 
                    
