<?php

function addTask(string $task_name): bool {
    $file = __DIR__ . '/tasks.txt';
    $tasks = [];

    if (file_exists($file)) {
        $tasks = json_decode(file_get_contents($file), true) ?? [];
    }

    foreach ($tasks as $task) {
        if (strtolower(trim($task['name'])) === strtolower(trim($task_name))) {
            return false;
        }
    }

    $tasks[] = [
        'id' => uniqid('task_'),
        'name' => trim($task_name),
        'completed' => false
    ];

    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
    return true;
}

function getAllTasks(): array {
    $file = __DIR__ . '/tasks.txt';
    return file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
}

function markTaskAsCompleted(string $task_id, bool $is_completed): bool {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return false;

    $tasks = json_decode(file_get_contents($file), true);
    foreach ($tasks as &$task) {
        if ($task['id'] === $task_id) {
            $task['completed'] = $is_completed;
            break;
        }
    }

    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));

    if ($is_completed) {
        updateStreak();
    }

    return true;
}

function deleteTask(string $task_id): bool {
    $file = __DIR__ . '/tasks.txt';
    if (!file_exists($file)) return false;

    $tasks = json_decode(file_get_contents($file), true);
    $filtered = array_filter($tasks, fn($task) => $task['id'] !== $task_id);

    file_put_contents($file, json_encode(array_values($filtered), JSON_PRETTY_PRINT));
    return true;
}

function generateVerificationCode(): string {
    return strval(rand(100000, 999999));
}

function subscribeEmail(string $email): bool {
    $subsFile = __DIR__ . '/subscribers.txt';
    $pendingFile = __DIR__ . '/pending_subscriptions.txt';

    $subscribers = file_exists($subsFile) ? json_decode(file_get_contents($subsFile), true) : [];
    if (!is_array($subscribers)) $subscribers = [];

    $pending = file_exists($pendingFile) ? json_decode(file_get_contents($pendingFile), true) : [];
    if (!is_array($pending)) $pending = [];

    if (in_array($email, $subscribers) || isset($pending[$email])) {
        return false;
    }

    $code = generateVerificationCode();
    $pending[$email] = [
        "code" => $code,
        "timestamp" => time()
    ];

    file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

    $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/src/verify.php?email=" . urlencode($email) . "&code=" . $code;

    $subject = "Verify subscription to Task Planner";
    $headers = "From: no-reply@example.com\r\nContent-Type: text/html\r\n";
    $message = "<p>Click the link below to verify your subscription to Task Planner:</p>
                <p><a id='verification-link' href='$verification_link'>Verify Subscription</a></p>";

    return mail($email, $subject, $message, $headers);
}

function verifySubscription(string $email, string $code): bool {
    $subsFile = __DIR__ . '/subscribers.txt';
    $pendingFile = __DIR__ . '/pending_subscriptions.txt';

    $pending = file_exists($pendingFile) ? json_decode(file_get_contents($pendingFile), true) : [];
    if (!is_array($pending)) $pending = [];

    file_put_contents(__DIR__ . '/debug_verify.txt', json_encode([
        'email' => $email,
        'code' => $code,
        'pending' => $pending[$email] ?? 'not_found',
        'timestamp' => time()
    ], JSON_PRETTY_PRINT));

    if (!isset($pending[$email])) {
        return false;
    }

    if ($pending[$email]['code'] !== $code) {
        return false;
    }

    // Check if code is expired (24 hours)
    if (time() - $pending[$email]['timestamp'] > 86400) {
        unset($pending[$email]);
        file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));
        return false;
    }

    unset($pending[$email]);
    file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));

    $subscribers = file_exists($subsFile) ? json_decode(file_get_contents($subsFile), true) : [];
    if (!is_array($subscribers)) $subscribers = [];

    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        file_put_contents($subsFile, json_encode($subscribers, JSON_PRETTY_PRINT));
    }

    return true;
}

function unsubscribeEmail(string $email): bool {
    $subsFile = __DIR__ . '/subscribers.txt';
    if (!file_exists($subsFile)) return false;

    $subscribers = json_decode(file_get_contents($subsFile), true);
    if (!is_array($subscribers)) return false;

    $subscribers = array_filter($subscribers, fn($e) => $e !== $email);

    file_put_contents($subsFile, json_encode(array_values($subscribers), JSON_PRETTY_PRINT));
    return true;
}

function sendTaskReminders(): void {
    $subsFile = __DIR__ . '/subscribers.txt';
    $subscribers = file_exists($subsFile) ? json_decode(file_get_contents($subsFile), true) : [];
    if (!is_array($subscribers)) $subscribers = [];

    $tasks = getAllTasks();
    $pending = array_filter($tasks, fn($t) => !$t['completed']);

    foreach ($subscribers as $email) {
        sendTaskEmail($email, $pending);
    }
}

function sendTaskEmail(string $email, array $pending_tasks): bool {
    $subject = 'Task Planner - Pending Tasks Reminder';
    $headers = "From: no-reply@example.com\r\nContent-Type: text/html\r\n";

    $task_list = "";
    foreach ($pending_tasks as $task) {
        $task_list .= "<li>" . htmlspecialchars($task['name']) . "</li>";
    }

    $unsubscribe_link = "http://" . $_SERVER['HTTP_HOST'] . "/src/unsubscribe.php?email=" . urlencode($email);
    $message = "<h2>Pending Tasks Reminder</h2>
                <p>Here are the current pending tasks:</p>
                <ul>$task_list</ul>
                <p><a id='unsubscribe-link' href='$unsubscribe_link'>Unsubscribe from notifications</a></p>";

    return mail($email, $subject, $message, $headers);
}

function updateStreak(): void {
    $file = __DIR__ . '/streak.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [
        'last_completed' => '',
        'streak' => 0,
        'graph' => []
    ];

    $today = date('Y-m-d');
    $dayName = date('D');

    if (!isset($data['graph'][$dayName])) {
        $data['graph'][$dayName] = 0;
    }
    $data['graph'][$dayName] += 1;

    if ($data['last_completed'] === $today) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        return;
    }

    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if ($data['last_completed'] === $yesterday) {
        $data['streak'] += 1;
    } else {
        $data['streak'] = 1;
    }

    $data['last_completed'] = $today;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}