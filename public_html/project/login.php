<?php
require_once(__DIR__ . "/../../lib/app.php");
$page_title = "Login";

$errors = [];
$identifier = "";
if (isset($_POST["identifier"], $_POST["password"])) {
    try {
        $identifier = trim($_POST["identifier"]);
        $password = $_POST["password"];

        $isEmail = str_contains($identifier, "@");
        if ($isEmail) {
            validate_email($identifier, $errors);
        } else {
            validate_username($identifier, $errors);
        }
        validate_password($password, $errors);
        if (!empty($errors)) {
            // Keep credential feedback generic on this page.
            $errors = ["Invalid username/email or password."];
        }
        if (empty($errors)) {
            $db = getDB();
            $stmt = $db->prepare(
                "SELECT id, email, username, password_hash FROM Users WHERE email = :identifier OR username = :identifier LIMIT 1"
            );
            $stmt->execute([":identifier" => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user["password_hash"])) {
                $errors[] = "Invalid username/email or password.";
            } else {
                session_regenerate_id(true);
                $_SESSION["user"] = [
                    "user_id" => (int) $user["id"],
                    "email" => $user["email"],
                    "username" => $user["username"],
                    "roles" => get_user_roles((int) $user["id"]),
                ];
                add_flash("Welcome back", "success");
                header("Location: " . project_url("dashboard.php"));
                exit;
            }
        }
    } catch (TypeError $e) {
        error_log("Login input failed: " . $e->getMessage());
        $identifier = ""; // Keep the form safe to redisplay after malformed input.
        $errors[] = "Invalid username/email or password.";
    } catch (Exception $e) {
        error_log("Login failed: " . $e->getMessage());
        $errors[] = "Login failed. Please try again.";
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
    <form method="post" action="login.php" onsubmit="return validate(this)">
    <?php
    render_input([
        "name" => "identifier",
        "label" => "Username or Email",
        "value" => $identifier,
        "attributes" => [
            "required" => true,
            "autocomplete" => "username",
            "pattern" => "(?:[a-z0-9_\\-]{3,30}|[^\\s@]+@[^\\s@]+\\.[^\\s@]+)",
            "title" => "Enter a username or email address",
        ],
    ]);

    render_input([
        "type" => "password",
        "name" => "password",
        "label" => "Password",
        "attributes" => [
            "required" => true,
            "minlength" => 8,
            "autocomplete" => "current-password",
        ],
    ]);
    render_button(["text" => "Login"]);
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
        if (form.identifier.value.includes("@")) {
            validate_email(form.identifier, errors);
        } else {
            validate_username(form.identifier, errors);
        }
        validate_password(form.password, errors);
        return show_validation_errors(errors);
    }
    </script>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>
