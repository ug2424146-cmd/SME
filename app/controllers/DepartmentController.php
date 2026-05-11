<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class DepartmentController
{
    public function create(array $data, int $actorId): void
    {
        $name = trim((string) ($data["department_name"] ?? ""));
        if ($name === "") {
            flash("error", "Department name is required.");
            return;
        }
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO departments (department_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        log_activity($actorId, "department_create", "Created department: " . $name);
        flash("success", "Department created.");
    }
}
