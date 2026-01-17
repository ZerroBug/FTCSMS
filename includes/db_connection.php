<?php
// Database connection for online host
$host = 'localhost';           // Usually localhost, but check with your host
$dbname = 'fasttra2_fasttrack_student_db'; // Replace with your live database name
$username = 'fasttra2_fasttra2';
// Replace with your database username
$password = 'fasttrack@Admin123'; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: Uncomment to check connection
    // echo "Connected successfully!";
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>