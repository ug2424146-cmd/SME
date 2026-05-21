<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";

require_auth();
$user = current_user();
$isEmployee = $user["role"] === "employee";
$userId = (int) $user["id"];

$type = (string) ($_GET["type"] ?? "task_progress");
$format = (string) ($_GET["format"] ?? "csv");
$statusFilter = (string) ($_GET["status"] ?? "");

if (!in_array($format, ["csv", "excel", "pdf"], true)) {
    $format = "csv";
}

if ($type === "task_progress") {
    $sql = "SELECT t.id, t.title, u.name assignee, t.priority, t.status, t.deadline, t.created_at
            FROM tasks t
            INNER JOIN users u ON u.id = t.assigned_to
            WHERE 1=1";
    $params = [];
    $types = "";

    if ($isEmployee) {
        $sql .= " AND t.assigned_to = ?";
        $params[] = $userId;
        $types .= "i";
    }

    if (in_array($statusFilter, ["pending", "in_progress", "completed"], true)) {
        $sql .= " AND t.status = ?";
        $params[] = $statusFilter;
        $types .= "s";
    }

    $sql .= " ORDER BY t.created_at DESC";

    if ($params !== []) {
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $rows = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    if ($format === "pdf") {
        header("Content-Type: text/html; charset=UTF-8");
        $title = $isEmployee ? "My Task Report" : "Task Progress Report";
        echo "<h2>" . htmlspecialchars($title, ENT_QUOTES, "UTF-8") . "</h2>";
        echo "<table border='1' cellpadding='6'><tr><th>Task ID</th><th>Title</th><th>Assignee</th><th>Priority</th><th>Status</th><th>Deadline</th><th>Created At</th></tr>";
        foreach ($rows as $row) {
            echo "<tr><td>" . htmlspecialchars((string) $row["id"]) . "</td><td>" . htmlspecialchars((string) $row["title"]) . "</td><td>" . htmlspecialchars((string) $row["assignee"]) . "</td><td>" . htmlspecialchars((string) $row["priority"]) . "</td><td>" . htmlspecialchars((string) $row["status"]) . "</td><td>" . htmlspecialchars((string) $row["deadline"]) . "</td><td>" . htmlspecialchars((string) $row["created_at"]) . "</td></tr>";
        }
        echo "</table>";
        exit;
    }

    header("Content-Type: text/csv");
    $filename = $format === "excel" ? "task_progress_report.xls" : "task_progress_report.csv";
    header("Content-Disposition: attachment; filename=" . $filename);
    $out = fopen("php://output", "w");
    fputcsv($out, ["Task ID", "Title", "Assignee", "Priority", "Status", "Deadline", "Created At"]);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

if ($type === "performance") {
    $sql = "SELECT u.name employee, r.name reviewer, p.rating, p.feedback, p.review_date
            FROM performance p
            INNER JOIN users u ON u.id = p.user_id
            INNER JOIN users r ON r.id = p.reviewer_id";
    $params = [];
    $types = "";

    if ($isEmployee) {
        $sql .= " WHERE p.user_id = ?";
        $params[] = $userId;
        $types = "i";
    }

    $sql .= " ORDER BY p.review_date DESC";

    if ($params !== []) {
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $rows = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    if ($format === "pdf") {
        header("Content-Type: text/html; charset=UTF-8");
        $title = $isEmployee ? "My Performance Report" : "Performance Report";
        echo "<h2>" . htmlspecialchars($title, ENT_QUOTES, "UTF-8") . "</h2>";
        echo "<table border='1' cellpadding='6'><tr><th>Employee</th><th>Reviewer</th><th>Rating</th><th>Feedback</th><th>Review Date</th></tr>";
        foreach ($rows as $row) {
            echo "<tr><td>" . htmlspecialchars((string) $row["employee"]) . "</td><td>" . htmlspecialchars((string) $row["reviewer"]) . "</td><td>" . htmlspecialchars((string) $row["rating"]) . "</td><td>" . htmlspecialchars((string) $row["feedback"]) . "</td><td>" . htmlspecialchars((string) $row["review_date"]) . "</td></tr>";
        }
        echo "</table>";
        exit;
    }

    header("Content-Type: text/csv");
    $filename = $format === "excel" ? "performance_report.xls" : "performance_report.csv";
    header("Content-Disposition: attachment; filename=" . $filename);
    $out = fopen("php://output", "w");
    fputcsv($out, ["Employee", "Reviewer", "Rating", "Feedback", "Review Date"]);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

http_response_code(400);
echo "Invalid report type";
