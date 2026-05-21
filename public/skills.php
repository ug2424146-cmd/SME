<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/controllers/SkillController.php";

require_auth();
$user = current_user();
$controller = new SkillController();
// All authenticated users can create skills and manage their profile
// Only admins and managers can see the full catalog
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

    <!-- Add Skill Form -->
    <div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 700; border-radius: 12px 12px 0 0; padding: 1.5rem; border: none;">
            ✨ Register a New Skill
        </div>
        <div class="card-body">
            <p style="color: #64748b; margin-bottom: 1.5rem;">
                Create and register a new skill to build the shared skills catalog. All employees can register and manage their skills.
            </p>
            <form method="post" action="<?= e(url('skills.php')) ?>" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_skill">
                <div class="col-md-8">
                    <label class="form-label" for="skill_name" style="font-weight: 600; color: #334155;">Skill Name *</label>
                    <input id="skill_name" class="form-control" name="skill_name" placeholder="e.g. Project Management, Data Analysis, Leadership..." required maxlength="120">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100 no-loading" style="font-weight: 600;">
                        ➕ Add Skill
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Your Skills Section -->
    <div class="card mb-4" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 700; border-radius: 12px 12px 0 0; padding: 1.5rem; border: none; display: flex; justify-content: space-between; align-items: center;">
            <span>📋 Your Skills</span>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-weight: 600;">
                <?= (int) $onProfileCount ?> saved
            </span>
        </div>
        <div class="card-body">
            <?php if ($onProfileCount === 0): ?>
                <div style="text-align: center; padding: 2rem; background: #f8fafc; border-radius: 8px; border: 2px dashed #cbd5e1;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
                    <h5 style="color: #667eea; font-weight: 700; margin-bottom: 0.5rem;">No Skills Yet</h5>
                    <p style="color: #64748b; margin: 0;">
                        Create a new skill above or select from the catalog below to get started!
                    </p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($mySkillsList as $row): ?>
                        <?php $sid = (int) $row["skill_id"]; ?>
                        <div class="col-lg-6">
                            <div style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%); padding: 1.5rem; border-radius: 10px; border-left: 5px solid #667eea; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                                <div style="flex: 1;">
                                    <h6 style="font-weight: 700; color: #1e293b; margin: 0 0 0.5rem;">
                                        🎯 <?= e((string) $row["skill_name"]) ?>
                                    </h6>
                                    <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600; text-transform: capitalize; display: inline-block; skill-level-badge" data-skill-id="<?= $sid ?>">
                                        <?= e((string) $row["proficiency_level"]) ?>
                                    </span>
                                </div>
                                <form method="post" action="<?= e(url('skills.php')) ?>" class="d-inline-flex gap-2 align-items-center js-skill-save-form" data-skill-id="<?= $sid ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="save_employee_skill">
                                    <input type="hidden" name="skill_id" value="<?= $sid ?>">
                                    <select class="form-select form-select-sm skill-level-select" name="proficiency_level" data-skill-id="<?= $sid ?>" required style="width: auto; min-width: 10rem;">
                                        <option value="beginner" <?= $row["proficiency_level"] === "beginner" ? "selected" : "" ?>>Beginner</option>
                                        <option value="intermediate" <?= $row["proficiency_level"] === "intermediate" ? "selected" : "" ?>>Intermediate</option>
                                        <option value="expert" <?= $row["proficiency_level"] === "expert" ? "selected" : "" ?>>Expert</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary no-loading js-skill-save-btn">💾 Save</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Skills Catalog Section - Only visible to admins and managers -->
    <?php if ($canViewCatalog): ?>
    <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 700; border-radius: 12px 12px 0 0; padding: 1.5rem; border: none; display: flex; justify-content: space-between; align-items: center;">
            <span>📚 Skills Catalog</span>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-weight: 600;">
                <?= (int) $catalogSkillCount ?> total
            </span>
        </div>
        <div class="card-body">
            <?php if (empty($skills)): ?>
                <div style="text-align: center; padding: 2rem; background: #f8fafc; border-radius: 8px; border: 2px dashed #cbd5e1;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">📭</div>
                    <h5 style="color: #667eea; font-weight: 700; margin-bottom: 0.5rem;">Empty Catalog</h5>
                    <p style="color: #64748b; margin: 0;">
                        Be the first to add a skill using the form above!
                    </p>
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
                            <div class="skill-card" style="background: white; border: 2px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; text-align: center; transition: all 0.3s ease; <?= $onProfile ? 'border-color: #667eea; background: linear-gradient(135deg, rgba(102, 126, 234, 0.02) 0%, rgba(118, 75, 162, 0.02) 100%);' : '' ?>" data-skill-id="<?= $sid ?>" onmouseover="this.style.boxShadow='0 8px 16px rgba(102, 126, 234, 0.2)'; this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='none'; this.style.transform='translateY(0)'">
                                <?php if ($onProfile): ?>
                                    <span style="display: inline-block; background: #dcfce7; color: #166534; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.75rem; font-weight: 700; margin-bottom: 1rem;">
                                        ✓ ON PROFILE
                                    </span>
                                <?php endif; ?>
                                
                                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
                                <h6 style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.05rem;">
                                    <?= e((string) $skill["skill_name"]) ?>
                                </h6>
                                
                                <form method="post" action="<?= e(url('skills.php')) ?>" class="js-skill-save-form" data-skill-id="<?= $sid ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="save_employee_skill">
                                    <input type="hidden" name="skill_id" value="<?= $sid ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label" style="font-weight: 600; color: #334155; display: block; margin-bottom: 0.5rem;">Proficiency</label>
                                        <select class="form-select skill-level-select" name="proficiency_level" data-skill-id="<?= $sid ?>" required>
                                            <option value="beginner" <?= $level === "beginner" ? "selected" : "" ?>>🟢 Beginner</option>
                                            <option value="intermediate" <?= $level === "intermediate" ? "selected" : "" ?>>🟡 Intermediate</option>
                                            <option value="expert" <?= $level === "expert" ? "selected" : "" ?>>🔴 Expert</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 no-loading js-skill-save-btn" style="font-weight: 600;">
                                        <?= $onProfile ? "📝 Update Profile" : "➕ Add to Profile" ?>
                                    </button>
                                </form>
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
