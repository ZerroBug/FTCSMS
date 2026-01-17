<?php
// =====================
// Database connection
// =====================

$host     = 'localhost';                      // MySQL host (usually localhost)
$dbname   = 'fasttra2_fasttrack_student_db'; // Your live database name
$username = 'fasttra2_fasttra2';             // Your database username
$password = 'fasttrack@Admin123';            // Your database password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch associative arrays by default
            PDO::ATTR_EMULATE_PREPARES => false            // Use real prepared statements
        ]
    );
    
    // Optional: Uncomment to test connection
    // echo "Database connected successfully!";
    
} catch (PDOException $e) {
    // Show error only temporarily, remove in production
    die("Database Connection Failed: " . $e->getMessage());
}
?>