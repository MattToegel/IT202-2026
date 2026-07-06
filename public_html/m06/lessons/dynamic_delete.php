<?php
require_once(__DIR__ . "/../../../lib/db.php");
require_once(__DIR__ . "/dynamic_utils.php");

$db = getDB();
$tableName = dynamic_table_name();

$id = (int)($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: dynamic_list.php?error=missing_id");
    exit;
}

$sql = "DELETE FROM `$tableName` WHERE id = :id LIMIT 1";
$params = [":id" => $id];

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        header("Location: dynamic_list.php?message=deleted");
    } else {
        header("Location: dynamic_list.php?error=not_found");
    }
    exit;
} catch (PDOException $e) {
    error_log("Dynamic delete failed: " . $e->getMessage());
    header("Location: dynamic_list.php?error=delete_failed");
    exit;
}
?>