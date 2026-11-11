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
?>