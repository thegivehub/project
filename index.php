<?php
// index.php - Updated with assignee functionality
require_once("config.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiveHub Deliverable Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #dbeafe;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Lexend', sans-serif;
            background-color: #f9fafb;
            color: var(--gray-800);
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--gray-800);
            text-align: center;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 2rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
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
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        .progress-card h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--gray-700);
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
            color: var(--gray-600);
        }

        .category-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .category-header {
            padding: 1rem 1.5rem;
            background: var(--gray-100);
            font-weight: 600;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .task-item {
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
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
        }

        .task-subcategory {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .task-assignee {
            display: flex;
            align-items: center;
            margin-left: auto;
            position: relative;
        }
        
        .assignee-selector {
            padding: 0.35rem 0.5rem;
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            background-color: white;
            font-size: 0.875rem;
            cursor: pointer;
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
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .summary-card h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--gray-700);
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
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--primary);
        }

        .summary-label {
            font-size: 0.875rem;
            color: var(--gray-600);
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
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
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
            color: var(--gray-700);
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
        }
        
        .assignee-info p {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .progress-container {
                grid-template-columns: 1fr;
            }
            
            .category-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
        }
    </style>
</head>
<body>
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
        document.addEventListener('DOMContentLoaded', function() {
            const tasks = [];
            const categories = {};
            const progressContainer = document.getElementById('progressContainer');
            const contentContainer = document.getElementById('contentContainer');
            const loadingDisplay = document.getElementById('loadingDisplay');
            const errorDisplay = document.getElementById('errorDisplay');
            let developers = [];
            
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
            
            // Fetch developers then tasks
            fetchDevelopers()
                .then(() => fetchTasks())
                .catch(error => {
                    console.error('Error initializing app:', error);
                    showError('Failed to initialize application. Please try again.');
                });
            
            function fetchDevelopers() {
                return fetch('get_developers.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        developers = data;
                    });
            }
            
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
                        
                        // Task list
                        const taskList = document.createElement('div');
                        taskList.className = 'task-list';
                        
                        // Sort subcategories alphabetically
                        const sortedSubcategories = Object.keys(categoryData.subcategories).sort();
                        
                        sortedSubcategories.forEach(subcategory => {
                            const subcategoryData = categoryData.subcategories[subcategory];
                            
                            // Sort tasks by ID
                            const sortedTasks = subcategoryData.tasks.sort((a, b) => a.id - b.id);
                            
                            sortedTasks.forEach(task => {
                                const taskItem = document.createElement('div');
                                taskItem.className = 'task-item';
                                
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
                                        <div class="task-subcategory">${subcategory}</div>
                                    </div>
                                    <div class="task-assignee">
                                        <select class="assignee-selector" data-id="${task.id}">
                                            <option value="">Unassigned</option>
                                            ${assigneeOptions}
                                        </select>
                                    </div>
                                `;
                                taskList.appendChild(taskItem);
                                
                                // Add event listener for checkbox
                                const checkbox = taskItem.querySelector('.task-checkbox');
                                checkbox.addEventListener('change', function() {
                                    updateTaskStatus(task.id, this.checked);
                                });
                                
                                // Add event listener for assignee dropdown
                                const assigneeSelector = taskItem.querySelector('.assignee-selector');
                                assigneeSelector.addEventListener('change', function() {
                                    updateTaskAssignee(task.id, this.value);
                                });
                            });
                        });
                        
                        categorySection.appendChild(taskList);
                        trancheElement.appendChild(categorySection);
                    });
                });
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
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
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
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
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
    </script>
</body>
</html> 
                    
