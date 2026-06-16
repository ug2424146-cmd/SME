<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/controllers/SkillController.php";

require_auth();
$user = current_user();
$controller = new SkillController();
$canViewCatalog = ($user["role"] === "admin" || $user["role"] === "manager");
$canCreateCatalogSkill = true;
$canManageSkills = true;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        flash("error", "Invalid CSRF token.");
    } else {
        $action = (string) ($_POST["action"] ?? "");
        if ($action === "create_skill" && $canCreateCatalogSkill) {
            $controller->createSkill($_POST, (int) $user["id"]);
        } elseif ($action === "save_employee_skill" && $canManageSkills) {
            $controller->upsertEmployeeSkill($_POST, (int) $user["id"]);
        } else {
            flash("error", "Could not complete that action.");
        }
    }
    header("Location: " . url("skills.php"));
    exit;
}

$skills = $mysqli->query("SELECT id, skill_name FROM skills ORDER BY skill_name ASC")->fetch_all(MYSQLI_ASSOC);

$mySkillsList = [];
$stmt = $mysqli->prepare(
    "SELECT es.skill_id, es.proficiency_level, s.skill_name
     FROM employee_skills es
     INNER JOIN skills s ON s.id = es.skill_id
     WHERE es.user_id = ?
     ORDER BY s.skill_name ASC"
);
$stmt->bind_param("i", $user["id"]);
$stmt->execute();
$mySkillsRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$mySkills = [];
foreach ($mySkillsRows as $r) {
    $sid = (int) $r["skill_id"];
    $mySkills[$sid] = (string) $r["proficiency_level"];
    $mySkillsList[] = $r;
}

$catalogSkillCount = count($skills);
$onProfileCount = count($mySkillsList);
$skillNamesById = [];
foreach ($skills as $s) {
    $skillNamesById[(int) $s["id"]] = (string) $s["skill_name"];
}

$successMessage = get_flash("success");
$errorMessage = get_flash("error");

$pageTitle = 'Skills Management';
$pageSubtitle = 'Add skills and track what is on your profile';
$currentPage = 'skills';

