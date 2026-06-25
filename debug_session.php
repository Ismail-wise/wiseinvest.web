<?php
// debug_session.php

// Start the session so we can read its data
session_start();

// Set the content type to plain text for easy reading
header('Content-Type: text/plain');

echo "--- DEBUGGING SESSION DATA ---\n\n";

echo "This file shows the data your server has stored for your current login session.\n";
echo "--------------------------------------------------------------------------\n\n";

// Check if the session is empty
if (empty($_SESSION)) {
    echo "RESULT: Your session is currently EMPTY.\n";
    echo "This means you are either not logged in, or the session failed to start.\n";
    echo "Please log in first, and then visit this page again without logging out.\n";
} else {
    echo "RESULT: Here is all the data stored in your session:\n\n";
    // Print the entire $_SESSION array in a readable format
    print_r($_SESSION);
}

echo "\n\n--------------------------------------------------------------------------\n";
echo "NEXT STEP:\n";
echo "1. Look for the [role] key in the output above.\n";
echo "2. For the 'Admin Panel' to appear, the value MUST be exactly: [role] => 'admin'\n";
echo "3. If it says something else (like 'Admin', 'user', or is missing), the problem is the value in your database 'users' table.\n";

?>
