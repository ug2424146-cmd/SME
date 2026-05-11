<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/controllers/TaskController.php";

require_auth();
$user = current_user();
$controller = new TaskController();
$smartSuggestion = null;

if ($user["role"] === "manager" || $user["role"] === "admin") {
    $smartQuery = $mysqli->query(
        "SELECT u.id, u.name, COUNT(t.id) current_load
         FROM users u
         INNER JOIN roles r ON r.id = u.role_id AND r.role_name = 'employee'
         LEFT JOIN tasks t ON t.assigned_to = u.id AND t.status IN ('pending', 'in_progress')
         GROUP BY u.id, u.name
         ORDER BY current_load ASC, u.name ASC
         LIMIT 1"
    );
    $smartSuggestion = $smartQuery->fetch_assoc() ?: null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        flash("error", "Invalid CSRF token.");
        header("Location: " . url("tasks.php"));
        exit;
    }

    $action = (string) ($_POST["action"] ?? "");
    if ($action === "create_task") {
        $controller->createTask($_POST, $user);
    } elseif ($action === "update_status") {
        $controller->updateTaskStatus($_POST, $user);
    } elseif ($action === "add_comment") {
        $controller->addComment($_POST, $user);
    } elseif ($action === "upload_attachment") {
        $controller->uploadAttachment($_FILES, $_POST, $user);
    } else {
        flash("error", "Unsupported action.");
    }

    header("Location: " . url("tasks.php"));
    exit;
}

$employees = [];
$skills = [];
if ($user["role"] === "manager" || $user["role"] === "admin") {
    $employeesResult = $mysqli->query(
        "SELECT u.id, u.name
         FROM users u
         INNER JOIN roles r ON r.id = u.role_id
         WHERE r.role_name = 'employee'
         ORDER BY u.name ASC"
    );
    while ($row = $employeesResult->fetch_assoc()) {
        $employees[] = $row;
    }
    $skills = $mysqli->query("SELECT id, skill_name FROM skills ORDER BY skill_name ASC")->fetch_all(MYSQLI_ASSOC);
}

$tasks = [];
if ($user["role"] === "employee") {
    $stmt = $mysqli->prepare(
        "SELECT t.id, t.title, t.description, t.status, t.priority, t.deadline, u.name AS assignee, s.skill_name AS required_skill
         FROM tasks t
         INNER JOIN users u ON u.id = t.assigned_to
         LEFT JOIN skills s ON s.id = t.required_skill_id
         WHERE t.assigned_to = ?
         ORDER BY t.created_at DESC
         LIMIT 100"
    );
    $stmt->bind_param("i", $user["id"]);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query(
        "SELECT t.id, t.title, t.description, t.status, t.priority, t.deadline, u.name AS assignee, s.skill_name AS required_skill
         FROM tasks t
         INNER JOIN users u ON u.id = t.assigned_to
         LEFT JOIN skills s ON s.id = t.required_skill_id
         ORDER BY t.created_at DESC
         LIMIT 100"
    );
}

while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

if (isset($stmt)) {
    $stmt->close();
}

$commentsByTask = [];
$attachmentsByTask = [];
$historyByTask = [];
$commentResult = $mysqli->query(
    "SELECT tc.task_id, tc.comment, tc.created_at, u.name
     FROM task_comments tc INNER JOIN users u ON u.id = tc.user_id
     ORDER BY tc.created_at DESC LIMIT 300"
);
while ($row = $commentResult->fetch_assoc()) {
    $commentsByTask[(int) $row["task_id"]][] = $row;
}
$attachmentResult = $mysqli->query(
    "SELECT task_id, file_name, file_path, created_at FROM task_attachments ORDER BY created_at DESC LIMIT 300"
);
while ($row = $attachmentResult->fetch_assoc()) {
    $attachmentsByTask[(int) $row["task_id"]][] = $row;
}
$historyResult = $mysqli->query(
    "SELECT th.task_id, th.action, th.details, th.created_at, u.name
     FROM task_history th LEFT JOIN users u ON u.id = th.user_id
     ORDER BY th.created_at DESC LIMIT 500"
);
while ($row = $historyResult->fetch_assoc()) {
    $historyByTask[(int) $row["task_id"]][] = $row;
}

