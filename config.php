<?php
declare(strict_types=1);

function loadEnvFile(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $separatorPos = strpos($line, '=');
        if ($separatorPos === false) {
            continue;
        }

        $key = trim(substr($line, 0, $separatorPos));
        $value = trim(substr($line, $separatorPos + 1));

        if ($key === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

loadEnvFile(__DIR__ . '/.env');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Configure these values with your Supabase project.
 * You can also set SUPABASE_URL and SUPABASE_API_KEY in environment variables.
 */
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? (getenv('SUPABASE_URL') ?: 'https://YOUR_ACTUAL_URL_HERE'));
define('SUPABASE_API_KEY', $_ENV['SUPABASE_API_KEY'] ?? (getenv('SUPABASE_API_KEY') ?: 'YOUR_ACTUAL_ANON_KEY_HERE'));
define('ADMIN_USER', $_ENV['ADMIN_USER'] ?? (getenv('ADMIN_USER') ?: 'admin'));
define('ADMIN_PASS', $_ENV['ADMIN_PASS'] ?? (getenv('ADMIN_PASS') ?: 'admin123'));

function supabaseRequest(string $method, string $endpoint, ?array $payload = null, array $extraHeaders = []): array
{
    $normalizedMethod = strtoupper($method);
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($endpoint, '/');
    $ch = curl_init($url);

    $headers = array_merge([
        'apikey: ' . SUPABASE_API_KEY,
        'Authorization: Bearer ' . SUPABASE_API_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
    ], $extraHeaders);

    if ($normalizedMethod === 'PATCH') {
        $hasPreferHeader = false;
        foreach ($headers as $header) {
            if (stripos($header, 'Prefer:') === 0) {
                $hasPreferHeader = true;
                break;
            }
        }

        if (!$hasPreferHeader) {
            $headers[] = 'Prefer: return=representation';
        }
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $normalizedMethod,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $responseBody = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($responseBody === false) {
        return [
            'ok' => false,
            'status' => $httpCode,
            'data' => null,
            'error' => $curlError !== '' ? $curlError : 'Unknown cURL error.',
        ];
    }

    $decoded = json_decode($responseBody, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'ok' => true,
            'status' => $httpCode,
            'data' => $decoded,
            'error' => null,
        ];
    }

    $message = 'Supabase request failed.';
    if (is_array($decoded) && isset($decoded['message'])) {
        $message = (string) $decoded['message'];
    }

    return [
        'ok' => false,
        'status' => $httpCode,
        'data' => $decoded,
        'error' => $message,
    ];
}

function isLoggedIn(): bool
{
    return isset($_SESSION['student']) && is_array($_SESSION['student']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: admin_login.php');
        exit;
    }
}

function formatTanggalIndonesia(string $dateTime): string
{
    try {
        $dt = new DateTime($dateTime);
    } catch (Exception $exception) {
        return $dateTime;
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    $monthNumber = (int) $dt->format('n');
    $monthName = $bulan[$monthNumber] ?? $dt->format('F');

    return $dt->format('d') . ' ' . $monthName . $dt->format(' Y, H:i');
}
