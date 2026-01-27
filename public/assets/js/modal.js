// Modal System
window.openModal = function(title, bodyContent, buttons = []) {
    const overlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalFooter = document.getElementById('modalFooter');
    
    modalTitle.textContent = title;
    modalBody.innerHTML = bodyContent;
    
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
        const playlists = await response.json();
        
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
        
        let bodyContent = '<div class="space-y-2">';
        playlists.forEach(playlist => {
            const cover = playlist.cover_path || '/assets/images/default-playlist.png';
            bodyContent += `
                <div class="playlist-option flex items-center gap-3 p-3 rounded-lg hover:bg-bg-card cursor-pointer transition-colors border-2 border-transparent" 
                     data-playlist-id="${playlist.id}"
                     onclick="selectPlaylist(this)">
                    <img src="${cover}" alt="${playlist.name}" class="w-12 h-12 rounded object-cover">
                    <div class="flex-1">
                        <div class="font-semibold">${playlist.name}</div>
                        <div class="text-xs text-text-sub">${playlist.song_count || 0} titre(s)</div>
                    </div>
                    <i class="fas fa-check text-primary opacity-0 check-icon"></i>
                </div>
            `;
        });
        bodyContent += '</div>';
        
        let selectedPlaylistId = null;
        
        window.selectPlaylist = function(element) {
            // Remove selection from all
            document.querySelectorAll('.playlist-option').forEach(opt => {
                opt.classList.remove('border-primary');
                opt.querySelector('.check-icon').classList.add('opacity-0');
            });
            
            // Select this one
            element.classList.add('border-primary');
            element.querySelector('.check-icon').classList.remove('opacity-0');
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
                            const res = await fetch(`/playlists/${selectedPlaylistId}/add/${songId}`, {
                                method: 'POST'
                            });
                            
                            if (res.ok) {
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
                <label class="block text-sm font-medium mb-2">Nom de la playlist</label>
                <input type="text" id="playlistName" 
                       class="w-full px-4 py-2 bg-bg-card border border-border-main rounded-lg focus:outline-none focus:border-primary"
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
                    const name = document.getElementById('playlistName').value.trim();
                    
                    if (!name) {
                        showNotification('Veuillez entrer un nom', 'error');
                        return;
                    }
                    
                    try {
                        const res = await fetch('/playlists/create', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `name=${encodeURIComponent(name)}`
                        });
                        
                        if (res.ok) {
                            closeModal();
                            showNotification('Playlist créée !', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification('Erreur lors de la création', 'error');
                        }
                    } catch (error) {
                        console.error(error);
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
