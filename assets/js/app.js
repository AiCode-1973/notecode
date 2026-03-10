let currentFilter = 'all';
let currentNoteId = null;
let currentPageId = null;
let pages = [];

document.addEventListener('DOMContentLoaded', () => {
    loadNotes();

    // Event listener for notebook form submission
    const noteForm = document.getElementById('note-form');
    noteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        saveNotebook();
    });

    // Close modals if clicking overlay
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                if (e.target.id === 'note-modal') closeModal();
                if (e.target.id === 'notebook-modal') closeNotebook();
            }
        });
    });
});

// --- Notebook Management ---

async function loadNotes() {
    const grid = document.getElementById('notes-grid');
    grid.innerHTML = '<div class="loading-state" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);"><i class="ri-loader-4-line ri-spin" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>Carregando seus cadernos...</div>';
    
    try {
        const response = await fetch(`api/notes.php?action=list&filter=${currentFilter}`);
        const notes = await response.json();
        
        if (notes.length === 0) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
                <i class="ri-book-2-line" style="font-size: 3rem; opacity: 0.2; display: block; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; font-weight: 500;">Nenhum caderno encontrado.</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Crie um caderno para começar a organizar suas páginas!</p>
            </div>`;
            return;
        }

        grid.innerHTML = '';
        notes.forEach(note => {
            const card = document.createElement('div');
            card.className = 'note-card';
            card.innerHTML = `
                <div onclick="openNotebook(${note.id}, '${note.title.replace(/'/g, "\\'")}')">
                    <h3 class="note-title">${note.title}</h3>
                    <p class="note-desc"><i class="ri-pages-line"></i> Abrir Caderno</p>
                </div>
                <div class="note-footer">
                    <span class="note-date">${new Date(note.created_at).toLocaleDateString('pt-BR')}</span>
                    <button class="fav-btn ${note.is_favorite ? 'active' : ''}" onclick="event.stopPropagation(); toggleFavorite(${note.id}, this)">
                        <i class="${note.is_favorite ? 'ri-star-fill' : 'ri-star-line'}"></i>
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (err) {
        console.error('Error loading notebooks:', err);
    }
}

function setFilter(filter) {
    currentFilter = filter;
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    if (filter === 'all') {
        document.getElementById('btn-all-notes').classList.add('active');
        document.getElementById('current-view-title').textContent = 'Todos os Cadernos';
    } else {
        document.getElementById('btn-favorites').classList.add('active');
        document.getElementById('current-view-title').textContent = 'Favoritos';
    }
    loadNotes();
}

async function toggleFavorite(id, btn) {
    try {
        const response = await fetch(`api/notes.php?action=toggle_fav&id=${id}`);
        const data = await response.json();
        btn.classList.toggle('active', data.is_favorite == 1);
        const icon = btn.querySelector('i');
        icon.className = data.is_favorite == 1 ? 'ri-star-fill' : 'ri-star-line';
        if (currentFilter === 'favorites' && data.is_favorite == 0) loadNotes();
    } catch (err) {
        console.error('Error toggling favorite:', err);
    }
}

function openModal() {
    document.getElementById('note-modal').style.display = 'flex';
    document.getElementById('title').focus();
}

function closeModal() {
    document.getElementById('note-modal').style.display = 'none';
    document.getElementById('note-form').reset();
}

