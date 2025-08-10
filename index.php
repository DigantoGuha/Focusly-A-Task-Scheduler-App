<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task-name'])) {
        $task_name = trim($_POST['task-name']);
        if ($task_name !== '') {
            addTask($task_name);
            $success = 'Task added successfully!';
        }
    }

    if (isset($_POST['complete-task-id'])) {
        $task_id = $_POST['complete-task-id'];
        $is_completed = isset($_POST['task-status']) && $_POST['task-status'] === 'on';
        markTaskAsCompleted($task_id, $is_completed);
        updateStreak();
    }

    if (isset($_POST['delete-task-id'])) {
        deleteTask($_POST['delete-task-id']);
    }

    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            subscribeEmail($email);
            $success = 'Subscription link sent!';
        }
    }

    header("Location: index.php");
    exit;
}

$tasks = getAllTasks();
$completedCount = count(array_filter($tasks, fn($t) => $t['completed']));
$completionPercent = count($tasks) > 0 ? intval(($completedCount / count($tasks)) * 100) : 0;
$streakData = file_exists('streak.json') ? json_decode(file_get_contents('streak.json'), true) : ['streak' => 0, 'graph' => []];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Focusly</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">

    <!-- Theme Selector -->
    <div class="theme-selector">
        <select id="theme-select" onchange="changeTheme(this.value)">
            <option value="theme-light">Light</option>
            <option value="theme-dark">Dark</option>
            <option value="theme-sky">Sky</option>
            <option value="theme-lavender">Lavender</option>
        </select>
    </div>

    <!-- Greeting + Clock -->
    <div class="greeting-block">
        <p id="greeting-text"></p>
        <p id="clock-time"></p>
    </div>

    <!-- Stat Cards -->
    <div class="side-card left-card">
        <h4>Progress</h4>
        <p><?php echo $completedCount; ?> completed</p>
    </div>
    <div class="side-card right-card">
        <h4>Total Tasks</h4>
        <p><?php echo count($tasks); ?> total</p>
    </div>

    <!-- App Title -->
    <h2 class="app-title">🧠 Focusly</h2>
    <div class="motivation">Plan simply. Get things done beautifully with Focusly.</div>

    <!-- Summary & Filters -->
    <div id="task-summary">
        <p>Total: <span id="total-count"><?php echo count($tasks); ?></span> |
        Completed: <span id="completed-count"><?php echo $completedCount; ?></span></p>
    </div>

    <div id="task-filters">
        <button onclick="filterTasks('all')">All</button>
        <button onclick="filterTasks('completed')">Completed</button>
        <button onclick="filterTasks('pending')">Pending</button>
    </div>

    <!-- Add Task -->
    <div class="centered-form">
        <form method="POST">
            <input type="text" name="task-name" id="task-name" placeholder="Enter new task" required>
            <button type="submit" id="add-task">Add Task</button>
        </form>
    </div>

    <!-- Task List -->
    <ul class="tasks-list" id="tasks-list">
        <?php foreach ($tasks as $task): ?>
            <li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="complete-task-id" value="<?php echo $task['id']; ?>">
                    <input type="checkbox" name="task-status" class="task-status" <?php if ($task['completed']) echo 'checked'; ?> onchange="this.form.submit()">
                </form>
                <?php echo htmlspecialchars($task['name']); ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete-task-id" value="<?php echo $task['id']; ?>">
                    <button class="delete-task" type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (count($tasks) === 0): ?>
        <p class="no-tasks">You're all caught up. Add your first task above.</p>
    <?php endif; ?>

    <hr>

    <!-- Email Subscription -->
    <h2>Subscribe for Task Reminders</h2>
    <div class="centered-form">
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required />
            <button id="submit-email" type="submit">Subscribe</button>
        </form>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast">Action successful!</div>

    <!-- Daily Streak -->
    <div class="streak-container">
        <div class="fire">🔥</div>
        <div>Current Streak:</div>
        <strong><?php echo $streakData['streak']; ?> days</strong>
    </div>

    <!-- Productivity Graph -->
    <div class="productivity-graph">
        <h3>Last 7 Days Productivity</h3>
        <div class="bar-container">
            <?php
            $graph = $streakData['graph'] ?? [];
            $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $todayIndex = date('w');
            for ($i = 6; $i >= 0; $i--) {
                $day = $days[($todayIndex - $i + 7) % 7];
                $value = $graph[$day] ?? 0;
                echo "<div><div class='bar' style='height: " . ($value * 20) . "px;'></div><div class='bar-label'>$day</div></div>";
            }
            ?>
        </div>
    </div>

    <!-- Progress Ring -->
    <div class="progress-ring">
        <svg viewBox="0 0 36 36">
            <path class="circle-bg"
                  d="M18 2.0845
                     a 15.9155 15.9155 0 0 1 0 31.831
                     a 15.9155 15.9155 0 0 1 0 -31.831"/>
            <path class="circle"
                  stroke-dasharray="<?php echo $completionPercent; ?>, 100"
                  d="M18 2.0845
                     a 15.9155 15.9155 0 0 1 0 31.831
                     a 15.9155 15.9155 0 0 1 0 -31.831"/>
        </svg>
        <p class="ring-text"><?php echo $completionPercent; ?>%</p>
    </div>

    <!-- Footer -->
    <p class="history-hint">Reminders are sent hourly. You can unsubscribe at any time.</p>
    <div class="footer-wave"></div>
    <footer class="footer">
        <p>Focusly by Diganto Guha · 2025</p>
    </footer>

    <!-- Scripts -->
    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 2500);
        }

        function changeTheme(theme) {
            document.body.className = theme;
            localStorage.setItem('selectedTheme', theme);
        }

        function filterTasks(type) {
            const items = document.querySelectorAll('.task-item');
            items.forEach(item => {
                if (type === 'all') item.style.display = 'flex';
                else if (type === 'completed') item.style.display = item.classList.contains('completed') ? 'flex' : 'none';
                else if (type === 'pending') item.style.display = item.classList.contains('completed') ? 'none' : 'flex';
            });
        }

        function updateGreeting() {
            const hour = new Date().getHours();
            const greeting = hour < 12 ? "Good morning" : hour < 17 ? "Good afternoon" : "Good evening";
            document.getElementById("greeting-text").textContent = `${greeting}, Diganto 👋`;
            document.getElementById("clock-time").textContent = new Date().toLocaleTimeString();
        }

        window.onload = function () {
            const savedTheme = localStorage.getItem('selectedTheme');
            if (savedTheme) {
                document.body.className = savedTheme;
                document.getElementById('theme-select').value = savedTheme;
            }
            updateGreeting();
            setInterval(updateGreeting, 1000);

            <?php if (!empty($success)) echo "showToast('$success');"; ?>
        };
    </script>
</body>
</html>
