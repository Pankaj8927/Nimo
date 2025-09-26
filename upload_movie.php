<?php
// Database connection
require_once "db_connect.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['movie-name'], FILTER_SANITIZE_STRING);
    $image_url = filter_var($_POST['image-url'], FILTER_SANITIZE_URL);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $movie_link = filter_var($_POST['movie-link'], FILTER_SANITIZE_URL);

    // Validate inputs
    if (empty($name) || empty($image_url) || empty($category) || empty($movie_link)) {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    try {
        // Prepare and execute the insert query
        $stmt = $conn->prepare("INSERT INTO movies (name, image_url, category, movie_link) VALUES (:name, :image_url, :category, :movie_link)");
        $stmt->execute([
            ':name' => $name,
            ':image_url' => $image_url,
            ':category' => $category,
            ':movie_link' => $movie_link
        ]);

        echo json_encode(["success" => "Movie uploaded successfully"]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to upload movie: " . $e->getMessage()]);
    }
}

$conn = null;
?>