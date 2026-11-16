<?php
// public_html/project/admin/edit_guide.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if (!is_int($id)) {
    add_flash("Missing guide id.", "danger");
    header("Location: " . project_url("admin/list_guides.php"));
    exit;
}

$errors = [];
$result = null;
try {
    $result = select(
        "SELECT id, title, primary_category, player_race, opponent_race, summary, source_url
         FROM Guides WHERE id = :id LIMIT 1",
        ["id" => $id]
    );
} catch (TypeError | Exception $e) {
    error_log("Guide lookup failed: " . $e->getMessage());
    add_flash("The guide could not be loaded.", "danger");
    header("Location: " . project_url("admin/list_guides.php"));
    exit;
}
if ($result === null) {
    add_flash("Guide not found.", "warning");
    header("Location: " . project_url("admin/list_guides.php"));
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] === "update_guide") {
    $title = "";
    $primary_category = "";
    $player_race = "";
    $opponent_race = "";
    $summary = "";
    $source_url = "";
    $title = guide_post_text("title");
    $primary_category = guide_post_text("primary_category");
    $player_race = guide_post_text("player_race");
    $opponent_race = guide_post_text("opponent_race");
    $summary = guide_post_text("summary");
    $source_url = guide_post_text("source_url");

    if ($title === "" || strlen($title) > 150) {
        $errors[] = "Title is required and must be 150 characters or less.";
    }
    if ($primary_category === "" || strlen($primary_category) > 50) {
        $errors[] = "Primary category is required and must be 50 characters or less.";
    }
    $valid_races = ["any", "terran", "protoss", "zerg"];
    if (!in_array($player_race, $valid_races, true)) {
        $errors[] = "Choose a valid player race.";
    }
    if (!in_array($opponent_race, $valid_races, true)) {
        $errors[] = "Choose a valid opponent race.";
    }
    if ($summary === "") {
        $errors[] = "Summary is required.";
    }
    if (strlen($source_url) > 500) {
        $errors[] = "Source URL must be 500 characters or less.";
    }
    $source_url_value = null;
    if ($source_url !== "") {
        $source_url_value = sc_nullable_url($source_url);
        if ($source_url_value === null) {
            $errors[] = "Source URL must be a valid HTTP or HTTPS URL.";
        }
    }

    if (empty($errors)) {
        // Keep matchup aligned with the editable race fields.
        $matchup = null;
        if ($player_race !== "any" && $opponent_race !== "any") {
            $matchup = $player_race[0] . "v" . $opponent_race[0];
        }

        try {
            update("Guides", [
                "id" => $id,
                "title" => $title,
                "primary_category" => $primary_category,
                "player_race" => $player_race,
                "opponent_race" => $opponent_race,
                "matchup" => $matchup,
                "summary" => $summary,
                "source_url" => $source_url_value,
            ]);
            add_flash("Guide updated.", "success");
            header("Location: " . project_url("admin/edit_guide.php?id=$id"));
            exit;
        } catch (TypeError | Exception $e) {
            error_log("Guide update failed: " . $e->getMessage());
            $errors[] = "The guide could not be updated.";
        }
    }

    // Keep safe submitted values visible after validation errors.
    $result = compact(
        "id", "title", "primary_category", "player_race",
        "opponent_race", "summary", "source_url"
    );
}

flash_errors($errors);
$page_title = "Edit StarCraft Guide";
?>
<!doctype html>
<html lang="en">
<head>
    <?php render_head($page_title); ?>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <form method="post" onsubmit="return validate(this);">
    <?php
    render_input([
        "id" => "edit-guide-title", "name" => "title", "label" => "Title",
        "value" => $result["title"],
        "attributes" => ["required" => true, "maxlength" => 150],
    ]);
    render_input([
        "id" => "edit-guide-category", "label" => "Primary Category",
        "name" => "primary_category",
        "value" => $result["primary_category"],
        "attributes" => ["required" => true, "maxlength" => 50],
    ]);
    render_input([
        "id" => "edit-guide-player-race", "label" => "Player Race", "type" => "select",
        "name" => "player_race",
        "value" => $result["player_race"],
        "options" => ["any" => "Any", "terran" => "Terran", "protoss" => "Protoss", "zerg" => "Zerg"],
    ]);
    render_input([
        "id" => "edit-guide-opponent-race", "label" => "Opponent Race", "type" => "select",
        "name" => "opponent_race",
        "value" => $result["opponent_race"],
        "options" => ["any" => "Any", "terran" => "Terran", "protoss" => "Protoss", "zerg" => "Zerg"],
    ]);
    render_input([
        "id" => "edit-guide-summary", "label" => "Summary", "type" => "textarea",
        "name" => "summary",
        "value" => $result["summary"],
        "attributes" => ["required" => true, "rows" => 5],
    ]);
    render_input([
        "id" => "edit-guide-source-url", "label" => "Source URL (optional)",
        "type" => "url",
        "name" => "source_url",
        "value" => $result["source_url"],
        "attributes" => ["maxlength" => 500],
    ]);
    render_button([
        "text" => "Update Guide",
        "variant" => "warning",
        "attributes" => ["name" => "action", "value" => "update_guide"],
    ]);
    ?>
        </form>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(["guides.js"]); ?>
</body>
</html>
