<div class="space-y-6 pb-24 pt-6"> <div class="relative bg-gradient-to-b from-purple-900/40 to-bg-main p-6 rounded-xl border border-white/5 shadow-2xl">
        <div class="flex flex-col md:flex-row gap-6 items-end">
            <div class="relative group shrink-0 mx-auto md:mx-0">
                <div class="w-48 h-48 md:w-56 md:h-56 rounded-lg overflow-hidden shadow-2xl border border-white/10">
                    <img src="/assets/images/default-cover.svg" 
                         alt="Titres Likés" 
                         class="w-full h-full object-cover transform transition duration-700 group-hover:scale-105">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    
                    <div class="absolute bottom-4 right-4 bg-primary p-3 rounded-full shadow-lg">
                        <i class="fas fa-heart text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="flex-1 text-center md:text-left w-full space-y-4">
                <div>
                    <h5 class="text-sm font-bold uppercase tracking-widest text-text-sub mb-2">Playlist</h5>
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white tracking-tight mb-2 drop-shadow-lg">
                        Titres Likés
                    </h1>
                    <p class="text-text-sub font-medium flex items-center justify-center md:justify-start gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                        <?= isset($likes) ? count($likes) : 0 ?> titres • Vos coups de cœur ❤️
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mt-6">
                    <?php if (!empty($likes)): ?>
                        <?php 
                            $allIds = array_column($likes, 'music_id'); 
                            $jsonIds = htmlspecialchars(json_encode($allIds));
                        ?>
                        
                        <button onclick='playQueue(<?= $jsonIds ?>, 0)' 
                                class="bg-primary hover:bg-primary-dark text-white rounded-full px-10 py-4 font-bold text-lg flex items-center gap-3 transition-all duration-300 transform hover:scale-105 shadow-xl shadow-primary/30 active:scale-95">
                            <i class="fas fa-play text-xl"></i>
                            TOUT LIRE
                        </button>

                        <button onclick='playQueue(<?= $jsonIds ?>, 0, true)' 
                                class="bg-white/10 hover:bg-white/20 text-white border border-white/10 rounded-full px-8 py-4 font-bold text-lg flex items-center gap-3 transition-all backdrop-blur-sm hover:border-white/30 active:scale-95">
                            <i class="fas fa-random text-xl text-text-sub group-hover:text-white"></i>
                            ALÉATOIRE
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-bg-card/30 rounded-xl p-4 md:p-6 border border-white/5 min-h-[400px]">
        
        <?php if (empty($likes)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-text-sub space-y-4">
                <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-heart-broken text-4xl opacity-50"></i>
                </div>
                <p class="text-xl font-medium">Aucun titre liké pour le moment</p>
                <a href="/search" class="text-primary hover:text-primary-dark font-semibold hover:underline">
                    Découvrir de la musique
                </a>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-[auto_1fr_auto] md:grid-cols-[auto_2fr_1fr_auto] gap-4 px-4 py-3 text-sm text-text-sub border-b border-white/5 uppercase tracking-wider font-semibold mb-2 sticky top-0 bg-bg-main/95 backdrop-blur z-10">
                <div class="w-12 text-center">#</div>
                <div>Titre</div>
                <div class="hidden md:block">Artiste</div>
                <div class="text-right pr-4"><i class="far fa-clock"></i></div>
            </div>

            <div class="space-y-2">
                <?php foreach ($likes as $index => $like): ?>
                    <?php 
                        // On prépare les données pour la carte
                        $music = [
                            'id' => $like['music_id'],
                            'title' => $like['title'],
                            'artist' => $like['artist'],
                            'cover_path' => $like['cover_path']
                        ];
                        // On inclut la nouvelle carte "Playlist Row"
                        require __DIR__ . '/../components/music-card-playlist.php'; 
                    ?>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</div>