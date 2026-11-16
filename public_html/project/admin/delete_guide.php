<?php
// public_html/project/admin/delete_guide.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$allowed_return_pages = [
    "guides.php" => project_url("guides.php"),
    "admin/list_guides.php" => project_url("admin/list_guides.php"),
];
$return_to = $allowed_return_pages["guides.php"];
if (isset($_GET["return_to"]) && is_string($_GET["return_to"])) {
    $requested_return_to = $_GET["return_to"];
    if (isset($allowed_return_pages[$requested_return_to])) {
        $return_to = $allowed_return_pages[$requested_return_to];
    }
}

$id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if (!isset($_POST["action"]) || $_POST["action"] !== "delete_guide" || !is_int($id)) {
    add_flash("Missing guide id.", "danger");
    header("Location: " . $return_to);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM Guides WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() === 1) {
        add_flash("Guide deleted.", "success");
    } else {
        add_flash("Guide not found.", "warning");
    }
} catch (TypeError | Exception $e) {
    error_log("Guide deletion failed: " . $e->getMessage());
    add_flash("The guide could not be deleted.", "danger");
}

header("Location: " . $return_to);
exit;
