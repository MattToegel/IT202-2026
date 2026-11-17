<?php
$isLoggedIn = is_logged_in();
?>
<link rel="stylesheet" href="<?php echo project_url("styles.css"); ?>">
<nav>
  <a href="<?php echo project_url("index.php"); ?>">Home</a>
  <?php if ($isLoggedIn): ?>
    <a href="<?php echo project_url("dashboard.php"); ?>">Dashboard</a>
    <a href="<?php echo project_url("profile.php"); ?>">Profile</a>
    <?php if (has_role("Admin")): ?>
      <a href="<?php echo project_url("admin/create_role.php"); ?>">Create Role</a>
      <a href="<?php echo project_url("admin/list_roles.php"); ?>">Roles</a>
      <a href="<?php echo project_url("admin/assign_roles.php"); ?>">Assign Roles</a>
      <a href="<?php echo project_url('admin/create_stock.php'); ?>">Create Stock</a>
      <a href="<?php echo project_url('admin/list_stocks.php'); ?>">List Stocks</a>
      <a href="<?php echo project_url('admin/create_company.php'); ?>">Create Company</a>
      <a href="<?php echo project_url('admin/list_companies.php'); ?>">List Companies</a>
    <?php endif; ?>
    <a href="<?php echo project_url("logout.php"); ?>">Logout</a>
  <?php else: ?>
    <a href="<?php echo project_url("login.php"); ?>">Login</a>
    <a href="<?php echo project_url("register.php"); ?>">Register</a>
  <?php endif; ?>
</nav>
<script src="<?php echo project_url("helpers.js"); ?>"></script>
