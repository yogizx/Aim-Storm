<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maatka');

echo "<h1>MAATKA Database Setup</h1>";

// 1. Connect to MySQL server
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<p style='color: red;'>Connection to MySQL failed: " . $conn->connect_error . "</p><p>Please check your DB_HOST, DB_USER, and DB_PASS in <b>db_config.php</b></p>");
}

echo "<p style='color: green;'>Successfully connected to MySQL server.</p>";

// 2. Create database if not exists
$db_name = DB_NAME;
if ($conn->query("CREATE DATABASE IF NOT EXISTS $db_name")) {
    echo "<p style='color: green;'>Database '$db_name' is ready.</p>";
} else {
    die("<p style='color: red;'>Error creating database: " . $conn->error . "</p>");
}

// 3. Select the database
$conn->select_db($db_name);

// 4. Read the .sql file
$sqlFile = 'database.sql';
if (!file_exists($sqlFile)) {
    die("<p style='color: red;'>Error: $sqlFile not found!</p>");
}

$sql = file_get_contents($sqlFile);

// 5. Execute multiple queries
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "<div style='background: #eaffea; border: 1px solid #00aa00; padding: 20px; border-radius: 10px; margin-top: 20px;'>";
    echo "<h2 style='color: #00aa00;'>All Tables Created Successfully!</h2>";
    echo "<p>Your database is now fully connected and configured.</p>";
    echo "<p><a href='admin_panel.php' style='background: #FFD700; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Admin Panel</a></p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>Error importing tables: " . $conn->error . "</p>";
}

$conn->close();
?>
