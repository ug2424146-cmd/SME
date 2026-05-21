<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class SkillController
{
    private function skillIdByName(string $skillName): ?int
    {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT id FROM skills WHERE skill_name = ? LIMIT 1");
        $stmt->bind_param("s", $skillName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? (int) $row["id"] : null;
    }

    private function addSkillToProfile(int $userId, int $skillId, string $level = "beginner"): bool
    {
        if (!in_array($level, ["beginner", "intermediate", "expert"], true)) {
            $level = "beginner";
        }

        global $mysqli;
        $stmt = $mysqli->prepare(
            "INSERT INTO employee_skills (user_id, skill_id, proficiency_level) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE proficiency_level = VALUES(proficiency_level)"
        );
        $stmt->bind_param("iis", $userId, $skillId, $level);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function createSkill(array $data, int $actorId): void
    {
        $skill = trim((string) ($data["skill_name"] ?? ""));
        if ($skill === "") {
            flash("error", "Skill name is required.");
            return;
        }

        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO skills (skill_name) VALUES (?)");
        $stmt->bind_param("s", $skill);
        $skillId = null;

        if ($stmt->execute()) {
            $skillId = (int) $stmt->insert_id;
            $stmt->close();
            log_activity($actorId, "skill_create", "Created skill: " . $skill);
        } else {
            $errno = $mysqli->errno;
            $stmt->close();
            if ($errno === 1062) {
                $skillId = $this->skillIdByName($skill);
                if ($skillId === null) {
                    flash("error", "A skill with that name already exists.");
                    return;
                }
            } else {
                flash("error", "Could not create skill. Please try again.");
                return;
            }
        }

        if ($this->addSkillToProfile($actorId, $skillId, "beginner")) {
            log_activity($actorId, "employee_skill_update", "Added skill #{$skillId} to profile");
            flash("success", "Skill saved. It appears under Your skills below.");
        } else {
            flash("success", "Skill added to the catalog. Open it below and click Save Skill to add it to your profile.");
        }
    }

    /**
     * @return array{ok: bool, message: string, level?: string}
     */
    public function upsertEmployeeSkill(array $data, int $userId, bool $useFlash = true): array
    {
        $skillId = (int) ($data["skill_id"] ?? 0);
        $level = trim((string) ($data["proficiency_level"] ?? ""));

        if ($skillId <= 0) {
            $message = "Invalid skill.";
            if ($useFlash) {
                flash("error", $message);
            }
            return ["ok" => false, "message" => $message];
        }

        if ($level === "") {
            $message = "Please choose a proficiency level.";
            if ($useFlash) {
                flash("error", $message);
            }
            return ["ok" => false, "message" => $message];
        }

        if (!in_array($level, ["beginner", "intermediate", "expert"], true)) {
            $message = "Invalid proficiency level.";
            if ($useFlash) {
                flash("error", $message);
            }
            return ["ok" => false, "message" => $message];
        }

        global $mysqli;
        $chk = $mysqli->prepare("SELECT id FROM skills WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $skillId);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc() === null) {
            $chk->close();
            $message = "That skill no longer exists.";
            if ($useFlash) {
                flash("error", $message);
            }
            return ["ok" => false, "message" => $message];
        }
        $chk->close();

        if (!$this->addSkillToProfile($userId, $skillId, $level)) {
            $message = "Could not save skill to your profile.";
            if ($useFlash) {
                flash("error", $message);
            }
            return ["ok" => false, "message" => $message];
        }

        log_activity($userId, "employee_skill_update", "Updated skill #" . $skillId . " level to " . $level);
        $message = "Your skill was updated.";
        if ($useFlash) {
            flash("success", $message);
        }

        return ["ok" => true, "message" => $message, "level" => $level, "skill_id" => $skillId];
    }
}
