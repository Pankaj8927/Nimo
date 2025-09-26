<?php
session_start();
require_once "config/db_connect.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $db = Database::getInstance();

    try {
        $stmt = $db->prepare("
            SELECT id, email, password, status, two_factor_enabled 
            FROM users 
            WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                echo json_encode(['success' => false, 'message' => 'Account not activated']);
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
                ->execute([$user['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'two_factor' => $user['two_factor_enabled']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}
?>