<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class SkillController
{
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
        $stmt->execute();
        $stmt->close();
        log_activity($actorId, "skill_create", "Created skill: " . $skill);
        flash("success", "Skill created.");
    }

    public function upsertEmployeeSkill(array $data, int $userId): void
    {
        $skillId = (int) ($data["skill_id"] ?? 0);
        $level = (string) ($data["proficiency_level"] ?? "beginner");
        if ($skillId <= 0 || !in_array($level, ["beginner", "intermediate", "expert"], true)) {
            flash("error", "Invalid skill update.");
            return;
        }
        global $mysqli;
        $stmt = $mysqli->prepare(
            "INSERT INTO employee_skills (user_id, skill_id, proficiency_level) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE proficiency_level = VALUES(proficiency_level)"
        );
        $stmt->bind_param("iis", $userId, $skillId, $level);
        $stmt->execute();
        $stmt->close();
        log_activity($userId, "employee_skill_update", "Updated skill #" . $skillId . " level to " . $level);
        flash("success", "Skill profile updated.");
    }
}
