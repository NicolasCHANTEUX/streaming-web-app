<div class="pb-8">
    <!-- Header avec gradient et bannière -->
    <div class="relative mb-8 pb-8 pt-12 px-6 rounded-xl overflow-hidden">
        <!-- Gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-purple-600/10 to-transparent"></div>
        
        <!-- Content -->
        <div class="relative z-10">
            <div class="flex items-end gap-6 mb-8">
                <!-- Icon/Cover -->
                <div class="w-28 h-28 rounded-xl bg-gradient-to-br from-primary to-pink-600 flex items-center justify-center shadow-2xl flex-shrink-0">
                    <i class="fas fa-list-music text-5xl text-white"></i>
                </div>
                
                <!-- Info -->
                <div class="flex-1">
                    <p class="text-sm font-semibold text-text-sub mb-2 uppercase tracking-wide">Playlist</p>
                    <h1 class="text-5xl md:text-6xl font-black mb-4 text-text-main leading-tight"><?= e($playlist->name) ?></h1>
                    <div class="text-text-sub text-base">
                        <?php if (!empty($playlist->description)): ?>
                            <p class="mb-2"><?= e($playlist->description) ?></p>
                        <?php endif; ?>
                        <p><span class="font-semibold text-text-main"><?= count($songs) ?></span> titre<?= count($songs) > 1 ? 's' : '' ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Actions principales -->
            <div class="flex items-center gap-4 flex-wrap">
                <?php if (!empty($songs)): ?>
                <button onclick="playAll()" class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-primary text-white font-bold text-base hover:scale-105 hover:bg-primary-hover transition-all shadow-lg">
                    <i class="fas fa-play text-sm"></i>
                    Tout lire
                </button>
                <button onclick="shuffleAll()" class="inline-flex items-center gap-3 px-6 py-4 rounded-full border-2 border-text-main/20 text-text-main font-semibold hover:border-text-main/40 hover:bg-bg-card transition-all">
                    <i class="fas fa-shuffle"></i>
                    Aléatoire
                </button>
                <?php endif; ?>
                <button onclick="showAddSongsModal()" class="inline-flex items-center gap-3 px-6 py-4 rounded-full border-2 border-text-main/20 text-text-main font-semibold hover:border-text-main/40 hover:bg-bg-card transition-all">
                    <i class="fas fa-plus"></i>
                    Ajouter
                </button>
                <button onclick="deletePlaylist(<?= $playlist->id ?>)" class="inline-flex items-center gap-3 px-6 py-4 rounded-full border-2 border-red-500/50 text-red-500 font-semibold hover:border-red-500 hover:bg-red-500/10 transition-all">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($songs)): ?>
        <!-- État vide avec design émotionnel -->
        <div class="text-center py-20 px-6">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-primary/10 to-pink-600/10 flex items-center justify-center">
                <i class="fas fa-music text-6xl text-text-sub"></i>
            </div>
            <h2 class="text-2xl font-bold text-text-main mb-3">Cette playlist est vide</h2>
            <p class="text-text-sub text-base max-w-md mx-auto mb-8">Ajoutez des morceaux pour commencer à construire votre playlist</p>
            <button onclick="showAddSongsModal()" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-full hover:scale-105 transition-transform shadow-lg">
                <i class="fas fa-plus"></i>
                Ajouter des morceaux
            </button>
        </div>
    <?php elseif (count($songs) <= 3): ?>
        <!-- Playlist courte avec suggestion -->
        <div class="px-4">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-text-main mb-3 flex items-center gap-2">
                    <i class="fas fa-music text-primary"></i>
                    Morceaux
                </h2>
                <div class="space-y-0.5">
                    <?php foreach ($songs as $index => $song): ?>
                        <?php component('music-card-playlist', ['track' => $song, 'index' => $index + 1, 'showIndex' => true, 'playlistId' => $playlist->id]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Message de suggestion -->
            <div class="mt-12 p-8 rounded-xl bg-gradient-to-br from-bg-card to-bg-surface border border-border-main">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-lightbulb text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-text-main mb-2">Enrichissez votre playlist</h3>
                        <p class="text-text-sub mb-4">Explorez votre bibliothèque et ajoutez plus de titres pour créer la playlist parfaite !</p>
                        <button onclick="showAddSongsModal()" class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all">
                            Ajouter des morceaux
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Liste complète avec séparateurs -->
        <div class="px-4">
            <div class="mb-4 flex items-center justify-between text-text-sub text-sm font-semibold uppercase tracking-wide px-4">
                <div class="flex items-center gap-6">
                    <span class="w-8 text-center">#</span>
                    <span>Titre</span>
                </div>
                <div class="flex items-center gap-12">
                    <span><i class="far fa-clock mr-1"></i> Durée</span>
                </div>
            </div>
            <div class="space-y-0.5">
                <?php foreach ($songs as $index => $song): ?>
                    <?php component('music-card-playlist', ['track' => $song, 'index' => $index + 1, 'showIndex' => true, 'playlistId' => $playlist->id]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const playlistId = <?= e($playlist->id) ?>;

// Play all songs in order
function playAll() {
    const songElements = document.querySelectorAll('[data-song-id]');
    if (songElements.length > 0) {
        const songIds = Array.from(songElements).map(el => parseInt(el.dataset.songId));
        Player.playQueue(songIds, 0);
    }
}

// Shuffle and play
function shuffleAll() {
    const songElements = Array.from(document.querySelectorAll('div.group[data-song-id]'));
    if (songElements.length > 0) {
        // Shuffle array
        for (let i = songElements.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [songElements[i], songElements[j]] = [songElements[j], songElements[i]];
        }
        const songIds = songElements.map(el => parseInt(el.dataset.songId));
        Player.playQueue(songIds, 0);
    }
}

// Download all songs
async function downloadAll() {
    const songElements = document.querySelectorAll('[data-song-id]');
    let count = 0;
    for (const element of songElements) {
        const songId = parseInt(element.dataset.songId);
        await addToDownloadQueue(songId);
        count++;
    }
    showNotification(`${count} titre${count > 1 ? 's' : ''} ajouté${count > 1 ? 's' : ''} à la file de téléchargement`);
}

async function removeSongFromPlaylist(songId) {
    if (!confirm('Retirer ce morceau de la playlist ?')) return;
    
    try {
        const response = await fetch(`/api/playlists/${playlistId}/remove-song`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'song_id=' + songId
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de la suppression');
        }
    } catch (error) {
        alert('Une erreur est survenue');
        console.error(error);
    }
}

async function deletePlaylist(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette playlist ? Cette action est irréversible.')) return;
    
    try {
        const response = await fetch(`/api/playlists/${id}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '/playlists';
        } else {
            alert('Erreur lors de la suppression de la playlist');
        }
    } catch (error) {
        alert('Une erreur est survenue');
        console.error(error);
    }
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function showAddSongsModal() {
    // Cette fonction sera implémentée pour afficher un modal avec toutes les chansons disponibles
    alert('Fonctionnalité "Ajouter des morceaux" à venir !');
}
</script>
