<?php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");
$page_title = "Assign Roles";
$errors = [];
$userSearch = "";
$users = [];
$roles = [];
try {
    $db = getDB();
    $userSearch = trim($_GET["username"] ?? $_POST["username"] ?? "");

    if (isset($_POST["action"]) && $_POST["action"] === "toggle_roles") {
        $userIds = validate_ids($_POST["users"] ?? [], $errors);
        $roleIds = validate_ids($_POST["roles"] ?? [], $errors, true);

        if (empty($errors) && (empty($userIds) || empty($roleIds))) {
            $errors[] = "Select at least one user and one role.";
        }
        if (empty($errors)) {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO UserRoles (user_id, role_id, is_active)
                     VALUES (:user_id, :role_id, 1)
                     ON DUPLICATE KEY UPDATE
                        is_active = IF(is_active = 1, 0, 1)"
                );
                $toggledCount = 0;
                $failedCount = 0;

                foreach ($userIds as $userId) {
                    foreach ($roleIds as $roleId) {
                        try {
                            $stmt->execute([
                                ":user_id" => $userId,
                                ":role_id" => $roleId,
                            ]);
                            $toggledCount++;
                        } catch (PDOException $e) {
                            $failedCount++;
                            error_log(
                                "Role assignment toggle failed for user $userId"
                                . " and role $roleId: " . $e->getMessage()
                            );
                        }
                    }
                }

                if ($toggledCount > 0) {
                    add_flash("$toggledCount selected user-role pair(s) were toggled.", "success");
                }

                if ($failedCount > 0) {
                    add_flash("$failedCount selected pair(s) could not be updated.", "warning");
                }
            } catch (PDOException $e) {
                error_log("Role assignment setup failed: " . $e->getMessage());
                add_flash("Could not update role assignments.", "danger");
            }
        }

        $redirect = "admin/assign_roles.php";
        if ($userSearch !== "") {
            $redirect .= "?username=" . rawurlencode($userSearch);
        }

        flash_errors($errors);
        header("Location: " . project_url($redirect));
        exit;
    }

    if ($userSearch !== "") {
        try {
            // The first LEFT JOIN connects Users to their UserRoles relationship rows.
            // The second LEFT JOIN connects each relationship row to the role name.
            // LEFT JOIN keeps matching users visible even if they have no role rows yet.
            // GROUP_CONCAT combines multiple role rows into one readable column.
            // CONCAT and IF label each existing user-role relationship active/inactive.
            // GROUP BY is needed because GROUP_CONCAT is an aggregate result.
            $stmt = $db->prepare(
                "SELECT Users.id, Users.username, Users.email,
                        GROUP_CONCAT(
                            CONCAT(
                                Roles.name,
                                ' (',
                                IF(UserRoles.is_active = 1, 'active', 'inactive'),
                                ')'
                            )
                            ORDER BY Roles.name
                            SEPARATOR ', '
                        ) AS roles
                 FROM Users
                 LEFT JOIN UserRoles
                    ON Users.id = UserRoles.user_id
                 LEFT JOIN Roles
                    ON Roles.id = UserRoles.role_id
                 WHERE Users.username LIKE :username
                 GROUP BY Users.id, Users.username, Users.email
                 ORDER BY Users.username
                 LIMIT 10"
            );
            $stmt->execute([":username" => "%$userSearch%"]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare(
                "SELECT id, name
                 FROM Roles
                 WHERE is_active = 1
                 ORDER BY name
                 LIMIT 10"
            );
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Role assignment page load failed: " . $e->getMessage());
            add_flash("Could not load users and roles.", "danger");
        }
    }
} catch (TypeError | ValueError $e) {
    error_log("Role page input failed: " . $e->getMessage());
    $userSearch = "";
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
        <div><label for="username">Find Users</label>
        <input id="username" name="username" value="<?php echo htmlspecialchars($userSearch); ?>"></div>
        <div><button type="submit">Search</button></div>
    </form>

    <form id="toggleForm" method="post" onsubmit="return validate(this);">
        <input type="hidden" name="action" value="toggle_roles">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($userSearch); ?>">
    </form>

    <?php if ($userSearch !== ""): ?>
        <table>
            <thead>
                <tr>
                    <th>Users</th>
                    <th>Roles To Toggle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php if (!empty($users)): ?>
                            <table>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <input form="toggleForm"
                                                   id="user_<?php echo (int)$user["id"]; ?>"
                                                   type="checkbox"
                                                   name="users[]"
                                                   value="<?php echo (int)$user["id"]; ?>">
                                            <label for="user_<?php echo (int)$user["id"]; ?>">
                                                <?php echo htmlspecialchars($user["username"]); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($user["roles"] ?? "No roles"); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <p>No users found.</p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <div>
                                    <input form="toggleForm"
                                           id="role_<?php echo (int)$role["id"]; ?>"
                                           type="checkbox"
                                           name="roles[]"
                                           value="<?php echo (int)$role["id"]; ?>">
                                    <label for="role_<?php echo (int)$role["id"]; ?>">
                                        <?php echo htmlspecialchars($role["name"]); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No active roles found.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div><button form="toggleForm" type="submit">Toggle Selected Pairs</button></div>
    <?php endif; ?>
    <script>
    /**
     * Require both bulk selections and use the shared flash feedback area.
     * @param {HTMLFormElement} form The submitted page form.
     * @returns {boolean} Whether browser validation allows submission.
     */
    function validate(form) {
        const errors = [];
        if (form.id === "toggleForm") {
            if (!document.querySelector('input[name="users[]"]:checked')) {
                errors.push("Select at least one user.");
            }
            if (!document.querySelector('input[name="roles[]"]:checked')) {
                errors.push("Select at least one role.");
            }
        } else if (!form.checkValidity()) {
            errors.push("Check the search field.");
        }
        return show_validation_errors(errors);
    }
    </script>
    <?php render_flash_messages(); ?>
</body>
</html>
