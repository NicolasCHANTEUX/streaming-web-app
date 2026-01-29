// Modal System
window.openModal = function(title, bodyContent, buttons = []) {
    const overlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalFooter = document.getElementById('modalFooter');
    
    console.log('openModal called with title:', title);
    console.log('Current modalBody content before:', modalBody.innerHTML.substring(0, 200));
    
    modalTitle.textContent = title;
    modalBody.innerHTML = bodyContent;
    
    console.log('New modalBody content after:', modalBody.innerHTML.substring(0, 200));
    
    // Build footer buttons
    modalFooter.innerHTML = '';
    buttons.forEach(btn => {
        const button = document.createElement('button');
        button.textContent = btn.text;
        button.className = btn.className || 'px-4 py-2 rounded-lg bg-primary text-white hover:bg-opacity-90 transition-colors';
        button.onclick = btn.onClick;
        modalFooter.appendChild(button);
    });
    
    // Add cancel button if not present
    if (buttons.length === 0 || !buttons.some(b => b.cancel)) {
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Annuler';
        cancelBtn.className = 'px-4 py-2 rounded-lg bg-bg-card text-text-sub hover:bg-opacity-80 transition-colors';
        cancelBtn.onclick = window.closeModal;
        modalFooter.appendChild(cancelBtn);
    }
    
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    document.body.style.overflow = 'hidden';
    
    // Debug: check for input after a short delay
    setTimeout(() => {
        const input = document.getElementById('playlistName');
        console.log('Input element after modal open:', input);
        console.log('Input HTML:', input ? input.outerHTML : 'NOT FOUND');
    }, 100);
};

window.closeModal = function() {
    const overlay = document.getElementById('modalOverlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
    document.body.style.overflow = '';
};

// Playlist Selection Modal
window.showPlaylistModal = async function(songId) {
    try {
        const response = await fetch('/api/playlists');
        const data = await response.json();
        const playlists = data.playlists || [];
        
        if (playlists.length === 0) {
            openModal(
                'Aucune playlist',
                '<p class="text-text-sub">Vous n\'avez pas encore créé de playlist. Créez-en une d\'abord !</p>',
                [{
                    text: 'Créer une playlist',
                    onClick: () => {
                        closeModal();
                        showCreatePlaylistModal();
                    }
                }]
            );
            return;
        }
        
        let bodyContent = '<div class="space-y-3">';
        playlists.forEach(playlist => {
            const cover = playlist.cover_path || '/assets/images/default-playlist.png';
            bodyContent += `
                <div class="playlist-option group flex items-center gap-4 p-4 rounded-xl bg-bg-card border-2 border-border-main hover:border-primary hover:bg-bg-hover cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md" 
                     data-playlist-id="${playlist.id}"
                     onclick="selectPlaylist(this)">
                    <div class="relative flex-shrink-0">
                        <img src="${cover}" alt="${playlist.name}" class="w-14 h-14 rounded-lg object-cover shadow-sm">
                        <div class="absolute inset-0 bg-primary/0 group-hover:bg-primary/10 rounded-lg transition-all duration-200"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-text-main group-hover:text-primary transition-colors truncate">${playlist.name}</div>
                        <div class="text-sm text-text-sub mt-0.5 flex items-center gap-1.5">
                            <i class="fas fa-music text-xs"></i>
                            <span>${playlist.song_count || 0} titre${(playlist.song_count || 0) > 1 ? 's' : ''}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-bg-surface border-2 border-border-main group-hover:border-primary flex items-center justify-center transition-all duration-200">
                        <i class="fas fa-check text-primary opacity-0 check-icon transition-opacity duration-200 text-sm"></i>
                    </div>
                </div>
            `;
        });
        bodyContent += '</div>';
        
        let selectedPlaylistId = null;
        
        window.selectPlaylist = function(element) {
            // Remove selection from all
            document.querySelectorAll('.playlist-option').forEach(opt => {
                opt.classList.remove('border-primary', 'bg-primary/5');
                opt.querySelector('.check-icon').classList.add('opacity-0');
                const circle = opt.querySelector('.w-8');
                if (circle) {
                    circle.classList.remove('bg-primary', 'border-primary');
                    circle.classList.add('bg-bg-surface', 'border-border-main');
                }
            });
            
            // Select this one with enhanced visual feedback
            element.classList.add('border-primary', 'bg-primary/5');
            const checkIcon = element.querySelector('.check-icon');
            const circle = element.querySelector('.w-8');
            
            checkIcon.classList.remove('opacity-0');
            checkIcon.classList.add('opacity-100');
            
            if (circle) {
                circle.classList.remove('bg-bg-surface', 'border-border-main');
                circle.classList.add('bg-primary', 'border-primary');
                checkIcon.classList.add('text-white');
            }
            
            selectedPlaylistId = element.dataset.playlistId;
        };
        
        openModal(
            'Ajouter à une playlist',
            bodyContent,
            [
                {
                    text: 'Annuler',
                    className: 'px-4 py-2 rounded-lg bg-bg-card text-text-sub hover:bg-opacity-80 transition-colors',
                    onClick: closeModal,
                    cancel: true
                },
                {
                    text: 'Ajouter',
                    className: 'px-4 py-2 rounded-lg bg-primary text-white hover:bg-opacity-90 transition-colors',
                    onClick: async () => {
                        if (!selectedPlaylistId) {
                            alert('Veuillez sélectionner une playlist');
                            return;
                        }
                        
                        try {
                            const res = await fetch(`/api/playlists/${selectedPlaylistId}/add-song`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-CSRF-Token': getCsrfToken()
                                },
                                body: `song_id=${songId}`
                            });
                            
                            const data = await res.json();
                            
                            if (data.success) {
                                closeModal();
                                showNotification('Titre ajouté à la playlist !', 'success');
                            } else {
                                showNotification('Erreur lors de l\'ajout', 'error');
                            }
                        } catch (error) {
                            console.error(error);
                            showNotification('Erreur réseau', 'error');
                        }
                    }
                }
            ]
        );
    } catch (error) {
        console.error('Error loading playlists:', error);
        showNotification('Erreur lors du chargement des playlists', 'error');
    }
};

