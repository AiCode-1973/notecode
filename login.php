<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoteCode - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container split-layout">
        <div class="auth-left">
            <div class="auth-logo-large">
                <img src="assets/img/logo.jpeg" alt="NoteCode Logo">
            </div>
        </div>
        <div class="auth-right">
            <div class="auth-card">
                <p>Seja bem-vindo de volta!</p>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="username">Usuário</label>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Digite seu usuário" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-primary">Entrar</button>
                </form>

                <div class="auth-footer">
                    Não tem uma conta? <a href="register.php">Crie agora</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
