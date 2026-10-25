<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Login";
$errors = [];
$identifier = "";

if (isset($_POST["identifier"], $_POST["password"])) {
    try {
        $identifier = trim($_POST["identifier"]);
        $is_email = str_contains($identifier, "@");
        if ($identifier === "") {
            $errors[] = "Enter your email or username.";
        } elseif ($is_email) {
            validate_email($identifier, $errors);
        } else {
            $identifier = strtolower($identifier);
            validate_username($identifier, $errors);
        }
        $password = $_POST["password"];
        if ($password === "") {
            $errors[] = "Invalid email or password.";
        }
        if (empty($errors)) {
            $stmt = getDB()->prepare(
                "SELECT id AS user_id, email, username, password_hash FROM Users
                 WHERE email = :email_identifier OR username = :username_identifier LIMIT 1"
            );
            $stmt->execute([":email_identifier" => $identifier, ":username_identifier" => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($password, $user["password_hash"])) {
                $errors[] = "Invalid email or password.";
            } else {
                session_regenerate_id(true);
                unset($user["password_hash"]);
                $user["user_id"] = (int) $user["user_id"];
                $user["roles"] = get_user_roles($user["user_id"]);
                $_SESSION["user"] = $user;
                flash("Welcome back.", "success");
                header("Location: dashboard.php");
                exit;
            }
        }
    } catch (TypeError $e) {
        error_log("Login input failed: " . $e->getMessage());
        $identifier = "";
        $errors[] = "Invalid email or password.";
    } catch (Exception $e) {
        error_log("Login failed: " . $e->getMessage());
        $errors[] = "Login failed. Please try again.";
    }
    if (!empty($errors)) {
        flash_errors($errors);
    }
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
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <form method="post" action="login.php" onsubmit="return validate(this);">
        <div>
            <label for="identifier">Email or Username</label>
            <input id="identifier" name="identifier" type="text" required pattern="(?:[a-z0-9_\-]{3,30}|[^@\s]+@[^@\s]+\.[^@\s]+)"
                   autocomplete="username" value="<?php echo htmlspecialchars($identifier); ?>">
        </div>

        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required minlength="8"
                   autocomplete="current-password">
        </div>

        <div>
            <button type="submit">Login</button>
        </div>
    </form>
    <script>
    function validate(form) {
        const errors = [];
        const identifier = form.identifier.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const usernamePattern = /^[a-z0-9_-]{3,30}$/;
        if (!identifier) {
            errors.push("Enter your email or username.");
        } else if (identifier.includes("@") && !emailPattern.test(identifier)) {
            errors.push("Enter a valid email address.");
        } else if (!identifier.includes("@") && !usernamePattern.test(identifier)) {
            errors.push("Use 3-30 lowercase letters, numbers, underscores, or hyphens.");
        }
        validate_password(form.password, errors);
        return show_validation_errors(errors);
    }
    </script>
    <?php render_flash_messages(); ?>
</body>
</html>
