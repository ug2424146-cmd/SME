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

<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= e($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($user["role"] === "manager" || $user["role"] === "admin"): ?>
    <div class="card border-0 shadow-sm rounded-2xl mb-4">
        <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Create New Task</h5>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <form method="post" id="create-task-form">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_task">

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-muted" for="title">Task Title <span class="text-danger">*</span></label>
                        <input class="form-control" id="title" name="title" placeholder="Enter a clear, descriptive task title" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted" for="priority">Priority Level</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="bg-light rounded-lg p-3 mb-3" id="assignment-section" data-skill-filter-url="<?= e(url('api/skills_employees.php')) ?>">
                    <h6 class="fw-semibold small uppercase text-primary mb-3"><i class="fas fa-user-tie me-1"></i>Assignment</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted" for="assigned_to">Assign To <span class="text-danger">*</span></label>
                            <select class="form-select" id="assigned_to" name="assigned_to" required>
                                <option value="">Select an employee</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= (int) $employee["id"] ?>"><?= e($employee["name"]) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($smartSuggestion): ?>
                                <div class="mt-2 p-2 rounded-2 bg-info bg-opacity-10 small text-info fw-semibold">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Suggested:</strong> <?= e((string) $smartSuggestion["name"]) ?> (current workload: <?= e((string) $smartSuggestion["current_load"]) ?> tasks)
                                </div>
                            <?php endif; ?>
                            <div id="skill-filter-info" style="display: none;" class="mt-2 p-2 rounded-2 bg-info bg-opacity-10 small text-info fw-semibold">
                                <span id="skill-filter-text"></span>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-info" id="clear-filter-btn">Clear filter</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted" for="required_skill_id"><i class="fas fa-bullseye me-1"></i>Required Skill</label>
                            <select class="form-select" id="required_skill_id" name="required_skill_id">
                                <option value="">No specific skill required</option>
                                <?php foreach ($skills as $skill): ?>
                                    <option value="<?= (int) $skill["id"] ?>"><?= e((string) $skill["skill_name"]) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-light rounded-lg p-3 mb-3">
                    <h6 class="fw-semibold small uppercase text-primary mb-3"><i class="fas fa-calendar-alt me-1"></i>Schedule & Options</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted" for="deadline">Deadline</label>
                            <input class="form-control" id="deadline" name="deadline" type="date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted d-block">Auto Assignment</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="auto_assign" value="1" id="auto_assign">
                                <label class="form-check-label small" for="auto_assign">Use AI skill matching</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted" for="description"><i class="fas fa-align-left me-1"></i>Description</label>
                    <textarea class="form-control" id="description" name="description" placeholder="Enter detailed task description, requirements, and any important notes..." rows="3" style="resize: vertical;"></textarea>
                </div>

                <button class="btn btn-primary w-100 fw-semibold" type="submit">
                    <i class="fas fa-plus me-1"></i> Create Task
                </button>
            </form>
        </div>
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

                // If auto-assign is selected, pick the top-ranked employee automatically
                const autoAssignCheckbox = document.querySelector('input[name="auto_assign"]');
                if (autoAssignCheckbox && autoAssignCheckbox.checked && data.employees.length > 0) {
                    // select the first employee option
                    const firstOpt = assignedToSelect.querySelector('option:not([value=""])');
                    if (firstOpt) {
                        assignedToSelect.value = firstOpt.value;
                    }
                }

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

        createTaskForm.addEventListener('submit', function(e) {
            const requiredSkillId = requiredSkillSelect.value;
            const selectedEmployeeId = assignedToSelect.value;

            if (requiredSkillId && currentFilteredEmployeeIds.size > 0) {
                if (!currentFilteredEmployeeIds.has(selectedEmployeeId)) {
                    e.preventDefault();
                    alert('Error: The selected employee does not have the required skill. Please select an employee from the filtered list.');
                    assignedToSelect.focus();
                    return false;
                }
            }

            if (requiredSkillId && currentFilteredEmployeeIds.size === 0) {
                e.preventDefault();
                alert('Error: No employees have the required skill.');
                return false;
            }
        });
    })();
    </script>
<?php endif; ?>

