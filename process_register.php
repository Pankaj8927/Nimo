// process_register.php
<?php
session_start();
require_once "config/db_connect.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $db = Database::getInstance();

    try {
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (:username, :email, :password)
        ");
        
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'username' => $username  // Add username to response
        ]);
    } catch(PDOException $e) {
        $message = $e->getCode() == 23000 ? 'Username or email already exists' : 'Registration failed';
        echo json_encode(['success' => false, 'message' => $message]);
    }
}
?>