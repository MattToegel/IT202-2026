<?php
// File: lib/user_helpers.php
/**
 * Used by navigation and guards after app.php starts the session.
 * @return bool Whether the session has a user entry; does not redirect.
 */
function is_logged_in(): bool
{
    return isset($_SESSION["user"]);
}

/**
 * Read the current email; the caller still escapes it for HTML output.
 * @return string Signed-in email, or an empty string when absent.
 */
function get_user_email(): string
{
    return is_logged_in() ? ($_SESSION["user"]["email"] ?? "") : "";
}

/** Read the authenticated account key for protected queries.
 * @return int Session-owned user ID, or zero when not signed in.
 */
function get_user_id(): int
{
    return (int) ($_SESSION["user"]["user_id"] ?? 0);
}

/** Translate database uniqueness failures into form feedback without exposing SQL.
 * @param PDOException $e The failed account write.
 * @param string[] $errors Collected page errors.
 */
function duplicate_user_detail(PDOException $e, array &$errors): void
{
    // MySQL 1062 means a duplicate value in a UNIQUE column.
    if (($e->errorInfo[1] ?? null) === 1062) {
        $errors[] = "That email or username is already in use.";
    } else {
        error_log("Account write failed: " . $e->getMessage());
        $errors[] = "Account details could not be saved. Please try again.";
    }
}

/** Read the signed-in username for reusable page output.
 * @return string Username, or an empty string when absent; escape it for HTML.
 */
function get_user_username(): string
{
    return $_SESSION["user"]["username"] ?? "";
}
?>