ob_start();
?>
<div id="skills-page" data-save-url="<?= e(url('api/employee_skill.php')) ?>" data-csrf="<?= e(csrf_token()) ?>">
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

    <div class="card border-0 shadow-sm rounded-2xl mb-4">
        <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>Register a New Skill</h5>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <p class="text-muted small mb-3">Create and register a new skill to build the shared catalog. All employees can register and manage their skills.</p>
            <form method="post" action="<?= e(url('skills.php')) ?>" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_skill">
                <div class="col-md-9">
                    <input id="skill_name" class="form-control" name="skill_name" placeholder="e.g. Project Management, Data Analysis, Leadership..." required maxlength="120">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold no-loading">
                        <i class="fas fa-plus me-1"></i> Add Skill
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-2xl mb-4">
        <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0"><i class="fas fa-user-check text-success me-2"></i>Your Skills</h5>
            <span class="badge bg-success bg-opacity-10 text-success fw-semibold px-3 py-2">
                <i class="fas fa-tag me-1"></i><?= (int) $onProfileCount ?> saved
            </span>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <?php if ($onProfileCount === 0): ?>
                <div class="text-center py-5">
                    <i class="fas fa-bullseye text-muted" style="font-size: 2.5rem; opacity: 0.4;"></i>
                    <h6 class="fw-bold mt-3 mb-2">No Skills Yet</h6>
                    <p class="text-muted small mb-0">Create a new skill above or select from the catalog to get started!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($mySkillsList as $row): ?>
                        <?php $sid = (int) $row["skill_id"]; ?>
                        <div class="col-lg-6">
                            <div class="d-flex align-items-start gap-3 p-3 rounded-lg border bg-white shadow-sm">
                                <div class="shrink-0">
                                    <i class="fas fa-bullseye text-primary" style="font-size: 1.25rem;"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h6 class="fw-bold mb-2 small"><?= e((string) $row["skill_name"]) ?></h6>
                                    <span class="badge bg-<?= $row["proficiency_level"] === 'expert' ? 'danger' : ($row["proficiency_level"] === 'intermediate' ? 'warning text-dark' : 'info text-dark') ?> bg-opacity-10 fw-semibold px-3 py-1 skill-level-badge" data-skill-id="<?= $sid ?>">
                                        <i class="fas fa-<?= $row["proficiency_level"] === 'expert' ? 'star' : ($row["proficiency_level"] === 'intermediate' ? 'chart-line' : 'seedling') ?> me-1"></i>
                                        <?= e(ucfirst((string) $row["proficiency_level"])) ?>
                                    </span>
                                </div>
                                <form method="post" action="<?= e(url('skills.php')) ?>" class="d-flex gap-2 align-items-center shrink-0 js-skill-save-form" data-skill-id="<?= $sid ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="save_employee_skill">
                                    <input type="hidden" name="skill_id" value="<?= $sid ?>">
                                    <select class="form-select form-select-sm skill-level-select" name="proficiency_level" data-skill-id="<?= $sid ?>" required style="width: auto; min-width: 8rem;">
                                        <option value="beginner" <?= $row["proficiency_level"] === "beginner" ? "selected" : "" ?>>Beginner</option>
                                        <option value="intermediate" <?= $row["proficiency_level"] === "intermediate" ? "selected" : "" ?>>Intermediate</option>
                                        <option value="expert" <?= $row["proficiency_level"] === "expert" ? "selected" : "" ?>>Expert</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary no-loading js-skill-save-btn">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($canViewCatalog): ?>
    <div class="card border-0 shadow-sm rounded-2xl">
        <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0"><i class="fas fa-book text-info me-2"></i>Skills Catalog</h5>
            <span class="badge bg-info bg-opacity-10 text-info fw-semibold px-3 py-2">
                <i class="fas fa-tag me-1"></i><?= (int) $catalogSkillCount ?> total
            </span>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <?php if (empty($skills)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 2.5rem; opacity: 0.4;"></i>
                    <h6 class="fw-bold mt-3 mb-2">Empty Catalog</h6>
                    <p class="text-muted small mb-0">Be the first to add a skill using the form above!</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($skills as $skill): ?>
                        <?php
                        $sid = (int) $skill["id"];
                        $onProfile = isset($mySkills[$sid]);
                        $level = $mySkills[$sid] ?? "beginner";
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border rounded-lg shadow-sm h-100 hover-lift-modern position-relative <?= $onProfile ? 'border-success border-2' : '' ?>">
                                <?php if ($onProfile): ?>
                                    <span class="absolute top-0 end-0 badge bg-success mt-2 me-2 fw-semibold px-3 py-1">
                                        <i class="fas fa-check me-1"></i>On Profile
                                    </span>
                                <?php endif; ?>
                                <div class="card-body p-4">
                                    <?php
                                    // Fetch a small list of employees who have this skill
                                    $empStmt = $mysqli->prepare(
                                        "SELECT u.id, u.name, es.proficiency_level
                                         FROM employee_skills es
                                         INNER JOIN users u ON u.id = es.user_id
                                         INNER JOIN roles r ON r.id = u.role_id AND r.role_name = 'employee'
                                         WHERE es.skill_id = ? AND u.is_active = 1
                                         ORDER BY CASE es.proficiency_level WHEN 'expert' THEN 0 WHEN 'intermediate' THEN 1 ELSE 2 END ASC, u.name ASC
                                         LIMIT 6"
                                    );
                                    $empStmt->bind_param('i', $sid);
                                    $empStmt->execute();
                                    $empRows = $empStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    $empStmt->close();
                                    ?>
                                    <i class="fas fa-bullseye text-primary mb-3 d-block" style="font-size: 1.75rem;"></i>
                                    <h6 class="fw-bold mb-3"><?= e((string) $skill["skill_name"]) ?></h6>
                                            <form method="post" action="<?= e(url('skills.php')) ?>" class="js-skill-save-form" data-skill-id="<?= $sid ?>">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="save_employee_skill">
                                        <input type="hidden" name="skill_id" value="<?= $sid ?>">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Proficiency</label>
                                            <select class="form-select form-select-sm skill-level-select" name="proficiency_level" data-skill-id="<?= $sid ?>" required>
                                                <option value="beginner" <?= $level === "beginner" ? "selected" : "" ?>>Beginner</option>
                                                <option value="intermediate" <?= $level === "intermediate" ? "selected" : "" ?>>Intermediate</option>
                                                <option value="expert" <?= $level === "expert" ? "selected" : "" ?>>Expert</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-<?= $onProfile ? 'outline-success' : 'primary' ?> btn-sm w-100 fw-semibold no-loading js-skill-save-btn">
                                            <i class="fas fa-<?= $onProfile ? 'pen' : 'plus' ?> me-1"></i>
                                            <?= $onProfile ? "Update Profile" : "Add to Profile" ?>
                                        </button>
                                    </form>
                                    <?php if (!empty($empRows)): ?>
                                        <div class="mt-3 small text-muted">
                                            <strong>Employees with this skill:</strong>
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                <?php foreach ($empRows as $er): ?>
                                                    <a href="<?= e(url('profile.php?user_id=' . (int)$er['id'])) ?>" class="badge bg-light border small text-decoration-none text-dark">
                                                        <?= e((string)$er['name']) ?>
                                                        <span class="ms-1 text-muted">(<?= e(ucfirst((string)$er['proficiency_level'] ?? 'beginner')) ?>)</span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$saveUrl = url('api/employee_skill.php');
$scripts = <<<HTML
<script>
(function () {
  const root = document.getElementById('skills-page');
  if (!root) return;

  const saveUrl = root.dataset.saveUrl;
  const csrf = root.dataset.csrf;
  const levelLabels = { beginner: 'Beginner', intermediate: 'Intermediate', expert: 'Expert' };
  let saveTimer = null;
  let saving = false;

  function syncSkillLevel(skillId, level, source) {
    document.querySelectorAll('.skill-level-select[data-skill-id="' + skillId + '"]').forEach(function (sel) {
      if (sel !== source) sel.value = level;
    });
    document.querySelectorAll('.skill-level-badge[data-skill-id="' + skillId + '"]').forEach(function (badge) {
      badge.textContent = levelLabels[level] || level;
    });
    const card = document.querySelector('.skill-card[data-skill-id="' + skillId + '"]');
    if (card) {
      card.classList.add('border-primary', 'border-2');
      let tag = card.querySelector('.js-on-profile-badge');
      if (!tag) {
        tag = document.createElement('span');
        tag.className = 'badge bg-success mb-2 js-on-profile-badge';
        tag.innerHTML = '<i class="fas fa-check me-1"></i>On your profile';
        card.querySelector('.card-body')?.prepend(tag);
      }
    }
  }

  async function persistSkillLevel(skillId, level) {
    if (saving) return;
    saving = true;
    const buttons = document.querySelectorAll('.js-skill-save-btn');
    buttons.forEach(function (b) { b.disabled = true; });

    try {
      const body = new FormData();
      body.append('csrf_token', csrf);
      body.append('skill_id', skillId);
      body.append('proficiency_level', level);

      const res = await fetch(saveUrl, { method: 'POST', body: body, credentials: 'same-origin' });
      const data = await res.json();

      if (!data.ok) {
        throw new Error(data.message || 'Could not save skill.');
      }

      syncSkillLevel(String(skillId), level);
      if (window.SME && typeof window.SME.notify === 'function') {
        window.SME.notify('Proficiency updated everywhere.', 'success');
      }
    } catch (err) {
      if (window.SME && typeof window.SME.notify === 'function') {
        window.SME.notify(err.message || 'Save failed.', 'danger');
      } else {
        alert(err.message || 'Save failed.');
      }
    } finally {
      saving = false;
      buttons.forEach(function (b) { b.disabled = false; });
    }
  }

  function scheduleSave(skillId, level) {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      persistSkillLevel(skillId, level);
    }, 400);
  }

  document.querySelectorAll('.skill-level-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      const skillId = sel.dataset.skillId;
      const level = sel.value;
      if (!skillId || !level) return;
      syncSkillLevel(skillId, level, sel);
      scheduleSave(skillId, level);
    });
  });

  document.querySelectorAll('.js-skill-save-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const skillId = form.dataset.skillId;
      const sel = form.querySelector('.skill-level-select');
      const level = sel ? sel.value : '';
      if (!skillId || !level) return;
      syncSkillLevel(skillId, level, sel);
      clearTimeout(saveTimer);
      persistSkillLevel(skillId, level);
    });
  });
})();
</script>
HTML;
include __DIR__ . '/../app/views/layouts/main.php';
