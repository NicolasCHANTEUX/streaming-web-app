<div class="pb-8">
    <!-- Header stylisé -->
    <div class="relative mb-8 pb-8 pt-12 px-6 rounded-xl overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-green-500/20 via-emerald-600/10 to-transparent"></div>
        
        <div class="relative z-10">
            <div class="flex items-end gap-6 mb-4">
                <div class="w-20 h-20 sm:w-28 sm:h-28 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-2xl flex-shrink-0">
                    <i class="fas fa-home text-3xl sm:text-5xl text-white"></i>
                </div>
                
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-semibold text-text-sub mb-2 uppercase tracking-wide">Accueil</p>
                    <h1 class="text-3xl sm:text-5xl md:text-6xl font-black mb-2 sm:mb-4 text-text-main leading-tight">Votre Musique</h1>
                    <p class="text-text-sub text-sm sm:text-base">
                        <span class="hidden sm:inline">Écoutez votre collection ou découvrez de nouveaux sons</span>
                        <span class="sm:hidden">Votre collection musicale</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($recentSongs)): ?>
        <section class="mb-10">
            <div class="flex justify-between items-center mb-5 px-2 sm:px-0">
                <h2 class="text-xl sm:text-2xl font-bold">Ajoutés récemment</h2>
                <a href="/music" class="text-text-sub text-sm hover:text-primary transition-colors">Voir tout</a>
            </div>
            
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
                    <?php foreach ($recentSongs as $index => $song): ?>
                        <?php 
                            $music = $song;
                            require __DIR__ . '/../components/music-card-playlist.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($playlists)): ?>
        <section>
            <div class="flex justify-between items-center mb-5 px-2 sm:px-0">
                <h2 class="text-xl sm:text-2xl font-bold">Vos Playlists</h2>
                <a href="/playlists" class="text-text-sub text-sm hover:text-primary transition-colors">Voir tout</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($playlists, 0, 6) as $playlist): ?>
                    <?php component('playlist-card', ['playlist' => $playlist]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($recentSongs) && empty($playlists)): ?>
        <div class="text-center py-20 px-6">
            <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-green-500/10 to-emerald-600/10 flex items-center justify-center">
                <i class="fas fa-music text-6xl text-text-sub"></i>
            </div>
            <h2 class="text-2xl font-bold text-text-main mb-3">Votre bibliothèque est vide</h2>
            <p class="text-text-sub text-base max-w-md mx-auto mb-8">Commencez par ajouter de la musique depuis YouTube ou vos propres fichiers</p>
            <a href="/add" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-full hover:scale-105 transition-transform shadow-lg">
                <i class="fas fa-plus"></i>
                Ajouter de la musique
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Rendre toutes les cartes de musique cliquables pour lancer la lecture
document.addEventListener('DOMContentLoaded', function() {
    const musicRows = document.querySelectorAll('.music-row[data-song-id]');
    console.log('🏠 Home page: Found', musicRows.length, 'music rows');
});
</script>