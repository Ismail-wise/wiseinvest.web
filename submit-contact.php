<?php
include 'includes/db.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Get and Clean Data
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $package = clean($_POST['package']);
    $message = clean($_POST['message']);

    // 2. Prepare SQL Statement (Prevents SQL Injection)
    $stmt = $conn->prepare("INSERT INTO contacts (name, email, package_interest, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $package, $message);

    // 3. Execute and Check
    if ($stmt->execute()) {
    // Redirect to the new Success Page
    header("Location: success.php");
    exit();
} else {
        // Error
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access this file directly
    header("Location: contact.php");
    exit();
}
?>