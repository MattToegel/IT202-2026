<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Register";
$errors = [];
$email = "";
$username = "";
if (isset($_POST["email"], $_POST["username"], $_POST["password"], $_POST["confirm_password"])) {
    try {
        $email = trim($_POST["email"]);
        $username = trim($_POST["username"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        validate_email($email, $errors);
        validate_username($username, $errors);
        validate_password($password, $errors);
        validate_passwords_match($password, $confirm_password, $errors);
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO Users (email, username, password_hash)
     VALUES (:email, :username, :password_hash)"
            );
            // Each key names a SQL placeholder; => pairs it with a PHP value.
            $stmt->execute([
                ":email" => $email,
                ":username" => $username,
                ":password_hash" => $hash,
            ]);
            add_flash("Account created", "success");
            header("Location: login.php");
            exit;
        }
    } catch (TypeError | ValueError $e) {
        // Either invalid types or unsupported values use this response.
        error_log("Registration input failed: " . $e->getMessage());
        $email = ""; // Keep the form safe to redisplay after malformed input.
        $username = "";
        $errors[] = "Registration failed. Please check the account fields.";
    } catch (PDOException $e) {
        duplicate_user_detail($e, $errors);
    } catch (Exception $e) {
        error_log("Registration failed: " . $e->getMessage());
        $errors[] = "Registration failed. Please try again.";
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
    <form method="post" action="register.php" onsubmit="return validate(this);">
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email"
                   required autocomplete="email"
                   value="<?php echo htmlspecialchars($email); ?>">
        </div>

        <div>
            <label for="username">Username</label>
            <input id="username" name="username" required minlength="3" maxlength="30"
                   pattern="[a-z0-9_\-]{3,30}" autocomplete="username"
                   value="<?php echo htmlspecialchars($username); ?>">
        </div>

        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password"
                   required minlength="8" autocomplete="new-password">
        </div>

        <div>
            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password"
                   required minlength="8" autocomplete="new-password">
        </div>

        <div>
            <button type="submit">Register</button>
        </div>
    </form>

    <script>
    /**
     * Run this page's chosen checks before submitting its form.
     * @param {HTMLFormElement} form Form being submitted.
     * @returns {boolean} True to allow submission, false to keep the form open.
     */
    function validate(form) {
        const errors = [];
        validate_email(form.email, errors);
        validate_username(form.username, errors);
        validate_password(form.password, errors);
        validate_passwords_match(form.password, form.confirm_password, errors);
        return show_validation_errors(errors);
    }
    </script>
    <?php render_flash_messages(); ?>
</body>
</html>
