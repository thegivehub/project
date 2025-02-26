<?php
    require_once("config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Task Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Project Task Tracker</h1>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Progress Overview</h2>
            <div id="progress-bars" class="space-y-4">
                <!-- Progress bars will be inserted here -->
            </div>
        </div>

        <form id="taskForm" class="space-y-8">
            <?php
            $sql = "SELECT DISTINCT category FROM tasks ORDER BY id";
            $result = $conn->query($sql);
            
            while ($category = $result->fetch_assoc()) {
                echo '<div class="bg-white p-6 rounded-lg shadow-md">';
                echo '<h2 class="text-2xl font-bold mb-4">' . htmlspecialchars($category['category']) . '</h2>';
                
                $sql_sub = "SELECT DISTINCT subcategory FROM tasks WHERE category = ? ORDER BY id";
                $stmt = $conn->prepare($sql_sub);
                $stmt->bind_param("s", $category['category']);
                $stmt->execute();
                $result_sub = $stmt->get_result();
                
                while ($subcategory = $result_sub->fetch_assoc()) {
                    echo '<div class="mb-6">';
                    echo '<h3 class="text-xl font-semibold mb-3">' . htmlspecialchars($subcategory['subcategory']) . '</h3>';
                    
                    $sql_tasks = "SELECT * FROM tasks WHERE category = ? AND subcategory = ? ORDER BY id";
                    $stmt_tasks = $conn->prepare($sql_tasks);
                    $stmt_tasks->bind_param("ss", $category['category'], $subcategory['subcategory']);
                    $stmt_tasks->execute();
                    $result_tasks = $stmt_tasks->get_result();
                    
                    while ($task = $result_tasks->fetch_assoc()) {
                        echo '<div class="flex items-center mb-2">';
                        echo '<input type="checkbox" id="task_' . $task['id'] . '" 
                              class="task-checkbox form-checkbox h-5 w-5 text-blue-600" 
                              data-task-id="' . $task['id'] . '"' . 
                              ($task['completed'] ? ' checked' : '') . '>';
                        echo '<label for="task_' . $task['id'] . '" class="ml-2">' . 
                             htmlspecialchars($task['task_name']) . '</label>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                
                echo '</div>';
            }
            ?>
        </form>
    </div>

    <script>
        function updateProgress() {
            fetch('handle_tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_progress'
            })
            .then(response => response.json())
            .then(data => {
                const progressContainer = document.getElementById('progress-bars');
                progressContainer.innerHTML = '';
                
                data.forEach(category => {
                    const percentage = Math.round((category.completed / category.total) * 100);
                    const progressBar = `
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="font-semibold">${category.category}</span>
                                <span>${percentage}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                    progressContainer.innerHTML += progressBar;
                });
            });
        }

        document.querySelectorAll('.task-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.dataset.taskId;
                const completed = this.checked;
                
                fetch('handle_tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update&task_id=${taskId}&completed=${completed ? 1 : 0}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateProgress();
                    }
                });
            });
        });

        // Initial progress update
        updateProgress();
    </script>
</body>
</html>
