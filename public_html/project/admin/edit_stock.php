<?php
// public_html/project/admin/edit_stock.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$page_title = "Edit Stock";
$id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);
if ($id <= 0) {
    add_flash("Missing stock id", "danger");
    header("Location: " . project_url("admin/list_stocks.php"));
    exit;
}

if (isset($_POST["save"])) {
    $updated_values = [];

    try {
        // Handle only the valid columns this brief edit form allows.
        foreach (["open", "high", "price"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            // Reject arrays or non-numeric text before converting the value to a float.
            if (!is_numeric($value) || $value < 0) {
                throw new InvalidArgumentException("Enter a valid $field_name value.");
            }

            $updated_values[$field_name] = (float)$value;
            // These stock values must stay positive.
            if ($updated_values[$field_name] <= 0) {
                throw new InvalidArgumentException("Enter a $field_name value greater than zero.");
            }
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Edit stock input failed: " . $e->getMessage());
        $errors[] = "Unable to process the stock values.";
    }

    if (empty($errors)) {
        $params = [
            ":id" => $id,
            ":open" => $updated_values["open"],
            ":high" => $updated_values["high"],
            ":price" => $updated_values["price"],
        ];

        try {
            update("Stocks", $params, ["id"]);
            add_flash("Stock updated", "success");
            header("Location: " . project_url("admin/list_stocks.php"));
            exit;
        } catch (Throwable $e) {
            error_log("Update stock failed: " . $e->getMessage());
            $errors[] = "Unable to update stock.";
        }
    }
}

flash_errors($errors);

try {
    $result = select(
        "SELECT open, high, price, symbol FROM Stocks WHERE id = :id LIMIT 1",
        [":id" => $id]
    );
} catch (Throwable $e) {
    error_log("Load stock for edit failed: " . $e->getMessage());
    add_flash("Unable to load that stock.", "danger");
    header("Location: " . project_url("admin/list_stocks.php"));
    exit;
}
if (!$result) {
    add_flash("Stock not found", "danger");
    header("Location: " . project_url("admin/list_stocks.php"));
    exit;
}
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
        <h1><?php echo htmlspecialchars($page_title); ?>: <?php echo htmlspecialchars($result["symbol"]); ?></h1>
        <form method="post" onsubmit="return validate(this);">
            <div>
                <label for="open">Open</label>
                <input id="open" name="open" type="number" min="0.0001" step="0.0001"
                value="<?php echo htmlspecialchars($result["open"]); ?>" required>
            </div>

            <div>
                <label for="high">High</label>
                <input id="high" name="high" type="number" min="0.0001" step="0.0001"
                value="<?php echo htmlspecialchars($result["high"]); ?>" required>
            </div>

            <div>
                <label for="price">Price</label>
                <input id="price" name="price" type="number" min="0.0001" step="0.0001"
                value="<?php echo htmlspecialchars($result["price"]); ?>" required>
            </div>

            <div>
                <button name="save" value="1" type="submit">Save Stock</button>
            </div>
        </form>
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
