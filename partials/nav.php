<?php
$isLoggedIn = is_logged_in();
?>
<link rel="stylesheet" href="/project/styles.css">
<nav>
  <a href="/project/index.php">Home</a>
  <?php if ($isLoggedIn): ?>
    <a href="/project/dashboard.php">Dashboard</a>
    <a href="/project/profile.php">Profile</a>
    <?php if (has_role("Admin")): ?>
      <a href="<?php echo project_url("admin/create_role.php"); ?>">Create Role</a>
      <a href="<?php echo project_url("admin/list_roles.php"); ?>">Roles</a>
      <a href="<?php echo project_url("admin/assign_roles.php"); ?>">Assign Roles</a>
    <?php endif; ?>
    <a href="/project/logout.php">Logout</a>
  <?php else: ?>
    <a href="/project/login.php">Login</a>
    <a href="/project/register.php">Register</a>
  <?php endif; ?>
</nav>
<script src="/project/helpers.js"></script>
