<?php
/**
 * Render the shared navigation using the current login state.
 * Called by each project page after app.php loads its helpers.
 */
function render_nav()
{
    require(__DIR__ . "/../partials/nav.php");
}
/**
 * Called near the end of a page after all its messages have been queued.
 * Renders the shared partial, which reads and clears the session queue.
 */
function render_flash_messages(): void
{
    require(__DIR__ . "/../partials/flash.php");
}

/**
 * Renders the shared document metadata and styles for one page.
 *
 * @param string $title Text displayed in the browser tab.
 */
function render_head(string $title): void
{
    require(__DIR__ . "/../partials/head.php");
}

/**
 * Loads Bootstrap and optional project-local scripts after the page content.
 * @param array $page_scripts Trusted paths relative to public_html/project.
 */
function render_scripts(array $page_scripts = []): void
{
    require(__DIR__ . "/../partials/scripts.php");
}

/**
 * Builds escaped HTML attributes from a small configuration array.
 *
 * @param array $attributes Attribute names and their values.
 * @return string Escaped attributes ready for an HTML element.
 */
function render_html_attributes(array $attributes): string
{
    $parts = [];

    foreach ($attributes as $name => $value) {
        if (!is_string($name) || !preg_match("/^[A-Za-z_:][A-Za-z0-9:_.-]*$/", $name)) {
            continue;
        }

        if ($value === false || $value === null) {
            continue;
        }

        $safe_name = htmlspecialchars($name);
        if ($value === true) {
            $parts[] = $safe_name;
            continue;
        }

        $safe_value = htmlspecialchars((string) $value);
        $parts[] = "$safe_name=\"$safe_value\"";
    }

    return implode(" ", $parts);
}

/**
 * Renders one form field from its configuration.
 *
 * @param array $field Label, name, type, value, and HTML attributes.
 */
function render_input(array $field): void
{
    require(__DIR__ . "/../partials/input.php");
}

/**
 * Renders one button from its configuration.
 *
 * @param array $button Text, type, variant, and HTML attributes.
 */
function render_button(array $button): void
{
    require(__DIR__ . "/../partials/button.php");
}

/**
 * Renders rows as a table with optional authorized actions.
 *
 * @param array $results Database or API records to display.
 * @param array $columns Row keys mapped to visible column headings.
 * @param array $actions Allowed links or forms for each row.
 * @param string $empty_message Message displayed when there are no rows.
 */
function render_table(
    array $results,
    array $columns,
    array $actions = [],
    string $empty_message = "No records found."
): void {
    require(__DIR__ . "/../partials/table.php");
}
?>