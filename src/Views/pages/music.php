<div class="pb-8">
    <div class="relative mb-8 pb-8 pt-12 px-6 rounded-xl overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 via-cyan-600/10 to-transparent"></div>
        
        <div class="relative z-10">
            <div class="flex items-end gap-6 mb-8">
                <div class="w-28 h-28 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-2xl flex-shrink-0">
                    <i class="fas fa-music text-5xl text-white"></i>
                </div>
                
                <div class="flex-1">
                    <p class="text-sm font-semibold text-text-sub mb-2 uppercase tracking-wide">Bibliothèque</p>
                    <h1 class="text-3xl md:text-5xl lg:text-6xl font-black mb-4 text-text-main leading-tight">Toutes les musiques</h1>
                    <p class="text-text-sub text-base"><span id="total-count" class="font-semibold text-text-main">--</span> titre<span id="plural">s</span></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="playAll()" id="play-all-btn" class="hidden inline-flex items-center justify-center gap-3 w-14 h-14 md:w-auto md:h-auto md:px-8 md:py-4 rounded-full bg-primary text-white font-bold text-base hover:scale-105 hover:bg-primary-hover transition-all shadow-lg">
                    <i class="fas fa-play text-lg md:text-sm"></i>
                    <span class="hidden md:inline">Tout lire</span>
                </button>
                
                <button onclick="shuffleAll()" id="shuffle-btn" class="hidden inline-flex items-center justify-center gap-3 w-14 h-14 md:w-auto md:h-auto md:px-6 md:py-4 rounded-full border-2 border-text-main/20 text-text-main font-semibold hover:border-text-main/40 hover:bg-bg-card transition-all">
                    <i class="fas fa-random text-lg md:text-base"></i>
                    <span class="hidden md:inline">Aléatoire</span>
                </button>

                <button onclick="cleanAllTitles()" class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-3 sm:py-2.5 rounded-full bg-transparent text-text-main border border-border-main font-semibold hover:border-text-main transition-colors">
                    <i class="fas fa-broom"></i>
                    <span class="hidden sm:inline">Clean Titles</span>
                </button>
                <button onclick="scanLibrary()" class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-3 sm:py-2.5 rounded-full bg-transparent text-text-main border border-border-main font-semibold hover:border-text-main transition-colors">
                    <i class="fas fa-sync"></i>
                    <span class="hidden sm:inline">Scan</span>
                </button>
            </div>
        </div>
    </div>

    <div id="empty-state" class="text-center py-20 px-6 hidden">
        <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-blue-500/10 to-cyan-600/10 flex items-center justify-center">
            <i class="fas fa-folder-open text-6xl text-text-sub"></i>
        </div>
        <h2 class="text-2xl font-bold text-text-main mb-3">Aucune musique dans la bibliothèque</h2>
        <p class="text-text-sub text-base max-w-md mx-auto mb-8">Commencez à ajouter vos musiques préférées !</p>
        <a href="/add" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-full hover:scale-105 transition-transform shadow-lg">
            <i class="fas fa-plus"></i>
            Ajouter de la musique
        </a>
    </div>

    <div id="music-list-container" class="hidden">
        <div class="mb-4 flex items-center justify-between text-text-sub text-sm font-semibold uppercase tracking-wide px-2 md:px-4">
            <div class="flex items-center gap-6">
                <span class="w-8 text-center">#</span>
                <span>Titre</span>
            </div>
            <div class="flex items-center gap-12">
                <span><i class="far fa-clock mr-1"></i> Actions</span>
            </div>
        </div>

        <div id="music-grid" class="flex flex-col">
            </div>
    </div>

    <div id="scroll-sentinel" class="h-10 w-full mt-4 flex justify-center items-center">
        <div id="loading-spinner" class="hidden animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
    </div>
</div>

<script src="/assets/js/clean-titles.js"></script>

