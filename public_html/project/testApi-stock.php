<?php
// public_html/project/testApi-stock.php
require_once(__DIR__ . "/../../lib/app.php");
require_role("Admin");

$decoded = null;
$errors = [];
$page_title = "Test Stock API";
$symbol = "IBM";

if (isset($_POST["source"])) {
    try {
        $symbol = trim($_POST["symbol"] ?? "");
        $source = $_POST["source"];

        if ($source !== "live" && $source !== "sample") {
            $errors[] = "Choose a valid API source.";
        } elseif ($source === "live" && $symbol === "") {
            $errors[] = "Enter a stock symbol before sending the request.";
        }

        if (empty($errors)) {
            if ($source === "sample") {
                $response = api_sample_response("stock-quote.json");
            } else {
                $response = api_get(
                    "https://alpha-vantage.p.rapidapi.com/query",
                    ["function" => "GLOBAL_QUOTE", "symbol" => $symbol],
                    ["key_name" => "STOCK_API_KEY", "host_name" => "STOCK_API_HOST"]
                );
            }

            $decoded = decode_api_response($response, "Global Quote", $errors);
        }
    } catch (Throwable $e) {
        error_log("API test failed: " . $e->getMessage());
        $errors[] = "Unable to process that request.";
        $symbol = "";
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
                <label for="symbol">Stock symbol</label>
                <input id="symbol" name="symbol" value="<?php echo htmlspecialchars($symbol); ?>" required>
            </div>
            <div>
                <button name="source" value="live" type="submit">Fetch Live Quote</button>
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
