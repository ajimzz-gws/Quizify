<?php
session_start();
require_once 'database.php';

class DB {
    public $pdo;
    
    public function __construct() {
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER, 
            DB_PASS
        );
    }
    
    public function get($table, $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }
}

class Auth {
    public function requireLogin() {
        if (empty($_SESSION['user_id'])) {
            header("Location: login.html");
            exit;
        }
    }
    
    public function requireRole($role) {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== $role) {
            header("Location: access_denied.php");
            exit;
        }
    }

    public function preventBackButton() {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

}

$db = new DB();
$auth = new Auth();

// if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember'])) {
//     $stmt = $db->pdo->prepare(
//         "SELECT * FROM users WHERE remember_token = ? AND remember_expires > NOW()"
//     );
//     $stmt->execute([$_COOKIE['remember']]);
//     $user = $stmt->fetch();
    
//     if ($user) {
//         $_SESSION['user_id'] = $user['id'];
//         $_SESSION['user_email'] = $user['email'];
//         $_SESSION['user_role'] = $user['role'];
//         $_SESSION['user_name'] = $user['full_name'];
//     }
// }