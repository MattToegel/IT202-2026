<?php
// partials/table.php
/**
 * Usage:
 * $columns = ["username" => "Username", "email" => "Email"];
 * $actions = [
 *     [
 *         "label" => "View",
 *         "url" => "user.php",
 *         "row_key" => "id",
 *         "parameter" => "id",
 *         "method" => "GET",
 *         "variant" => "primary",
 *     ],
 *     [
 *         "label" => "Edit",
 *         "url" => "admin/edit_user.php",
 *         "row_key" => "id",
 *         "parameter" => "id",
 *         "method" => "GET",
 *         "variant" => "secondary",
 *     ],
 *     [
 *         "label" => "Delete",
 *         "url" => "admin/delete_user.php",
 *         "row_key" => "id",
 *         "parameter" => "id",
 *         "method" => "POST",
 *         "action" => "delete_user",
 *         "query_parameters" => ["return_to" => "admin/list_users.php"],
 *         "variant" => "danger",
 *     ],
 * ];
 *
 * render_table(
 *     $results,
 *     $columns,
 *     $actions
 * );
 *
 * View and Edit render as links; Delete renders as a POST form.
 */
if (!isset($columns)) {
    error_log('Missing $columns definition');
    $columns = [];
}
if (!isset($empty_message)) {
    $empty_message = "No records to show";
}
if (!isset($results)) {
    error_log('Missing $results definition');
    $results = [];
}

$column_count = count($columns);
if (!empty($actions)) {
    $column_count++;
}
?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <?php foreach ($columns as $label): ?>
                    <th scope="col"><?php echo htmlspecialchars((string) $label); ?></th>
                <?php endforeach; ?>
                <?php if (!empty($actions)): ?>
                    <th scope="col">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr>
                    <td colspan="<?php echo $column_count; ?>">
                        <?php echo htmlspecialchars($empty_message); ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($results as $result): ?>
                <tr>
                    <?php foreach ($columns as $key => $label): ?>
                        <td><?php echo htmlspecialchars((string) ($result[$key] ?? "")); ?></td>
                    <?php endforeach; ?>

                    <?php if (!empty($actions)): ?>
                        <td class="d-flex flex-wrap gap-2">
                            <?php foreach ($actions as $action): ?>
                                <?php
                                // The page decides which authorized actions reach this partial.
                                $row_key = (string) ($action["row_key"] ?? "id");
                                $parameter = (string) ($action["parameter"] ?? "id");
                                $method = strtoupper((string) ($action["method"] ?? "GET"));
                                $label = (string) ($action["label"] ?? "Open");
                                $variant = (string) ($action["variant"] ?? "secondary");
                                $query_parameters = $action["query_parameters"] ?? [];
                                if (!is_array($query_parameters)) {
                                    $query_parameters = [];
                                }
                                $row_value = $result[$row_key] ?? null;

                                if ($row_value === null || !isset($action["url"])) {
                                    continue;
                                }

                                $url = project_url((string) $action["url"]);
                                ?>
                                <?php if ($method === "POST"): ?>
                                    <?php
                                    $form_url = $url;
                                    if (!empty($query_parameters)) {
                                        $form_url .= "?" . http_build_query($query_parameters);
                                    }
                                    ?>
                                    <form method="post" action="<?php echo htmlspecialchars($form_url); ?>" onsubmit="return validate(this);">

                                            <input type="hidden" name="<?php echo htmlspecialchars($parameter); ?>"
                                                value="<?php echo htmlspecialchars((string) $row_value); ?>">
                                        <?php render_button([
                                            "text" => $label,
                                            "variant" => $variant,
                                            "attributes" => [
                                                "class" => "btn-sm", "name" => "action",
                                                "value" => $action["action"] ?? "submit",
                                            ],
                                        ]); ?>
                                    </form>
                                <?php else: ?>
                                    <?php
                                    $query_parameters[$parameter] = $row_value;
                                    $href = $url . "?" . http_build_query($query_parameters);
                                    ?>
                                    <a class="btn btn-<?php echo htmlspecialchars($variant); ?> btn-sm"
                                        href="<?php echo htmlspecialchars($href); ?>">
                                        <?php echo htmlspecialchars($label); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