async function saveNotebook() {
    const title = document.getElementById('title').value;
    try {
        const response = await fetch(`api/notes.php?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description: '' })
        });
        const result = await response.json();
        if (result.id) {
            closeModal();
            loadNotes();
        }
    } catch (err) {
        console.error('Error saving notebook:', err);
    }
}

// --- OneNote Page Management ---

async function openNotebook(id, title) {
    currentNoteId = id;
    currentPageId = null;
    document.getElementById('current-notebook-title').textContent = title;
    document.getElementById('notebook-modal').style.display = 'flex';
    
    // Reset editor
    document.getElementById('notebook-editor-placeholder').style.display = 'flex';
    document.getElementById('page-editor-form').style.display = 'none';
    
    await loadPages();
}

function closeNotebook() {
    document.getElementById('notebook-modal').style.display = 'none';
    currentNoteId = null;
    currentPageId = null;
}

async function loadPages() {
    const list = document.getElementById('pages-list');
    list.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.8rem;">Carregando páginas...</div>';
    
    try {
        const response = await fetch(`api/notes.php?action=list_pages&note_id=${currentNoteId}`);
        pages = await response.json();
        
        list.innerHTML = '';
        if (pages.length === 0) {
            list.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.8rem;">Sem páginas ainda.</div>';
            return;
        }

        pages.forEach(page => {
            const div = document.createElement('div');
            div.className = `page-item ${currentPageId == page.id ? 'active' : ''}`;
            div.innerHTML = `<span>${page.title || 'Sem título'}</span><i class="ri-arrow-right-s-line"></i>`;
            div.onclick = () => selectPage(page);
            list.appendChild(div);
        });
    } catch (err) {
        console.error('Error loading pages:', err);
    }
}

async function createNewPage() {
    try {
        const response = await fetch(`api/notes.php?action=create_page`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ note_id: currentNoteId, title: 'Nova Página' })
        });
        const result = await response.json();
        if (result.id) {
            currentPageId = result.id;
            await loadPages();
            const newPage = pages.find(p => p.id == result.id);
            if (newPage) selectPage(newPage);
        }
    } catch (err) {
        console.error('Error creating page:', err);
    }
}

function selectPage(page) {
    currentPageId = page.id;
    
    // Update active class in list
    document.querySelectorAll('.page-item').forEach(item => item.classList.remove('active'));
    document.getElementById('pages-list').querySelectorAll('.page-item').forEach(item => {
        if (item.textContent.includes(page.title)) item.classList.add('active');
    });

    document.getElementById('notebook-editor-placeholder').style.display = 'none';
    document.getElementById('page-editor-form').style.display = 'flex';
    
    document.getElementById('page-title').value = page.title;
    document.getElementById('page-content').value = page.content;
    
    document.getElementById('save-status').textContent = 'Salvo';
}

async function saveCurrentPage() {
    if (!currentPageId) return;
    
    const title = document.getElementById('page-title').value;
    const content = document.getElementById('page-content').value;
    const status = document.getElementById('save-status');
    
    status.textContent = 'Salvando...';
    
    try {
        const response = await fetch(`api/notes.php?action=update_page`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: currentPageId, title, content })
        });
        const result = await response.json();
        if (result.status === 'success') {
            status.textContent = 'Alterações salvas';
            // Update page title in sidebar without full reload
            const activePage = document.querySelector('.page-item.active span');
            if (activePage) activePage.textContent = title || 'Sem título';
            
            // Update local pages array
            const idx = pages.findIndex(p => p.id == currentPageId);
            if (idx !== -1) {
                pages[idx].title = title;
                pages[idx].content = content;
            }
        }
    } catch (err) {
        console.error('Error saving page:', err);
        status.textContent = 'Erro ao salvar';
    }
}

async function deleteCurrentPage() {
    if (!confirm('Excluir esta página?')) return;
    
    try {
        const response = await fetch(`api/notes.php?action=delete_page&id=${currentPageId}`);
        const result = await response.json();
        if (result.status === 'success') {
            currentPageId = null;
            document.getElementById('page-editor-form').style.display = 'none';
            document.getElementById('notebook-editor-placeholder').style.display = 'flex';
            await loadPages();
        }
    } catch (err) {
        console.error('Error deleting page:', err);
    }
}

async function deleteNote() {
    if (!confirm('Você realmente deseja excluir este caderno e todas as suas páginas? Esta ação é irreversível!')) return;
    
    try {
        const response = await fetch(`api/notes.php?action=delete&id=${currentNoteId}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            closeNotebook();
            loadNotes();
        }
    } catch (err) {
        console.error('Error deleting notebook:', err);
    }
}
