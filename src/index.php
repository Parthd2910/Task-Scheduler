<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name']) && !empty(trim($_POST['task-name']))) {
        addTask(trim($_POST['task-name']));
    }

    if (isset($_POST['task_id'], $_POST['completed'])) {
        markTaskAsCompleted($_POST['task_id'], $_POST['completed'] === 'true');
    }

    if (isset($_POST['delete_id'])) {
        deleteTask($_POST['delete_id']);
    }

    if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $subscribed = subscribeEmail(trim($_POST['email']));
        if ($subscribed) {
            echo "<script>alert('Verification email sent! Please check your inbox.');</script>";
        } else {
            echo "<script>alert('You have already requested a subscription or an error occurred.');</script>";
        }
    }

    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}


$tasks = getAllTasks();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Scheduler</title>
    <style>
		body{text-align:center;}
        .task-list { margin: 10px 400px;list-style: none; padding: 0; text-align:center;}
        .task-item { padding: 8px; border-bottom: 1px solid #eee; display: flex; align-items: center; }
        .completed { text-decoration: line-through; color: #888; }
        .task-status { margin-right: 10px; }
        .delete-task { margin-left: auto; background: none; border: none; color: red; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Task Scheduler</h1>
    
    <form method="POST">
        <input type="text" name="task-name" id="task-name" placeholder="Enter task" required>
        <button type="submit" id="add-task">Add Task</button>
    </form>

    <ul class="task-list">
        <?php foreach ($tasks as $task): ?>
        <li class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
            <form method="POST" style="display:inline">
                <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                <input type="hidden" name="completed" value="<?= $task['completed'] ? 'false' : 'true' ?>">
                <input type="checkbox" class="task-status" <?= $task['completed'] ? 'checked' : '' ?> onchange="this.form.submit()">
                <span><?= htmlspecialchars($task['name']) ?></span>
            </form>
            <form method="POST" style="display:inline">
                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($task['id']) ?>">
                <button type="submit" class="delete-task">Delete</button>
            </form>
        </li>
        <?php endforeach; ?>
    </ul>

    <h2>Email Reminders</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Your email" required>
        <button type="submit" id="submit-email">Subscribe</button>
    </form>
</body>
</html>