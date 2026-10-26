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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) === 0) {
            $errors[] = "Invalid email or password.";
        } else {
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
</head>
<body>
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
    <script>
    function validate(form) {
        const email = form.email.value.trim();
        const password = form.password.value;
        const errors = [];

        if (!email || !form.email.validity.valid) {
            errors.push("Enter a valid email address.");
        }

        if (password.length < 8) {
            errors.push("Password must be at least 8 characters.");
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
