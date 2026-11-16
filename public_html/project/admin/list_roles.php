<?php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");
$page_title = "Roles";
$errors = [];
$search = "";
$roles = [];
try {
    $search = trim($_GET["q"] ?? "");

    if (isset($_POST["role_id"])) {
        $role_id = filter_var($_POST["role_id"], FILTER_VALIDATE_INT);

        if ($role_id !== false && $role_id !== 0) {
            try {
                $db = getDB();
                $stmt = $db->prepare(
                    "UPDATE Roles
                     SET is_active = !is_active
                     WHERE id = :id LIMIT 1"
                );
                $stmt->execute([":id" => $role_id]);
                add_flash("Role status updated.", "success");
            } catch (PDOException $e) {
                error_log("Role status update failed: " . $e->getMessage());
                add_flash("Could not update the role status.", "danger");
            }
        }

        header("Location: " . project_url("admin/list_roles.php"));
        exit;
    }

    try {
        $params = [];
        $where = "";

        if ($search !== "") {
            $where = "WHERE name LIKE :name_search OR description LIKE :description_search";
            $params[":name_search"] = "%$search%";
            $params[":description_search"] = "%$search%";
        }

        $db = getDB();
        $stmt = $db->prepare(
            "SELECT id, name, description, is_active, created, modified
             FROM Roles
             $where
             ORDER BY modified DESC
             LIMIT 10"
        );
        $stmt->execute($params);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Role list failed: " . $e->getMessage());
        add_flash("Could not load roles.", "danger");
    }
} catch (TypeError | ValueError $e) {
    error_log("Role page input failed: " . $e->getMessage());
    $search = "";
    $errors[] = "Please check the submitted fields.";
} catch (Exception $e) {
    error_log("Role page failed: " . $e->getMessage());
    $errors[] = "The role request could not be completed.";
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
    <?php
// public_html/project/admin/list_roles.php form and display blocks
?>
<form method="get" onsubmit="return validate(this);">
    <?php
    render_input([
        "label" => "Search",
        "name" => "q",
        "value" => $search,
    ]);
    render_button(["text" => "Search"]);
    ?>
</form>

<?php
foreach ($roles as &$role) {
    if ($role["is_active"]) {
        $role["status"] = "Active";
    } else {
        $role["status"] = "Inactive";
    }
}

unset($role);

unset($role);

$role_columns = [
    "name" => "Name",
    "description" => "Description",
    "status" => "Status",
];

$role_actions = [[
    "label" => "Toggle Status",
    "url" => "admin/list_roles.php",
    "method" => "POST",
    "parameter" => "role_id",
    "variant" => "warning",
]];

render_table(
    $roles,
    $role_columns,
    $role_actions
);
?>
    <script>
    /**
     * Check the search or status form before the independent PHP handler.
     * @param {HTMLFormElement} form The submitted page form.
     * @returns {boolean} Whether browser validation allows submission.
     */
    function validate(form) {
        const errors = [];
        if (!form.checkValidity()) {
            errors.push("Check the submitted fields.");
        }
        return show_validation_errors(errors);
    }
    </script>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
