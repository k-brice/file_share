<?php
/**
 * Auth Controller
 */

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct(PDO $db) {
        $this->userModel = new User($db);
    }

    public function register($name, $email, $password) {
        if ($this->userModel->findByEmail($email)) {
            throw new Exception("Email already registered.");
        }
        return $this->userModel->create($name, $email, $password);
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Invalid email or password.");
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }

    public function logout() {
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: login.php");
            exit;
        }
    }
}
