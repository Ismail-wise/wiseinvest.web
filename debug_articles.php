<?php
// TEMPORARY DEBUG — delete this file after use
include 'includes/db.php';

echo "<pre style='font-family:monospace;font-size:14px;padding:20px;'>";

// 1. Connection
echo "✅ DB Connected: " . $conn->host_info . "\n\n";

// 2. Does articles table exist?
$r = $conn->query("SHOW TABLES LIKE 'articles'");
if (!$r || $r->num_rows === 0) {
    echo "❌ articles table does NOT exist!\n";
    die("</pre>");
}
echo "✅ articles table exists\n";

// 3. Show columns
echo "\n--- Columns in articles ---\n";
$cols = $conn->query("SHOW COLUMNS FROM articles");
while ($c = $cols->fetch_assoc()) {
    echo "  " . $c['Field'] . " (" . $c['Type'] . ")" . ($c['Field'] === 'views' ? " ← views column" : "") . "\n";
}

// 4. Row count
$cnt = $conn->query("SELECT COUNT(*) FROM articles")->fetch_row()[0];
echo "\n--- Row count: $cnt ---\n";
if ($cnt == 0) {
    echo "⚠️  Table is empty — no articles have been saved\n";
}

// 5. Try the exact query articles.php uses
echo "\n--- Testing main query ---\n";
$test = $conn->query("SELECT *, summary AS title, published_date AS created_at, COALESCE(views,0) AS views FROM articles WHERE 1=1 ORDER BY published_date DESC");
if (!$test) {
    echo "❌ Query FAILED: " . $conn->error . "\n";
} else {
    echo "✅ Query OK — returned " . $test->num_rows . " rows\n";
    if ($test->num_rows > 0) {
        $row = $test->fetch_assoc();
        echo "   First row: id=" . $row['id'] . ", title=" . substr($row['title'], 0, 50) . "\n";
        echo "   image=" . $row['image'] . "\n";
    }
}

// 6. Test views migration
echo "\n--- Testing views column migration ---\n";
$vc = $conn->query("SHOW COLUMNS FROM `articles` LIKE 'views'");
if (!$vc || $vc->num_rows === 0) {
    echo "⚠️  views column missing — attempting to add...\n";
    $alt = $conn->query("ALTER TABLE `articles` ADD COLUMN `views` INT UNSIGNED NOT NULL DEFAULT 0");
    echo $alt ? "✅ views column added successfully\n" : "❌ ALTER failed: " . $conn->error . "\n";
} else {
    echo "✅ views column exists\n";
}

echo "\n--- Done ---\n";
echo "</pre>";
