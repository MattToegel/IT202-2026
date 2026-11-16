/**
 * Checks a Guide form and shows errors through the existing shared flash area.
 * @param {HTMLFormElement} form The submitted form.
 * @returns {boolean} Whether the form can be submitted to PHP.
 */
function validate(form) {
    const errors = [];
    for (const input of form.querySelectorAll("input, select, textarea")) {
        if (!input.checkValidity()) {
            errors.push(input.validationMessage);
        }
    }
    return show_validation_errors(errors);
}
