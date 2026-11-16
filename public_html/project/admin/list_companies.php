<?php
// public_html/project/admin/list_companies.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");
$page_title = "List Companies";

$results = [];

try {
    $results = selectAll(
        "SELECT id, symbol, name, type, region, is_api
         FROM Companies
         ORDER BY modified DESC
         LIMIT 10"
    );
} catch (Throwable $e) {
    error_log("List companies failed: " . $e->getMessage());
    add_flash("Unable to load companies.", "danger");
}
?>
<!doctype html>
<html lang="en">
<head>
    <?php render_head($page_title); ?>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <h1>Companies</h1>
        <?php
foreach ($results as &$result) {
    $result["source"] = $result["is_api"] ? "API" : "Manual";
}
unset($result);
$columns = ["symbol" => "Symbol", "name" => "Name", "type" => "Type", "region" => "Region", "source" => "Source"];
$actions = [["label" => "Edit", "url" => "admin/edit_company.php"]];
render_table($results, $columns, $actions);
?>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
