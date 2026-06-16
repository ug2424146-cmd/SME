<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/helpers/app.php";
require_auth();
$user = current_user();

if (!in_array($user['role'], ['manager', 'admin', 'employee'], true)) {
    http_response_code(403);
    exit('Forbidden');
}

$employees = $mysqli->query("SELECT u.id, u.name FROM users u INNER JOIN roles r ON r.id=u.role_id WHERE r.role_name='employee' ORDER BY u.name")->fetch_all(MYSQLI_ASSOC);

$showAll = in_array($user['role'], ['manager', 'admin'], true);

$rows = [];
foreach ($employees as $e) {
    if (!$showAll && $e['id'] !== $user['id']) continue;
    $d = compute_user_rating((int)$e['id'], true);
    $rows[] = ['id' => $e['id'], 'name' => $e['name'], 'detail' => $d];
}

$pageTitle = 'Rating Breakdown';
$pageSubtitle = 'System-calculated rating components per employee';
$currentPage = 'performance';
ob_start();
?>
<div class="card border-0 shadow-sm rounded-2xl mb-4">
    <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0">
        <h5 class="fw-bold mb-0">Rating Breakdown</h5>
    </div>
    <div class="card-body px-4 pb-4 pt-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Late</th>
                        <th>Avg Proficiency</th>
                        <th>Computed Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= e($r['name']) ?></td>
                            <td><?= (int)$r['detail']['assigned'] ?></td>
                            <td><?= (int)$r['detail']['completed'] ?></td>
                            <td><?= (int)$r['detail']['late'] ?></td>
                            <td><?= e((string)$r['detail']['avg_proficiency']) ?></td>
                            <td class="fw-bold"><?= e((string)$r['detail']['rating']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$scripts = '';
require_once __DIR__ . '/../app/views/layouts/main.php';
