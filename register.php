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
    <title>NoteCode - Registrar</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1>NoteCode</h1>
            <p>Comece a organizar suas idéias hoje!</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="auth.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="username">Escolha um Usuário</label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="Ex: joao_silva" required>
                </div>
                <div class="form-group">
                    <label for="password">Sua Senha</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary">Criar Conta</button>
            </form>

            <div class="auth-footer">
                Já tem uma conta? <a href="login.php">Faça login</a>
            </div>
        </div>
    </div>
</body>
</html>
