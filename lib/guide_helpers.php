<?php
// lib/guide_helpers.php
// Require this file from lib/app.php after db_helpers.php and render_functions.php.

/** Returns trimmed form text; malformed array submissions become empty. */
function guide_post_text(string $name): string
{
    $value = $_POST[$name] ?? "";
    return is_string($value) ? trim($value) : "";
}

/** Returns the filters supported by the StarCraft guide lists. */
function guide_filter_rules(): array
{
    return [
        "title",
        "primary_category",
        "game" => ["sc1", "sc2"],
        "player_race" => ["protoss", "zerg", "terran"],
        "opponent_race" => ["protoss", "zerg", "terran"],
    ];
}

/**
 * Builds the StarCraft guide conditions and PDO parameters.
 *
 * @param array $filters Validated guide filter values.
 * @param string $column_prefix Trusted table or alias prefix, including the
 * trailing period, such as "g.".
 * @return array SQL joined by AND and its named parameters.
 */
function build_guide_filter_query(
    array $filters,
    string $column_prefix = ""
): array {
    $conditions = [];
    $params = [];

    foreach (["title", "primary_category"] as $name) {
        if (!empty($filters[$name])) {
            $column = db_qualified_identifier($name, $column_prefix);
            $conditions[] = $column . " LIKE :" . $name;
            $params[$name] = "%" . $filters[$name] . "%";
        }
    }

    foreach (["game", "player_race", "opponent_race"] as $name) {
        if (!empty($filters[$name])) {
            $column = db_qualified_identifier($name, $column_prefix);
            $conditions[] = "LOWER(" . $column . ") = :" . $name;
            $params[$name] = strtolower($filters[$name]);
        }
    }

    return [
        "sql" => implode(" AND ", $conditions),
        "params" => $params,
    ];
}

/** Renders the guide filter form with validated list state and sort labels. */
function render_guide_search(
    array $filters,
    int $limit,
    string $sort,
    string $direction,
    array $sort_options
): void {
    require(__DIR__ . "/../partials/guide_search.php");
}
