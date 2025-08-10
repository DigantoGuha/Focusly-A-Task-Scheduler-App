# Focusly – Task Management System

Focusly is a sleek, lightweight, and feature-rich *PHP-based Task Manager* designed for productivity and simplicity.  
It helps users create, track, and manage tasks efficiently, with email reminders, daily streaks, and a visual productivity dashboard.

---

## 🚀 Features
- *Task Management* – Add, mark complete, and delete tasks easily.
- *Email Reminders* – Sends automated hourly reminders for pending tasks.
- *Subscription Verification* – Secure email verification before subscribing to reminders.
- *Daily Streak Tracker* – Motivates you by tracking consecutive days of task completion.
- *Productivity Graph* – Visual weekly productivity stats.
- *Dark & Light Theme Friendly* – Minimal and responsive design.

---

## 🛠️ Tech Stack
- *Frontend:* HTML5, CSS3, JavaScript
- *Backend:* PHP (Pure PHP, no frameworks)
- *Data Storage:* JSON files (tasks.txt, subscribers.txt, pending_subscriptions.txt, streak.json)
- *Email Service:* PHP mail() function (tested with Mailpit for local development)

---

## 📂 Project Structure
Focusly/
│
├── src/
│   ├── index.php                # Main dashboard UI
│   ├── functions.php            # Core backend logic
│   ├── verify.php               # Subscription verification
│   ├── unsubscribe.php          # Unsubscription handler
│   ├── tasks.txt                # Stores tasks in JSON
│   ├── subscribers.txt          # Stores verified subscribers
│   ├── pending_subscriptions.txt# Stores pending email verifications
│   ├── streak.json              # Stores streak and productivity data
│   └── style.css                # Styling
│
└── README.md
## ⚙️ Installation & Setup

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/<DigantoGuha>/focusly.git
cd focusly/src

[13:02, 10/8/2025] Diganto: Email Reminder Flow
	1.	User subscribes with email.
	2.	A verification link is sent.
	3.	After verification, the user receives hourly reminders until tasks are completed.
[13:02, 10/8/2025] Diganto: Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you’d like to change
