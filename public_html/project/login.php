<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Login";

$errors = [];
$email = "";
$notice = "";
if (isset($_POST["email"], $_POST["password"])) {
    try {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        validate_email($email, $errors);
        validate_password($password, $errors);
        if (!empty($errors)) {
            // Keep credential feedback generic on this page.
            $errors = ["Invalid email or password."];
        }
        if (empty($errors)) {
            $stmt = getDB()->prepare(
                "SELECT id, email, password_hash FROM Users WHERE email = :email LIMIT 1"
            );
            $stmt->execute([":email" => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user["password_hash"])) {
                $errors[] = "Invalid email or password.";
            } else {
                session_regenerate_id(true);
                $_SESSION["user"] = [
                    "user_id" => (int) $user["id"],
                    "email" => $user["email"],
                ];
                header("Location: dashboard.php");
                exit;
            }
        }
    } catch (TypeError $e) {
        error_log("Login input failed: " . $e->getMessage());
        $email = ""; // Keep the form safe to redisplay after malformed input.
        $errors[] = "Invalid email or password.";
    } catch (Exception $e) {
        error_log("Login failed: " . $e->getMessage());
        $errors[] = "Login failed. Please try again.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php render_nav(); ?>
    <h1><?php echo htmlspecialchars($page_title); ?></h1>
    <div id="feedback">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
        <p><?php echo htmlspecialchars($notice); ?></p>
    </div>
    <form method="post" action="login.php" onsubmit="return validate(this);">
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required
                   autocomplete="email"
                   value="<?php echo htmlspecialchars($email); ?>">
        </div>

        <div>
            <label for="password">Password</label>
            <input id="password" name="password" type="password"
                   required minlength="8"
                   autocomplete="current-password">
        </div>

        <div>
            <button type="submit">Login</button>
        </div>
    </form>
    <script src="helpers.js" defer></script>
    <script>
    /**
     * Run this page's chosen checks before submitting its form.
     * @param {HTMLFormElement} form Form being submitted.
     * @returns {boolean} True to allow submission, false to keep the form open.
     */
    function validate(form) {
        const errors = [];
        validate_email(form.email, errors);
        validate_password(form.password, errors);
        return show_validation_errors(errors);
    }
    </script>
</body>
</html>