<div class="mb-3">
    <h5 class="fw-bold mb-3"><i class="fas fa-list-check text-primary me-2"></i>Task Overview</h5>

    <?php if (count($tasks) === 0): ?>
        <div class="card border-0 shadow-sm rounded-2xl">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox text-muted" style="font-size: 2.5rem; opacity: 0.4;"></i>
                <h6 class="fw-bold mt-3 mb-2">No Tasks Available</h6>
                <p class="text-muted small mb-0">
                    <?php if ($user["role"] === "employee"): ?>
                        You don't have any assigned tasks yet. Check back soon!
                    <?php else: ?>
                        Create a new task to get started with task management.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($tasks as $task):
            $isUrgent = !empty($task["deadline"]) && strtotime($task["deadline"]) < strtotime("+7 days");
            $statusClass = str_replace('_', '-', $task["status"]);
            $priorityClass = $task["priority"];
        ?>
            <div class="card border-0 shadow-sm rounded-2xl mb-3 hover-lift-modern" style="border-left: 4px solid <?= $priorityClass === 'high' ? '#ef4444' : ($priorityClass === 'medium' ? '#f59e0b' : '#10b981') ?> !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <h6 class="fw-bold mb-2"><?= e((string) $task["title"]) ?></h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge fw-semibold px-3 py-1 small <?= $task["status"] === 'completed' ? 'bg-success bg-opacity-10 text-success' : ($task["status"] === 'in_progress' ? 'bg-info bg-opacity-10 text-info' : 'bg-warning bg-opacity-10 text-warning') ?>">
                                    <i class="fas fa-<?= $task["status"] === 'completed' ? 'check-circle' : ($task["status"] === 'in_progress' ? 'spinner' : 'clock') ?> me-1"></i>
                                    <?= e(ucfirst(str_replace('_', ' ', (string) $task["status"]))) ?>
                                </span>
                                <span class="badge fw-semibold px-3 py-1 small <?= $priorityClass === 'high' ? 'bg-danger bg-opacity-10 text-danger' : ($priorityClass === 'medium' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success') ?>">
                                    <i class="fas fa-flag me-1"></i>
                                    <?= e(ucfirst($priorityClass)) ?>
                                </span>
                                <?php if (!empty($task["required_skill"])): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-1 small">
                                        <i class="fas fa-bullseye me-1"></i><?= e((string) $task["required_skill"]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($task["description"])): ?>
                        <p class="text-muted small mb-3 bg-light rounded-2 p-3"><?= e((string) $task["description"]) ?></p>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                        <div class="d-flex align-items-center gap-3">
                            <small class="fw-semibold text-muted">
                                <i class="fas fa-user me-1"></i><?= e((string) $task["assignee"]) ?>
                            </small>
                            <?php if (!empty($task["deadline"])): ?>
                                <small class="<?= $isUrgent ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <i class="fas fa-calendar-alt me-1"></i><?= date("M d, Y", strtotime((string) $task["deadline"])) ?>
                                    <?php if ($isUrgent): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold ms-1 small">Due soon!</span>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
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
                            <button class="btn btn-sm btn-outline-primary fw-semibold" onclick="toggleTaskDetails(<?= (int) $task['id'] ?>)">
                                <i class="fas fa-caret-down me-1"></i>Details
                            </button>
                        </div>
                    </div>

                    <div id="task-details-<?= (int) $task['id'] ?>" class="mt-3 pt-3 border-top" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-light rounded-lg p-3">
                                    <h6 class="fw-semibold small mb-3"><i class="fas fa-comments text-primary me-1"></i>Comments</h6>
                                    <form method="post" class="mb-3">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="add_comment">
                                        <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" name="comment" placeholder="Add a comment..." required>
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i></button>
                                        </div>
                                    </form>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach (($commentsByTask[(int) $task["id"]] ?? []) as $comment): ?>
                                            <div class="bg-white rounded-2 p-3 mb-2 shadow-sm">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold small"><?= e((string) $comment["name"]) ?></span>
                                                    <small class="text-muted"><?= date("M d, h:i A", strtotime((string) $comment["created_at"])) ?></small>
                                                </div>
                                                <p class="text-muted small mb-0"><?= e((string) $comment["comment"]) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-lg p-3 mb-3">
                                    <h6 class="fw-semibold small mb-3"><i class="fas fa-paperclip text-primary me-1"></i>Attachments</h6>
                                    <form method="post" enctype="multipart/form-data" class="mb-3">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="upload_attachment">
                                        <input type="hidden" name="task_id" value="<?= (int) $task["id"] ?>">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" type="file" name="attachment" required>
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-upload"></i></button>
                                        </div>
                                    </form>
                                    <div>
                                        <?php foreach (($attachmentsByTask[(int) $task["id"]] ?? []) as $attachment): ?>
                                            <div class="d-flex align-items-center gap-2 bg-white rounded-2 p-2 mb-1 shadow-sm">
                                                <i class="fas fa-file text-primary"></i>
                                                <a href="<?= e(url((string) $attachment["file_path"])) ?>" target="_blank" class="small fw-medium flex-1 text-decoration-none">
                                                    <?= e((string) $attachment["file_name"]) ?>
                                                </a>
                                                <small class="text-muted"><?= date("M d", strtotime((string) $attachment["created_at"])) ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="bg-light rounded-lg p-3">
                                    <h6 class="fw-semibold small mb-3"><i class="fas fa-history text-primary me-1"></i>Activity History</h6>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach (($historyByTask[(int) $task["id"]] ?? []) as $h): ?>
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                <div>
                                                    <strong class="small"><?= e((string) $h["action"]) ?></strong>
                                                    <?php if ($h["name"]): ?>
                                                        <span class="small text-muted"> by <?= e((string) $h["name"]) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?= date("M d, h:i A", strtotime((string) $h["created_at"])) ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$scripts = <<<HTML
<script>
function toggleTaskDetails(taskId) {
    const detailsDiv = document.getElementById('task-details-' + taskId);
    if (detailsDiv) {
        detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
    }
}
</script>
HTML;
require_once __DIR__ . "/../app/views/layouts/main.php";
