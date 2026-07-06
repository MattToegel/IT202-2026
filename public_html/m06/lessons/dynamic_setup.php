<?php
require_once(__DIR__ . "/dynamic_nav.php");

$tableName = dynamic_table_name();
$ignoredColumns = dynamic_ignored_columns();

$db = getDB();

try {
    // The table name is hard coded above.
    // If it came from user input, it would need a strict allowlist before SQL.
    $columnsStmt = $db->query("SHOW COLUMNS FROM `$tableName`");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
    //echo "<pre>" . var_export($columns, true) . "</pre>";
} catch (PDOException $e) {
    error_log("Dynamic setup failed: " . $e->getMessage());
    die("Unable to load the dynamic table setup.");
}
?>