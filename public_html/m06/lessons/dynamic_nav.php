<?php
require_once(__DIR__ . "/../../../lib/db.php");
require_once(__DIR__ . "/dynamic_utils.php");

function render_dynamic_nav(): void
{
    ?>
    <nav>
        <ul>
            <li><a href="<?php echo dynamic_lesson_url("dynamic_create.php"); ?>">Create Sample</a></li>
            <li><a href="<?php echo dynamic_lesson_url("dynamic_list.php"); ?>">List Samples</a></li>
            <li><a href="<?php echo dynamic_lesson_url("dynamic_edit.php?id=1"); ?>">Edit Sample #1</a></li>
        </ul>
    </nav>
    <?php
}
?>