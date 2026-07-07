<?php
require_once(__DIR__ . "/dynamic_setup.php");

$messages = [
    "deleted" => "Sample deleted.",
];
$errors = [
    "missing_id" => "Missing record id.",
    "not_found" => "Record not found.",
    "load_failed" => "Unable to load record.",
    "delete_failed" => "Unable to delete record.",
];

// The URL carries a short code; this page maps the code to friendly text.
$messageKey = $_GET["message"] ?? "";
$errorKey = $_GET["error"] ?? "";
$feedback = $messages[$messageKey] ?? $errors[$errorKey] ?? "";

try {
    $stmt = $db->query("SELECT * FROM `$tableName` ORDER BY id DESC LIMIT 500");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dynamic list failed: " . $e->getMessage());
    $rows = [];
    $feedback = "Unable to load records.";
}
?>

<?php render_dynamic_nav(); ?>

<?php if ($feedback !== ""): ?>
    <p><?php echo htmlspecialchars($feedback); ?></p>
<?php endif; ?>

<?php if (!empty($rows)): ?>
    <table>
        <thead>
            <tr>
                <?php foreach (array_keys($rows[0]) as $heading): ?>
                    <th><?php echo htmlspecialchars($heading); ?></th>
                <?php endforeach; ?>
                <th>actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($row as $value): ?>
                        <td><?php echo htmlspecialchars((string)$value); ?></td>
                    <?php endforeach; ?>
                    <td>
                        <a href="dynamic_edit.php?id=<?php echo (int)$row["id"]; ?>">edit</a>
                        <a href="dynamic_delete.php?id=<?php echo (int)$row["id"]; ?>">delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No records found.</p>
<?php endif; ?>