<?php
// partials/nav.php
// Usage: call render_nav() immediately after the opening <body> tag.
$isLoggedIn = is_logged_in();
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars(project_url("styles.css")); ?>">
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
    <div class="container">
        <a class="navbar-brand" href="<?php echo project_url("index.php"); ?>">IT202 Project</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#projectNav" aria-controls="projectNav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="projectNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo project_url("index.php"); ?>">Home</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?php echo project_url("guides.php"); ?>">Guides</a></li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo project_url("dashboard.php"); ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo project_url("profile.php"); ?>">Profile</a></li>
                    <?php if (has_role("Admin")): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/create_role.php"); ?>">Create Role</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/list_roles.php"); ?>">List Roles</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/assign_roles.php"); ?>">Assign Roles</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/create_stock.php"); ?>">Create Stock</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/list_stocks.php"); ?>">List Stocks</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/create_company.php"); ?>">Create Company</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/list_companies.php"); ?>">List Companies</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/create_guide.php"); ?>">Create Guide</a></li>
                                <li><a class="dropdown-item" href="<?php echo project_url("admin/list_guides.php"); ?>">List Guides</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo project_url("logout.php"); ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo project_url("login.php"); ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo project_url("register.php"); ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<script src="<?php echo htmlspecialchars(project_url("helpers.js")); ?>"></script>
