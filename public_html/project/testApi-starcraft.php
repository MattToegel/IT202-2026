<?php
// public_html/project/testApi-starcraft.php
require_once(__DIR__ . "/../../lib/app.php");

$page_title = "Test StarCraft Guides API";
$game = isset($_POST["game"]) ? strtolower(guide_post_text("game")) : "sc2";
$response = null; // HTTP-shaped result from the sample loader or live API.
$decoded = null;  // Validated JSON body.
$rows = [];       // Project-shaped rows mapped from guide items.
$errors = [];

if (isset($_POST["action"]) && in_array($_POST["action"], ["test_sample", "test_guides"], true)) {
    if (!in_array($game, ["sc1", "sc2"], true)) {
        $errors[] = "Choose a supported StarCraft game.";
    } else {
        try {
            if ($_POST["action"] === "test_sample") {
                // Save guides-response-20.json as lib/api_samples/starcraft-guides.json first.
                $game = "sc2"; // The supplied cached response is for StarCraft II.
                $response = api_sample_response("starcraft-guides.json");
            } else {
                // Explicit live action only; this may use a billed RapidAPI quota.
                $response = api_get(
                    "https://unofficial-starcraft-guides-api.p.rapidapi.com/api/v1/games/$game/guides/index.json",
                    [],
                    ["key_name" => "STARCRAFT_API_KEY", "host_name" => "STARCRAFT_API_HOST"]
                );
            }
            $decoded = decode_api_response($response, "guides", $errors);
            if ($decoded !== null) {
                foreach ($decoded["guides"] as $row) {
                    if (!is_array($row)) {
                        error_log("StarCraft sample contains a non-object guide item.");
                        continue;
                    }
                    $mapped = map_sc_guide($row);
                    if ($mapped !== null) {
                        $rows[] = $mapped;
                    }
                }
                if ($decoded["guides"] !== [] && $rows === []) {
                    $errors[] = "The response contained no usable guides.";
                }
            }
        } catch (TypeError | Exception $e) {
            error_log("StarCraft API test failed: " . $e->getMessage());
            $errors[] = "The guides could not be tested.";
        }
    }
}

flash_errors($errors);
?>
<!doctype html>
<html lang="en">
<head><?php render_head($page_title); ?></head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <p>The saved sample is for StarCraft II. The selected game applies to the live API action.</p>
        <form method="post" onsubmit="return validate(this);">
            <?php render_input([
                "id" => "starcraft-game", "label" => "Game", "name" => "game", "type" => "select",
                "value" => $game, "options" => ["sc1" => "StarCraft", "sc2" => "StarCraft II"],
            ]); ?>
            <?php render_button(["text" => "Test Saved SC2 Sample", "attributes" => ["name" => "action", "value" => "test_sample"]]); ?>
            <?php render_button(["text" => "Test Live API", "variant" => "secondary", "attributes" => ["name" => "action", "value" => "test_guides"]]); ?>
        </form>
        <?php if ($response !== null): ?>
            <p>HTTP status: <?php echo (int) $response["status"]; ?></p>
            <h2>Mapped Rows</h2>
            <pre><?php echo htmlspecialchars(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
            <h2>Decoded JSON</h2>
            <pre><?php echo htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        <?php endif; ?>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(["guides.js"]); ?>
</body>
</html>
