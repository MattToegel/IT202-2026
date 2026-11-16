<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Profile";
if (!is_logged_in()) {
    add_flash("Please log in first", "warning");
    header("Location: login.php");
    exit;
}
$currentUserId = get_user_id();
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, email, username FROM Users WHERE id = :user_id LIMIT 1");
    $stmt->execute([":user_id" => $currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$currentUser) {
        unset($_SESSION["user"]);
        add_flash("Please log in again", "warning");
        header("Location: login.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Profile lookup failed: " . $e->getMessage());
    add_flash("Profile could not be loaded", "danger");
    header("Location: dashboard.php");
    exit;
}
$errors = [];
$username = $currentUser["username"];
$email = $currentUser["email"];
if (isset($_POST["action"], $_POST["username"], $_POST["email"]) && $_POST["action"] === "details") {
    try {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        validate_username($username, $errors);
        validate_email($email, $errors);
        if (empty($errors)) {
            $stmt = $db->prepare("UPDATE Users SET username = :username, email = :email WHERE id = :user_id LIMIT 1");
            $stmt->execute([":username" => $username, ":email" => $email, ":user_id" => $currentUserId]);
            $_SESSION["user"]["username"] = $username;
            $_SESSION["user"]["email"] = $email;
            add_flash("Profile updated", "success");
            header("Location: profile.php");
            exit;
        }
    } catch (TypeError | ValueError $e) {
        error_log("Profile input failed: " . $e->getMessage());
        $username = $currentUser["username"];
        $email = $currentUser["email"];
        $errors[] = "Please check the account fields.";
    } catch (PDOException $e) {
        duplicate_user_detail($e, $errors);
    } catch (Exception $e) {
        error_log("Profile update failed: " . $e->getMessage());
        $errors[] = "Profile could not be updated.";
    }
}
if (isset($_POST["action"], $_POST["current_password"], $_POST["new_password"], $_POST["confirm_password"])
    && $_POST["action"] === "password") {
    try {
        $currentPassword = $_POST["current_password"];
        $newPassword = $_POST["new_password"];
        $confirmPassword = $_POST["confirm_password"];
        validate_password($newPassword, $errors);
        validate_passwords_match($newPassword, $confirmPassword, $errors);
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT password_hash FROM Users WHERE id = :user_id LIMIT 1");
            $stmt->execute([":user_id" => $currentUserId]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$account || !password_verify($currentPassword, $account["password_hash"])) {
                $errors[] = "Current password is incorrect.";
            }
        }
        if (empty($errors)) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE Users SET password_hash = :hash WHERE id = :user_id LIMIT 1");
            $stmt->execute([":hash" => $hash, ":user_id" => $currentUserId]);
            add_flash("Password updated", "success");
            header("Location: profile.php");
            exit;
        }
    } catch (TypeError | ValueError $e) {
        error_log("Password input failed: " . $e->getMessage());
        $errors[] = "Please check the password fields.";
    } catch (Exception $e) {
        error_log("Password update failed: " . $e->getMessage());
        $errors[] = "Password could not be updated.";
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
    <div class="row g-4">
    <section class="col-lg-6">
        <h2>Account Details</h2>
        <form method="post" action="profile.php" onsubmit="return validate(this)">
            <?php
            render_input(["type" => "hidden", "name" => "action", "value" => "details"]);
            render_input([
                "name" => "username",
                "value" => $username,
                "attributes" => [
                    "required" => true,
                    "minlength" => 3,
                    "maxlength" => 30,
                    "pattern" => "[a-z0-9_\\-]{3,30}",
                    "title" => "Must be lowercase alphanumeric and can use underscore or hyphens",
                    "autocomplete" => "username",
                ],
            ]);
            render_input([
                "type" => "email",
                "name" => "email",
                "value" => $email,
                "attributes" => ["required" => true, "autocomplete" => "email"],
            ]);
            render_button(["text" => "Save details"]);
            ?>
        </form>
    </section>

    <section class="col-lg-6">
        <h2>Change password</h2>
        <form method="post" action="profile.php" onsubmit="return validate(this)">
            <?php
            render_input(["type" => "hidden", "name" => "action", "value" => "password"]);
            render_input([
                "type" => "password",
                "name" => "current_password",
                "label" => "Current password",
                "attributes" => [
                    "required" => true,
                    "autocomplete" => "current-password",
                ],
            ]);
            render_input([
                "type" => "password",
                "name" => "new_password",
                "label" => "New password",
                "attributes" => [
                    "required" => true,
                    "minlength" => 8,
                    "autocomplete" => "new-password",
                ],
            ]);
            render_input([
                "type" => "password",
                "name" => "confirm_password",
                "label" => "Confirm new password",
                "attributes" => [
                    "required" => true,
                    "minlength" => 8,
                    "autocomplete" => "new-password",
                ],
            ]);
            render_button(["text" => "Change password", "variant" => "warning"]);
            ?>
        </form>
    </section>
</div>
    <script>
    /**
     * Validate the submitted details or password form with the shared field helpers.
     * @param {HTMLFormElement} form Submitted profile form.
     * @returns {boolean} Whether submission may proceed.
     */
    function validate(form) {
        const errors = [];
        if (form.elements.action.value === "details") {
            validate_username(form.username, errors);
            validate_email(form.email, errors);
        } else {
            if (!form.current_password.value) {
                errors.push("Enter the current password.");
            }
            validate_password(form.new_password, errors);
            validate_passwords_match(form.new_password, form.confirm_password, errors);
        }
        return show_validation_errors(errors);
    }
    </script>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
