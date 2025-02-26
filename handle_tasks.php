<?php
// handle_tasks.php
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $task_id = $_POST['task_id'];
                $completed = $_POST['completed'] ? 1 : 0;
                
                $stmt = $conn->prepare("UPDATE tasks SET completed = ? WHERE id = ?");
                $stmt->bind_param("ii", $completed, $task_id);
                $stmt->execute();
                
                echo json_encode(['success' => true]);
                break;
                
            case 'get_progress':
                $sql = "SELECT category, COUNT(*) as total, SUM(completed) as completed FROM tasks GROUP BY category";
                $result = $conn->query($sql);
                
                $progress = [];
                while ($row = $result->fetch_assoc()) {
                    $progress[] = $row;
                }
                
                echo json_encode($progress);
                break;
        }
    }
    exit;
}

// GET request - return all tasks
$sql = "SELECT id, category, subcategory, task_name, completed, tranche FROM tasks ORDER BY category, subcategory, id";
$result = $conn->query($sql);

$tasks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

echo json_encode($tasks);
?>
