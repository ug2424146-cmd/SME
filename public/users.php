<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/controllers/UserController.php";

require_role(["admin"]);
$currentUser = current_user();
$controller = new UserController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        flash("error", "Invalid CSRF token.");
        header("Location: " . url("users.php"));
        exit;
    }

    $action = (string) ($_POST["action"] ?? "");
    if ($action === "create_user") {
        $controller->createUser($_POST);
    } elseif ($action === "update_user") {
        $controller->updateUser($_POST, (int) $currentUser["id"]);
    } elseif ($action === "delete_user") {
        $controller->deleteUser($_POST, (int) $currentUser["id"]);
    } else {
        flash("error", "Unsupported action.");
    }

    header("Location: " . url("users.php"));
    exit;
}

$users = [];
$result = $mysqli->query(
    "SELECT u.id, u.name, u.email, u.is_active, COALESCE(r.role_name, u.role) AS role, u.created_at
     FROM users u
     LEFT JOIN roles r ON r.id = u.role_id
     ORDER BY u.created_at DESC"
);
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$successMessage = get_flash("success");
$errorMessage = get_flash("error");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management - SME Platform</title>
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>?v=<?= time() ?>" rel="stylesheet">
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
        .user-name-button {
            background: transparent;
            border: 0;
            color: #4f46e5;
            font-weight: 700;
            padding: 0;
            text-align: left;
        }
        .user-name-button:hover {
            color: #312e81;
            text-decoration: underline;
        }
        .user-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .user-detail-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.9rem 1rem;
        }
        .user-detail-label {
            color: #6b7280;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }
        .user-detail-value {
            color: #111827;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        .status-badge {
            border-radius: 999px;
            display: inline-flex;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.35rem 0.7rem;
        }
        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }
        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .user-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        @media (max-width: 767.98px) {
            .user-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="sidebar-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-brand">
                🏢 SME Platform
            </a>
        </div>
        <div class="sidebar-user">
            <div class="sidebar-user-name"><?= e((string) $currentUser["name"]) ?></div>
            <div class="sidebar-user-role"><?= e((string) $currentUser["role"]) ?></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">Main Menu</div>
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="<?= e(url('tasks.php')) ?>" class="sidebar-link">
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
            <div class="sidebar-section mt-4">Management</div>
            <a href="<?= e(url('team_progress.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">👥</span>
                <span>Team Progress</span>
            </a>
            <div class="sidebar-section mt-4">Reports</div>
            <a href="<?= e(url('reports_center.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📈</span>
                <span>Reports</span>
            </a>
            <div class="sidebar-section mt-4">Administration</div>
            <a href="<?= e(url('users.php')) ?>" class="sidebar-link active">
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
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰ Menu</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1>User Management</h1>
                <p>Manage system users and roles</p>
            </div>
        </div>

        <main class="container py-4">

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?= e($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="alert alert-danger"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5">Create User</h2>
            <form method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_user">

                <div class="col-md-4">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" class="form-control" name="name" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" class="form-control" name="email" type="email" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="role">Role</label>
                    <select id="role" class="form-select" name="role">
                        <option value="employee" selected>Employee</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" class="form-control" name="password" type="password" minlength="8" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="is_active">Status</label>
                    <select id="is_active" class="form-select" name="is_active">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5">All Users</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <button
                                    type="button"
                                    class="user-name-button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#userDetailsModal"
                                    data-user-id="<?= (int) $user["id"] ?>"
                                    data-user-name="<?= e((string) $user["name"]) ?>"
                                    data-user-email="<?= e((string) $user["email"]) ?>"
                                    data-user-role="<?= e((string) $user["role"]) ?>"
                                    data-user-status="<?= (int) $user["is_active"] === 1 ? "Active" : "Inactive" ?>"
                                    data-user-active="<?= (int) $user["is_active"] ?>"
                                    data-user-created="<?= e((string) $user["created_at"]) ?>"
                                >
                                    <?= e((string) $user["name"]) ?>
                                </button>
                            </td>
                            <td><?= e((string) $user["email"]) ?></td>
                            <td><?= e((string) $user["role"]) ?></td>
                            <td><?= e((string) $user["created_at"]) ?></td>
                            <td>
                                <div class="user-actions">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal"
                                        data-user-id="<?= (int) $user["id"] ?>"
                                        data-user-name="<?= e((string) $user["name"]) ?>"
                                        data-user-email="<?= e((string) $user["email"]) ?>"
                                        data-user-role="<?= e((string) $user["role"]) ?>"
                                        data-user-active="<?= (int) $user["is_active"] ?>"
                                    >
                                        Edit
                                    </button>
                                    <form method="post" onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= (int) $user["id"] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit" <?= (int) $user["id"] === (int) $currentUser["id"] ? "disabled" : "" ?>>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </main>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5" id="editUserModalLabel">Edit User</h2>
                        <div class="text-muted small">Update the user's profile, role, status, or password.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_name">Name</label>
                            <input id="edit_name" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_email">Email</label>
                            <input id="edit_email" class="form-control" name="email" type="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_role">Role</label>
                            <select id="edit_role" class="form-select" name="role">
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="edit_is_active">Status</label>
                            <select id="edit_is_active" class="form-select" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="edit_password">New Password</label>
                            <input id="edit_password" class="form-control" name="password" type="password" minlength="8" placeholder="Leave blank to keep current password">
                            <div class="form-text">Only enter a password if you want to reset it.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5" id="userDetailsModalLabel">User Details</h2>
                    <div class="text-muted small" id="modalUserSubtitle">Basic account information</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="user-detail-grid">
                    <div class="user-detail-item">
                        <div class="user-detail-label">Full Name</div>
                        <div class="user-detail-value" id="modalUserName">-</div>
                    </div>
                    <div class="user-detail-item">
                        <div class="user-detail-label">Email Address</div>
                        <div class="user-detail-value" id="modalUserEmail">-</div>
                    </div>
                    <div class="user-detail-item">
                        <div class="user-detail-label">Role</div>
                        <div class="user-detail-value" id="modalUserRole">-</div>
                    </div>
                    <div class="user-detail-item">
                        <div class="user-detail-label">Status</div>
                        <div class="user-detail-value">
                            <span class="status-badge" id="modalUserStatus">-</span>
                        </div>
                    </div>
                    <div class="user-detail-item">
                        <div class="user-detail-label">User ID</div>
                        <div class="user-detail-value" id="modalUserId">-</div>
                    </div>
                    <div class="user-detail-item">
                        <div class="user-detail-label">Created Date</div>
                        <div class="user-detail-value" id="modalUserCreated">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

const userDetailsModal = document.getElementById('userDetailsModal');
if (userDetailsModal) {
    userDetailsModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        const status = button.getAttribute('data-user-status') || '-';
        const statusBadge = document.getElementById('modalUserStatus');

        document.getElementById('userDetailsModalLabel').textContent = button.getAttribute('data-user-name') || 'User Details';
        document.getElementById('modalUserSubtitle').textContent = button.getAttribute('data-user-email') || 'Basic account information';
        document.getElementById('modalUserName').textContent = button.getAttribute('data-user-name') || '-';
        document.getElementById('modalUserEmail').textContent = button.getAttribute('data-user-email') || '-';
        document.getElementById('modalUserRole').textContent = button.getAttribute('data-user-role') || '-';
        document.getElementById('modalUserId').textContent = button.getAttribute('data-user-id') || '-';
        document.getElementById('modalUserCreated').textContent = button.getAttribute('data-user-created') || '-';

        statusBadge.textContent = status;
        statusBadge.className = 'status-badge ' + (status === 'Active' ? 'active' : 'inactive');
    });
}

const editUserModal = document.getElementById('editUserModal');
if (editUserModal) {
    editUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) {
            return;
        }

        document.getElementById('edit_user_id').value = button.getAttribute('data-user-id') || '';
        document.getElementById('edit_name').value = button.getAttribute('data-user-name') || '';
        document.getElementById('edit_email').value = button.getAttribute('data-user-email') || '';
        document.getElementById('edit_role').value = button.getAttribute('data-user-role') || 'employee';
        document.getElementById('edit_is_active').value = button.getAttribute('data-user-active') || '1';
        document.getElementById('edit_password').value = '';

        const userName = button.getAttribute('data-user-name') || 'User';
        document.getElementById('editUserModalLabel').textContent = 'Edit ' + userName;
    });
}
</script>
</body>
</html>

