<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class TeamProgressController
{
    private function isEmployeeUser(int $userId): bool
    {
        global $mysqli;
        $stmt = $mysqli->prepare(
            "SELECT COALESCE(r.role_name, u.role) AS role_name FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row && ($row["role_name"] ?? "") === "employee";
    }

    public function assignToDepartment(array $data, int $actorId): void
    {
        $userId = (int) ($data["user_id"] ?? 0);
        $departmentId = (int) ($data["department_id"] ?? 0);
        if ($userId <= 0 || $departmentId <= 0) {
            flash("error", "Choose an employee and a department.");
            return;
        }
        if (!$this->isEmployeeUser($userId)) {
            flash("error", "Only employees can be assigned to a department.");
            return;
        }

        global $mysqli;
        $chk = $mysqli->prepare("SELECT id FROM departments WHERE id = ? LIMIT 1");
        $chk->bind_param("i", $departmentId);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc() === null) {
            $chk->close();
            flash("error", "Department not found.");
            return;
        }
        $chk->close();

        $stmt = $mysqli->prepare(
            "INSERT IGNORE INTO user_departments (user_id, department_id) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $userId, $departmentId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            flash("error", "That employee is already in the selected department.");
            return;
        }

        log_activity($actorId, "team_assign", "Assigned user #{$userId} to department #{$departmentId}");
        flash("success", "Employee added to the department.");
    }

    public function removeFromDepartment(array $data, int $actorId): void
    {
        $userId = (int) ($data["user_id"] ?? 0);
        $departmentId = (int) ($data["department_id"] ?? 0);
        if ($userId <= 0 || $departmentId <= 0) {
            flash("error", "Choose an employee and a department to remove.");
            return;
        }

        global $mysqli;
        $stmt = $mysqli->prepare(
            "DELETE FROM user_departments WHERE user_id = ? AND department_id = ?"
        );
        $stmt->bind_param("ii", $userId, $departmentId);
        $stmt->execute();
        $removed = $stmt->affected_rows;
        $stmt->close();

        if ($removed === 0) {
            flash("error", "No matching department assignment found.");
            return;
        }

        log_activity($actorId, "team_unassign", "Removed user #{$userId} from department #{$departmentId}");
        flash("success", "Employee removed from that department.");
    }
}
