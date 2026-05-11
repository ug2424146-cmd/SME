<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class UserController
{
    private function resolveRole(string $roleName): ?array
    {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT id, role_name FROM roles WHERE role_name = ? LIMIT 1");
        $stmt->bind_param("s", $roleName);
        $stmt->execute();
        $role = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $role ?: null;
    }

    public function createUser(array $data): void
    {
        $name = trim((string) ($data["name"] ?? ""));
        $email = trim((string) ($data["email"] ?? ""));
        $role = (string) ($data["role"] ?? "employee");
        $password = (string) ($data["password"] ?? "");
        $isActive = isset($data["is_active"]) ? (int) $data["is_active"] : 1;

        if ($name === "" || $email === "" || $password === "") {
            flash("error", "Name, email, and password are required.");
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash("error", "Invalid email address.");
            return;
        }

        if (!in_array($role, ["admin", "manager", "employee"], true)) {
            $role = "employee";
        }

        if (strlen($password) < 8) {
            flash("error", "Password must be at least 8 characters.");
            return;
        }

        global $mysqli;

        $checkStmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $existing = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($existing) {
            flash("error", "Email already exists.");
            return;
        }

        $roleData = $this->resolveRole($role);
        if (!$roleData) {
            flash("error", "Invalid role.");
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, role_id, role, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $roleId = (int) $roleData["id"];
        $roleName = (string) $roleData["role_name"];
        $stmt->bind_param("sssisi", $name, $email, $hashedPassword, $roleId, $roleName, $isActive);
        $stmt->execute();
        $newUserId = $stmt->insert_id;
        $stmt->close();

        create_notification($newUserId, "Welcome", "Your SME Platform account has been created.");
        log_activity((int) (current_user()["id"] ?? 0), "user_create", "Created user #" . $newUserId);
        flash("success", "User created successfully.");
    }

    public function updateUser(array $data, int $currentUserId): void
    {
        $userId = (int) ($data["user_id"] ?? 0);
        $name = trim((string) ($data["name"] ?? ""));
        $role = (string) ($data["role"] ?? "employee");
        $newPassword = (string) ($data["password"] ?? "");
        $isActive = isset($data["is_active"]) ? (int) $data["is_active"] : 1;

        if ($userId <= 0 || $name === "") {
            flash("error", "Invalid user update data.");
            return;
        }

        if (!in_array($role, ["admin", "manager", "employee"], true)) {
            $role = "employee";
        }

        global $mysqli;
        $roleData = $this->resolveRole($role);
        if (!$roleData) {
            flash("error", "Invalid role.");
            return;
        }
        $roleId = (int) $roleData["id"];
        $roleName = (string) $roleData["role_name"];

        if ($newPassword !== "") {
            if (strlen($newPassword) < 8) {
                flash("error", "New password must be at least 8 characters.");
                return;
            }
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("UPDATE users SET name = ?, role_id = ?, role = ?, is_active = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sisisi", $name, $roleId, $roleName, $isActive, $hashedPassword, $userId);
        } else {
            $stmt = $mysqli->prepare("UPDATE users SET name = ?, role_id = ?, role = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("sisii", $name, $roleId, $roleName, $isActive, $userId);
        }

        $stmt->execute();
        $stmt->close();

        // Keep current session role/name in sync when admin updates own account.
        if ($currentUserId === $userId && isset($_SESSION["user"])) {
            $_SESSION["user"]["name"] = $name;
            $_SESSION["user"]["role"] = $roleName;
        }

        log_activity($currentUserId, "user_update", "Updated user #" . $userId);
        flash("success", "User updated successfully.");
    }

    public function deleteUser(array $data, int $currentUserId): void
    {
        $userId = (int) ($data["user_id"] ?? 0);
        if ($userId <= 0) {
            flash("error", "Invalid user selected.");
            return;
        }

        if ($userId === $currentUserId) {
            flash("error", "You cannot delete your own account.");
            return;
        }

        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        log_activity($currentUserId, "user_delete", "Deleted user #" . $userId);
        flash("success", "User deleted successfully.");
    }
}
