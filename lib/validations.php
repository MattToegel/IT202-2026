<?php
// File: lib/validations.php
/**
 * Check an email before the page uses it.
 * @param string $email Email already trimmed by the calling page.
 * @param string[] $errors Page errors; an invalid value appends one error by reference.
 * @return bool True when valid, false when an error is added.
 */
function validate_email(string $email, array &$errors): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
        return false;
    }
    return true;
}

/**
 * Check the shared minimum password length without changing the value.
 * @param string $password Password exactly as submitted.
 * @param string[] $errors Page errors; a short password appends one error.
 * @return bool Whether the minimum length is met.
 */
function validate_password(string $password, array &$errors): bool
{
    if (strlen($password) < 8) {
        $errors[] = "Password is too short.";
        return false;
    }
    return true;
}

/**
 * Compare two password values exactly, without trimming either one.
 * @param string $password Password being confirmed.
 * @param string $confirm Confirmation entered by the user.
 * @param string[] $errors Page errors; a mismatch appends one error.
 * @return bool True only when the two values match.
 */
function validate_passwords_match(string $password, string $confirm, array &$errors): bool
{
    if ($password !== $confirm) {
        $errors[] = "Passwords must match.";
        return false;
    }
    return true;
}

/**
 * Check the shared username rule for registration and account updates.
 * @param string $username Already trimmed by the calling page.
 * @param string[] $errors Page errors; an invalid value appends one message.
 * @return bool Whether the username has 3-30 lowercase letters, digits, underscores or hyphens.
 */
function validate_username(string $username, array &$errors): bool
{
    if (!preg_match('/^[a-z0-9_-]{3,30}$/', $username)) {
        $errors[] = "Username must use 3-30 lowercase letters, numbers, underscores or hyphens.";
        return false;
    }
    return true;
}

/** Validate checkbox IDs before any bulk relationship is changed.
 * @param array $values Submitted checkbox values.
 * @param string[] $errors Collected form errors.
 * @param bool $allowNegative Permit seeded negative role IDs when true.
 * @return int[] Unique valid IDs, or an empty array after an invalid value.
 */
function validate_ids(array $values, array &$errors, bool $allowNegative = false): array
{
    $ids = [];
    foreach ($values as $value) {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        if ($id === false || $id === 0 || (!$allowNegative && $id < 0)) {
            $errors[] = "Select valid numeric IDs.";
            return [];
        }
        $ids[] = $id;
    }
    return array_unique($ids);
}
?>
