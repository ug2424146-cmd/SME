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
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <small class="text-muted">Click column headers to sort.</small>
                </div>
                <div>
                    <button id="rb-export-csv" class="btn btn-sm btn-outline-secondary">Export CSV</button>
                </div>
            </div>
            <table id="rb-table" class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th data-key="name" style="cursor:pointer">Employee</th>
                        <th data-key="assigned" style="cursor:pointer">Assigned</th>
                        <th data-key="completed" style="cursor:pointer">Completed</th>
                        <th data-key="late" style="cursor:pointer">Late</th>
                        <th data-key="avg_proficiency" style="cursor:pointer">Avg Proficiency</th>
                        <th data-key="rating" style="cursor:pointer">Computed Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail modal -->
<div class="modal fade" id="rb-detail-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rating details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="rb-detail-content">Loading…</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$apiUrl = e(url('api/rating_breakdown.php'));
$scripts = <<<HTML
<script>
const API_BASE = "{$apiUrl}";
let rbRows = [];
let rbSortKey = 'rating';
let rbSortDir = -1; // -1 desc, 1 asc

function compareRows(a,b,key) {
  const av = (a.detail && typeof a.detail[key] !== 'undefined') ? a.detail[key] : (a[key] ?? '');
  const bv = (b.detail && typeof b.detail[key] !== 'undefined') ? b.detail[key] : (b[key] ?? '');
  if (!isNaN(av) && !isNaN(bv)) {
    return (av - bv);
  }
  return String(av).localeCompare(String(bv));
}

function renderTable() {
  const tbody = document.querySelector('#rb-table tbody');
  if (!rbRows || rbRows.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-muted">No data</td></tr>';
    return;
  }
  const rows = rbRows.slice();
  rows.sort((a,b) => rbSortDir * compareRows(a,b,rbSortKey));
  tbody.innerHTML = '';
  rows.forEach(r => {
    const d = r.detail || {};
    const tr = document.createElement('tr');
    tr.style.cursor = 'pointer';
    tr.dataset.userId = r.id || '';
    tr.innerHTML = '<td>' + (r.name||'Unknown') + '</td>' +
                   '<td>' + (d.assigned||0) + '</td>' +
                   '<td>' + (d.completed||0) + '</td>' +
                   '<td>' + (d.late||0) + '</td>' +
                   '<td>' + (d.avg_proficiency||0) + '</td>' +
                   '<td class="fw-bold">' + (d.rating||0) + '</td>';
    tr.addEventListener('click', () => openDetailModal(r.id));
    tbody.appendChild(tr);
  });
}

async function loadRatingBreakdown(userId) {
  const url = API_BASE + (userId ? '?user_id=' + userId : '');
  try {
    const res = await fetch(url, { credentials: 'same-origin' });
    const data = await res.json();
    if (!data.ok) {
      document.querySelector('#rb-table tbody').innerHTML = '<tr><td colspan="6" class="text-danger">Error: ' + (data.message||'Failed') + '</td></tr>';
      rbRows = [];
      return;
    }
    rbRows = data.rows || [];
    renderTable();
  } catch (err) {
    document.querySelector('#rb-table tbody').innerHTML = '<tr><td colspan="6" class="text-danger">Fetch error</td></tr>';
    rbRows = [];
  }
}

function setupSorting() {
  document.querySelectorAll('#rb-table thead th[data-key]').forEach(th => {
    th.addEventListener('click', function() {
      const key = this.dataset.key;
      if (rbSortKey === key) rbSortDir = -rbSortDir; else { rbSortKey = key; rbSortDir = -1; }
      renderTable();
    });
  });
}

function downloadCSV() {
  if (!rbRows || rbRows.length === 0) return;
  const headers = ['Employee','Assigned','Completed','Late','Avg Proficiency','Computed Rating'];
  const lines = [headers.join(',')];
  rbRows.forEach(r => {
    const d = r.detail || {};
    const row = [
      '"' + (r.name||'') + '"',
      d.assigned||0,
      d.completed||0,
      d.late||0,
      d.avg_proficiency||0,
      d.rating||0
    ];
    lines.push(row.join(','));
  });
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'rating_breakdown.csv';
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

async function openDetailModal(userId) {
  const modalEl = document.getElementById('rb-detail-modal');
  const content = document.getElementById('rb-detail-content');
  content.innerHTML = 'Loading…';
  const url = API_BASE + '?user_id=' + userId;
  try {
    const res = await fetch(url, { credentials: 'same-origin' });
    const data = await res.json();
    if (!data.ok || !data.rows || data.rows.length === 0) {
      content.innerHTML = '<div class="text-danger">No details available.</div>';
    } else {
      const r = data.rows[0];
      const d = r.detail || {};
      let html = '<h6>' + (r.name || 'Employee') + '</h6>';
      html += '<dl class="row">';
      html += '<dt class="col-sm-4">Assigned tasks</dt><dd class="col-sm-8">' + (d.assigned||0) + '</dd>';
      html += '<dt class="col-sm-4">Completed</dt><dd class="col-sm-8">' + (d.completed||0) + '</dd>';
      html += '<dt class="col-sm-4">Late</dt><dd class="col-sm-8">' + (d.late||0) + '</dd>';
      html += '<dt class="col-sm-4">Avg Proficiency</dt><dd class="col-sm-8">' + (d.avg_proficiency||0) + '</dd>';
      html += '<dt class="col-sm-4">Completion Score</dt><dd class="col-sm-8">' + (d.completion_score||0) + '</dd>';
      html += '<dt class="col-sm-4">Timeliness Score</dt><dd class="col-sm-8">' + (d.timeliness_score||0) + '</dd>';
      html += '<dt class="col-sm-4">Proficiency Score</dt><dd class="col-sm-8">' + (d.proficiency_score||0) + '</dd>';
      html += '<dt class="col-sm-4">Weights</dt><dd class="col-sm-8">Completion: ' + (d.weights?.completion ?? '') + ', Timeliness: ' + (d.weights?.timeliness ?? '') + ', Proficiency: ' + (d.weights?.proficiency ?? '') + '</dd>';
      html += '<dt class="col-sm-4">Computed Rating</dt><dd class="col-sm-8"><strong>' + (d.rating||0) + '</strong></dd>';
      html += '</dl>';
      content.innerHTML = html;
    }
  } catch (err) {
    content.innerHTML = '<div class="text-danger">Fetch error</div>';
  }
  // show modal via bootstrap
  try {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  } catch (e) {
    // fallback: make visible
    modalEl.style.display = 'block';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  setupSorting();
  loadRatingBreakdown();
  const sel = document.getElementById('rb-employee-select');
  if (sel) sel.addEventListener('change', function() { loadRatingBreakdown(this.value || 0); });
  const csvBtn = document.getElementById('rb-export-csv');
  if (csvBtn) csvBtn.addEventListener('click', downloadCSV);
});
</script>
HTML;
require_once __DIR__ . '/../app/views/layouts/main.php';
