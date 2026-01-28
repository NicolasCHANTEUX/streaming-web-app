// Song options modal functionality
let currentSongId = null;

/**
 * Get CSRF token from meta tag
 */
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.content : '';
}

/**
 * Show song options modal
 */
function showSongOptions(songId, title, artist) {
    console.log('=== SHOW SONG OPTIONS ===');
    console.log('Song ID:', songId);
    console.log('Title:', title);
    console.log('Artist:', artist);
    
    currentSongId = songId;
    const modal = document.getElementById('songOptionsModal');
    const titleElement = document.getElementById('songOptionsTitle');
    const artistElement = document.getElementById('songOptionsArtist');
    
    console.log('Modal element:', modal);
    console.log('Title element:', titleElement);
    console.log('Artist element:', artistElement);
    
    if (!modal || !titleElement || !artistElement) {
        console.error('Modal elements not found!');
        return;
    }
    
    titleElement.textContent = title || 'Titre inconnu';
    artistElement.textContent = artist || 'Artiste inconnu';
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    document.body.style.overflow = 'hidden';
    console.log('Modal opened successfully');
}

/**
 * Close song options modal
 */
function closeSongOptions() {
    console.log('=== CLOSE SONG OPTIONS ===');
    const modal = document.getElementById('songOptionsModal');
    if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Reset current song ID
 */
function resetSongId() {
    console.log('=== RESET SONG ID ===');
    currentSongId = null;
}

/**
 * Edit song information
 */
function editSongInfo() {
    console.log('=== EDIT SONG INFO ===');
    console.log('Current song ID:', currentSongId);
    
    if (!currentSongId) {
        console.error('No song ID set!');
        return;
    }
    
    closeSongOptions();
    
    const url = `/api/songs/${currentSongId}`;
    console.log('Fetching song data from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success && data.song) {
                console.log('Song data:', data.song);
                document.getElementById('editSongTitle').value = data.song.title || '';
                document.getElementById('editSongArtist').value = data.song.artist || '';
                document.getElementById('editSongAlbum').value = data.song.album || '';
                
                const editModal = document.getElementById('editSongModal');
                console.log('Edit modal element:', editModal);
                if (editModal) {
                    editModal.classList.remove('hidden');
                    editModal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                    console.log('Edit modal opened');
                } else {
                    console.error('Edit modal element not found!');
                }
            } else {
                console.error('Invalid response data:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching song data:', error);
            showNotification('Erreur lors de la récupération des données', 'error');
        });
}

/**
 * Close edit song modal
 */
function closeEditSong() {
    console.log('=== CLOSE EDIT MODAL ===');
    const modal = document.getElementById('editSongModal');
    if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    resetSongId();
}

/**
 * Add to playlist from options menu
 */
function addToPlaylistFromOptions() {
    console.log('=== ADD TO PLAYLIST FROM OPTIONS ===');
    console.log('Current song ID:', currentSongId);
    
    if (!currentSongId) {
        console.error('No song ID set!');
        return;
    }
    
    closeSongOptions();
    console.log('Calling showPlaylistModal with ID:', currentSongId);
    
    if (typeof showPlaylistModal === 'function') {
        showPlaylistModal(currentSongId);
    } else {
        console.error('showPlaylistModal function not found!');
        showNotification('Fonction non disponible', 'error');
    }
}

/**
 * Show song details
 */
function showSongDetails() {
    console.log('=== SHOW SONG DETAILS ===');
    console.log('Current song ID:', currentSongId);
    
    if (!currentSongId) {
        console.error('No song ID set!');
        return;
    }
    
    const url = `/api/songs/${currentSongId}`;
    console.log('Fetching details from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success && data.song) {
                const song = data.song;
                console.log('Song details:', song);
                
                const duration = song.duration ? formatDuration(song.duration) : 'Inconnue';
                const fileSize = song.file_size ? formatFileSize(song.file_size) : 'Inconnue';
                
                const details = `📀 Titre: ${song.title || 'Inconnu'}
🎤 Artiste: ${song.artist || 'Inconnu'}
💿 Album: ${song.album || 'Inconnu'}
⏱️ Durée: ${duration}
📦 Taille: ${fileSize}
📅 Ajouté le: ${new Date(song.created_at).toLocaleDateString('fr-FR')}`;
                
                console.log('Showing alert with details');
                alert(details);
            } else {
                console.error('Invalid response:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching song details:', error);
            showNotification('Erreur lors de la récupération des détails', 'error');
        });
}

