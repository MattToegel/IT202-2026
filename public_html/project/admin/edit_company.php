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
        $params = [
            ":id" => $id,
            ":name" => $updated_values["name"],
            ":type" => $updated_values["type"],
            ":region" => $updated_values["region"],
        ];

        try {
            update("Companies", $params, ["id"]);
            add_flash("Company updated", "success");
            header("Location: " . project_url("admin/list_companies.php"));
            exit;
        } catch (Throwable $e) {
            error_log("Update company failed: " . $e->getMessage());
            $errors[] = "Unable to update company.";
        }
    }
}

flash_errors($errors);

try {
    $result = select(
        "SELECT `name`, `region`, `type`, `symbol` FROM Companies WHERE id = :id LIMIT 1",
        [":id" => $id]
    );
} catch (Throwable $e) {
    error_log("Load company for edit failed: " . $e->getMessage());
    add_flash("Unable to load that company.", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}
if (!$result) {
    add_flash("Company not found", "danger");
    header("Location: " . project_url("admin/list_companies.php"));
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <?php render_head($page_title); ?>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <h1><?php echo htmlspecialchars($page_title); ?>: <?php echo htmlspecialchars($result["symbol"]); ?></h1>
        <form method="post" onsubmit="return validate(this);">
            <?php render_input([
    "name" => "name",
    "id" => "name",
    "label" => "Name",
    "value" => $result["name"],
    "attributes" => ["maxlength" => "120", "required" => true],
]); ?>

            <?php render_input([
    "name" => "type",
    "id" => "type",
    "label" => "Type",
    "value" => $result["type"],
    "attributes" => ["maxlength" => "40"],
]); ?>

            <?php render_input([
    "name" => "region",
    "id" => "region",
    "label" => "Region",
    "value" => $result["region"],
    "attributes" => ["maxlength" => "80"],
]); ?>

            <div>
                <?php render_button(["text" => "Save Company", "attributes" => ["name" => "save", "value" => "1"]]); ?>
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
    <?php render_scripts(); ?>
</body>
</html>
