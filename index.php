<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NoteCode</title>
    <!-- Remix Icon for modern iconography -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-shell">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="assets/img/logo.png" alt="NoteCode Logo" style="max-width: 100%; height: auto; display: block;">
            </div>

            <nav class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" id="btn-all-notes" onclick="setFilter('all')">
                        <i class="ri-sticky-note-line"></i> Todas as Notas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="btn-favorites" onclick="setFilter('favorites')">
                        <i class="ri-star-line"></i> Favoritos
                    </a>
                </li>
            </nav>

            <div class="sidebar-footer">
                <div style="padding: 0 16px 16px; margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <div style="width: 24px; height: 24px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.7rem;">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <?php echo $_SESSION['username']; ?>
                </div>
                <button class="logout-btn" onclick="window.location.href='auth.php?logout=1'">
                    <i class="ri-logout-box-line"></i> Sair
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header-actions">
                <div class="page-title">
                    <h2 id="current-view-title">Todas as Notas</h2>
                </div>
                <button class="btn-new-note" onclick="openModal()">
                    <i class="ri-add-line"></i> Nova Nota
                </button>
            </header>

            <div id="notes-grid" class="note-grid">
                <!-- Notes will be loaded here via JS -->
                <div class="loading-state" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="ri-loader-4-line ri-spin" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                    Carregando suas notas...
                </div>
            </div>
        </main>
    </div>

    <!-- Modal for Create/Edit -->
    <div id="note-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">
                <i class="ri-close-line"></i>
            </button>
            <h3 style="margin-bottom: 24px; font-family: 'Outfit'; font-size: 1.5rem;" id="modal-title">Nova Nota</h3>
            
            <form id="note-form">
                <input type="hidden" id="note-id">
                <div class="form-group">
                    <label for="title">Título</label>
                    <input type="text" id="title" class="form-input" placeholder="Título da nota" required>
                </div>
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea id="description" class="form-input" placeholder="Escreva aqui sua nota..." style="min-height: 200px; resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px;">
                    <button type="button" id="delete-note-btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 10px 16px; border-radius: 8px; font-weight: 500; cursor: pointer; display: none;" onclick="deleteNote()">
                        <i class="ri-delete-bin-line"></i> Excluir
                    </button>
                    <button type="submit" class="btn-primary" style="width: auto; min-width: 140px;">Salvar Nota</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