/**
 * Share song
 */
function shareSong() {
    console.log('=== SHARE SONG ===');
    console.log('Current song ID:', currentSongId);
    
    if (!currentSongId) {
        console.error('No song ID set!');
        return;
    }
    
    const url = `${window.location.origin}/song/${currentSongId}`;
    console.log('URL to share:', url);
    
    if (navigator.clipboard) {
        console.log('Using navigator.clipboard API');
        navigator.clipboard.writeText(url)
            .then(() => {
                console.log('URL copied successfully');
                showNotification('Lien copié dans le presse-papier', 'success');
                closeSongOptions();
                resetSongId();
            })
            .catch(error => {
                console.error('Error copying to clipboard:', error);
                showNotification('Erreur lors de la copie du lien', 'error');
            });
    } else {
        console.log('Using fallback copy method');
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        console.log('URL copied with fallback method');
        showNotification('Lien copié dans le presse-papier', 'success');
        closeSongOptions();
        resetSongId();
    }
}

/**
 * Delete song
 */
function deleteSong() {
    console.log('=== DELETE SONG ===');
    console.log('Current song ID:', currentSongId);
    
    if (!currentSongId) {
        console.error('No song ID set!');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer définitivement cette chanson ? Cette action est irréversible.')) {
        console.log('User cancelled deletion');
        return;
    }
    
    const url = `/api/songs/${currentSongId}`;
    const csrfToken = getCsrfToken();
    console.log('DELETE request to:', url);
    console.log('CSRF Token:', csrfToken);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-Token': csrfToken
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            console.log('Song deleted successfully');
            showNotification('Chanson supprimée avec succès', 'success');
            closeSongOptions();
            setTimeout(() => location.reload(), 500);
        } else {
            console.error('Delete failed:', data.error);
            showNotification(data.error || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting song:', error);
        showNotification('Erreur lors de la suppression', 'error');
    });
}

/**
 * Format duration in seconds to MM:SS
 */
function formatDuration(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

/**
 * Format file size in bytes to human-readable format
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    console.log('=== SHOW NOTIFICATION ===');
    console.log('Message:', message);
    console.log('Type:', type);
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 animate-slide-in ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    } text-white font-medium`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Handle edit song form submission
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== SONG OPTIONS JS LOADED ===');
    
    const editForm = document.getElementById('editSongForm');
    console.log('Edit form element:', editForm);
    
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('=== SUBMIT EDIT FORM ===');
            console.log('Current song ID:', currentSongId);
            
            if (!currentSongId) {
                console.error('No song ID set!');
                return;
            }
            
            const formData = {
                title: document.getElementById('editSongTitle').value,
                artist: document.getElementById('editSongArtist').value,
                album: document.getElementById('editSongAlbum').value
            };
            console.log('Form data:', formData);
            
            try {
                const url = `/api/songs/${currentSongId}`;
                const csrfToken = getCsrfToken();
                console.log('PUT request to:', url);
                console.log('CSRF Token:', csrfToken);
                
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify(formData)
                });
                
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    console.log('Song updated successfully');
                    showNotification('Informations mises à jour avec succès', 'success');
                    closeEditSong();
                    setTimeout(() => location.reload(), 500);
                } else {
                    console.error('Update failed:', data.error);
                    showNotification(data.error || 'Erreur lors de la mise à jour', 'error');
                }
            } catch (error) {
                console.error('Error updating song:', error);
                showNotification('Erreur lors de la mise à jour', 'error');
            }
        });
    }
    
    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSongOptions();
            closeEditSong();
        }
    });
    
    // Close modals on background click
    setTimeout(() => {
        const songOptionsModal = document.getElementById('songOptionsModal');
        const editSongModal = document.getElementById('editSongModal');
        
        if (songOptionsModal) {
            songOptionsModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeSongOptions();
                    resetSongId();
                }
            });
        }
        
        if (editSongModal) {
            editSongModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEditSong();
                }
            });
        }
    }, 100);
});
