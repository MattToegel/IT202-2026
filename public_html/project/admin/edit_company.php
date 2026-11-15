<?php
// public_html/project/admin/edit_company.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$page_title = "Edit Company";
$id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);
if ($id <= 0) {
    add_flash("Missing company id", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}

if (isset($_POST["save"])) {
    $updated_values = [];

    try {
        // Handle only the valid columns this brief edit form allows.
        foreach (["name", "type", "region"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            $updated_values[$field_name] = trim($value);
            $max = $field_name === "name" ? 120 : ($field_name === "type" ? 40 : 80);
            if (strlen($updated_values[$field_name]) > $max) {
                throw new InvalidArgumentException("The $field_name value is too long.");
            }
        }

        if ($updated_values["name"] === "") {
            throw new InvalidArgumentException("Enter a company name.");
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Edit company input failed: " . $e->getMessage());
        $errors[] = "Unable to process the company values.";
    }

    if (empty($errors)) {
        $data = [
            ":id" => $id,
            ":name" => $updated_values["name"],
            ":type" => $updated_values["type"],
            ":region" => $updated_values["region"],
        ];

        try {
            $db = getDB();
            $stmt = $db->prepare(
                "UPDATE Companies
                 SET name = :name, type = :type, region = :region
                 WHERE id = :id"
            );
            $stmt->execute($data);
            add_flash("Company updated", "success");
            header("Location: " . project_url("admin/list_companies.php"));
            exit;
        } catch (PDOException $e) {
            error_log("Update company failed: " . $e->getMessage());
            $errors[] = "Unable to update company.";
        }
    }
}

flash_errors($errors);

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT `name`, `region`, `type`, `symbol` FROM Companies WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Load company for edit failed: " . $e->getMessage());
    add_flash("Unable to load that company.", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}
if (!$company) {
    add_flash("Company not found", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
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
        <h1><?php echo htmlspecialchars($page_title); ?>: <?php echo htmlspecialchars($company["symbol"]); ?></h1>
        <form method="post" onsubmit="return validate(this);">
            <div>
                <label for="name">Name</label>
                <input id="name" name="name" maxlength="120" value="<?php echo htmlspecialchars($company["name"]); ?>" required>
            </div>

            <div>
                <label for="type">Type</label>
                <input id="type" name="type" maxlength="40" value="<?php echo htmlspecialchars($company["type"]); ?>">
            </div>

            <div>
                <label for="region">Region</label>
                <input id="region" name="region" maxlength="80" value="<?php echo htmlspecialchars($company["region"]); ?>">
            </div>

            <div>
                <button name="save" value="1" type="submit">Save Company</button>
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
