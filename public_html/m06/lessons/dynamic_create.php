<?php
require_once(__DIR__ . "/dynamic_setup.php");

$message = "";
$generatedSql = "";
$generatedParams = [];

if (isset($_POST["create"])) {
    $data = [];

    foreach ($columns as $column) {
        $field = $column["Field"];

        if (should_ignore_column($column, $ignoredColumns)) {
            continue;
        }

        if (input_type_for_column($column) === "checkbox") {
            // Unchecked checkboxes are not submitted, so missing means 0.
            $data[$field] = isset($_POST[$field]) ? 1 : 0;
        } else {
            $data[$field] = trim($_POST[$field] ?? "");
        }
    }

    $columnNames = array_keys($data);
    $escapedColumns = [];
    $placeholders = [];

    foreach ($columnNames as $name) {
        $escapedColumns[] = "`$name`";
        $placeholders[] = ":$name";
    }

    $sql = "INSERT INTO `$tableName` ("
        . implode(", ", $escapedColumns)
        . ") VALUES ("
        . implode(", ", $placeholders)
        . ")";

    $generatedSql = $sql;
    $generatedParams = $data;

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        $message = "Sample created.";
    } catch (PDOException $e) {
        error_log("Dynamic create failed: " . $e->getMessage());
        $message = "Unable to create the sample.";
    }
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

<!-- TODO add the dynamic create form snippet here. -->
 <!-- Add this under the generated SQL/params output from the previous snippet. -->
<form method="post">
    <?php foreach ($columns as $column): ?>
        <?php if (should_ignore_column($column, $ignoredColumns)) continue; ?>

        <?php render_dynamic_field($column); ?>
    <?php endforeach; ?>

    <button type="submit" name="create">Create</button>
</form>