<?php
require_once 'functions.php';

$email = $_GET['email'] ?? '';
$success = false;

if ($email) {
    $success = unsubscribeEmail($email);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe from Task Updates</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .unsubscribe-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        #unsubscription-heading {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        .unsubscribe-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        .success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .failure {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .unsubscribe-button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .unsubscribe-button:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="unsubscribe-container">
        <h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>

        <?php if (!$email): ?>
            <div class="unsubscribe-message failure">
                ❌ Invalid unsubscribe link.
            </div>
        <?php elseif ($success): ?>
            <div class="unsubscribe-message success">
                ✅ <strong><?= htmlspecialchars($email) ?></strong> has been successfully unsubscribed from task updates.
            </div>
        <?php else: ?>
            <div class="unsubscribe-message failure">
                ❌ Unsubscription failed. Email may already be unsubscribed or invalid.
            </div>
        <?php endif; ?>

        <a href="index.php" class="unsubscribe-button">Return to Task Planner</a>
    </div>
</body>
</html>
