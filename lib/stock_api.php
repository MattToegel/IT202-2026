<?php
// lib/stock_api.php
// lib/app.php imports api_helper.php before this wrapper.

/**
 * Converts Alpha Vantage JSON keys and values into a cleaner project-friendly shape.
 *
 * @param array $api_record One entity from an Alpha Vantage API response.
 * @return array Record with normalized field names and numeric values.
 */
function transform_alpha_vantage_record(array $api_record): array
{
    $transformed_record = [];

    foreach ($api_record as $json_key => $value) {
        // Alpha Vantage uses names like "01. symbol" and "07. latest trading day".
        $key_parts = explode(" ", $json_key, 2);
        $field_name = $json_key;
        if (count($key_parts) === 2) {
            $field_name = $key_parts[1];
        }
        $field_name = str_replace(" ", "_", $field_name);

        if (is_string($value)) {
            $value = str_replace("%", "", $value);
        }
        if (in_array($field_name, ["open", "high", "low", "price", "volume", "change", "previous_close", "change_percent", "matchScore"], true) && is_numeric($value)) {
            // Scientific notation may contain a fractional value without a decimal point.
            if (strpos((string)$value, ".") !== false || stripos((string)$value, "e") !== false) {
                $value = (float)$value;
            } else {
                $value = (int)$value;
            }
        }

        $transformed_record[$field_name] = $value;
    }

    return $transformed_record;
}

/**
 * Fetches and validates a quote before mapping it to the Stocks table shape.
 *
 * @param string $symbol Stock symbol accepted by the external API.
 * @param array $errors Validation and API response errors for the page.
 * @return array|null One project-shaped row, or null when no valid quote is available.
 */
function fetch_quote(string $symbol, array &$errors): ?array
{
    $result = api_get(
        "https://alpha-vantage.p.rapidapi.com/query",
        ["function" => "GLOBAL_QUOTE", "symbol" => $symbol],
        ["key_name" => "STOCK_API_KEY", "host_name" => "STOCK_API_HOST"]
    );

    $json_key = "Global Quote";
    $decoded = decode_api_response($result, $json_key, $errors);
    if ($decoded === null) {
        return null;
    }
    $quote_json = $decoded[$json_key];

    $quote = transform_alpha_vantage_record($quote_json);
    if (!$quote) {
        return null; // A valid response can contain no matching quote.
    }
    $valid = is_string($quote["symbol"] ?? null)
        && preg_match('/^[A-Za-z0-9.\-]{1,10}$/', $quote["symbol"]);
    $max_value = 99999999.9999; // DECIMAL(12, 4): eight digits before the decimal point.
    foreach (["open", "high", "low", "price"] as $field) {
        $value = $quote[$field] ?? null;
        $valid = $valid && is_numeric($value) && is_finite((float)$value)
            && $value >= 0 && $value <= $max_value;
    }
    $valid = $valid && ($quote["price"] ?? 0) > 0;

    // An absent trading date stays NULL, as allowed by the table definition.
    $day = $quote["latest_trading_day"] ?? null;
    if ($day !== null) {
        $date = is_string($day) ? DateTimeImmutable::createFromFormat("!Y-m-d", $day) : false;
        $valid = $valid && $date && $date->format("Y-m-d") === $day;
    }
    if (!$valid) {
        error_log("fetch_quote received invalid symbol, numeric or date values");
        $errors[] = "The API returned an unusable stock quote.";
        return null;
    }

    return [
        "symbol" => $quote["symbol"],
        "open" => $quote["open"],
        "high" => $quote["high"],
        "low" => $quote["low"],
        "price" => $quote["price"],
        "volume" => $quote["volume"] ?? 0,
        "latest_trading_day" => $quote["latest_trading_day"] ?? null,
        "change_percent" => $quote["change_percent"] ?? 0,
        "is_api" => 1,
    ];
}

/**
 * Searches companies and maps usable matches to the Companies table shape.
 *
 * @param string $keywords Search text accepted by the external API.
 * @param array $errors Validation and API response errors for the page.
 * @return array Usable company rows; errors distinguish invalid responses from no matches.
 */
function search_companies(string $keywords, array &$errors): array
{
    $result = api_get(
        "https://alpha-vantage.p.rapidapi.com/query",
        ["function" => "SYMBOL_SEARCH", "keywords" => $keywords],
        ["key_name" => "STOCK_API_KEY", "host_name" => "STOCK_API_HOST"]
    );

    $json_key = "bestMatches";
    $decoded = decode_api_response($result, $json_key, $errors);
    if ($decoded === null) {
        return [];
    }
    $matches_json = $decoded[$json_key];

    $companies = [];
    foreach ($matches_json as $match_json) {
        if (!is_array($match_json)) {
            continue;
        }

        $company = transform_alpha_vantage_record($match_json);
        $valid = true;
        foreach (["symbol" => 10, "name" => 120, "type" => 40, "region" => 80, "currency" => 10] as $field => $max) {
            $value = $company[$field] ?? "";
            if (!is_string($value) || strlen(trim($value)) > $max) {
                $valid = false;
                break;
            }
            $company[$field] = trim($value);
        }
        if (!$valid || !preg_match('/^[A-Za-z0-9.\-]{1,10}$/', $company["symbol"]) || $company["name"] === "") {
            error_log("search_companies skipped unusable company fields");
            continue;
        }

        $companies[] = [
            "symbol" => $company["symbol"],
            "name" => $company["name"] ?? "",
            "type" => $company["type"] ?? "",
            "region" => $company["region"] ?? "",
            "currency" => $company["currency"] ?? "",
            "is_api" => 1,
        ];
    }

    if ($matches_json && !$companies) {
        $errors[] = "The API returned no usable company data.";
    }
    return $companies;
}
