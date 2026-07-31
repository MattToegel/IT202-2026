<?php
session_start();

// Every project page loads app.php, so one check protects every POST handler.
require_once(__DIR__ . "/csrf_helpers.php");
require_valid_csrf_token();

require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/db_helpers.php");
require_once(__DIR__ . "/pagination_helpers.php");
require_once(__DIR__ . "/render_functions.php");
// url_helpers.php must load before helpers or partials that call project_url().
require_once(__DIR__ . "/url_helpers.php");
require_once(__DIR__ . "/validations.php");
// Keep user_helpers.php before role_helpers.php.
// has_role() depends on is_logged_in().
require_once(__DIR__ . "/user_helpers.php");
require_once(__DIR__ . "/flash_messages.php");
require_once(__DIR__ . "/duplicate_user_details.php");
// require_role() depends on flash() and project_url().
require_once(__DIR__ . "/role_helpers.php");
require_once(__DIR__ . "/api_helper.php");
require_once(__DIR__ . "/stock_api.php");
require_once(__DIR__ . "/starcraft_api.php");
