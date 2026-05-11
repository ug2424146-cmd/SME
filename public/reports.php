<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_role(["admin", "manager"]);

$type = $_GET["type"] ?? "task_progress";
$format = $_GET["format"] ?? "csv";
$statusFilter = $_GET["status"] ?? "";

if ($type === "task_progress") {
    $sql = "SELECT t.id, t.title, u.name assignee, t.priority, t.status, t.deadline, t.created_at
            FROM tasks t INNER JOIN users u ON u.id = t.assigned_to";
    if (in_array($statusFilter, ["pending", "in_progress", "completed"], true)) {
        $sql .= " WHERE t.status = '" . $mysqli->real_escape_string($statusFilter) . "'";
    }
    $sql .= " ORDER BY t.created_at DESC";
    $rows = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

    if ($format === "pdf") {
        header("Content-Type: text/html; charset=UTF-8");
        echo "<h2>Task Progress Report</h2><table border='1' cellpadding='6'><tr><th>Task ID</th><th>Title</th><th>Assignee</th><th>Priority</th><th>Status</th><th>Deadline</th><th>Created At</th></tr>";
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
    $rows = $mysqli->query(
        "SELECT u.name employee, r.name reviewer, p.rating, p.feedback, p.review_date
         FROM performance p
         INNER JOIN users u ON u.id = p.user_id
         INNER JOIN users r ON r.id = p.reviewer_id
         ORDER BY p.review_date DESC"
    )->fetch_all(MYSQLI_ASSOC);
    if ($format === "pdf") {
        header("Content-Type: text/html; charset=UTF-8");
        echo "<h2>Performance Report</h2><table border='1' cellpadding='6'><tr><th>Employee</th><th>Reviewer</th><th>Rating</th><th>Feedback</th><th>Review Date</th></tr>";
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
