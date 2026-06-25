<?php
header('Content-Type: application/json');

$ticker = isset($_GET['ticker']) ? strtoupper(trim($_GET['ticker'])) : '';
if (!$ticker) {
    echo json_encode(['error' => 'No ticker provided']);
    exit;
}

// Cache settings
$cacheFile = __DIR__ . "/cache_{$ticker}.json";
$cacheTime = 60; // seconds

// Serve from cache if fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

$url = "https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?interval=1m&range=1d&includePrePost=true";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
        "Accept: application/json"
    ],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
curl_close($ch);

if (!$response || stripos($response, 'Too Many Requests') !== false) {
    echo json_encode(['error' => 'Yahoo temporarily rate-limited. Please wait 1 minute.']);
    exit;
}

// Save cache
file_put_contents($cacheFile, $response);

echo $response;
