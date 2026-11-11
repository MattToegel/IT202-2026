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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
</head>
<body>
    <?php render_nav(); ?>
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <form method="post" action="profile.php" onsubmit="return validate(this);">
        <input type="hidden" name="action" value="details">
        <div>
            <label for="username">Username</label>
            <input id="username" name="username" required minlength="3" maxlength="30"
                   pattern="[a-z0-9_\-]{3,30}" value="<?php echo htmlspecialchars($username); ?>">
        </div>
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div>
            <button type="submit">Save details</button>
        </div>
    </form>
    <form method="post" action="profile.php" onsubmit="return validate(this);">
        <input type="hidden" name="action" value="password">
        <div>
            <label for="current_password">Current password</label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
        </div>
        <div>
            <label for="new_password">New password</label>
            <input id="new_password" name="new_password" type="password" required minlength="8" autocomplete="new-password">
        </div>
        <div>
            <label for="confirm_password">Confirm new password</label>
            <input id="confirm_password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">
        </div>
        <div>
            <button type="submit">Change password</button>
        </div>
    </form>
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
    <?php render_flash_messages(); ?>
</body>
</html>
