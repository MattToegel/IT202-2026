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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Enter a valid email address.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password is too short.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords must match.";
        }
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
            $notice = "Account created";
            $email = "";
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

    <script>
    function validate(form) {
        const email = form.email.value.trim();
        const password = form.password.value;
        const confirmPassword = form.confirm_password.value;
        const errors = [];

        if (!email || !form.email.validity.valid) {
            errors.push("Enter a valid email address.");
        }

        if (password.length < 8) {
            errors.push("Password must be at least 8 characters.");
        }

        if (password !== confirmPassword) {
            errors.push("Passwords must match.");
        }

        // Reuse the same feedback area that PHP fills after a POST.
        const feedback = document.getElementById("feedback");
        feedback.replaceChildren();
        for (const error of errors) {
            const message = document.createElement("p");
            message.textContent = error;
            feedback.appendChild(message);
        }
        return errors.length === 0;
    }
    </script>
</body>
</html>
