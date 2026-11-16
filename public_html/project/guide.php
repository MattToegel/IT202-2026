<?php
// public_html/project/guide.php
require_once(__DIR__ . "/../../lib/app.php");

$id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
if (!is_int($id)) {
    add_flash("Missing guide id.", "danger");
    header("Location: " . project_url("guides.php"));
    exit;
}

$result = null;
try {
    $result = select(
        "SELECT id, api_id, excerpt, game, primary_category, slug,
                source_author, source_url, status, summary, title, video,
                opponent_race, player_race, matchup, created, modified
         FROM Guides
         WHERE id = :id
         LIMIT 1",
        ["id" => $id]
    );
} catch (TypeError | Exception $e) {
    error_log("Guide lookup failed: " . $e->getMessage());
    add_flash("The guide could not be loaded.", "danger");
    header("Location: " . project_url("guides.php"));
    exit;
}
if ($result === null) {
    add_flash("Guide not found.", "warning");
    header("Location: " . project_url("guides.php"));
    exit;
}
$video_url = sc_nullable_url($result["video"] ?? null);
$source_url = sc_nullable_url($result["source_url"] ?? null);
$page_title = $result["title"];
?>
<!doctype html>
<html lang="en">
<head><?php render_head($page_title); ?></head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <article class="card">
            <div class="card-body">
                <h1 class="card-title"><?php echo htmlspecialchars($page_title); ?></h1>
                <p class="text-body-secondary">
                    <?php echo htmlspecialchars((string) $result["game"]); ?> |
                    <?php echo htmlspecialchars((string) ($result["primary_category"] ?? "Uncategorized")); ?> |
                    <?php echo htmlspecialchars((string) ($result["status"] ?? "Unknown status")); ?>
                </p>

                <dl class="row">
                    <dt class="col-sm-3">Player Race</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($result["player_race"] ?? "Any")); ?></dd>
                    <dt class="col-sm-3">Opponent Race</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($result["opponent_race"] ?? "Any")); ?></dd>
                    <?php if (!empty($result["matchup"])): ?>
                        <dt class="col-sm-3">Matchup</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars((string) $result["matchup"]); ?></dd>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($result["excerpt"])): ?>
                    <p class="lead"><?php echo htmlspecialchars((string) $result["excerpt"]); ?></p>
                <?php endif; ?>
                <?php if (!empty($result["summary"])): ?>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars((string) $result["summary"])); ?></p>
                <?php endif; ?>

                <?php if ($source_url !== null): ?>
                    <p>
                        <a href="<?php echo htmlspecialchars($source_url); ?>"
                            target="_blank" rel="noopener noreferrer">
                            <?php echo htmlspecialchars((string) ($result["source_author"] ?? "Original source")); ?>
                        </a>
                    </p>
                <?php endif; ?>
                <?php if ($video_url !== null): ?>
                    <p>
                        <a href="<?php echo htmlspecialchars($video_url); ?>"
                            target="_blank" rel="noopener noreferrer">Watch Video</a>
                    </p>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?php echo project_url("guides.php"); ?>">Back To Guides</a>
            </div>
        </article>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
