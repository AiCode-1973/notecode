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
                <img src="assets/img/logo.jpeg" alt="NoteCode Logo" style="max-height: 128px; width: 228px; display: block;">
            </div>

            <nav class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" id="btn-all-notes" onclick="setFilter('all')">
                        <i class="ri-book-shelf-line"></i> Meus Cadernos
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
                    <h2 id="current-view-title">Meus Cadernos</h2>
                </div>
                <button class="btn-new-note" onclick="openModal()">
                    <i class="ri-add-line"></i> Novo Caderno
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

    <!-- Modal for Create (Notebook/Section) -->
    <div id="note-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">
                <i class="ri-close-line"></i>
            </button>
            <h3 style="margin-bottom: 24px; font-family: 'Outfit'; font-size: 1.5rem;" id="modal-title">Novo Caderno</h3>
            
            <form id="note-form">
                <input type="hidden" id="note-id">
                <div class="form-group">
                    <label for="title">Título do Caderno</label>
                    <input type="text" id="title" class="form-input" placeholder="Ex: Faculdade, Trabalho, Diário" required>
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
                    <button type="submit" class="btn-primary" style="width: auto; min-width: 140px;">Criar Caderno</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Notebook View (OneNote style) -->
    <div id="notebook-modal" class="modal-overlay">
        <div class="modal-content modal-notebook">
            <header class="notebook-header">
                <h3 id="current-notebook-title" style="font-family: 'Outfit';">Carregando...</h3>
                <div style="display: flex; gap: 10px;">
                    <button class="btn-primary" style="width: auto; background: transparent; border: 1px solid var(--glass-border); color: #ef4444;" onclick="deleteNote()">
                        <i class="ri-delete-bin-line"></i> Excluir Caderno
                    </button>
                    <button class="modal-close" style="position: static;" onclick="closeNotebook()">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </header>

            <div class="notebook-layout">
                <!-- Sidebar: Pages List -->
                <aside class="notebook-sidebar">
                    <button class="btn-add-page" onclick="createNewPage()">
                        <i class="ri-add-line"></i> Nova Página
                    </button>
                    <div id="pages-list" class="pages-list">
                        <!-- Pages will load here -->
                    </div>
                </aside>

                <!-- Content: Editor Area -->
                <main class="notebook-editor" id="notebook-editor-placeholder">
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted);">
                        <i class="ri-article-line" style="font-size: 4rem; opacity: 0.2; margin-bottom: 1rem;"></i>
                        <p>Selecione uma página para começar</p>
                    </div>
                </main>

                <main class="notebook-editor" id="page-editor-form" style="display: none;">
                    <input type="text" id="page-title" class="page-title-input" placeholder="Título da Página" onblur="saveCurrentPage()">
                    
                    <div class="editor-toolbar">
                        <button class="toolbar-btn" onclick="execCommand('bold')" title="Negrito"><i class="ri-bold"></i></button>
                        <button class="toolbar-btn" onclick="execCommand('italic')" title="Itálico"><i class="ri-italic"></i></button>
                        <button class="toolbar-btn" onclick="execCommand('underline')" title="Sublinhado"><i class="ri-underline"></i></button>
                        <div style="width: 1px; height: 24px; background: var(--glass-border); margin: 0 4px;"></div>
                        <button class="toolbar-btn" onclick="execCommand('justifyLeft')" title="Esquerda"><i class="ri-align-left"></i></button>
                        <button class="toolbar-btn" onclick="execCommand('justifyCenter')" title="Centro"><i class="ri-align-center"></i></button>
                        <button class="toolbar-btn" onclick="execCommand('justifyRight')" title="Direita"><i class="ri-align-right"></i></button>
                        <div style="width: 1px; height: 24px; background: var(--glass-border); margin: 0 4px;"></div>
                        <button class="toolbar-btn" onclick="execCommand('insertUnorderedList')" title="Lista"><i class="ri-list-unordered"></i></button>
                        <div style="width: 1px; height: 24px; background: var(--glass-border); margin: 0 4px;"></div>
                        <input type="color" class="toolbar-select" onchange="execCommand('foreColor', this.value)" style="height: 32px; width: 40px; padding: 0; cursor: pointer;" title="Cor da Letra">
                        <select class="toolbar-select" onchange="execCommand('fontSize', this.value)">
                            <option value="3">Tamanho</option>
                            <option value="1">Pequeno</option>
                            <option value="3">Normal</option>
                            <option value="5">Grande</option>
                            <option value="7">Extra Grande</option>
                        </select>
                    </div>

                    <div id="page-content" class="page-content-area" contenteditable="true" data-placeholder="Comece a escrever aqui..." onmouseup="saveSelection()" onkeyup="saveSelection()" onblur="saveCurrentPage()"></div>
                    
                    <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--glass-border);">
                        <span id="save-status" style="font-size: 0.8rem; color: var(--text-muted);">Alterações salvas</span>
                        <button style="background: transparent; border: none; color: #ef4444; font-size: 0.9rem; cursor: pointer;" onclick="deleteCurrentPage()">
                            <i class="ri-delete-bin-7-line"></i> Excluir Página
                        </button>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
