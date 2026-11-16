<?php
/**
 * Render the shared navigation using the current login state.
 * Called by each project page after app.php loads its helpers.
 */
function render_nav()
{
    require(__DIR__ . "/../partials/nav.php");
}
?>