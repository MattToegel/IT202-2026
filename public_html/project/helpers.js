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
 * Replace #feedback with the current errors as safe text rows.
 * @param {string[]} errors Results collected by the page's validate function.
 * @returns {boolean} True when no errors block submission.
 */
function show_validation_errors(errors) {
    const feedback = document.getElementById("feedback");
    feedback.replaceChildren();
    for (const error of errors) {
        const message = document.createElement("p");
        message.textContent = error;
        feedback.appendChild(message);
    }
    return errors.length === 0;
}
