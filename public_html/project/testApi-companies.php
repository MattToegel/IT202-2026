<?php
// public_html/project/testApi-companies.php
require_once(__DIR__ . "/../../lib/app.php");
require_role("Admin");

$decoded = null;
$errors = [];
$page_title = "Test Company API";
$search = "micro";

if (isset($_POST["source"])) {
    try {
        $search = trim($_POST["search"] ?? "");
        $source = $_POST["source"];

        if ($source !== "live" && $source !== "sample") {
            $errors[] = "Choose a valid API source.";
        } elseif ($source === "live" && $search === "") {
            $errors[] = "Enter search text before sending the request.";
        }

        if (empty($errors)) {
            if ($source === "sample") {
                $result = api_sample_response("company-search.json");
            } else {
                $result = api_get(
                    "https://alpha-vantage.p.rapidapi.com/query",
                    ["function" => "SYMBOL_SEARCH", "keywords" => $search],
                    ["key_name" => "STOCK_API_KEY", "host_name" => "STOCK_API_HOST"]
                );
            }

            $decoded = decode_api_response($result, "bestMatches", $errors);
        }
    } catch (Throwable $e) {
        error_log("API test failed: " . $e->getMessage());
        $errors[] = "Unable to process that request.";
        $search = "";
    }
}

flash_errors($errors);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <form method="post" onsubmit="return validate(this);">
            <div>
                <label for="search">Search text</label>
                <input id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" required>
            </div>
            <div>
                <button name="source" value="live" type="submit">Search Live API</button>
            </div>
            <div>
                <button name="source" value="sample" type="submit">Use Cached Sample</button>
            </div>
        </form>

        <?php if ($decoded !== null): ?>
            <pre><?php echo htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)); ?></pre>
        <?php endif; ?>
    </main>
    <?php render_flash_messages(); ?>
    <script>
        /** Check this form before submission and reuse the shared flash area.
         * @param {HTMLFormElement} form The submitted API-data form.
         * @returns {boolean} True when the browser may submit the form to PHP.
         */
        function validate(form) {
            const errors = [];
            for (const input of form.querySelectorAll("input")) {
                if (input.required && input.value.trim() === "") {
                    errors.push(`Enter a value for ${input.name.replaceAll("_", " ")}.`);
                }
                if (input.type === "number" && input.value !== "" &&
                    (!Number.isFinite(Number(input.value)) || Number(input.value) < Number(input.min))) {
                    errors.push(`Enter a valid ${input.name}.`);
                }
            }
            return show_validation_errors(errors);
        }
    </script>
</body>
</html>
