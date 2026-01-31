<div>
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Music Library</h1>
        <div class="flex gap-3">
            <button onclick="cleanAllTitles()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-transparent text-text-main border border-border-main font-semibold hover:border-text-main transition-colors">
                <i class="fas fa-broom"></i> Clean All Titles
            </button>
            <button onclick="scanLibrary()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-transparent text-text-main border border-border-main font-semibold hover:border-text-main transition-colors">
                <i class="fas fa-sync"></i> Scan Directory
            </button>
        </div>
    </div>

    <!-- Container pour les musiques chargées dynamiquement -->
    <div id="music-grid" class="bg-bg-surface rounded-lg overflow-hidden">
        <!-- Les musiques seront chargées ici par JavaScript -->
    </div>

    <!-- Message si aucune musique -->
    <div id="empty-state" class="text-center py-16 text-text-sub hidden">
        <i class="fas fa-folder-open text-5xl mb-5 text-border-main"></i>
        <h2 class="text-text-main text-2xl mb-2.5">No music found</h2>
        <p class="mb-5">Add music to your library to get started</p>
        <a href="/add" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">Add Music</a>
    </div>

    <!-- Sentinel pour détecter le scroll + Spinner -->
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
    
    if (!grid || !sentinel) {
        console.warn('⚠️ Infinite scroll: Elements not found');
        return;
    }

    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let totalLoaded = 0;

    // Fonction pour créer une carte de musique (format list)
    function createMusicCard(track) {
        // Échapper les quotes pour éviter les injections
        const safeTitle = (track.title || 'Unknown Title').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeArtist = (track.artist || 'Unknown Artist').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeCover = (track.cover_path || '/assets/images/default-cover.svg').replace(/'/g, "\\'");
        
        const duration = track.duration && track.duration > 0 
            ? new Date(track.duration * 1000).toISOString().substr(14, 5) 
            : '';

        return `
            <div class="group flex items-center px-4 py-3 rounded-lg hover:bg-bg-card transition-all cursor-pointer" 
                 data-song-id="${track.id}"
                 onclick="Player.play(${track.id})">
                
                <!-- Cover -->
                <img 
                    src="${safeCover}" 
                    alt="${safeTitle}"
                    class="w-10 h-10 rounded object-cover mr-4 flex-shrink-0 shadow-md"
                    loading="lazy"
                >
                
                <!-- Title & Artist -->
                <div class="flex-1 min-w-0 mr-4">
                    <div class="text-base font-medium mb-0.5 truncate text-text-main group-hover:text-primary transition-colors">
                        ${track.title || 'Unknown Title'}
                    </div>
                    <div class="text-sm text-text-sub truncate">
                        ${track.artist || 'Unknown Artist'}
                    </div>
                </div>
                
                <!-- Duration -->
                ${duration ? `
                    <div class="text-sm text-text-sub mr-6 flex-shrink-0 hidden sm:block">
                        ${duration}
                    </div>
                ` : ''}
                
                <!-- Actions -->
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                    <button class="like-btn text-text-sub hover:text-primary p-2 transition-colors rounded-full hover:bg-bg-surface" 
                            onclick="toggleLike(${track.id}, this)" 
                            data-song-id="${track.id}"
                            title="Ajouter aux favoris">
                        <i class="far fa-heart"></i>
                    </button>
                    <button class="text-text-sub hover:text-text-main p-2 transition-colors rounded-full hover:bg-bg-surface"
                            onclick="openSongOptions(event, ${track.id}, '${safeTitle}', '${safeArtist}', '${safeCover}')"
                            title="Plus d'options">
                        <i class="fas fa-ellipsis-v"></i>
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
                // Ajouter chaque musique au DOM
                data.tracks.forEach(track => {
                    const cardHtml = createMusicCard(track);
                    grid.insertAdjacentHTML('beforeend', cardHtml);
                });
                
                totalLoaded += data.tracks.length;
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
                    grid.classList.add('hidden');
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
})();
</script>
