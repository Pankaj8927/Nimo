<?php
// Database connection
require_once "db_connect.php";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "socialauth_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->query("SELECT * FROM movies");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($movies);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch movies: " . $e->getMessage()]);
}

$conn = null;
?>