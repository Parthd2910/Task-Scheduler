<?php
/**
 * Adds a new task to the task list
 */
function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    $task_name = trim($task_name);
    if ($task_name === '') return false;

    $tasks = getAllTasks();

    foreach ($tasks as $task) {
        if (strcasecmp($task['name'], $task_name) === 0) {
            return false; 
        }
    }

    $new_id = count($tasks) > 0 ? max(array_column($tasks, 'id')) + 1 : 1;
    $line = $new_id . '|' . $task_name . '|0' . PHP_EOL;

    return file_put_contents($file, $line, FILE_APPEND) !== false;
}
/**
 * Retrieves all tasks from JSON file
 */
function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return [];

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $tasks = [];

    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3) {
            $tasks[] = [
                'id' => (int)$parts[0],
                'name' => $parts[1],
                'completed' => (int)$parts[2]
            ];
        }
    }

    return $tasks;
}


/**
 * Marks task as completed/incomplete (1/0)
 */
function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return false;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = false;

    foreach ($lines as $index => $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3 && $parts[0] == $task_id) {
            $parts[2] = $is_completed ? '1' : '0';
            $lines[$index] = implode('|', $parts);
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    return $updated;
}


/**
 * Deletes a task
 */
function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return false;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated_lines = [];
    $deleted = false;

    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3) {
            if ($parts[0] == $task_id) {
                $deleted = true; 
                continue;
            }
            $updated_lines[] = $line;
        }
    }

    if ($deleted) {
        file_put_contents($file, implode(PHP_EOL, $updated_lines) . PHP_EOL);
    }

    return $deleted;
}


/**
 * Generates a 6-digit verification code
 * 
 * @return string The generated verification code.
 */
function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}


/**
 * Subscribe an email address to task notifications.
 *
 * Generates a verification code, stores the pending subscription,
 * and sends a verification email to the subscriber.
 *
 * @param string $email The email address to subscribe.
 * @return bool True if verification email sent successfully, false otherwise.
 */
function subscribeEmail(string $email): bool {
    $file = __DIR__ . '/pending_subscriptions.txt';

    if (file_exists($file)) {
        $entries = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($entries as $entry) {
            list($existingEmail, ) = explode('|', $entry);
            if (trim($existingEmail) === trim($email)) {
                return false; 
            }
        }
    }

    $code = generateVerificationCode(); 

    $entry = $email . '|' . $code . PHP_EOL;
    file_put_contents($file, $entry, FILE_APPEND);

    $verificationLink = 'http://' . $_SERVER['HTTP_HOST'] . '/task-scheduler-Parthd2910-main/src/verify.php?email=' . urlencode($email) . '&code=' . $code;

    $subject = 'Verify subscription to Task Planner';
    $message = '
        <p>Click the link below to verify your subscription to Task Planner:</p>
        <p><a id="verification-link" href="' . $verificationLink . '">Verify Subscription</a></p>
    ';
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html\r\n";

    return mail($email, $subject, $message, $headers);
}


/**
 * Verifies an email subscription
 * 
 * @param string $email The email address to verify.
 * @param string $code The verification code.
 * @return bool True on success, false on failure.
 */
function verifySubscription(string $email, string $code): bool {
    $pending_file     = __DIR__ . '/pending_subscriptions.txt';
    $subscribers_file = __DIR__ . '/subscribers.txt';

    if (!file_exists($pending_file)) {
        return false;
    }

    $pending_entries = file($pending_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated_pending = [];
    $verified = false;

    foreach ($pending_entries as $entry) {
        $parts = explode('|', $entry);
        if (count($parts) !== 2) {
            continue;
        }

        list($pendingEmail, $pendingCode) = $parts;

        if ($pendingEmail === $email && $pendingCode === $code) {
            $subscribers = [];
            if (file_exists($subscribers_file)) {
                $subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
            if (!in_array($email, $subscribers)) {
                file_put_contents($subscribers_file, $email . PHP_EOL, FILE_APPEND);
            }
            $verified = true;
        } else {
            $updated_pending[] = $entry;
        }
    }

    if ($verified) {
        file_put_contents($pending_file, implode(PHP_EOL, $updated_pending) . PHP_EOL);
    }

    return $verified;
}


/**
 * Unsubscribes an email from the subscribers list
 * 
 * @param string $email The email address to unsubscribe.
 * @return bool True if email was removed, false otherwise.
 */
function unsubscribeEmail(string $email): bool {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    if (!file_exists($subscribers_file)) return false;

    $lines = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated_lines = [];
    $removed = false;
    $emailLower = strtolower(trim($email));

    foreach ($lines as $line) {
        if (strtolower(trim($line)) === $emailLower) {
            $removed = true; 
        } else {
            $updated_lines[] = $line;
        }
    }

    if ($removed) {
        file_put_contents($subscribers_file, implode(PHP_EOL, $updated_lines) . PHP_EOL);
    }

    return $removed;
}

/**
 * Sends task reminders to all subscribers
 * Internally calls  sendTaskEmail() for each subscriber
 */
function sendTaskReminders(): void {
    $subscribers_file = __DIR__ . '/subscribers.txt';
    if (!file_exists($subscribers_file)) return;

    $subscribers = file($subscribers_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($subscribers)) return;

    $tasks = getAllTasks();

    $pending_tasks = array_filter($tasks, fn($task) => !$task['completed']);

    if (empty($pending_tasks)) return;

    foreach ($subscribers as $email) {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

        sendTaskEmail($email, $pending_tasks);
    }
}


/**
 * Sends a task reminder email to a subscriber with pending tasks.
 *
 * @param string $email The email address of the subscriber.
 * @param array $pending_tasks Array of pending tasks to include in the email.
 * @return bool True if email was sent successfully, false otherwise.
 */
function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';

    $taskListHtml = '<ul>';
    foreach ($pending_tasks as $task) {
        $taskListHtml .= '<li>' . htmlspecialchars($task['name']) . '</li>';
    }
    $taskListHtml .= '</ul>';

	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$unsubscribeLink = 'http://' . $host . '/task-scheduler-Parthd2910-main/src/unsubscribe.php?email=' . urlencode($email);

    $message = '
    <html>
    <head>
        <title>Pending Tasks Reminder</title>
    </head>
    <body>
        <h2>Pending Tasks Reminder</h2>
        <p>Here are the current pending tasks:</p>
        ' . $taskListHtml . '
        <p><a id="unsubscribe-link" href="' . $unsubscribeLink . '">Unsubscribe from notifications</a></p>
    </body>
    </html>
    ';

    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}
