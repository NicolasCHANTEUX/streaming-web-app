<div class="pb-8">
    <!-- Header avec gradient et bannière - VERSION COMPACTE -->
    <div class="relative mb-6 pb-5 pt-6 px-5 rounded-xl overflow-hidden">
        <!-- Gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-purple-600/10 to-transparent"></div>
        
        <!-- Content -->
        <div class="relative z-10">
            <div class="flex items-center gap-4 mb-5">
                <!-- Icon - Plus petit -->
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-xl bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center shadow-xl flex-shrink-0">
                    <i class="fas fa-heart text-3xl md:text-4xl text-white"></i>
                </div>
                
                <!-- Info - Plus compacte -->
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-4xl font-black mb-1.5 text-text-main leading-tight">Titres Likés</h1>
                    <p class="text-text-sub text-sm font-medium">
                        <span class="font-bold text-text-main"><?= count($songs) ?></span> titre<?= count($songs) > 1 ? 's' : '' ?>
                    </p>
                </div>
            </div>
            
            <?php if (!empty($songs)): ?>
            <!-- Actions principales - Hiérarchie claire -->
            <div class="flex items-center gap-3">
                <!-- Action primaire : grosse, visible -->
                <button onclick="playAll()" class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-full bg-primary text-white font-bold text-base hover:scale-105 hover:bg-primary-hover transition-all shadow-lg">
                    <i class="fas fa-play text-sm"></i>
                    <span>Tout lire</span>
                </button>
                <!-- Action secondaire : plus discrète -->
                <button onclick="shuffleAll()" class="inline-flex items-center justify-center w-12 h-12 rounded-full border-2 border-text-main/30 text-text-main hover:border-text-main hover:bg-bg-card transition-all" title="Lecture aléatoire">
                    <i class="fas fa-shuffle text-base"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($songs)): ?>
        <!-- État vide avec design émotionnel -->
        <div class="text-center py-20 px-6">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-primary/10 to-purple-600/10 flex items-center justify-center">
                <i class="far fa-heart text-6xl text-text-sub"></i>
            </div>
            <h2 class="text-2xl font-bold text-text-main mb-3">Aucun titre liké pour le moment</h2>
            <p class="text-text-sub text-base max-w-md mx-auto mb-8">Commencez à construire votre collection en likant vos musiques préférées !</p>
            <a href="/music" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-full hover:scale-105 transition-transform shadow-lg">
                <i class="fas fa-compass"></i>
                Explorer la bibliothèque
            </a>
        </div>
    <?php elseif (count($songs) <= 3): ?>
        <!-- Playlist courte avec suggestion -->
        <div class="px-4">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-text-main mb-3 flex items-center gap-2">
                    <i class="fas fa-music text-primary"></i>
                    Vos titres
                </h2>
                <div class="space-y-0.5">
                    <?php foreach ($songs as $index => $song): ?>
                        <?php component('music-card', ['track' => $song, 'index' => $index + 1, 'showIndex' => true]); ?>
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
                        <p class="text-text-sub mb-4">Explorez notre bibliothèque et ajoutez plus de titres pour créer votre collection parfaite !</p>
                        <a href="/music" class="inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all">
                            Découvrir plus de musiques
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Liste complète optimisée mobile -->
        <div class="px-2 sm:px-4">
            <!-- Header - Masqué sur mobile pour gagner de la place -->
            <div class="mb-3 hidden md:flex items-center justify-between text-text-sub text-xs font-semibold uppercase tracking-wide px-4">
                <div class="flex items-center gap-6">
                    <span class="w-10 text-center">#</span>
                    <span>Titre</span>
                </div>
                <div class="flex items-center gap-8">
                    <span><i class="far fa-clock mr-1"></i> Durée</span>
                    <span class="w-24">Actions</span>
                </div>
            </div>
            <div class="space-y-1">
                <?php foreach ($songs as $index => $song): ?>
                    <?php component('music-card', ['track' => $song, 'index' => $index + 1, 'showIndex' => true]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Play all songs in order
function playAll() {
    const songElements = document.querySelectorAll('div.group[data-song-id]');
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

// Download all liked songs
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
</script>
