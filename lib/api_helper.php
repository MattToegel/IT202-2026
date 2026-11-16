<?php
// lib/api_helper.php
require_once(__DIR__ . "/load_api_keys.php");

/** Send a server-side GET or form-encoded POST without exposing its key to the browser.
 * @param string $url Fixed provider endpoint from its documentation, never a submitted URL.
 * @param string $method GET or POST, selected by the wrapper functions below.
 * @param array $data Query parameters for GET or form fields for POST.
 * @param array $options Headers and environment-setting names used for authentication.
 * @return array{ok: bool, status: int, error: string, body: string} Raw response for decoding.
 */
function api_request(string $url, string $method, array $data = [], array $options = []): array
{
    global $API_KEYS;

    $headers = $options["headers"] ?? [];
    $key_name = $options["key_name"] ?? "";

    if ($key_name !== "") {
        if (!isset($API_KEYS[$key_name]) || $API_KEYS[$key_name] === "") {
            return ["ok" => false, "status" => 0, "error" => "Missing API key setting", "body" => ""];
        }

        $key_header = $options["key_header"]
            ?? (isset($options["host_name"]) ? "X-RapidAPI-Key" : "x-api-key");
        $key_prefix = $options["key_prefix"] ?? "";
        $headers[] = $key_header . ": " . $key_prefix . $API_KEYS[$key_name];
    }

    if (isset($options["host_name"])) {
        $host_name = $options["host_name"];
        if (!isset($API_KEYS[$host_name]) || $API_KEYS[$host_name] === "") {
            return ["ok" => false, "status" => 0, "error" => "Missing API host setting", "body" => ""];
        }

        $host_header = $options["host_header"] ?? "X-RapidAPI-Host";
        $headers[] = $host_header . ": " . $API_KEYS[$host_name];
    }

    $request_url = $url;
    if ($method === "GET" && !empty($data)) {
        $separator = str_contains($url, "?") ? "&" : "?";
        $request_url .= $separator . http_build_query($data);
    }

    $ch = curl_init($request_url);
    if ($ch === false) {
        return ["ok" => false, "status" => 0, "error" => "Unable to start cURL", "body" => ""];
    }

    $curl_options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($method === "POST") {
        $curl_options[CURLOPT_POST] = true;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    }
    curl_setopt_array($ch, $curl_options);

    $body = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    unset($ch);

    return [
        "ok" => $curl_errno === 0 && $status >= 200 && $status < 300,
        "status" => $status,
        "error" => $curl_error,
        "body" => $body === false ? "" : $body,
    ];
}

/** Read an endpoint with URL-encoded query parameters.
 * @param string $url Fixed provider endpoint.
 * @param array $data Query parameters.
 * @param array $options Authentication options passed to api_request().
 * @return array Raw request result; this does not decode JSON.
 */
function api_get(string $url, array $data = [], array $options = []): array
{
    return api_request($url, "GET", $data, $options);
}

/** Submit form-encoded fields; a JSON-body API needs a different body/content type.
 * @param string $url Fixed provider endpoint.
 * @param array $data Form fields.
 * @param array $options Authentication options passed to api_request().
 * @return array Raw request result; this does not decode JSON.
 */
function api_post(string $url, array $data = [], array $options = []): array
{
    return api_request($url, "POST", $data, $options);
}

// Replace the TODO with these functions in lib/api_helper.php.
/**
 * Decodes a successful API response and appends user-friendly errors when it is unusable.
 *
 * @param array $response Response returned by api_get(), api_post(), or api_sample_response().
 * @param string|null $expected_json_key JSON key to inspect, or null to use the root array.
 * @param array $errors Shared validation-style error list.
 * @return array|null Decoded response array, or null when validation fails.
 */
function decode_api_response(array $response, ?string $expected_json_key, array &$errors): ?array
{
    if (!$response["ok"]) {
        $errors[] = $response["status"] === 0
            ? "The API is not configured or could not be reached."
            : "The API request failed. Please try again later.";
        error_log("API request failed: " . ($response["error"] ?: "HTTP " . $response["status"]));
        return null;
    }

    $body = trim($response["body"]);
    if ($body === "") {
        $errors[] = "The API returned an empty response.";
        error_log("API returned an empty response.");
        return null;
    }

    $decoded = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        $errors[] = "The API returned an unexpected response.";
        error_log("API returned invalid JSON: " . json_last_error_msg());
        return null;
    }

    if (
        $expected_json_key !== null &&
        (!isset($decoded[$expected_json_key]) ||
            !is_array($decoded[$expected_json_key]))
    ) {
        $errors[] = "The API response did not contain the expected data.";
        error_log("API response is missing expected JSON key: " . $expected_json_key);
        return null;
    }

    return $decoded;
}

/**
 * Loads one sanitized cached response using the same result shape as api_get() and api_post().
 *
 * @param string $file_name JSON sample file stored under lib/api_samples/.
 * @return array Request-shaped result that decode_api_response() can validate.
 */
function api_sample_response(string $file_name): array
{
    $sample_path = __DIR__ . "/api_samples/" . basename($file_name);
    if (!is_file($sample_path)) {
        return ["ok" => false, "status" => 0, "error" => "Cached sample is missing", "body" => ""];
    }

    $body = file_get_contents($sample_path);
    if ($body === false) {
        return ["ok" => false, "status" => 0, "error" => "Cached sample could not be read", "body" => ""];
    }

    return ["ok" => true, "status" => 200, "error" => "", "body" => $body];
}
