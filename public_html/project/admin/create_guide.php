<?php
// public_html/project/admin/create_guide.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$page_title = "Create StarCraft Guide";
$errors = [];
$title = "";
$primary_category = "";
$player_race = "any";
$opponent_race = "any";
$summary = "";
$source_url = "";
$game = "sc2";
$action = $_POST["action"] ?? "";
$game = isset($_POST["game"]) ? strtolower(guide_post_text("game")) : "sc2";

if ($action === "import_guides") {
    try {
        $rows = fetch_starcraft_guides($game, $errors);
        if (empty($errors) && !empty($rows)) {
            // Intentional opt-in upsert: a later import replaces provider-owned
            // fields, including edits to an imported row. Manual rows have NULL api_id.
            $result = insert("Guides", $rows, [
                "update_duplicate" => true,
                "columns_to_update" => [
                    "excerpt", "game", "primary_category", "slug",
                    "source_author", "source_url", "status", "summary", "title",
                    "video", "opponent_race", "player_race", "matchup",
                ],
            ]);
            add_flash($result["rowCount"] . " API guide row(s) saved.", "success");
            header("Location: " . project_url("admin/create_guide.php"));
            exit;
        } elseif (empty($errors)) {
            $errors[] = "No guides were returned for this game.";
        }
    } catch (TypeError | Exception $e) {
        error_log("Guide import failed: " . $e->getMessage());
        $errors[] = "The guides could not be imported.";
    }
}

if ($action === "create_guide") {
    $supported_games = ["sc1", "sc2"];
    if (!in_array($game, $supported_games, true)) {
        $errors[] = "Choose a supported StarCraft game.";
    }
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
        // Build a matchup only when both selected races are specific.
        $matchup = null;
        if ($player_race !== "any" && $opponent_race !== "any") {
            $matchup = $player_race[0] . "v" . $opponent_race[0];
        }

        try {
            insert("Guides", [
                "api_id" => null,
                "excerpt" => null,
                "game" => $game,
                "primary_category" => $primary_category,
                "slug" => null,
                "source_author" => null,
                "source_url" => $source_url_value,
                "status" => "active",
                "summary" => $summary,
                "title" => $title,
                "video" => null,
                "opponent_race" => $opponent_race,
                "player_race" => $player_race,
                "matchup" => $matchup,
            ]);
            add_flash("Guide created.", "success");
            header("Location: " . project_url("admin/create_guide.php"));
            exit;
        } catch (TypeError | Exception $e) {
            error_log("Manual guide creation failed: " . $e->getMessage());
            $errors[] = "The guide could not be created.";
        }
    }
}

flash_errors($errors);
// TODO: Add the form document from the slide below here.
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

<div class="row g-4">
    <section id="guideApiForm" class="col-12 col-xl-6">
        <h2>Import API Guides</h2>
        <p>Call getGameGuides() for one game. Matching API IDs refresh provider fields, including local edits to those fields.</p>
        <form method="post" onsubmit="return validate(this);">
            <?php
            render_input([
                "id" => "import-game", "label" => "Game",
                "type" => "select",
                "name" => "game",
                "value" => $game,
                "options" => ["sc1" => "StarCraft", "sc2" => "StarCraft II"],
            ]);
            render_button([
                "text" => "Import API Guides",
                "variant" => "success",
                "attributes" => ["name" => "action", "value" => "import_guides"],
            ]);
            ?>
        </form>
    </section>

    <section id="guideManualForm" class="col-12 col-xl-6">
        <h2>Create Manual Guide</h2>
        <form method="post" onsubmit="return validate(this);">
            <?php
            render_input([
                "id" => "guide-title", "name" => "title", "label" => "Title",
                "value" => $title,
                "attributes" => ["required" => true, "maxlength" => 150],
            ]);
            render_input([
                "id" => "guide-category", "label" => "Primary Category",
                "name" => "primary_category",
                "value" => $primary_category,
                "attributes" => ["required" => true, "maxlength" => 50],
            ]);
            render_input([
                "id" => "guide-game", "label" => "Game",
                "type" => "select",
                "name" => "game",
                "value" => $game,
                "options" => ["sc1" => "StarCraft", "sc2" => "StarCraft II"],
            ]);
            render_input([
                "id" => "guide-player-race", "label" => "Player Race", "type" => "select",
                "name" => "player_race",
                "value" => $player_race,
                "options" => ["any" => "Any", "terran" => "Terran", "protoss" => "Protoss", "zerg" => "Zerg"],
            ]);
            render_input([
                "id" => "guide-opponent-race", "label" => "Opponent Race", "type" => "select",
                "name" => "opponent_race",
                "value" => $opponent_race,
                "options" => ["any" => "Any", "terran" => "Terran", "protoss" => "Protoss", "zerg" => "Zerg"],
            ]);
            render_input([
                "id" => "guide-summary", "label" => "Summary", "type" => "textarea",
                "name" => "summary",
                "value" => $summary,
                "attributes" => ["required" => true, "rows" => 5],
            ]);
            render_input([
                "id" => "guide-source-url", "label" => "Source URL (optional)",
                "type" => "url",
                "name" => "source_url",
                "value" => $source_url,
                "attributes" => ["maxlength" => 500],
            ]);
            render_button([
                "text" => "Create Manual Guide",
                "attributes" => ["name" => "action", "value" => "create_guide"],
            ]);
            ?>
        </form>
    </section>
</div>

    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(["guides.js"]); ?>
</body>
</html>
