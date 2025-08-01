<?php
// handle_tasks.php
require_once("config.php");
require_once("lib/Parsedown.php");
$Parsedown = new Parsedown();

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
                
            case 'update_assignee':
                $task_id = $_POST['task_id'];
                $assignee_id = $_POST['assignee_id'];
                
                $stmt = $conn->prepare("UPDATE tasks SET assignee_id = ? WHERE id = ?");
                $stmt->bind_param("ii", $assignee_id, $task_id);
                $stmt->execute();
                
                echo json_encode(['success' => true]);
                break;
                
            case 'update_notes':
                $task_id = $_POST['task_id'];
                $notes = $_POST['notes'];
                
                // Store the raw markdown
                $stmt = $conn->prepare("UPDATE tasks SET notes = ? WHERE id = ?");
                $stmt->bind_param("si", $notes, $task_id);
                $result = $stmt->execute();
                
                echo json_encode(['success' => $result]);
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
                
            case 'get_assignee_stats':
                $sql = "SELECT 
                        a.assignee_id, 
                        COUNT(*) as total_tasks, 
                        SUM(a.completed) as completed_tasks,
                        (SELECT COUNT(*) FROM tasks WHERE assignee_id = a.assignee_id AND tranche = '1') as tranche1_tasks,
                        (SELECT COUNT(*) FROM tasks WHERE assignee_id = a.assignee_id AND tranche = '2') as tranche2_tasks,
                        (SELECT COUNT(*) FROM tasks WHERE assignee_id = a.assignee_id AND tranche = '3') as tranche3_tasks
                    FROM tasks a 
                    WHERE a.assignee_id IS NOT NULL 
                    GROUP BY a.assignee_id";
                
                $result = $conn->query($sql);
                
                $stats = [];
                while ($row = $result->fetch_assoc()) {
                    $stats[] = $row;
                }
                
                echo json_encode($stats);
                break;
        }
    }
    exit;
}

// GET request - return all tasks
$sql = "SELECT id, category, subcategory, task_name, completed, assignee_id, tranche, notes FROM tasks ORDER BY category, subcategory, id";
$result = $conn->query($sql);

$tasks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Store the original markdown for editing
        if (!empty($row['notes'])) {
            $row['notes_markdown'] = $row['notes'];
            // Convert Markdown to HTML for display
            $row['notes'] = $Parsedown->text($row['notes']);
        }
        $tasks[] = $row;
    }
}

echo json_encode($tasks);
?>
