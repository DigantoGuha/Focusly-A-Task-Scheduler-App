<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if running in CLI (for testing)
if (php_sapi_name() === 'cli') {
    $_GET['email'] = $argv[1] ?? '';
    $_GET['code'] = $argv[2] ?? '';
}

require_once __DIR__.'/functions.php';

$email = $_GET['email'] ?? '';
$code = $_GET['code'] ?? '';
$success = false;

if ($email && $code) {
    $success = verifySubscription($email, $code);
}

// Log verification attempt
file_put_contents(__DIR__.'/verification_log.txt', 
    date('Y-m-d H:i:s')." - Email: $email, Code: $code, Success: ".($success ? 'Yes' : 'No')."\n", 
    FILE_APPEND
);

// Set proper content type
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Verification</title>
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
        .verify-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .verify-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        .verify-message {
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
        .verify-button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .verify-button:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <h1 class="verify-title">Subscription Verification</h1>
        
        <?php if ($success): ?>
            <div class="verify-message success">
                ✅ You have successfully verified your email subscription!
            </div>
            <p>You will now receive task reminders to <strong><?= htmlspecialchars($email) ?></strong></p>
        <?php else: ?>
            <div class="verify-message failure">
                ❌ Verification failed
            </div>
            <p>Possible reasons:</p>
            <ul style="text-align: left; margin: 1rem auto; max-width: 300px;">
                <li>The verification link has expired</li>
                <li>The email was already verified</li>
                <li>Invalid verification code</li>
            </ul>
        <?php endif; ?>

        <a href="index.php" class="verify-button">Return to Task Planner</a>
    </div>
</body>
</html>