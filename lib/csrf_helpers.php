<?php
/**
 * Returns the CSRF token for the current session, creating it when needed.
 */
function csrf_token(): string
{
    if (
        !isset($_SESSION["csrf_token"])
        || !is_string($_SESSION["csrf_token"])
        || $_SESSION["csrf_token"] === ""
    ) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

/**
 * Renders the hidden token field required by project POST forms.
 */
function render_csrf_input(): void
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8");
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Stops a POST request when its submitted token does not match the session.
 */
function require_valid_csrf_token(): void
{
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
        return;
    }

    $submitted_token = $_POST["csrf_token"] ?? "";
    if (
        !is_string($submitted_token)
        || !hash_equals(csrf_token(), $submitted_token)
    ) {
        http_response_code(403);
        exit("Invalid CSRF token.");
    }
}

/**
 * Replaces the token after the authenticated session identity changes.
 */
function rotate_csrf_token(): void
{
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
