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

$pageTitle = 'Task Management';
$pageSubtitle = 'Create, assign, and track tasks efficiently';
$currentPage = 'tasks';

ob_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>?v=<?= time() ?>" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --light: #f8fafc;
            --dark: #1e293b;
        }

        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        }

        .card { 
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07) !important;
            border: 1px solid rgba(0,0,0,0.05) !important;
            transition: all 0.3s ease !important;
        }

        .card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

        .card-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            font-weight: 700 !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem !important;
            border: none !important;
        }

        .btn { 
            border-radius: 8px !important;
            font-weight: 700 !important;
            padding: 0.85rem 1.75rem !important;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: none !important;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
        }
        
        .btn-primary:hover { 
            transform: translateY(-3px) !important; 
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5) !important; 
        }

        .btn-primary:active {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
        }

        .btn-success { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; 
            color: white !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        }
        
        .btn-success:hover { 
            transform: translateY(-3px) !important; 
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.5) !important; 
        }

        .btn-success:active {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
        }

        .btn-outline-primary {
            color: #667eea !important;
            border: 2px solid #667eea !important;
            background: transparent !important;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .btn-outline-primary:hover {
            background: #667eea !important;
            color: white !important;
            transform: translateY(-3px) !important;
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4) !important;
        }

        .btn-outline-primary:active {
            transform: translateY(-1px) !important;
        }

        .btn-sm { 
            padding: 0.6rem 1.25rem !important; 
            font-size: 0.85rem !important; 
            font-weight: 600 !important;
        }

        .sidebar { 
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
        }

        .sidebar-brand { color: white !important; font-weight: 700 !important; }
        .sidebar-link { color: rgba(255,255,255,0.85) !important; transition: all 0.3s ease !important; }
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
            padding: 3rem 0 !important;
            border-radius: 0 !important;
            margin-bottom: 2.5rem !important;
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.25) !important;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .page-header h1 { 
            font-weight: 800 !important; 
            font-size: 2.5rem !important; 
            margin: 0 !important;
            letter-spacing: -0.5px;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-top: 0.75rem;
            margin-bottom: 0;
            font-weight: 500;
        }

        /* Task Card Styles */
        .task-card {
            background: white !important;
            border-left: 5px solid #667eea;
            border-radius: 12px !important;
            margin-bottom: 1.75rem;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .task-card:hover {
            box-shadow: 0 16px 32px rgba(0,0,0,0.12) !important;
            transform: translateY(-6px);
        }

        .task-card.priority-high { border-left-color: #ef4444; }
        .task-card.priority-medium { border-left-color: #f59e0b; }
        .task-card.priority-low { border-left-color: #10b981; }

        .task-card-header {
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: start;
            border-bottom: 1.5px solid rgba(0,0,0,0.05);
            background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(248,250,252,0.5) 100%);
        }

        .task-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .task-meta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .badge-status {
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            transition: all 0.3s ease;
        }

        .badge-status:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .badge-status.pending { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #7f1d1d; border: 1px solid #fca5a5; }
        .badge-status.in-progress { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #0c4a6e; border: 1px solid #93c5fd; }
        .badge-status.completed { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #166534; border: 1px solid #86efac; }

        .badge-priority {
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            transition: all 0.3s ease;
        }

        .badge-priority:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .badge-priority.high { background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%); color: #7f1d1d; border: 1px solid #f87171; }
        .badge-priority.medium { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); color: #92400e; border: 1px solid #fb923c; }
        .badge-priority.low { background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%); color: #166534; border: 1px solid #4ade80; }

        .task-description {
            color: #475569;
            font-size: 0.95rem;
            margin: 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            line-height: 1.6;
        }

        .task-footer {
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1.5px solid rgba(0,0,0,0.05);
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .task-assignee {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: #1e293b;
            font-weight: 600;
        }

        .task-deadline {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .task-deadline.urgent { 
            color: #ef4444; 
            font-weight: 700;
            background: rgba(239, 68, 68, 0.08);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .task-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* Filter & Controls */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            background-color: #fff;
            transition: all 0.3s ease;
            height: auto;
        }

        .form-control::placeholder {
            color: #cbd5e1;
            font-style: italic;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15), inset 0 1px 2px rgba(0,0,0,0.05);
            background-color: #fff;
        }

        .form-control:hover:not(:focus), .form-select:hover:not(:focus) {
            border-color: #cbd5e1;
        }

        .form-label {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .form-label .required {
            color: #ef4444;
            font-weight: 700;
            margin-left: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        /* Modal & Comments */
        .comments-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .comments-section h5 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 1.25rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .comment-item {
            background: white;
            padding: 1.125rem;
            border-radius: 8px;
            margin-bottom: 0.875rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .comment-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            transform: translateX(4px);
        }

        .comment-author {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .comment-time {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-left: 0.75rem;
            font-weight: 500;
        }

        .comment-text {
            color: #475569;
            margin-top: 0.75rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Create Task Form */
        .create-task-form {
            background: white;
            padding: 2.5rem;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2.5rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .create-task-form .form-group {
            margin-bottom: 1.75rem;
        }

        .create-task-form .form-group label {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.85rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section {
            background: linear-gradient(135deg, rgba(102,126,234,0.02) 0%, rgba(118,75,162,0.02) 100%);
            padding: 1.75rem;
            border-radius: 10px;
            margin-bottom: 1.75rem;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .form-section-title {
            font-weight: 700;
            color: #667eea;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.2);
        }

        .input-group {
            display: flex;
            gap: 0.5rem;
            align-items: stretch;
        }

        .input-group .form-control {
            flex: 1;
            border-radius: 8px 0 0 8px;
        }

        .input-group .btn {
            border-radius: 0 8px 8px 0;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .smart-suggestion {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 5px solid #3b82f6;
            padding: 1.25rem;
            border-radius: 8px;
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: #0c4a6e;
            border: 1px solid #bfdbfe;
            animation: slideInDown 0.4s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .smart-suggestion strong {
            color: #0c4a6e;
            font-weight: 700;
        }

        .smart-suggestion::before {
            content: '💡 ';
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                height: 100vh;
                z-index: 999;
                transition: left 0.3s ease;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 998;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .sidebar-toggle {
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1000;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            }

            .sidebar-toggle:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
            }

            .page-header {
                padding: 2rem 0 !important;
                margin-bottom: 1.5rem !important;
            }

            .page-header h1 {
                font-size: 1.85rem !important;
            }

            .page-header p {
                font-size: 0.95rem;
            }

            .task-card-header {
                flex-direction: column;
            }

            .task-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .filters-section {
                flex-direction: column;
            }

            .create-task-form {
                padding: 1.75rem;
            }

            .form-section {
                padding: 1.25rem;
            }

            .row > [class*='col-'] {
                margin-bottom: 1rem;
            }

            .task-meta {
                gap: 0.5rem;
            }

            .btn {
                padding: 0.75rem 1.25rem !important;
                font-size: 0.9rem !important;
            }

            input.form-control, select.form-select {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .page-header h1 {
                font-size: 1.5rem !important;
            }

            .task-title {
                font-size: 1.1rem;
            }

            .create-task-form {
                padding: 1.5rem;
            }

            .comment-item {
                padding: 0.875rem;
            }

            .task-card-header,
            .task-footer,
            .task-description {
                padding: 1rem;
            }

            .btn-sm {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.75rem !important;
            }
        }
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
            <div class="sidebar-section d-flex align-items-center">
                <span class="me-2">☰</span>
                <span>Main Menu</span>
            </div>
            <div class="main-menu-row">
                <a href="<?= e(url('dashboard.php')) ?>" class="main-menu-item">
                    <span class="icon">📊</span>
                    <span class="label">Dashboard</span>
                </a>
                <a href="<?= e(url('tasks.php')) ?>" class="main-menu-item active">
                    <span class="icon">📋</span>
                    <span class="label">Tasks</span>
                </a>
                <a href="<?= e(url('skills.php')) ?>" class="main-menu-item">
                    <span class="icon">🎯</span>
                    <span class="label">Skills</span>
                </a>
                <a href="<?= e(url('performance.php')) ?>" class="main-menu-item">
                    <span class="icon">⭐</span>
                    <span class="label">Performance</span>
                </a>
            </div>
            
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
                <h1><?= e($pageTitle) ?></h1>
                <p><?= e($pageSubtitle) ?></p>
            </div>
        </div>

        <main class="container py-4">
            <!-- Alerts -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✓ Success!</strong> <?= e($successMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>✕ Error!</strong> <?= e($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Create Task Form -->
            <?php if ($user["role"] === "manager" || $user["role"] === "admin"): ?>
                <div class="create-task-form">
                    <h2 style="font-weight: 800; color: #1e293b; margin-bottom: 2rem; font-size: 1.75rem; letter-spacing: -0.5px;">
                        ✨ Create New Task
                    </h2>
                    <form method="post" id="create-task-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="create_task">

                        <!-- Task Basics Section -->
                        <div class="form-section">
                            <div class="form-section-title">📝 Task Details</div>
                            <div class="row">
                                <div class="col-md-8 form-group">
                                    <label class="form-label" for="title">Task Title <span class="required">*</span></label>
                                    <input class="form-control" id="title" name="title" placeholder="Enter a clear, descriptive task title" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="form-label" for="priority">Priority Level</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="low">🟢 Low</option>
                                        <option value="medium" selected>🟡 Medium</option>
                                        <option value="high">🔴 High</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Section -->
                        <div class="form-section" id="assignment-section" data-skill-filter-url="<?= e(url('api/skills_employees.php')) ?>">
                            <div class="form-section-title">👤 Assignment</div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label" for="assigned_to">Assign To <span class="required">*</span></label>
                                    <select class="form-select" id="assigned_to" name="assigned_to" required>
                                        <option value="">Select an employee</option>
                                        <?php foreach ($employees as $employee): ?>
                                            <option value="<?= (int) $employee["id"] ?>"><?= e($employee["name"]) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($smartSuggestion): ?>
                                        <div class="smart-suggestion">
                                            <strong>Suggested:</strong> <?= e((string) $smartSuggestion["name"]) ?> (current workload: <?= e((string) $smartSuggestion["current_load"]) ?> tasks)
                                        </div>
                                    <?php endif; ?>
                                    <div id="skill-filter-info" style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: #dbeafe; border: 1px solid #bfdbfe; border-radius: 6px; color: #0c4a6e; font-size: 0.9rem; font-weight: 500;">
                                        <span id="skill-filter-text"></span>
                                        <button type="button" class="btn btn-link btn-sm" id="clear-filter-btn" style="margin-left: 0.5rem; padding: 0; color: #0c4a6e; text-decoration: underline;">Clear filter</button>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label" for="required_skill_id">🎯 Required Skill</label>
                                    <select class="form-select" id="required_skill_id" name="required_skill_id">
                                        <option value="">No specific skill required</option>
                                        <?php foreach ($skills as $skill): ?>
                                            <option value="<?= (int) $skill["id"] ?>"><?= e((string) $skill["skill_name"]) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Section -->
                        <div class="form-section">
                            <div class="form-section-title">📅 Schedule & Options</div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label" for="deadline">Deadline</label>
                                    <input class="form-control" id="deadline" name="deadline" type="date">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label d-block">⚙️ Auto Assignment</label>
                                    <div class="form-check" style="margin-top: 0.5rem;">
                                        <input class="form-check-input" type="checkbox" name="auto_assign" value="1" id="auto_assign">
                                        <label class="form-check-label" for="auto_assign" style="color: #475569; font-weight: 500;">Use AI skill matching</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="form-section">
                            <div class="form-section-title">📄 Description</div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <textarea class="form-control" id="description" name="description" placeholder="Enter detailed task description, requirements, and any important notes..." rows="4" style="resize: vertical;"></textarea>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button class="btn btn-primary" type="submit" style="flex: 1; justify-content: center;">
                                ➕ Create Task
                            </button>
                        </div>
                    </form>
                </div>
                <script>
                (function() {
                    const assignmentSection = document.getElementById('assignment-section');
                    const createTaskForm = document.getElementById('create-task-form');
                    if (!assignmentSection || !createTaskForm) return;

                    const requiredSkillSelect = document.getElementById('required_skill_id');
                    const assignedToSelect = document.getElementById('assigned_to');
                    const filterInfoDiv = document.getElementById('skill-filter-info');
                    const filterTextSpan = document.getElementById('skill-filter-text');
                    const clearFilterBtn = document.getElementById('clear-filter-btn');
                    const skillFilterUrl = assignmentSection.dataset.skillFilterUrl;

                    const allEmployeeOptions = Array.from(assignedToSelect.querySelectorAll('option:not([value=""])'));
                    const employeesBySkill = {};
                    let currentFilteredEmployeeIds = new Set();

                    async function filterEmployeesBySkill(skillId) {
                        if (!skillId) {
                            showAllEmployees();
                            filterInfoDiv.style.display = 'none';
                            currentFilteredEmployeeIds.clear();
                            return;
                        }

                        try {
                            const response = await fetch(skillFilterUrl + '?skill_id=' + skillId);
                            const data = await response.json();

                            if (!data.ok) {
                                console.error('Error fetching employees:', data.message);
                                return;
                            }

                            employeesBySkill[skillId] = data.employees || [];
                            const filteredEmployeeIds = new Set(data.employees.map(e => String(e.id)));
                            currentFilteredEmployeeIds = filteredEmployeeIds;

                            // Clear current options (except the placeholder)
                            assignedToSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());

                            if (filteredEmployeeIds.size === 0) {
                                const opt = document.createElement('option');
                                opt.value = '';
                                opt.textContent = 'No employees with this skill';
                                assignedToSelect.appendChild(opt);
                                filterInfoDiv.style.display = 'block';
                                filterTextSpan.textContent = 'No employees have this skill.';
                                return;
                            }

                            // Add filtered options in order of proficiency
                            const skillName = requiredSkillSelect.options[requiredSkillSelect.selectedIndex].text;
                            const proficiencyOrder = { 'expert': 0, 'intermediate': 1, 'beginner': 2 };
                            
                            data.employees.sort((a, b) => {
                                const profA = proficiencyOrder[a.proficiency_level] || 999;
                                const profB = proficiencyOrder[b.proficiency_level] || 999;
                                if (profA !== profB) return profA - profB;
                                return a.name.localeCompare(b.name);
                            });

                            data.employees.forEach(emp => {
                                const opt = document.createElement('option');
                                opt.value = emp.id;
                                const profBadge = emp.proficiency_level ? ' (' + emp.proficiency_level.charAt(0).toUpperCase() + emp.proficiency_level.slice(1) + ')' : '';
                                opt.textContent = emp.name + profBadge;
                                assignedToSelect.appendChild(opt);
                            });

                            filterInfoDiv.style.display = 'block';
                            filterTextSpan.textContent = data.employees.length + ' employee(s) with "' + skillName + '" skill';
                        } catch (err) {
                            console.error('Error filtering employees:', err);
                        }
                    }

                    function showAllEmployees() {
                        assignedToSelect.querySelectorAll('option:not([value=""])').forEach(opt => opt.remove());
                        allEmployeeOptions.forEach(opt => {
                            assignedToSelect.appendChild(opt.cloneNode(true));
                        });
                    }

                    requiredSkillSelect.addEventListener('change', function() {
                        const skillId = this.value;
                        filterEmployeesBySkill(skillId);
                    });

                    clearFilterBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        requiredSkillSelect.value = '';
                        showAllEmployees();
                        filterInfoDiv.style.display = 'none';
                    });

                    // Form validation before submission
                    createTaskForm.addEventListener('submit', function(e) {
                        const requiredSkillId = requiredSkillSelect.value;
                        const selectedEmployeeId = assignedToSelect.value;

                        // If a required skill is selected, verify the employee has that skill
                        if (requiredSkillId && currentFilteredEmployeeIds.size > 0) {
                            if (!currentFilteredEmployeeIds.has(selectedEmployeeId)) {
                                e.preventDefault();
                                alert('⚠️ Error: The selected employee does not have the required skill.\n\nPlease select an employee from the filtered list.');
                                assignedToSelect.focus();
                                return false;
                            }
                        }

                        // If no employees available for selected skill, prevent submission
                        if (requiredSkillId && currentFilteredEmployeeIds.size === 0) {
                            e.preventDefault();
                            alert('⚠️ Error: No employees have the required skill.');
                            return false;
                        }
                    });
                })();
                </script>
            <?php endif; ?>

            <!-- Task List Section -->
            <div style="margin-bottom: 2rem;">
                <h2 style="font-weight: 700; color: #1e293b; margin-bottom: 1.5rem; font-size: 1.5rem;">
                    📋 Task Overview
                </h2>

                <?php if (count($tasks) === 0): ?>
                    <div class="card" style="background: linear-gradient(135deg, #f0f4ff 0%, #e9f0ff 100%); border: 2px dashed #667eea; text-align: center; padding: 3rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                        <h3 style="color: #667eea; font-weight: 700; margin-bottom: 0.5rem;">No Tasks Available</h3>
                        <p style="color: #64748b; margin: 0;">
                            <?php if ($user["role"] === "employee"): ?>
                                You don't have any assigned tasks yet. Check back soon!
                            <?php else: ?>
                                Create a new task to get started with task management.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($tasks as $task): 
                        $isUrgent = !empty($task["deadline"]) && strtotime($task["deadline"]) < strtotime("+7 days");
                        $statusClass = str_replace('_', '-', $task["status"]);
                        $priorityClass = $task["priority"];
                    ?>
                        <div class="task-card priority-<?= e($priorityClass) ?>">
                            <!-- Task Header -->
                            <div class="task-card-header">
                                <div style="flex: 1;">
                                    <h3 class="task-title"><?= e((string) $task["title"]) ?></h3>
                                    <div class="task-meta">
                                        <span class="badge-status <?= e($statusClass) ?>"><?= e(ucfirst(str_replace('_', ' ', (string) $task["status"]))) ?></span>
                                        <span class="badge-priority <?= e($priorityClass) ?>">
                                            <?php 
                                                if ($task["priority"] === "high") echo "🔴 High";
                                                elseif ($task["priority"] === "medium") echo "🟡 Medium";
                                                else echo "🟢 Low";
                                            ?>
                                        </span>
                                        <?php if (!empty($task["required_skill"])): ?>
                                            <span style="background: #e0e7ff; color: #3730a3; padding: 0.35rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                                🎯 <?= e((string) $task["required_skill"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Task Description -->
                            <?php if (!empty($task["description"])): ?>
                                <p class="task-description"><?= e((string) $task["description"]) ?></p>
                            <?php endif; ?>

                            <!-- Task Footer -->
                            <div class="task-footer">
                                <div>
                                    <div class="task-assignee">
                                        👤 <strong><?= e((string) $task["assignee"]) ?></strong>
                                    </div>
                                    <?php if (!empty($task["deadline"])): ?>
                                        <div class="task-deadline <?= $isUrgent ? "urgent" : "" ?>">
                                            📅 <?= date("M d, Y", strtotime((string) $task["deadline"])) ?>
                                            <?php if ($isUrgent): ?>
                                                <span style="color: #ef4444; font-weight: 700;"> • Due soon!</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="task-actions">
                                    <?php if ($user["role"] === "employee" || $user["role"] === "manager" || $user["role"] === "admin"): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                            <select class="form-select form-select-sm" name="status" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                                <option value="pending" <?= $task["status"] === "pending" ? "selected" : "" ?>>Pending</option>
                                                <option value="in_progress" <?= $task["status"] === "in_progress" ? "selected" : "" ?>>In Progress</option>
                                                <option value="completed" <?= $task["status"] === "completed" ? "selected" : "" ?>>Completed</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick="toggleTaskDetails(<?= (int) $task['id'] ?>)">
                                        📝 Details
                                    </button>
                                </div>
                            </div>

                            <!-- Collapsible Details Section -->
                            <div id="task-details-<?= (int) $task['id'] ?>" style="display: none; padding: 1.5rem; border-top: 1px solid rgba(0,0,0,0.05); background: #f8fafc;">
                                <div class="row">
                                    <!-- Comments -->
                                    <div class="col-md-6">
                                        <div class="comments-section">
                                            <h5 style="font-weight: 700; color: #1e293b; margin-bottom: 1rem;">💬 Comments</h5>
                                            <form method="post" class="mb-2">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="add_comment">
                                                <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                                <div class="input-group">
                                                    <input class="form-control" name="comment" placeholder="Add a comment..." required>
                                                    <button class="btn btn-primary" type="submit">Send</button>
                                                </div>
                                            </form>
                                            <div>
                                                <?php foreach (($commentsByTask[(int) $task["id"]] ?? []) as $comment): ?>
                                                    <div class="comment-item">
                                                        <div>
                                                            <span class="comment-author"><?= e((string) $comment["name"]) ?></span>
                                                            <span class="comment-time"><?= date("M d, h:i A", strtotime((string) $comment["created_at"])) ?></span>
                                                        </div>
                                                        <p class="comment-text"><?= e((string) $comment["comment"]) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attachments & History -->
                                    <div class="col-md-6">
                                        <!-- Attachments -->
                                        <div class="comments-section">
                                            <h5 style="font-weight: 700; color: #1e293b; margin-bottom: 1rem;">📎 Attachments</h5>
                                            <form method="post" enctype="multipart/form-data" class="mb-2">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <input type="hidden" name="action" value="upload_attachment">
                                                <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                                <div class="input-group">
                                                    <input class="form-control" type="file" name="attachment" required>
                                                    <button class="btn btn-primary" type="submit">Upload</button>
                                                </div>
                                            </form>
                                            <div>
                                                <?php foreach (($attachmentsByTask[(int) $task["id"]] ?? []) as $attachment): ?>
                                                    <div style="padding: 0.75rem; background: white; border-radius: 6px; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                                        <span style="font-size: 1.25rem;">📄</span>
                                                        <a href="<?= e(url((string) $attachment["file_path"])) ?>" target="_blank" style="color: #667eea; text-decoration: none; flex: 1; font-weight: 500;">
                                                            <?= e((string) $attachment["file_name"]) ?>
                                                        </a>
                                                        <span style="font-size: 0.8rem; color: #94a3b8;"><?= date("M d", strtotime((string) $attachment["created_at"])) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <!-- History -->
                                        <div class="comments-section" style="margin-top: 1rem;">
                                            <h5 style="font-weight: 700; color: #1e293b; margin-bottom: 1rem;">📜 Activity History</h5>
                                            <div style="max-height: 250px; overflow-y: auto;">
                                                <?php foreach (($historyByTask[(int) $task["id"]] ?? []) as $h): ?>
                                                    <div style="padding: 0.75rem; border-left: 3px solid #cbd5e1; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                                        <div style="color: #475569;">
                                                            <strong><?= e((string) $h["action"]) ?></strong> 
                                                            <?php if ($h["name"]): ?>
                                                                by <strong><?= e((string) $h["name"]) ?></strong>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="color: #94a3b8; font-size: 0.85rem;">
                                                            <?= date("M d, h:i A", strtotime((string) $h["created_at"])) ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    }

    function toggleTaskDetails(taskId) {
        const detailsDiv = document.getElementById('task-details-' + taskId);
        if (detailsDiv) {
            detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
        }
    }

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
</script>

<?php
$content = ob_get_clean();
$scripts = '';
include __DIR__ . '/../app/views/layouts/main.php';
?>

