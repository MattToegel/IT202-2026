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
    <?php render_head($page_title); ?>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <form method="post" action="register.php" onsubmit="return validate(this)">
    <?php
    render_input([
        "type" => "email",
        "name" => "email",
        "label" => "Email",
        "value" => $email,
        "attributes" => ["required" => true, "autocomplete" => "email"],
    ]);

    render_input([
        "name" => "username",
        "label" => "Username",
        "value" => $username,
        "attributes" => [
            "required" => true,
            "minlength" => 3,
            "maxlength" => 30,
            "pattern" => "[a-z0-9_\\-]{3,30}",
            "autocomplete" => "username",
        ],
    ]);

    render_input([
        "type" => "password",
        "name" => "password",
        "label" => "Password",
        "attributes" => ["required" => true, "minlength" => 8, "autocomplete" => "new-password"],
    ]);

    render_input([
        "type" => "password",
        "name" => "confirm_password",
        "label" => "Confirm Password",
        "attributes" => ["required" => true, "minlength" => 8, "autocomplete" => "new-password"],
    ]);
    render_button(["text" => "Register"]);
    ?>
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
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
