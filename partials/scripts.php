<?php
// partials/scripts.php - loaded by render_scripts() before </body>.
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
<?php foreach ($page_scripts as $script): ?>
    <script src="<?php echo htmlspecialchars(project_url($script)); ?>"></script>
<?php endforeach; ?>
