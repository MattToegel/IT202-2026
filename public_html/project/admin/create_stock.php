<?php
// public_html/project/admin/create_stock.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$page_title = "Create Stock";
$row = null;

if (isset($_POST["fetch_stock"])) {

    try {
        $symbol = trim($_POST["symbol"] ?? "");
        if (!preg_match('/^[A-Za-z0-9.\-]{1,10}$/', $symbol)) {
            throw new InvalidArgumentException("Enter a symbol of 1-10 letters, digits, dots or hyphens.");
        }
        $row = fetch_quote($symbol, $errors);
        if (empty($errors) && !$row) {
            $errors[] = "No stock quote was found.";
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Fetch stock failed: " . $e->getMessage());
        $errors[] = "Unable to fetch that stock right now.";
    }
} elseif (isset($_POST["create_stock"])) {

    try {
        $symbol = trim($_POST["symbol"] ?? "");
        if (!preg_match('/^[A-Za-z0-9.\-]{1,10}$/', $symbol)) {
            throw new InvalidArgumentException("Enter a stock symbol.");
        }

        foreach (["open", "high", "low", "price"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            if (!is_numeric($value) || $value < 0) {
                $label = str_replace("_", " ", $field_name);
                throw new InvalidArgumentException("Enter a valid $label value.");
            }
        }

        $latest_trading_day = $_POST["latest_trading_day"] ?? "";
        $date = DateTimeImmutable::createFromFormat("!Y-m-d", $latest_trading_day);
        if (!$date || $date->format("Y-m-d") !== $latest_trading_day) {
            throw new InvalidArgumentException("Enter a valid latest trading day.");
        }

        if ($_POST["price"] <= 0) {
            throw new InvalidArgumentException("Enter a price greater than zero.");
        }

        // Build an allowlisted row instead of passing raw $_POST to SQL.
        $row = [
            "symbol" => $symbol,
            "open" => (float)$_POST["open"],
            "high" => (float)$_POST["high"],
            "low" => (float)$_POST["low"],
            "price" => (float)$_POST["price"],
            "latest_trading_day" => $latest_trading_day,
            "is_api" => 0,
        ];
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Create stock input failed: " . $e->getMessage());
        $errors[] = "Unable to process the stock values.";
    }
}

if ($row && empty($errors)) {
    // The API row has more fields than this first INSERT uses.
    // Keep only the placeholders named in the SQL below.
    $params = [
        ":symbol" => $row["symbol"],
        ":open" => $row["open"],
        ":high" => $row["high"],
        ":low" => $row["low"],
        ":price" => $row["price"],
        ":latest_trading_day" => $row["latest_trading_day"],
        ":is_api" => $row["is_api"],
    ];

    try {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO Stocks (symbol, open, high, low, price, latest_trading_day, is_api)
             VALUES (:symbol, :open, :high, :low, :price, :latest_trading_day, :is_api)"
        );
        $stmt->execute($params);
        add_flash("Created stock " . $row["symbol"], "success");
        header("Location: " . project_url("admin/list_stocks.php"));
        exit;
    } catch (PDOException $e) {
        error_log("Create stock failed: " . $e->getMessage());
        $error_code = 0;
        if (isset($e->errorInfo[1])) {
            $error_code = (int)$e->errorInfo[1];
        }

        if ($error_code === 1062) {
            add_flash("A stock with this symbol already exists. No changes were made.", "warning");
        } else {
            add_flash("Unable to create stock.", "danger");
        }
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

        <section>
            <form method="post" onsubmit="return validate(this);">
                <h2>Fetch From API</h2>
                <div>
                    <label for="symbol_fetch">Symbol</label>
                    <input id="symbol_fetch" name="symbol" required pattern="[A-Za-z0-9.\-]{1,10}">
                </div>
                <div>
                    <button name="fetch_stock" value="1" type="submit">Fetch Stock</button>
                </div>
            </form>
        </section>

        <section>
            <form method="post" onsubmit="return validate(this);">
                <h2>Create Manually</h2>
                <div>
                    <label for="symbol_create">Symbol</label>
                    <input id="symbol_create" name="symbol" required pattern="[A-Za-z0-9.\-]{1,10}">
                </div>

                <div>
                    <label for="open">Open</label>
                    <input id="open" name="open" type="number" min="0" step="0.0001" required>
                </div>

                <div>
                    <label for="high">High</label>
                    <input id="high" name="high" type="number" min="0" step="0.0001" required>
                </div>

                <div>
                    <label for="low">Low</label>
                    <input id="low" name="low" type="number" min="0" step="0.0001" required>
                </div>

                <div>
                    <label for="price">Price</label>
                    <input id="price" name="price" type="number" min="0.0001" step="0.0001" required>
                </div>

                <div>
                    <label for="latest_trading_day">Latest trading day</label>
                    <input id="latest_trading_day" name="latest_trading_day" type="date" required>
                </div>

                <div>
                    <button name="create_stock" value="1" type="submit">Create Stock</button>
                </div>
            </form>
        </section>
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
