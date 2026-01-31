<div class="space-y-4 pb-24 pt-4"> <?php
    // Logique pour récupérer l'image de la première musique likée
    $playlistCover = '/assets/images/default-cover.svg';
    if (!empty($likes) && !empty($likes[0]['cover_path'])) {
        $playlistCover = '/assets/images/covers/' . $likes[0]['cover_path'];
    }
    ?>

    <div class="relative bg-gradient-to-b from-purple-900/40 to-bg-main p-4 md:p-6 rounded-xl border border-white/5 shadow-lg">
        <div class="flex flex-row gap-4 items-center md:items-end">
            
            <div class="shrink-0">
                <div class="w-28 h-28 md:w-48 md:h-48 rounded-lg overflow-hidden shadow-xl border border-white/10 relative group">
                    <img src="<?= htmlspecialchars($playlistCover) ?>" 
                         alt="Titres Likés" 
                         class="w-full h-full object-cover">
                    <div class="absolute bottom-2 right-2 bg-primary p-2 rounded-full shadow-md">
                        <i class="fas fa-heart text-white text-sm md:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="flex-1 min-w-0 flex flex-col justify-center gap-2 md:gap-4">
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-widest text-text-sub hidden md:block">Playlist</h5>
                    <h1 class="text-2xl md:text-5xl font-black text-white tracking-tight truncate drop-shadow-lg">
                        Titres Likés
                    </h1>
                    <p class="text-text-sub text-xs md:text-sm font-medium mt-1">
                        <?= isset($likes) ? count($likes) : 0 ?> titres
                    </p>
                </div>

                <div class="flex items-center gap-3 mt-1">
                    <?php if (!empty($likes)): ?>
                        <?php 
                            $allIds = array_column($likes, 'music_id'); 
                            $jsonIds = htmlspecialchars(json_encode($allIds));
                        ?>
                        
                        <button onclick='playQueue(<?= $jsonIds ?>, 0)' 
                                class="bg-primary hover:bg-primary-dark text-white rounded-full w-10 h-10 md:px-6 md:w-auto md:h-12 font-bold flex items-center justify-center gap-2 transition-all shadow-lg hover:scale-105">
                            <i class="fas fa-play text-sm md:text-lg ml-0.5"></i>
                            <span class="hidden md:inline">LECTURE</span>
                        </button>

                        <button onclick='playQueue(<?= $jsonIds ?>, 0, true)' 
                                class="bg-white/10 hover:bg-white/20 text-white rounded-full w-10 h-10 md:px-6 md:w-auto md:h-12 font-bold flex items-center justify-center gap-2 transition-all hover:scale-105">
                            <i class="fas fa-random text-sm md:text-lg"></i>
                            <span class="hidden md:inline">ALÉATOIRE</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-bg-card/20 rounded-xl p-2 md:p-4 border border-white/5 min-h-[300px]">
        
        <?php if (empty($likes)): ?>
            <div class="flex flex-col items-center justify-center py-12 text-text-sub space-y-3">
                <i class="fas fa-heart-broken text-3xl opacity-50"></i>
                <p class="text-base font-medium">Aucun titre liké</p>
            </div>
        <?php else: ?>
            
            <div class="space-y-1">
                <?php foreach ($likes as $index => $like): ?>
                    <?php 
                        // Préparation explicite des variables pour la carte
                        // Note : on passe $like directement comme $music pour éviter les confusions
                        $music = $like; 
                        // Si ton tableau $likes a 'music_id' au lieu de 'id', on corrige ici :
                        if (!isset($music['id']) && isset($music['music_id'])) {
                            $music['id'] = $music['music_id'];
                        }
                        
                        // On inclut la carte
                        require __DIR__ . '/../components/music-card-playlist.php'; 
                    ?>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</div>