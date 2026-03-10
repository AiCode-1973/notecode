<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'register') {
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Usuário e senha são obrigatórios.";
            header("Location: register.php");
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            $_SESSION['success'] = "Registro concluído! Faça login.";
            header("Location: login.php");
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error'] = "Nome de usuário já existe.";
            } else {
                $_SESSION['error'] = "Ocorreu um erro inesperado.";
            }
            header("Location: register.php");
        }
    } elseif ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
        } else {
            $_SESSION['error'] = "Usuário ou senha inválidos.";
            header("Location: login.php");
        }
    }
} elseif (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
}
?>
