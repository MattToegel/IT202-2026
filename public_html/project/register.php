<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Register";
$errors = [];
$email = "";
$notice = "";
if (isset($_POST["email"], $_POST["password"], $_POST["confirm_password"])) {
    try {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        validate_registration_input($email, $password, $confirm_password, $errors);
        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO Users (email, password_hash)
     VALUES (:email, :password_hash)"
            );
            // Each key names a SQL placeholder; => pairs it with a PHP value.
            $stmt->execute([
                ":email" => $email,
                ":password_hash" => $hash,
            ]);
            flash_notice("Account created");
            header("Location: login.php");
            exit;
        }
    } catch (TypeError | ValueError $e) {
        // Either invalid types or unsupported values use this response.
        error_log("Registration input failed: " . $e->getMessage());
        $email = ""; // Keep the form safe to redisplay after malformed input.
        $errors[] = "Registration failed. Please check the account fields.";
    } catch (PDOException $e) {
        // MySQL 1062 means a duplicate unique value; this table's email is unique.
        if (($e->errorInfo[1] ?? null) === 1062) {
            $errors[] = "That email is already registered.";
        } else {
            error_log("Registration query failed: " . $e->getMessage());
            $errors[] = "Registration failed. Please try again.";
        }
    } catch (Exception $e) {
        error_log("Registration failed: " . $e->getMessage());
        $errors[] = "Registration failed. Please try again.";
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
    <form method="post" action="register.php" onsubmit="return validate(this);">
        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email"
                   required autocomplete="email"
                   value="<?php echo htmlspecialchars($email); ?>">
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

    <script src="helpers.js" defer></script>
    <script>
    function validate(form) {
        return showFeedback(registrationErrors(form));
    }
    </script>
</body>
</html>
