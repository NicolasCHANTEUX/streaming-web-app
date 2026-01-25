// Main Application JavaScript

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDuration(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

// Scan Library Function
async function scanLibrary() {
    if (!confirm('This will scan the music directory and add all found files to the database. Continue?')) {
        return;
    }

    try {
        const response = await fetch('/api/music/scan', {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            alert(`Successfully imported ${data.imported} songs!`);
            location.reload();
        } else {
            alert('Error scanning library');
        }
    } catch (error) {
        alert('An error occurred while scanning');
        console.error(error);
    }
}

// Add to Playlist
async function addToPlaylist(songId) {
    // Get all playlists
    try {
        const response = await fetch('/api/playlists');
        const data = await response.json();

        if (!data.success || data.playlists.length === 0) {
            alert('Please create a playlist first');
            return;
        }

        // Show selection dialog
        const playlistId = prompt('Enter playlist ID:\n' + 
            data.playlists.map(p => `${p.id}: ${p.name}`).join('\n'));

        if (!playlistId) return;

        // Add song to playlist
        const addResponse = await fetch(`/api/playlists/${playlistId}/add-song`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `song_id=${songId}`
        });

        const addData = await addResponse.json();

        if (addData.success) {
            alert('Song added to playlist!');
        } else {
            alert('Error adding song to playlist');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}

// Show Options Menu
function showOptions(songId) {
    const options = [
        'Add to Playlist',
        'View Details',
        'Delete'
    ];

    const choice = prompt('Choose an option:\n' + 
        options.map((opt, i) => `${i + 1}: ${opt}`).join('\n'));

    switch(choice) {
        case '1':
            addToPlaylist(songId);
            break;
        case '2':
            window.location.href = `/music/${songId}`;
            break;
        case '3':
            deleteSong(songId);
            break;
    }
}

// Delete Song
async function deleteSong(songId) {
    if (!confirm('Are you sure you want to delete this song? This will also delete the file.')) {
        return;
    }

    try {
        const response = await fetch(`/api/music/${songId}/delete`, {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            alert('Song deleted');
            location.reload();
        } else {
            alert('Error deleting song');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}

// Service Worker Registration (for PWA)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered:', registration);
            })
            .catch(error => {
                console.log('SW registration failed:', error);
            });
    });
}

// Check online status
window.addEventListener('online', () => {
    console.log('Back online');
});

window.addEventListener('offline', () => {
    console.log('Connection lost');
    alert('You are offline. Some features may not work.');
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Space bar for play/pause
    if (e.code === 'Space' && e.target === document.body) {
        e.preventDefault();
        if (Player.isPlaying) {
            Player.pause();
        } else {
            Player.resume();
        }
    }

    // Arrow keys for previous/next
    if (e.ctrlKey || e.metaKey) {
        if (e.code === 'ArrowLeft') {
            e.preventDefault();
            Player.previous();
        } else if (e.code === 'ArrowRight') {
            e.preventDefault();
            Player.next();
        }
    }
});

// Auto-hide notifications
document.addEventListener('DOMContentLoaded', () => {
    const messages = document.querySelectorAll('.success-message, .error-message, .info-message');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 300);
        }, 5000);
    });
});

// Responsive image loading
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

console.log('Music Streaming App initialized');
