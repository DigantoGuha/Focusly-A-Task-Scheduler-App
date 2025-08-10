<?php
require_once 'functions.php';

$subscribersFile = __DIR__ . '/subscribers.txt';
$tasks = getAllTasks();


$pending_tasks = array_filter($tasks, fn($task) => !$task['completed']);

if (!file_exists($subscribersFile)) {
    exit;
}

$subscribers = json_decode(file_get_contents($subscribersFile), true);

foreach ($subscribers as $email) {
    sendTaskEmail($email, $pending_tasks);
}
