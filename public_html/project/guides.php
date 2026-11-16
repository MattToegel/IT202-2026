<?php
// public_html/project/guides.php
require_once(__DIR__ . "/../../lib/app.php");

$sort_options = [
    "modified" => "Recently Updated",
    "title" => "Title",
    "primary_category" => "Category",
    "game" => "Game",
    "player_race" => "Player Race",
    "opponent_race" => "Opponent Race",
];
$allowed_sort_columns = array_keys($sort_options);
$list_config = [
    "filters" => guide_filter_rules(),
    "sort_columns" => $allowed_sort_columns,
];

$list_state = build_list_query_state($_GET, $list_config);
$filters = $list_state["filters"];
$sort = $list_state["sort"];
$direction = $list_state["direction"];
$order_by = $list_state["order_by"];
$limit = $list_state["limit"];

$filter_query = build_guide_filter_query($filters);
$where = "";
if (!empty($filter_query["sql"])) {
    $where = "WHERE " . $filter_query["sql"];
}
$params = $filter_query["params"];

$results = [];
try {
    $results = selectAll(
        "SELECT id, title, primary_category, player_race, opponent_race, matchup,
                IF(api_id IS NULL, 'Manual', 'API') AS source
         FROM Guides
         $where
         ORDER BY $order_by, id ASC
         LIMIT $limit",
        $params
    );
} catch (TypeError | Exception $e) {
    error_log("Guide list failed: " . $e->getMessage());
    add_flash("Guides could not be loaded.", "danger");
}

$page_title = "StarCraft Guides";
// Define the visible table columns before the page renders them.
$guide_columns = [
    "title" => "Title",
    "primary_category" => "Category",
    "player_race" => "Player",
    "opponent_race" => "Opponent",
    "source" => "Source",
];

// View is public. Only an Admin receives management actions.
$guide_actions = [["label" => "View", "url" => "guide.php", "variant" => "primary"]];
if (has_role("Admin")) {
    $guide_actions[] = ["label" => "Edit", "url" => "admin/edit_guide.php", "variant" => "warning"];
    $guide_actions[] = [
        "label" => "Delete",
        "url" => "admin/delete_guide.php",
        "method" => "POST",
        "action" => "delete_guide",
        "query_parameters" => ["return_to" => "guides.php"],
        "variant" => "danger",
    ];
}
?>
<!doctype html>
<html lang="en">
<head><?php render_head($page_title); ?></head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <?php render_guide_search(
            $filters,
            $limit,
            $sort,
            $direction,
            $sort_options
        ); ?>
        <?php render_table(
            $results,
            $guide_columns,
            $guide_actions,
            "No guides have been saved yet."
        ); ?>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(["guides.js"]); ?>
</body>
</html>
