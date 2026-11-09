// File: public_html/project/helpers.js
/**
 * Check an email field using its HTML constraints.
 * @param {HTMLInputElement} emailInput Field to check.
 * @param {string[]} errors Page errors; an invalid field adds one message.
 * @returns {boolean} True when valid, false when an error is added.
 */
function validate_email(emailInput, errors) {
    if (!emailInput.value.trim() || !emailInput.validity.valid) {
        errors.push("Enter a valid email address.");
        return false;
    }
    return true;
}

/**
 * Check the shared password length without changing the field value.
 * @param {HTMLInputElement} passwordInput Password field to check.
 * @param {string[]} errors Page errors; a short password adds one message.
 * @returns {boolean} Whether the minimum length is met.
 */
function validate_password(passwordInput, errors) {
    if (passwordInput.value.length < 8) {
        errors.push("Password must be at least 8 characters.");
        return false;
    }
    return true;
}

/**
 * Compare password fields exactly; their names may differ between pages.
 * @param {HTMLInputElement} passwordInput Password being confirmed.
 * @param {HTMLInputElement} confirmInput Confirmation field.
 * @param {string[]} errors Page errors; a mismatch adds one message.
 * @returns {boolean} True only when the values match.
 */
function validate_passwords_match(passwordInput, confirmInput, errors) {
    if (passwordInput.value !== confirmInput.value) {
        errors.push("Passwords must match.");
        return false;
    }
    return true;
}

/**
 * Check a username field with the same rule as PHP.
 * @param {HTMLInputElement} input Field to validate.
 * @param {string[]} errors Collected page errors.
 * @returns {boolean} Whether the username is valid.
 */
function validate_username(input, errors) {
    if (!/^[a-z0-9_-]{3,30}$/.test(input.value.trim())) {
        errors.push("Username must use 3-30 lowercase letters, numbers, underscores or hyphens.");
        return false;
    }
    return true;
}

/**
 * Add safe text to #flash on this page; does not write to the PHP session.
 * @param {string} message Text to display.
 * @param {string} color Display category, such as info or danger.
 */
function add_flash(message = "", color = "info") {
    const flash = document.getElementById("flash");
    const row = document.createElement("div");
    row.className = "row justify-content-center";
    const alert = document.createElement("div");
    alert.className = `alert alert-${color}`;
    alert.textContent = message;
    row.appendChild(alert);
    flash.appendChild(row);
}

/**
 * Replace #flash with the current form's validation errors.
 * @param {string[]} errors Results collected by the page's validate function.
 * @returns {boolean} True when no errors block submission.
 */
function show_validation_errors(errors) {
    document.getElementById("flash").replaceChildren();
    for (const error of errors) {
        add_flash(error, "danger");
    }
    return errors.length === 0;
}
