// File: public_html/project/helpers.js
/** Render collected browser errors in the same feedback area used by PHP. */
function showFeedback(errors) {
    const feedback = document.getElementById("feedback");
    feedback.replaceChildren();
    for (const error of errors) {
        const message = document.createElement("p");
        message.textContent = error;
        feedback.appendChild(message);
    }
    return errors.length === 0;
}

/** Return registration errors without rendering them or changing password values. */
function registrationErrors(form) {
    const errors = [];
    const email = form.email.value.trim();
    const password = form.password.value;
    if (!email || !form.email.validity.valid) {
        errors.push("Enter a valid email address.");
    }
    if (password.length < 8) {
        errors.push("Password must be at least 8 characters.");
    }
    if (password !== form.confirm_password.value) {
        errors.push("Passwords must match.");
    }
    return errors;
}

/** Return login form errors; the server still verifies the credentials. */
function loginErrors(form) {
    const errors = [];
    const email = form.email.value.trim();
    if (!email || !form.email.validity.valid) {
        errors.push("Enter a valid email address.");
    }
    if (form.password.value.length < 8) {
        errors.push("Password must be at least 8 characters.");
    }
    return errors;
}
