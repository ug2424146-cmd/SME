<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class TaskController
{
    private function trackHistory(int $taskId, ?int $userId, string $action, ?string $details = null): void
    {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO task_history (task_id, user_id, action, details) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $taskId, $userId, $action, $details);
        $stmt->execute();
        $stmt->close();
    }

    private function chooseBestEmployee(int $requiredSkillId): ?int
    {
        global $mysqli;
        $stmt = $mysqli->prepare(
            "SELECT u.id,
                    COALESCE(CASE es.proficiency_level
                        WHEN 'expert' THEN 3
                        WHEN 'intermediate' THEN 2
                        ELSE 1 END, 0) AS skill_score,
                    COUNT(t.id) AS workload
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id AND r.role_name = 'employee'
             LEFT JOIN employee_skills es ON es.user_id = u.id AND es.skill_id = ?
             LEFT JOIN tasks t ON t.assigned_to = u.id AND t.status IN ('pending', 'in_progress')
             WHERE u.is_active = 1
             GROUP BY u.id, es.proficiency_level
             ORDER BY skill_score DESC, workload ASC, u.id ASC
             LIMIT 1"
        );
        $stmt->bind_param("i", $requiredSkillId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (int) $row["id"] : null;
    }

    public function createTask(array $data, array $user): void
    {
        if ($user["role"] !== "manager" && $user["role"] !== "admin") {
            http_response_code(403);
            exit("Forbidden");
        }

        $title = trim((string) ($data["title"] ?? ""));
        $description = trim((string) ($data["description"] ?? ""));
        $assignedTo = (int) ($data["assigned_to"] ?? 0);
        $requiredSkillId = (int) ($data["required_skill_id"] ?? 0);
        $autoAssign = (int) ($data["auto_assign"] ?? 0) === 1;
        $priority = (string) ($data["priority"] ?? "medium");
        $deadline = trim((string) ($data["deadline"] ?? ""));

        if ($title === "") {
            flash("error", "Title is required.");
            return;
        }

        if (!in_array($priority, ["low", "medium", "high"], true)) {
            $priority = "medium";
        }

        global $mysqli;
        if ($autoAssign && $requiredSkillId > 0) {
            $bestEmployeeId = $this->chooseBestEmployee($requiredSkillId);
            if ($bestEmployeeId !== null) {
                $assignedTo = $bestEmployeeId;
            }
        }

        if ($assignedTo <= 0) {
            flash("error", "Assigned employee is required.");
            return;
        }

        $employeeCheck = $mysqli->prepare(
            "SELECT u.id
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND r.role_name = 'employee' AND u.is_active = 1
             LIMIT 1"
        );
        $employeeCheck->bind_param("i", $assignedTo);
        $employeeCheck->execute();
        $employee = $employeeCheck->get_result()->fetch_assoc();
        $employeeCheck->close();

        if (!$employee) {
            flash("error", "Selected user is not a valid employee.");
            return;
        }

        $deadlineValue = $deadline !== "" ? $deadline : null;
        $stmt = $mysqli->prepare(
            "INSERT INTO tasks (title, description, assigned_to, assigned_by, required_skill_id, priority, status, deadline)
             VALUES (?, ?, ?, ?, NULLIF(?, 0), ?, 'pending', ?)"
        );
        $stmt->bind_param("ssiiiss", $title, $description, $assignedTo, $user["id"], $requiredSkillId, $priority, $deadlineValue);
        $stmt->execute();
        $taskId = $stmt->insert_id;
        $stmt->close();

        create_notification($assignedTo, "New Task Assigned", "You have been assigned task: " . $title);
        log_activity((int) $user["id"], "task_create", "Created task #" . $taskId);
        $this->trackHistory($taskId, (int) $user["id"], "task_created", "Task created and assigned");
        flash("success", "Task created successfully.");
    }

    public function updateTaskStatus(array $data, array $user): void
    {
        $taskId = (int) ($data["task_id"] ?? 0);
        $status = (string) ($data["status"] ?? "");
        $allowedStatuses = ["pending", "in_progress", "completed"];

        if ($taskId <= 0 || !in_array($status, $allowedStatuses, true)) {
            flash("error", "Invalid task update.");
            return;
        }

        global $mysqli;
        if ($user["role"] === "employee") {
            $stmt = $mysqli->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
            $stmt->bind_param("sii", $status, $taskId, $user["id"]);
        } else {
            $stmt = $mysqli->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $taskId);
        }

        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();

        if ($affectedRows < 1) {
            flash("error", "Task not found or update not allowed.");
            return;
        }

        log_activity((int) $user["id"], "task_status_update", "Updated task #" . $taskId . " to " . $status);
        $this->trackHistory($taskId, (int) $user["id"], "status_updated", "Status changed to " . $status);
        flash("success", "Task status updated.");
    }

    public function addComment(array $data, array $user): void
    {
        $taskId = (int) ($data["task_id"] ?? 0);
        $comment = trim((string) ($data["comment"] ?? ""));
        if ($taskId <= 0 || $comment === "") {
            flash("error", "Comment cannot be empty.");
            return;
        }
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $taskId, $user["id"], $comment);
        $stmt->execute();
        $stmt->close();
        $this->trackHistory($taskId, (int) $user["id"], "comment_added", $comment);
        flash("success", "Comment added.");
    }

    public function uploadAttachment(array $files, array $data, array $user): void
    {
        $taskId = (int) ($data["task_id"] ?? 0);
        if ($taskId <= 0 || !isset($files["attachment"]) || $files["attachment"]["error"] !== UPLOAD_ERR_OK) {
            flash("error", "Attachment upload failed.");
            return;
        }
        $file = $files["attachment"];
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', "_", basename((string) $file["name"]));
        $targetRel = "uploads/tasks/" . time() . "_" . $safeName;
        $targetAbs = __DIR__ . "/../../public/" . $targetRel;
        if (!move_uploaded_file((string) $file["tmp_name"], $targetAbs)) {
            flash("error", "Could not save attachment.");
            return;
        }
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO task_attachments (task_id, user_id, file_name, file_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $taskId, $user["id"], $safeName, $targetRel);
        $stmt->execute();
        $stmt->close();
        $this->trackHistory($taskId, (int) $user["id"], "attachment_uploaded", $safeName);
        flash("success", "Attachment uploaded.");
    }
}
