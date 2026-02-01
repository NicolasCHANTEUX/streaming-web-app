<div class="pb-8">
    <div class="relative mb-8 pb-8 pt-12 px-6 rounded-xl overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-purple-600/10 to-transparent"></div>
        
        <div class="relative z-10">
            <div class="flex items-end gap-6 mb-8">
                <div class="w-28 h-28 rounded-xl bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center shadow-2xl flex-shrink-0">
                    <i class="fas fa-heart text-5xl text-white"></i>
                </div>
                
                <div class="flex-1">
                    <p class="text-sm font-semibold text-text-sub mb-2 uppercase tracking-wide">Playlist</p>
                    <h1 class="text-3xl md:text-5xl lg:text-6xl font-black mb-4 text-text-main leading-tight">Titres Likés</h1>
                    <p class="text-text-sub text-base"><span class="font-semibold text-text-main"><?= isset($songs) ? count($songs) : 0 ?></span> titre<?= (isset($songs) && count($songs) > 1) ? 's' : '' ?></p>
                </div>
            </div>
            
            <?php if (!empty($songs)): ?>
            <div class="flex items-center gap-4">
                <button onclick="playAll()" class="inline-flex items-center justify-center gap-3 w-14 h-14 md:w-auto md:h-auto md:px-8 md:py-4 rounded-full bg-primary text-white font-bold text-base hover:scale-105 hover:bg-primary-hover transition-all shadow-lg">
                    <i class="fas fa-play text-lg md:text-sm"></i>
                    <span class="hidden md:inline">Tout lire</span>
                </button>
                
                <button onclick="shuffleAll()" class="inline-flex items-center justify-center gap-3 w-14 h-14 md:w-auto md:h-auto md:px-6 md:py-4 rounded-full border-2 border-text-main/20 text-text-main font-semibold hover:border-text-main/40 hover:bg-bg-card transition-all">
                    <i class="fas fa-random text-lg md:text-base"></i>
                    <span class="hidden md:inline">Aléatoire</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($songs)): ?>
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
    <?php else: ?>
        <div class="">
            <div class="mb-4 flex items-center justify-between text-text-sub text-sm font-semibold uppercase tracking-wide px-2 md:px-4">
                <div class="flex items-center gap-6">
                    <span class="w-8 text-center">#</span>
                    <span>Titre</span>
                </div>
                <div class="flex items-center gap-12">
                    <span><i class="far fa-clock mr-1"></i> Actions</span>
                </div>
            </div>

            <div class="flex flex-col"> 
                <?php foreach ($songs as $index => $song): ?>
                    <?php 
                        $music = $song;
                        require __DIR__ . '/../components/music-card-playlist.php'; 
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Play all songs in order
function playAll() {
    const songElements = document.querySelectorAll('.music-row[data-song-id]');
    if (songElements.length > 0) {
        const songIds = Array.from(songElements).map(el => parseInt(el.dataset.songId));
        
        if(typeof playQueue === 'function') {
            playQueue(songIds, 0);
        }
    }
}

// Shuffle and play
function shuffleAll() {
    const songElements = document.querySelectorAll('.music-row[data-song-id]');
    if (songElements.length > 0) {
        const songIds = Array.from(songElements).map(el => parseInt(el.dataset.songId));

        // Shuffle
        for (let i = songIds.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [songIds[i], songIds[j]] = [songIds[j], songIds[i]];
        }
        
        if(typeof playQueue === 'function') {
            playQueue(songIds, 0);
        }
    }
}
</script>