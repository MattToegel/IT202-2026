<?php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");
$page_title = "Create Role";
$errors = [];
$name = "";
$description = "";

if (isset($_POST["name"], $_POST["description"])) {
    try {
        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);

        if (strlen($name) < 2 || strlen($name) > 30) {
            $errors[] = "Role name must be 2-30 characters.";
        }

        if (!preg_match('/^[A-Za-z][A-Za-z0-9 ._-]*$/', $name)) {
            $errors[] = "Role name must start with a letter and use only letters, numbers, spaces, periods, underscores, or hyphens.";
        }

        if (strlen($description) > 255) {
            $errors[] = "Description must be 255 characters or less.";
        }

        if (empty($errors)) {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO Roles (name, description)
                 VALUES (:name, :description)"
            );
            $stmt->execute([
                ":name" => $name,
                ":description" => $description,
            ]);

            add_flash("Role created.", "success");
            header("Location: " . project_url("admin/list_roles.php"));
            exit;
        }
    } catch (TypeError | ValueError $e) {
        error_log("Role input failed: " . $e->getMessage());
        $name = "";
        $description = "";
        $errors[] = "Please check the role fields.";
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            $errors[] = "That role name already exists.";
        } else {
            error_log("Role creation failed: " . $e->getMessage());
            $errors[] = "Role could not be created.";
        }
    } catch (Exception $e) {
        error_log("Role creation failed: " . $e->getMessage());
        $errors[] = "Role could not be created.";
    }
}
flash_errors($errors);
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
    <!-- public_html/project/admin/create_role.php form -->
<form method="post" onsubmit="return validate(this);">
    <?php
    render_input([
        "name" => "name",
        "label" => "Role Name",
        "value" => $name,
        "attributes" => [
            "required" => true,
            "minlength" => 2,
            "maxlength" => 30,
            "pattern" => "[A-Za-z][A-Za-z0-9 ._\\-]*",
            "pattern" => "[A-Za-z][A-Za-z0-9 ._\\-]*",
        ],
    ]);
    render_input([
        "type" => "textarea",
        "name" => "description",
        "value" => $description,
        "attributes" => [
            "maxlength" => 255,
            "rows" => 3,
        ],
    ]);
    render_button(["text" => "Create Role"]);
    ?>
</form>
    <script>
    /**
     * Validate role fields before the independent PHP handler.
     * @param {HTMLFormElement} form The submitted page form.
     * @returns {boolean} Whether browser validation allows submission.
     */
    function validate(form) {
        const errors = [];
        if (!form.elements.name.validity.valid) {
            errors.push("Check the role name.");
        }
        if (!form.description.validity.valid) {
            errors.push("Check the description length.");
        }
        return show_validation_errors(errors);
    }
    </script>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
