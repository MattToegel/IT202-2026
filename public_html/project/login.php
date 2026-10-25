<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Login";
$errors = [];
$email = "";

if (isset($_POST["email"], $_POST["password"])) {
    try {
        $email = trim($_POST["email"]);
        validate_email($email, $errors);
        $password = $_POST["password"];
        if ($password === "") {
            $errors[] = "Invalid email or password.";
        }
        if (empty($errors)) {
            $stmt = getDB()->prepare(
                "SELECT id AS user_id, email, password_hash FROM Users
                 WHERE email = :email LIMIT 1"
            );
            $stmt->execute([":email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user || !password_verify($password, $user["password_hash"])) {
                $errors[] = "Invalid email or password.";
            } else {
                session_regenerate_id(true);
                unset($user["password_hash"]);
                $user["user_id"] = (int) $user["user_id"];
                $_SESSION["user"] = $user;
                flash("Welcome back.", "success");
                header("Location: dashboard.php");
                exit;
            }
        }
    } catch (TypeError $e) {
        error_log("Login input failed: " . $e->getMessage());
        $email = "";
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
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required
                   autocomplete="email" value="<?php echo htmlspecialchars($email); ?>">
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
        validate_email(form.email, errors);
        validate_password(form.password, errors);
        return show_validation_errors(errors);
    }
    </script>
    <?php render_flash_messages(); ?>
</body>
</html>
