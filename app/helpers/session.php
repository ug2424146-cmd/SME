<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        "cookie_httponly" => true,
        "cookie_samesite" => "Lax",
        "use_strict_mode" => true,
    ]);
}

function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function verify_csrf_token(?string $token): bool
{
    return !empty($token) && !empty($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);
}

function current_user(): ?array
{
    return $_SESSION["user"] ?? null;
}

function require_auth(): void
{
    if (current_user() === null) {
        header("Location: " . url("index.php"));
        exit;
    }
}

function require_role(array $roles): void
{
    require_auth();
    $user = current_user();
    if (!in_array($user["role"], $roles, true)) {
        http_response_code(403);
        exit("Forbidden");
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function app_base_path(): string
{
    $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
    $publicPos = strpos($scriptName, "/public/");

    if ($publicPos === false) {
        return "";
    }

    return substr($scriptName, 0, $publicPos + 8);
}

function url(string $path): string
{
    return app_base_path() . "/" . ltrim($path, "/");
}

function flash(string $key, string $message): void
{
    $_SESSION["flash"][$key] = $message;
}

function get_flash(string $key): ?string
{
    if (!isset($_SESSION["flash"][$key])) {
        return null;
    }

    $message = $_SESSION["flash"][$key];
    unset($_SESSION["flash"][$key]);
    return $message;
}
