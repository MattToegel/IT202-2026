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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
</head>
<body>
    <?php render_nav(); ?>
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <form method="get" onsubmit="return validate(this);">
        <div><label for="q">Search</label>
        <input id="q" name="q" value="<?php echo htmlspecialchars($search); ?>"></div>
        <div><button type="submit">Search</button></div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?php echo htmlspecialchars($role["name"]); ?></td>
                    <td><?php echo htmlspecialchars($role["description"]); ?></td>
                    <td><?php echo $role["is_active"] ? "Active" : "Inactive"; ?></td>
                    <td>
                        <form method="post" onsubmit="return validate(this);">
                            <input type="hidden" name="role_id" value="<?php echo (int)$role["id"]; ?>">
                            <div><button type="submit"><?php echo $role["is_active"] ? "Deactivate" : "Activate"; ?></button></div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
    <?php render_flash_messages(); ?>
</body>
</html>
