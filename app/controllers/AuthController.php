<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class AuthController
{
    public function login(): void
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . url("index.php"));
            exit;
        }

        if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
            $_SESSION["error"] = "Invalid CSRF token.";
            header("Location: " . url("index.php"));
            exit;
        }

        $email = trim((string) ($_POST["email"] ?? ""));
        $password = (string) ($_POST["password"] ?? "");

        if ($email === "" || $password === "") {
            $_SESSION["error"] = "Email and password are required.";
            header("Location: " . url("index.php"));
            exit;
        }

        global $mysqli;
        $stmt = $mysqli->prepare(
            "SELECT u.id, u.name, u.email, u.password, u.is_active, COALESCE(r.role_name, u.role) AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user["password"])) {
            $_SESSION["error"] = "Invalid login credentials.";
            header("Location: " . url("index.php"));
            exit;
        }
        if ((int) $user["is_active"] !== 1) {
            $_SESSION["error"] = "Your account is inactive. Contact admin.";
            header("Location: " . url("index.php"));
            exit;
        }

        session_regenerate_id(true);
        $_SESSION["user"] = [
            "id" => (int) $user["id"],
            "name" => $user["name"],
            "email" => $user["email"],
            "role" => $user["role_name"],
        ];
        log_activity((int) $user["id"], "login", "User logged in");

        header("Location: " . url("dashboard.php"));
        exit;
    }

    public function logout(): void
    {
        $userId = $_SESSION["user"]["id"] ?? null;
        if (is_int($userId)) {
            log_activity($userId, "logout", "User logged out");
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header("Location: " . url("index.php"));
        exit;
    }
}
