<?php
// File: lib/validations.php
/** Called by registration to append field errors without changing submitted values. */
function validate_registration_input(
    string $email,
    string $password,
    string $confirm_password,
    array &$errors
): void {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password is too short.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords must match.";
    }
}

/** Called by login to append one generic error for invalid credential input. */
function validate_login_input(string $email, string $password, array &$errors): void
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) === 0) {
        $errors[] = "Invalid email or password.";
    }
}
?>
