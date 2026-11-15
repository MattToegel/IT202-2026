<?php
// public_html/project/admin/create_company.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$page_title = "Create Company";
$rows = [];

if (isset($_POST["fetch_companies"])) {

    try {
        $search = trim($_POST["search"] ?? "");
        if ($search === "") {
            throw new InvalidArgumentException("Enter company search text.");
        }
        $rows = search_companies($search, $errors);
        if (empty($errors) && !$rows) {
            $errors[] = "No companies matched that search.";
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Company search failed: " . $e->getMessage());
        $errors[] = "Unable to search for companies right now.";
    }
} elseif (isset($_POST["create_company"])) {

    try {
        $values = [];
        foreach (["symbol" => 10, "name" => 120, "type" => 40, "region" => 80, "currency" => 10] as $field => $max) {
            $values[$field] = trim($_POST[$field] ?? "");
            if (strlen($values[$field]) > $max) {
                throw new InvalidArgumentException("The $field value is too long.");
            }
        }
        if (!preg_match('/^[A-Za-z0-9.\-]{1,10}$/', $values["symbol"]) || $values["name"] === "") {
            throw new InvalidArgumentException("Enter a valid symbol and company name.");
        }

        // Wrap one manual row so API and manual paths use the same insert loop.
        $rows[] = [
            "symbol" => $values["symbol"],
            "name" => $values["name"],
            "type" => $values["type"],
            "region" => $values["region"],
            "currency" => $values["currency"],
            "is_api" => 0,
        ];
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Create company input failed: " . $e->getMessage());
        $errors[] = "Unable to process the company values.";
    }
}

if ($rows && empty($errors)) {
    $saved = 0;
    $duplicates = 0;

    try {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO Companies (symbol, name, type, region, currency, is_api)
             VALUES (:symbol, :name, :type, :region, :currency, :is_api)"
        );

        foreach ($rows as $row) {
            try {
                $stmt->execute($row);
                $saved++;
            } catch (PDOException $e) {
                if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                    $duplicates++;
                    continue;
                }
                throw $e;
            }
        }

        if ($saved > 0) {
            add_flash("Saved $saved company record(s).", "success");
        }
        if ($duplicates > 0) {
            add_flash("Skipped $duplicates duplicate company record(s).", "warning");
        }
        header("Location: " . project_url("admin/list_companies.php"));
        exit;
    } catch (PDOException $e) {
        error_log("Create companies failed: " . $e->getMessage());
        add_flash("Unable to save the company records.", "danger");
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
                <h2>Search The API</h2>
                <div>
                    <label for="search">Search text</label>
                    <input id="search" name="search" required>
                </div>
                <div>
                    <button name="fetch_companies" value="1" type="submit">Search Companies</button>
                </div>
            </form>
        </section>

        <section>
            <form method="post" onsubmit="return validate(this);">
                <h2>Create Manually</h2>
                <div>
                    <label for="symbol">Symbol</label>
                    <input id="symbol" name="symbol" required pattern="[A-Za-z0-9.\-]{1,10}">
                </div>
                <div>
                    <label for="name">Company name</label>
                    <input id="name" name="name" maxlength="120" required>
                </div>

                <div>
                    <label for="type">Type</label>
                    <input id="type" name="type" maxlength="40">
                </div>

                <div>
                    <label for="region">Region</label>
                    <input id="region" name="region" maxlength="80">
                </div>

                <div>
                    <label for="currency">Currency</label>
                    <input id="currency" name="currency" maxlength="10">
                </div>

                <div>
                    <button name="create_company" value="1" type="submit">Create Company</button>
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
