<?php
// lib/starcraft_api.php

/**
 * Returns a trimmed string or null for a missing, empty, or non-string value.
 *
 * @param mixed $value API value to normalize.
 * @return string|null Clean string or null.
 */
function sc_nullable_string(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    if ($value === "") {
        return null;
    }

    return $value;
}

/**
 * Returns a safe HTTP(S) URL or null.
 *
 * @param mixed $value API value to validate.
 * @return string|null Valid URL or null.
 */
function sc_nullable_url(mixed $value): ?string
{
    $url = sc_nullable_string($value);
    if ($url === null || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return null;
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ["http", "https"], true)) {
        return null;
    }

    return $url;
}

/**
 * Maps one getGameGuides() item to the stable columns used by the project.
 *
 * @param array $guide One provider guide item.
 * @return array|null Mapped guide or null when required data is invalid.
 */
function map_sc_guide(array $guide): ?array
{
    $required_json_keys = ["id", "game", "title"];
    foreach ($required_json_keys as $json_key) {
        if (sc_nullable_string($guide[$json_key] ?? null) === null) {
            error_log("StarCraft guide is missing JSON key: $json_key");
            return null;
        }
    }

    $source_url = sc_nullable_url($guide["source"]["url"] ?? null);
    if ($source_url === null) {
        $source_url = sc_nullable_url($guide["source"]["canonicalUrl"] ?? null);
    }

    $row = [
        "api_id" => sc_nullable_string($guide["id"]),
        "excerpt" => sc_nullable_string($guide["excerpt"]["text"] ?? null),
        "game" => sc_nullable_string($guide["game"]),
        "primary_category" => sc_nullable_string($guide["primaryCategory"] ?? null),
        "slug" => sc_nullable_string($guide["slug"] ?? null),
        "source_author" => sc_nullable_string($guide["source"]["author"] ?? null),
        "source_url" => $source_url,
        "status" => sc_nullable_string($guide["status"] ?? null),
        "summary" => sc_nullable_string($guide["summary"]["text"] ?? null),
        "title" => sc_nullable_string($guide["title"]),
        "video" => sc_nullable_string($guide["video"] ?? null),
        "opponent_race" => sc_nullable_string($guide["opponentRace"] ?? null),
        "player_race" => sc_nullable_string($guide["playerRace"] ?? null),
        "matchup" => sc_nullable_string($guide["matchup"] ?? null),
    ];

    $max_lengths = [
        "api_id" => 100, "game" => 20, "primary_category" => 50,
        "slug" => 160, "source_author" => 100, "source_url" => 500,
        "status" => 30, "title" => 150, "video" => 500,
        "opponent_race" => 20, "player_race" => 20, "matchup" => 20,
    ];
    foreach ($max_lengths as $column => $max_length) {
        if ($row[$column] !== null && strlen($row[$column]) > $max_length) {
            error_log("StarCraft guide field exceeds Guides.$column length.");
            return null;
        }
    }

    return $row;
}

/**
 * Calls RapidAPI's getGameGuides() endpoint for one supported StarCraft game.
 *
 * @param string $game Requested game slug: sc1 or sc2.
 * @param array $errors Shared validation-style error list.
 * @return array Project-shaped guide rows.
 */
function fetch_starcraft_guides(string $game, array &$errors): array
{
    $game = strtolower(trim($game));
    $supported_games = ["sc1", "sc2"];
    if (!in_array($game, $supported_games, true)) {
        $errors[] = "Choose a supported StarCraft game.";
        error_log("Unsupported StarCraft game requested: $game");
        return [];
    }

    $response = api_get(
        "https://unofficial-starcraft-guides-api.p.rapidapi.com/api/v1/games/$game/guides/index.json",
        [],
        ["key_name" => "STARCRAFT_API_KEY", "host_name" => "STARCRAFT_API_HOST"]
    );
    $decoded = decode_api_response($response, "guides", $errors);
    if ($decoded === null) {
        return [];
    }

    $rows = [];
    foreach ($decoded["guides"] as $guide) {
        if (!is_array($guide)) {
            error_log("StarCraft API returned a non-object guide item.");
            continue;
        }

        $row = map_sc_guide($guide);
        if ($row !== null) {
            $rows[] = $row;
        }
    }

    if ($decoded["guides"] !== [] && $rows === []) {
        $errors[] = "The API returned no usable guides.";
    }

    return $rows;
}
