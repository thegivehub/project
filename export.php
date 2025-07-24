<?php
// export.php - Handles exporting tasks to Markdown or printable format
require_once("config.php");
require_once("lib/Parsedown.php");
$Parsedown = new Parsedown();

// Validate parameters
$type = isset($_GET['type']) && in_array($_GET['type'], ['markdown', 'print']) ? $_GET['type'] : 'markdown';
$tranche = isset($_GET['tranche']) && in_array($_GET['tranche'], ['1', '2', '3']) ? $_GET['tranche'] : '1';

// Get tranche name
$trancheNames = [
    '1' => 'Tranche 1 - MVP',
    '2' => 'Tranche 2 - Testnet',
    '3' => 'Tranche 3 - Mainnet'
];
$trancheName = $trancheNames[$tranche];

// Get tasks for the selected tranche
$sql = "SELECT id, category, subcategory, task_name, completed, assignee_id, notes FROM tasks 
        WHERE tranche = ? 
        ORDER BY category, subcategory, id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tranche);
$stmt->execute();
$result = $stmt->get_result();

// Prepare tasks data
$tasks = [];
$categories = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tasks[] = $row;
        
        // Organize by category and subcategory
        if (!isset($categories[$row['category']])) {
            $categories[$row['category']] = [];
        }
        
        if (!isset($categories[$row['category']][$row['subcategory']])) {
            $categories[$row['category']][$row['subcategory']] = [];
        }
        
        $categories[$row['category']][$row['subcategory']][] = $row;
    }
}

// Generate Markdown content
function generateMarkdown($categories, $trancheName) {
    global $conn;

    $developers = json_decode(file_get_contents("developers.json"));
    // Start with tranche name as heading
    $markdown = "# $trancheName\n\n";
    
    // Add each category
    foreach ($categories as $categoryName => $subcategories) {
        $markdown .= "## $categoryName\n\n";
        
        // Add each subcategory
        foreach ($subcategories as $subcategoryName => $tasks) {
            $markdown .= "### $subcategoryName\n\n";
            
            // Add each task
            foreach ($tasks as $task) {
                $checkbox = $task['completed'] ? 'x' : ' ';
                
                // Get assignee name if exists
                $assigneeText = '';
                if (!empty($task['assignee_id'])) {
                    //$assigneeText = " (Assigned to: {$developers[$task['assignee_id']-1]->name})";
                }
                
                // Format notes if they exist
                $notesText = '';
                if (!empty($task['notes'])) {
                    $notesText = "\n   " . str_replace("\n", "\n   ", $task['notes']);
                    $notesText = preg_replace("/&lt;/", "<", preg_replace("/&gt;/", ">", $task['notes']));
                    $notesText = preg_replace("/&nbsp;/", " ", $notesText);
                }
                
                $markdown .= "- [$checkbox] {$task['task_name']}$assigneeText$notesText\n";
            }
            
            $markdown .= "\n";
        }
    }
    
    return $markdown;
}

// Generate markdown
$markdown = generateMarkdown($categories, $trancheName);

// Output based on type
if ($type === 'markdown') {
    // Plain markdown format
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($trancheName); ?> - Markdown Export</title>
        <style>
            body {
                font-family: monospace;
                white-space: pre-wrap;
                padding: 20px;
                max-width: 1000px;
                margin: 0 auto;
                line-height: 1.5;
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
                z-index: 1000;
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
            .return-link {
                position: fixed;
                top: 10px;
                left: 10px;
                padding: 4px 8px;
                background: #f3f4f6;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                text-decoration: none;
                color: #374151;
                font-size: 14px;
            }
            .return-link:hover {
                background: #e5e7eb;
            }
        </style>
    </head>
    <body>
        <a href="index.php" class="return-link">← Back to dashboard</a>
        <div class="toolbar">
            <button class="copy-btn" onclick="copyContent()">Copy Markdown</button>
            <span class="copy-feedback" id="copyFeedback">Copied!</span>
        </div>
<?php echo htmlspecialchars($markdown); ?>
        <script>
            function copyContent() {
                const content = document.body.innerText.replace(/Copy Markdown\s+Copied!/, '').replace(/← Back to dashboard/, '');
                navigator.clipboard.writeText(content)
                    .then(function() {
                        const feedback = document.getElementById('copyFeedback');
                        feedback.style.display = 'inline';
                        setTimeout(function() {
                            feedback.style.display = 'none';
                        }, 2000);
                    })
                    .catch(function(err) {
                        console.error('Could not copy text: ', err);
                        alert('Failed to copy. Please try selecting all text manually and copying.');
                    });
            }
        </script>
    </body>
    </html>
    <?php
} else {
    // Printable version with HTML
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($trancheName); ?> - Print View</title>
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
                z-index: 1000;
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
            .return-link {
                position: fixed;
                top: 10px;
                left: 10px;
                padding: 4px 8px;
                background: #f3f4f6;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                text-decoration: none;
                color: #374151;
                font-size: 14px;
            }
            .return-link:hover {
                background: #e5e7eb;
            }
            ul.task-list {
                list-style: none;
                padding-left: 1.5em;
            }
            ul.task-list li {
                margin-bottom: 0.5em;
            }
            ul.task-list li input[type="checkbox"] {
                margin-right: 0.5em;
            }
        </style>
    </head>
    <body>
        <a href="index.php" class="return-link no-print">← Back to dashboard</a>
        <div class="toolbar no-print">
            <button class="print-btn" onclick="window.print()">Print</button>
        </div>
<div class="markdown-content">
<?php echo $Parsedown->text($markdown); ?>
</div>
    </body>
    </html>
    <?php
}
?>