<script>
// Infinite Scroll for Music Library - Inline pour compatibilité SPA
(function initInfiniteScroll() {
    console.log('🚀 Initializing infinite scroll...');
    
    const grid = document.getElementById('music-grid');
    const sentinel = document.getElementById('scroll-sentinel');
    const spinner = document.getElementById('loading-spinner');
    const emptyState = document.getElementById('empty-state');
    const listContainer = document.getElementById('music-list-container');
    const totalCount = document.getElementById('total-count');
    const playAllBtn = document.getElementById('play-all-btn');
    const shuffleBtn = document.getElementById('shuffle-btn');
    
    if (!grid || !sentinel) {
        console.warn('⚠️ Infinite scroll: Elements not found');
        return;
    }

    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let totalLoaded = 0;
    let allSongIds = [];

    // Fonction pour créer une carte de musique (format liste comme Liked avec bordures)
    function createMusicCard(track, index) {
        const safeTitle = (track.title || 'Unknown Title').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeArtist = (track.artist || 'Unknown Artist').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeCover = (track.cover_path || '/assets/images/default-cover.svg').replace(/'/g, "\\'");

        // Note : Les classes ici (p-2, rounded-lg, border-white/10, mb-2) assurent le style "cadre" demandé
        return `
            <div class="group relative flex items-center gap-3 p-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all mb-2 music-row" data-song-id="${track.id}">
                
                <div class="w-6 md:w-10 flex justify-center shrink-0">
                    <span class="text-text-sub text-xs md:text-sm font-medium group-hover:hidden">
                        ${index}
                    </span>
                    <button onclick="playMusic(${track.id})" class="hidden group-hover:flex text-white hover:text-primary transition-colors transform hover:scale-110">
                        <i class="fas fa-play text-xs md:text-sm"></i>
                    </button>
                </div>

                <div class="flex items-center gap-3 flex-1 min-w-0">
                    
                    <div class="relative shrink-0 w-10 h-10 md:w-12 md:h-12">
                        <img src="${safeCover}" 
                             alt="${safeTitle}" 
                             class="w-full h-full object-cover rounded-md shadow-sm" 
                             loading="lazy">
                    </div>

                    <div class="flex flex-col min-w-0 justify-center overflow-hidden">
                        <h4 class="text-white font-medium text-sm truncate pr-2 group-hover:text-primary transition-colors">
                            ${track.title || 'Unknown Title'}
                        </h4>
                        <p class="text-text-sub text-xs truncate">
                            ${track.artist || 'Unknown Artist'}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1 md:gap-4 shrink-0">
                    
                    <button class="like-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition active:scale-90"
                            onclick="event.stopPropagation(); toggleLike(${track.id}, this)"
                            data-song-id="${track.id}">
                        <i class="far fa-heart text-sm md:text-base"></i>
                    </button>

                    <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition text-text-sub hover:text-white"
                            onclick="openSongOptions(event, ${track.id}, '${safeTitle}', '${safeArtist}', '${safeCover}')">
                        <i class="fas fa-ellipsis-v text-xs md:text-sm"></i>
                    </button>
                </div>

            </div>
        `;
    }

    // Fonction de chargement
    async function loadMoreMusic() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        spinner.classList.remove('hidden');
        console.log(`📦 Loading page ${currentPage}...`);

        try {
            const response = await fetch(`/api/music/list?page=${currentPage}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('📥 API Response:', data);

            if (data.success && data.tracks && data.tracks.length > 0) {
                // Afficher le container de liste
                listContainer.classList.remove('hidden');
                
                // Ajouter chaque musique au DOM avec index global
                data.tracks.forEach((track, i) => {
                    const globalIndex = totalLoaded + i + 1;
                    const cardHtml = createMusicCard(track, globalIndex);
                    grid.insertAdjacentHTML('beforeend', cardHtml);
                    allSongIds.push(track.id);
                });
                
                totalLoaded += data.tracks.length;
                
                // Mettre à jour le compteur total
                totalCount.textContent = totalLoaded;
                
                // Afficher les boutons Play All / Shuffle
                playAllBtn.classList.remove('hidden');
                shuffleBtn.classList.remove('hidden');
                
                console.log(`✅ Loaded ${data.tracks.length} tracks (total: ${totalLoaded})`);
                
                currentPage++;
                hasMore = data.hasMore;
                
                // Vérifier le statut "liked" pour tous les nouveaux morceaux
                if (typeof checkLikeStatus === 'function') {
                    data.tracks.forEach(track => {
                        checkLikeStatus(track.id);
                    });
                }
                
            } else {
                hasMore = false;
                
                // Si aucune musique n'a été chargée du tout, afficher le message vide
                if (totalLoaded === 0) {
                    listContainer.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                    console.log('📭 No music in library');
                } else {
                    console.log('✅ All music loaded');
                }
            }
        } catch (error) {
            console.error('❌ Error loading music:', error);
            hasMore = false;
            
            // Afficher un message d'erreur
            if (totalLoaded === 0) {
                grid.innerHTML = `
                    <div class="text-center py-8 text-text-sub">
                        <i class="fas fa-exclamation-triangle text-3xl mb-3 text-red-500"></i>
                        <p>Erreur lors du chargement de la bibliothèque</p>
                    </div>
                `;
            }
        } finally {
            isLoading = false;
            spinner.classList.add('hidden');
        }
    }

    // L'Observateur : détecte quand la "sentinel" devient visible
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMore && !isLoading) {
            console.log('👁️ Sentinel visible, loading more...');
            loadMoreMusic();
        }
    }, { 
        rootMargin: '300px' // Commence à charger 300px avant d'atteindre le bas
    });

    // Démarrer l'observation
    observer.observe(sentinel);
    
    // Premier chargement automatique
    console.log('🎬 Starting initial load...');
    loadMoreMusic();
    
    // Exposer les fonctions globalement pour les boutons
    window.playAll = function() {
        if (allSongIds.length > 0 && typeof playQueue === 'function') {
            playQueue(allSongIds, 0);
        }
    };
    
    window.shuffleAll = function() {
        if (allSongIds.length > 0 && typeof playQueue === 'function') {
            const shuffled = [...allSongIds];
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            playQueue(shuffled, 0);
        }
    };
})();
</script>