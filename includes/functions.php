<?php
// includes/functions.php

/**
 * Get Stock Price with Caching (Your original function, preserved)
 */
function getStockPrice($symbol, $conn) {
    // --- NO CHANGES WERE MADE TO THIS FUNCTION. IT IS PRESERVED. ---
    $api_key = 'd4vnrupr01qs25f19tk0d4vnrupr01qs25f19tkg'; 
    $cache_minutes = 15; 
    $symbol = strtoupper(trim($symbol));

    $sql = "SELECT price, last_updated FROM stock_cache WHERE symbol = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $symbol);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $last_updated = strtotime($row['last_updated']);
        if ((time() - $last_updated) < ($cache_minutes * 60)) {
            return (float)$row['price'];
        }
    }

    $url = "https://finnhub.io/api/v1/quote?symbol=" . $symbol . "&token=" . $api_key;
    $ctx = stream_context_create(['http'=> ['timeout' => 2]]);
    $response = @file_get_contents($url, false, $ctx); 
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['c']) && $data['c'] > 0) {
            $new_price = $data['c'];
            $update_sql = "INSERT INTO stock_cache (symbol, price, last_updated) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE price = ?, last_updated = NOW()";
            $save = $conn->prepare($update_sql);
            $save->bind_param("sdd", $symbol, $new_price, $new_price);
            $save->execute();
            return (float)$new_price;
        }
    }
    return isset($row['price']) ? (float)$row['price'] : 0.00;
}

/**
 * Get Full Quote for Ticker (Your original function, preserved)
 */
function getStockQuote($symbol, $conn) {
    // --- NO CHANGES WERE MADE TO THIS FUNCTION. IT IS PRESERVED. ---
    $api_key = 'd4vnrupr01qs25f19tk0d4vnrupr01qs25f19tkg'; 
    $symbol = strtoupper(trim($symbol));
    
    $url = "https://finnhub.io/api/v1/quote?symbol=" . $symbol . "&token=" . $api_key;
    
    $ctx = stream_context_create(['http'=> ['timeout' => 1]]); 
    $response = @file_get_contents($url, false, $ctx);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['c'])) {
            return [
                'price' => $data['c'],
                'change' => $data['d'],
                'percent' => $data['dp']
            ];
        }
    }
    return ['price' => 0, 'change' => 0, 'percent' => 0];
}

// === NEW FUNCTION TO BE ADDED ===
/**
 * Create the stock_cache table if it doesn't exist.
 * This prevents crashes on new installations.
 */
function ensureCacheTableExists($conn) {
    $tableName = 'stock_cache';
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (
              `symbol` varchar(20) NOT NULL,
              `price` decimal(10,2) NOT NULL,
              `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`symbol`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
    if (!$conn->query($sql)) {
        // Handle error if table creation fails
        die("Error creating cache table: " . $conn->error);
    }
}
?>
