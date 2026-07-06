<?php
function dynamic_lesson_url(string $path = ""): string
{
    // ltrim() removes any leading slash so the path does not become //dynamic_list.php.
    $path = ltrim($path, "/");
    return "/m06/lessons/$path";
}

function dynamic_table_name(): string
{
    // The table name is hard coded for this lesson.
    return "Samples";
}

function dynamic_ignored_columns(): array
{
    // These columns are managed by the database or the page flow.
    return ["id", "created", "modified"];
}

function should_ignore_column(array $column, array $ignoredColumns): bool
{
    // in_array() checks whether the column name appears in the ignore list.
    return in_array($column["Field"], $ignoredColumns, true);
}

function input_type_for_column(array $column): string
{
    $type = strtolower($column["Type"]);
    $field = strtolower($column["Field"]);

    if (str_contains($field, "email")) {
        return "email";
    }

    if (str_contains($field, "url") || str_contains($field, "website")) {
        return "url";
    }

    // str_contains() checks if one text value appears inside another text value.
    if (str_contains($type, "tinyint(1)")) {
        return "checkbox";
    }

    if (str_contains($type, "date") && !str_contains($type, "datetime") && !str_contains($type, "timestamp")) {
        return "date";
    }

    if (str_contains($type, "timestamp") || str_contains($type, "datetime")) {
        return "datetime-local";
    }

    if (
        str_contains($type, "int")
        || str_contains($type, "decimal")
        || str_contains($type, "float")
    ) {
        return "number";
    }

    if (str_contains($type, "text")) {
        return "textarea";
    }

    if (str_contains($type, "time")) {
        return "time";
    }

    return "text";
}

function render_dynamic_field(array $column, $value = ""): void
{
    $field = $column["Field"];
    $type = input_type_for_column($column);
    $safeField = htmlspecialchars($field);
    $safeValue = htmlspecialchars((string)$value);
    ?>

    <label for="<?php echo $safeField; ?>">
        <?php echo $safeField; ?>
    </label>

    <?php if ($type === "textarea"): ?>
        <textarea id="<?php echo $safeField; ?>"
            name="<?php echo $safeField; ?>"><?php echo $safeValue; ?></textarea>
    <?php elseif ($type === "checkbox"): ?>
        <?php $checked = (int)$value === 1 ? "checked" : ""; ?>
        <input type="checkbox"
            id="<?php echo $safeField; ?>"
            name="<?php echo $safeField; ?>"
            value="1"
            <?php echo $checked; ?>>
    <?php else: ?>
        <input type="<?php echo htmlspecialchars($type); ?>"
            id="<?php echo $safeField; ?>"
            name="<?php echo $safeField; ?>"
            value="<?php echo $safeValue; ?>">
    <?php endif; ?>
    <?php
}
?>