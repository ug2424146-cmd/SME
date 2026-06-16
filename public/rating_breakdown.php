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
        <div class="mb-3">
            <?php if (in_array($user['role'], ['manager','admin'], true)): ?>
                <label class="form-label small fw-semibold text-muted">Filter Employee</label>
                <select id="rb-employee-select" class="form-select form-select-sm mb-2">
                    <option value="">All employees</option>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= (int)$e['id'] ?>"><?= e($e['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table id="rb-table" class="table table-hover align-middle mb-0 small">
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
                    <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$apiUrl = e(url('api/rating_breakdown.php'));
$scripts = <<<HTML
<script>
async function loadRatingBreakdown(userId) {
  const base = "{$apiUrl}";
  const url = base + (userId ? '?user_id=' + userId : '');
  try {
    const res = await fetch(url, { credentials: 'same-origin' });
    const data = await res.json();
    const tbody = document.querySelector('#rb-table tbody');
    if (!data.ok) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Error: ' + (data.message||'Failed') + '</td></tr>';
      return;
    }
    if (!data.rows || data.rows.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-muted">No data</td></tr>';
      return;
    }
    tbody.innerHTML = '';
    data.rows.forEach(r => {
      const d = r.detail || {};
      const tr = document.createElement('tr');
      tr.innerHTML = '<td>' + (r.name||'Unknown') + '</td>' +
                     '<td>' + (d.assigned||0) + '</td>' +
                     '<td>' + (d.completed||0) + '</td>' +
                     '<td>' + (d.late||0) + '</td>' +
                     '<td>' + (d.avg_proficiency||0) + '</td>' +
                     '<td class="fw-bold">' + (d.rating||0) + '</td>';
      tbody.appendChild(tr);
    });
  } catch (err) {
    const tbody = document.querySelector('#rb-table tbody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-danger">Fetch error</td></tr>';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  loadRatingBreakdown();
  const sel = document.getElementById('rb-employee-select');
  if (sel) {
    sel.addEventListener('change', function() {
      loadRatingBreakdown(this.value || 0);
    });
  }
});
</script>
HTML;
require_once __DIR__ . '/../app/views/layouts/main.php';
