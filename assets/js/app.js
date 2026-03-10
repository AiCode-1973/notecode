let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', () => {
    loadNotes();

    // Event listener for form submission
    const noteForm = document.getElementById('note-form');
    noteForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        saveNote();
    });
});

async function loadNotes() {
    const grid = document.getElementById('notes-grid');
    grid.innerHTML = '<div class="loading-state" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);"><i class="ri-loader-4-line ri-spin" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>Carregando suas notas...</div>';
    
    try {
        const response = await fetch(`api/notes.php?action=list&filter=${currentFilter}`);
        const notes = await response.json();
        
        if (notes.length === 0) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
                <i class="ri-sticky-note-line" style="font-size: 3rem; opacity: 0.2; display: block; margin-bottom: 1rem;"></i>
                <p style="font-size: 1.1rem; font-weight: 500;">Nenhuma nota encontrada.</p>
                <p style="font-size: 0.9rem; margin-top: 8px;">Comece criando sua primeira idéia!</p>
            </div>`;
            return;
        }

        grid.innerHTML = '';
        notes.forEach(note => {
            const card = document.createElement('div');
            card.className = 'note-card';
            card.innerHTML = `
                <div onclick="openModal(${JSON.stringify(note).replace(/"/g, '&quot;')})">
                    <h3 class="note-title">${note.title}</h3>
                    <p class="note-desc">${note.description || 'Sem descrição'}</p>
                </div>
                <div class="note-footer">
                    <span class="note-date">${new Date(note.created_at).toLocaleDateString('pt-BR')}</span>
                    <button class="fav-btn ${note.is_favorite ? 'active' : ''}" onclick="toggleFavorite(${note.id}, this)">
                        <i class="${note.is_favorite ? 'ri-star-fill' : 'ri-star-line'}"></i>
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (err) {
        console.error('Error loading notes:', err);
    }
}

function setFilter(filter) {
    currentFilter = filter;
    
    // Update active UI state
    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    if (filter === 'all') {
        document.getElementById('btn-all-notes').classList.add('active');
        document.getElementById('current-view-title').textContent = 'Todas as Notas';
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
        
        // If we're on Favorites tab, reload to remove item if unfavorited
        if (currentFilter === 'favorites' && data.is_favorite == 0) {
            loadNotes();
        }
    } catch (err) {
        console.error('Error toggling favorite:', err);
    }
}

function openModal(note = null) {
    const modal = document.getElementById('note-modal');
    const form = document.getElementById('note-form');
    const modalTitle = document.getElementById('modal-title');
    const deleteBtn = document.getElementById('delete-note-btn');
    
    form.reset();
    document.getElementById('note-id').value = '';
    
    if (note) {
        modalTitle.textContent = 'Editar Nota';
        document.getElementById('note-id').value = note.id;
        document.getElementById('title').value = note.title;
        document.getElementById('description').value = note.description;
        deleteBtn.style.display = 'block';
    } else {
        modalTitle.textContent = 'Nova Nota';
        deleteBtn.style.display = 'none';
    }
    
    modal.style.display = 'flex';
    document.getElementById('title').focus();
}

function closeModal() {
    document.getElementById('note-modal').style.display = 'none';
}

// Close modal if clicking overlay
document.getElementById('note-modal').addEventListener('click', (e) => {
    if (e.target.id === 'note-modal') closeModal();
});

async function saveNote() {
    const id = document.getElementById('note-id').value;
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    
    const action = id ? 'update' : 'create';
    
    try {
        const response = await fetch(`api/notes.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, title, description })
        });
        const result = await response.json();
        
        if (result.status === 'success' || result.id) {
            closeModal();
            loadNotes();
        }
    } catch (err) {
        console.error('Error saving note:', err);
    }
}

async function deleteNote() {
    const id = document.getElementById('note-id').value;
    if (!id || !confirm('Tem certeza que deseja excluir esta nota?')) return;
    
    try {
        const response = await fetch(`api/notes.php?action=delete&id=${id}`);
        const result = await response.json();
        
        if (result.status === 'success') {
            closeModal();
            loadNotes();
        }
    } catch (err) {
        console.error('Error deleting note:', err);
    }
}
