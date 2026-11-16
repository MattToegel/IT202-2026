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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Stocks</h1>
        <table>
            <thead>
                <tr><th>Symbol</th><th>Price</th><th>Latest Trading Day</th><th>Source</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <?php
                    $source_label = "Manual";
                    if ($result["is_api"]) {
                        $source_label = "API";
                    }
                    $latest_trading_day = "N/A";
                    if (isset($result["latest_trading_day"])) {
                        $latest_trading_day = $result["latest_trading_day"];
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result["symbol"]); ?></td>
                        <td><?php echo htmlspecialchars($result["price"]); ?></td>
                        <td><?php echo htmlspecialchars($latest_trading_day); ?></td>
                        <td><?php echo $source_label; ?></td>
                        <td><a href="edit_stock.php?id=<?php echo urlencode($result["id"]); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php render_flash_messages(); ?>
</body>
</html>
