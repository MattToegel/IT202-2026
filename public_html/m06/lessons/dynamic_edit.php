<?php
require_once(__DIR__ . "/dynamic_setup.php");

$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);
if ($id <= 0) {
    header("Location: dynamic_list.php?error=missing_id");
    exit;
}

$message = "";
$generatedSql = "";
$generatedParams = [];

// TODO add the dynamic edit update snippet here before the selected row reloads.
// Replace the update TODO in dynamic_edit.php with this block.
if (isset($_POST["save"])) {
    $data = [":id" => $id];
    $setParts = [];

    foreach ($columns as $column) {
        $field = $column["Field"];

        if (should_ignore_column($column, $ignoredColumns)) {
            continue;
        }

        $placeholder = ":$field";
        $setParts[] = "`$field` = $placeholder";

        if (input_type_for_column($column) === "checkbox") {
            // Unchecked checkboxes are not submitted, so missing means 0.
            $data[$placeholder] = isset($_POST[$field]) ? 1 : 0;
        } else {
            $data[$placeholder] = trim($_POST[$field] ?? "");
        }
    }

    $sql = "UPDATE `$tableName` SET "
        . implode(", ", $setParts)
        . " WHERE id = :id";

    $generatedSql = $sql;
    $generatedParams = $data;

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        $message = "Sample updated.";
    } catch (PDOException $e) {
        error_log("Dynamic update failed: " . $e->getMessage());
        $message = "Unable to update the sample.";
    }
}


try {
    $stmt = $db->prepare("SELECT * FROM `$tableName` WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dynamic edit fetch failed: " . $e->getMessage());
    header("Location: dynamic_list.php?error=load_failed");
    exit;
}

if (!$record) {
    header("Location: dynamic_list.php?error=not_found");
    exit;
}
?>

<?php render_dynamic_nav(); ?>

<?php if ($message !== ""): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if ($generatedSql !== ""): ?>
    <h2>Generated SQL</h2>
    <pre><?php echo htmlspecialchars($generatedSql); ?></pre>

    <h2>Generated Params</h2>
    <pre><?php echo htmlspecialchars(print_r($generatedParams, true)); ?></pre>
<?php endif; ?>

<!-- TODO add the dynamic edit form snippet here. -->
 <!-- Add this where dynamic_edit.php says to add the form snippet. -->
<form method="post">
    <!-- Hidden inputs submit values that the user should not edit directly. -->
    <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

    <?php foreach ($columns as $column): ?>
        <?php if (should_ignore_column($column, $ignoredColumns)) continue; ?>
        <?php
            $field = $column["Field"];
            // The selected record supplies the sticky/current value for each field.
            $value = $record[$field] ?? "";
        ?>

        <?php render_dynamic_field($column, $value); ?>
    <?php endforeach; ?>

    <button type="submit" name="save">Save</button>
</form>