// Create Playlist Modal
window.showCreatePlaylistModal = function() {
    const bodyContent = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2 text-text-main">Nom de la playlist</label>
                <input type="text" id="modalPlaylistName" 
                       class="w-full px-4 py-2 bg-bg-card border border-border-main rounded-lg focus:outline-none focus:border-primary text-text-main"
                       placeholder="Ma super playlist">
            </div>
        </div>
    `;
    
    openModal(
        'Créer une playlist',
        bodyContent,
        [
            {
                text: 'Annuler',
                className: 'px-4 py-2 rounded-lg bg-bg-card text-text-sub hover:bg-opacity-80 transition-colors',
                onClick: closeModal,
                cancel: true
            },
            {
                text: 'Créer',
                className: 'px-4 py-2 rounded-lg bg-primary text-white hover:bg-opacity-90 transition-colors',
                onClick: async () => {
                    // Search within modal only
                    const modal = document.getElementById('modalBody');
                    const nameInput = modal.querySelector('#modalPlaylistName');
                    const name = nameInput ? nameInput.value.trim() : '';
                    
                    console.log('=== CREATE PLAYLIST DEBUG ===');
                    console.log('Modal element:', modal);
                    console.log('Input element:', nameInput);
                    console.log('Name value:', name);
                    console.log('Name length:', name.length);
                    
                    if (!name) {
                        console.error('Validation failed: empty name');
                        showNotification('Veuillez entrer un nom', 'error');
                        return;
                    }
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    console.log('CSRF token meta:', csrfToken);
                    console.log('CSRF token value:', csrfToken ? csrfToken.content : 'NULL');
                    
                    const requestBody = `name=${encodeURIComponent(name)}`;
                    console.log('Request body:', requestBody);
                    
                    const headers = { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': csrfToken ? csrfToken.content : ''
                    };
                    console.log('Request headers:', headers);
                    
                    try {
                        console.log('Sending request to /api/playlists/create');
                        const res = await fetch('/api/playlists/create', {
                            method: 'POST',
                            headers: headers,
                            body: requestBody
                        });
                        
                        console.log('Response status:', res.status);
                        console.log('Response headers:', [...res.headers.entries()]);
                        
                        const responseText = await res.text();
                        console.log('Response text:', responseText);
                        
                        let data;
                        try {
                            data = JSON.parse(responseText);
                            console.log('Parsed JSON:', data);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            console.error('Raw response:', responseText);
                            showNotification('Erreur: réponse invalide du serveur', 'error');
                            return;
                        }
                        
                        if (res.ok && data.success) {
                            closeModal();
                            showNotification('Playlist créée !', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            console.error('Server error:', data);
                            showNotification(data.error || 'Erreur lors de la création', 'error');
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        console.error('Error stack:', error.stack);
                        showNotification('Erreur réseau', 'error');
                    }
                }
            }
        ]
    );
    
    // Focus input
    setTimeout(() => document.getElementById('playlistName').focus(), 100);
};

// Notification System
window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-5 px-5 py-3 rounded-lg shadow-lg z-50 animate-slide-in-right ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-bg-surface text-text-main border border-border-main'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};