$successMessage = get_flash("success");
$errorMessage = get_flash("error");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tasks - SME Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('../assets/css/style.css')) ?>?v=<?= time() ?>" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important; }
        .card { border-radius: 12px !important; box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important; border: none !important; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; font-weight: 600 !important; border-radius: 12px 12px 0 0 !important; padding: 1rem 1.5rem !important; }
        .btn { border-radius: 8px !important; font-weight: 600 !important; padding: 0.75rem 1.5rem !important; transition: all 0.3s ease !important; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; border: none !important; }
        .btn-success { background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important; border: none !important; }
        .btn-info { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important; border: none !important; }
        .btn-warning { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important; border: none !important; }
        .btn:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important; }
        .sidebar { background: linear-gradient(180deg, #667eea 0%, #764ba2 100%) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important; }
        .sidebar-brand { color: white !important; font-weight: 700 !important; }
        .sidebar-link { color: rgba(255,255,255,0.85) !important; }
        .sidebar-link:hover { background: rgba(255,255,255,0.15) !important; color: white !important; }
        .sidebar-link.active { background: rgba(255,255,255,0.25) !important; color: white !important; }
        .main-content { 
            margin-left: 280px !important; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important; 
            min-height: 100vh !important; 
            padding-top: 0 !important;
        }
        .main-content > main {
            padding-top: 2rem !important;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            padding: 2.5rem 0 !important;
            border-radius: 12px !important;
            margin-bottom: 2rem !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .page-header h1 { font-weight: 800 !important; font-size: 2.25rem !important; margin: 0 !important; }
        .table thead th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; }
    </style>
</head>
<body class="bg-light">
<div class="sidebar-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-brand">
                🏢 SME Platform
            </a>
        </div>
        
        <div class="sidebar-user">
            <div class="sidebar-user-name"><?= e($user["name"]) ?></div>
            <div class="sidebar-user-role"><?= e($user["role"]) ?></div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="sidebar-section">Main Menu</div>
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="<?= e(url('tasks.php')) ?>" class="sidebar-link active">
                <span class="sidebar-link-icon">📋</span>
                <span>Tasks</span>
            </a>
            <a href="<?= e(url('skills.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">🎯</span>
                <span>Skills</span>
            </a>
            <a href="<?= e(url('performance.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">⭐</span>
                <span>Performance</span>
            </a>
            
            <?php if ($user["role"] !== "employee"): ?>
                <div class="sidebar-section mt-4">Management</div>
                <a href="<?= e(url('team_progress.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">👥</span>
                    <span>Team Progress</span>
                </a>
            <?php endif; ?>
            
            <div class="sidebar-section mt-4">Reports</div>
            <a href="<?= e(url('reports_center.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📈</span>
                <span>Reports</span>
            </a>
            
            <?php if ($user["role"] === "admin"): ?>
                <div class="sidebar-section mt-4">Administration</div>
                <a href="<?= e(url('users.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">👤</span>
                    <span>Users</span>
                </a>
                <a href="<?= e(url('departments.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">🏢</span>
                    <span>Departments</span>
                </a>
                <a href="<?= e(url('settings.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">⚙️</span>
                    <span>Settings</span>
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <a href="<?= e(url('notifications_view.php')) ?>" class="sidebar-footer-link">
                <span>🔔</span>
                <span>Notifications</span>
            </a>
            <a href="<?= e(url('profile.php')) ?>" class="sidebar-footer-link">
                <span>👤</span>
                <span>My Profile</span>
            </a>
            <a href="<?= e(url('logout.php')) ?>" class="sidebar-footer-link">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        ☰ Menu
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1>Task Management</h1>
                <p>Manage and track all tasks</p>
            </div>
        </div>

        <main class="container py-4">

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= e($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($user["role"] === "manager" || $user["role"] === "admin"): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Create Task</h2>
                <form method="post" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="create_task">

                    <div class="col-md-6">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control" id="title" name="title" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="assigned_to">Assign To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Select employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?= (int) $employee["id"] ?>"><?= e($employee["name"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($smartSuggestion): ?>
                            <small class="text-muted">
                                Suggested: <?= e((string) $smartSuggestion["name"]) ?> (workload: <?= e((string) $smartSuggestion["current_load"]) ?>)
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="required_skill_id">Required Skill</label>
                        <select class="form-select" id="required_skill_id" name="required_skill_id">
                            <option value="">None</option>
                            <?php foreach ($skills as $skill): ?>
                                <option value="<?= (int) $skill["id"] ?>"><?= e((string) $skill["skill_name"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">Auto Assign</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="auto_assign" value="1" id="auto_assign">
                            <label class="form-check-label" for="auto_assign">Use AI matching</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="priority">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="deadline">Deadline</label>
                        <input class="form-control" id="deadline" name="deadline" type="date">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5">Task List</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Assignee</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($tasks) === 0): ?>
                        <tr><td colspan="6" class="text-muted">No tasks available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td>
                                    <strong><?= e((string) $task["title"]) ?></strong><br>
                                    <small class="text-muted"><?= e((string) ($task["description"] ?? "")) ?></small>
                                </td>
                                <td><?= e((string) $task["assignee"]) ?></td>
                                <td><?= e((string) $task["priority"]) ?></td>
                                <td><?= e((string) $task["status"]) ?></td>
                                <td><?= e((string) ($task["deadline"] ?? "-")) ?></td>
                                <td>
                                    <?php if ($user["role"] === "employee" || $user["role"] === "manager" || $user["role"] === "admin"): ?>
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                            <select class="form-select form-select-sm" name="status">
                                                <option value="pending" <?= $task["status"] === "pending" ? "selected" : "" ?>>Pending</option>
                                                <option value="in_progress" <?= $task["status"] === "in_progress" ? "selected" : "" ?>>In Progress</option>
                                                <option value="completed" <?= $task["status"] === "completed" ? "selected" : "" ?>>Completed</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                        </form>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-2">Required skill: <?= e((string) ($task["required_skill"] ?? "-")) ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" class="bg-light">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <strong>Comments</strong>
                                            <form method="post" class="mt-2 d-flex gap-2">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="add_comment">
                                                <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                                <input class="form-control form-control-sm" name="comment" placeholder="Write comment">
                                                <button class="btn btn-sm btn-outline-primary">Post</button>
                                            </form>
                                            <?php foreach (($commentsByTask[(int) $task["id"]] ?? []) as $comment): ?>
                                                <div class="small mt-2"><strong><?= e((string) $comment["name"]) ?>:</strong> <?= e((string) $comment["comment"]) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Attachments</strong>
                                            <form method="post" enctype="multipart/form-data" class="mt-2 d-flex gap-2">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="upload_attachment">
                                                <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                                <input class="form-control form-control-sm" type="file" name="attachment" required>
                                                <button class="btn btn-sm btn-outline-secondary">Upload</button>
                                            </form>
                                            <?php foreach (($attachmentsByTask[(int) $task["id"]] ?? []) as $attachment): ?>
                                                <div class="small mt-2"><a href="<?= e(url((string) $attachment["file_path"])) ?>" target="_blank"><?= e((string) $attachment["file_name"]) ?></a></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>History</strong>
                                            <?php foreach (($historyByTask[(int) $task["id"]] ?? []) as $h): ?>
                                                <div class="small mt-2"><?= e((string) $h["created_at"]) ?> - <?= e((string) $h["action"]) ?><?= $h["name"] ? " by " . e((string) $h["name"]) : "" ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}
</script>
</body>
</html>
