<?php
// public_html/project/admin/list_stocks.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");
$page_title = "List Stocks";

$results = [];

try {
    $results = selectAll(
        "SELECT id, symbol, price, latest_trading_day, is_api
         FROM Stocks
         ORDER BY modified DESC
         LIMIT 10"
    );
} catch (Throwable $e) {
    error_log("List stocks failed: " . $e->getMessage());
    add_flash("Unable to load stocks.", "danger");
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
        <h1>Stocks</h1>
        <?php
foreach ($results as &$result) {
    $result["source"] = $result["is_api"] ? "API" : "Manual";
    $result["latest_trading_day"] = $result["latest_trading_day"] ?? "N/A";
}
unset($result);
$columns = ["symbol" => "Symbol", "price" => "Price", "latest_trading_day" => "Latest Trading Day", "source" => "Source"];
$actions = [["label" => "Edit", "url" => "admin/edit_stock.php"]];
render_table($results, $columns, $actions);
?>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
