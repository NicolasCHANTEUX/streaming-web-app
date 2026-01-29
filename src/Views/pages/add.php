<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Ajouter de la musique</h1>
        <p class="text-text-sub">Téléchargez depuis YouTube ou importez depuis Spotify</p>
    </div>

    <!-- Import In Progress Alert -->
    <div id="importInProgressAlert" class="hidden mb-6 bg-orange-500/10 border-2 border-orange-500/50 rounded-xl p-5">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                <i class="fas fa-download text-orange-500 text-xl animate-pulse"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-orange-500 mb-1 flex items-center gap-2">
                    Importation en cours
                    <span class="text-sm font-normal text-text-sub" id="importProgressSummary"></span>
                </h3>
                <p class="text-text-sub text-sm mb-3">Une importation CSV est actuellement en cours. Cliquez ci-dessous pour voir les détails ou l'arrêter.</p>
                <div class="flex gap-3">
                    <button onclick="resumeImportView()" class="px-4 py-2 rounded-lg bg-orange-500 text-white font-semibold hover:bg-orange-600 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-eye"></i>
                        Voir la progression
                    </button>
                    <button onclick="stopImportFromAlert()" class="px-4 py-2 rounded-lg border-2 border-red-500/50 text-red-500 font-semibold hover:bg-red-500/10 transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-stop"></i>
                        Arrêter l'importation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-4 mb-8 border-b border-border-main">
        <button 
            onclick="switchTab('youtube')" 
            id="youtubeTab"
            class="tab-button px-6 py-3 font-semibold text-text-sub hover:text-text-main transition-colors relative active"
        >
            <i class="fab fa-youtube text-red-500 mr-2"></i>
            YouTube
        </button>
        <button 
            onclick="switchTab('spotify')" 
            id="spotifyTab"
            class="tab-button px-6 py-3 font-semibold text-text-sub hover:text-text-main transition-colors relative"
        >
            <i class="fab fa-spotify text-green-500 mr-2"></i>
            Spotify CSV
        </button>
    </div>

    <!-- YouTube Search Tab -->
    <div id="youtubeContent" class="tab-content">
        <div class="mb-6">
            <form id="youtubeSearchForm" onsubmit="searchYoutube(event)" class="max-w-2xl">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="youtubeQuery" 
                        name="q" 
                        placeholder="Rechercher des chansons, artistes, albums..." 
                        required
                        class="flex-1 px-5 py-3 rounded-lg bg-bg-surface text-text-main outline-none focus:ring-2 focus:ring-primary transition-all"
                    >
                    <button type="submit" class="px-6 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors inline-flex items-center gap-2">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>

        <div id="searchResults" class="space-y-3"></div>
        <div id="downloadStatus" class="mt-6 hidden"></div>
    </div>

    <!-- Spotify Import Tab -->
    <div id="spotifyContent" class="tab-content hidden">
        <!-- Header avec gradient -->
        <div class="relative mb-8 pb-8 pt-12 px-6 rounded-xl overflow-hidden">
            <!-- Gradient background -->
            <div class="absolute inset-0 bg-gradient-to-br from-green-600/20 via-primary/10 to-transparent"></div>
            
            <!-- Content -->
            <div class="relative z-10">
                <!-- Info box -->
                <div class="bg-bg-card border border-border-main rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                        </div>
                        <div class="text-sm text-text-sub">
                            <p class="font-semibold text-text-main mb-2">Comment obtenir votre fichier CSV ?</p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Rendez-vous sur <a href="https://www.spotify.com/account/privacy/" target="_blank" class="text-primary hover:underline">spotify.com/account/privacy</a></li>
                                <li>Connectez-vous et descendez jusqu'à "Télécharger vos données"</li>
                                <li>Demandez vos données et attendez l'email (peut prendre quelques jours)</li>
                                <li>Téléchargez l'archive et extrayez le fichier <code class="px-1 py-0.5 bg-bg-surface rounded text-xs">Liked_Songs.csv</code></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="px-4 max-w-4xl mx-auto">
            <!-- File Input -->
            <div class="bg-bg-card border-2 border-dashed border-border-main rounded-xl p-8 mb-6 text-center hover:border-primary/50 transition-colors">
                <input 
                    type="file" 
                    id="csvFileInput" 
                    accept=".csv"
                    class="hidden"
                    onchange="handleFileSelect(event)"
                >
                <label for="csvFileInput" class="cursor-pointer block">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-file-csv text-4xl text-primary"></i>
                    </div>
                    <p class="text-text-main font-semibold mb-2">Sélectionnez votre fichier CSV</p>
                    <p class="text-text-sub text-sm">Cliquez ici ou déposez votre fichier <span class="text-primary">Liked_Songs.csv</span></p>
                    <p id="fileName" class="text-primary text-sm font-medium mt-3 hidden"></p>
                </label>
            </div>

            <!-- Stats Preview -->
            <div id="statsPreview" class="hidden mb-6">
                <div class="bg-bg-card border border-border-main rounded-lg p-6">
                    <h3 class="text-lg font-bold text-text-main mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-primary"></i>
                        Aperçu du fichier
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-bg-surface rounded-lg">
                            <div class="text-3xl font-black text-primary mb-1" id="totalTracks">0</div>
                            <div class="text-sm text-text-sub">Titres trouvés</div>
                        </div>
                        <div class="text-center p-4 bg-bg-surface rounded-lg">
                            <div class="text-3xl font-black text-green-500 mb-1" id="readyTracks">0</div>
                            <div class="text-sm text-text-sub">Prêts à importer</div>
                        </div>
                        <div class="text-center p-4 bg-bg-surface rounded-lg">
                            <div class="text-3xl font-black text-yellow-500 mb-1" id="invalidTracks">0</div>
                            <div class="text-sm text-text-sub">Lignes invalides</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Control Buttons -->
            <div id="controlButtons" class="hidden flex items-center justify-center gap-4 mb-6">
                <button 
                    id="startImportBtn" 
                    onclick="startImport()"
                    class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-primary text-white font-bold text-base hover:scale-105 hover:bg-primary-hover transition-all shadow-lg">
                    <i class="fas fa-download"></i>
                    Lancer l'importation
                </button>
                <button 
                    id="cancelImportBtn"
                    onclick="cancelImport()"
                    class="hidden inline-flex items-center gap-3 px-6 py-4 rounded-full border-2 border-red-500/50 text-red-500 font-semibold hover:bg-red-500/10 transition-all">
                    <i class="fas fa-stop"></i>
                    Annuler
                </button>
            </div>

            <!-- Progress Section -->
            <div id="progressSection" class="hidden mb-6">
                <div class="bg-bg-card border border-border-main rounded-lg p-6">
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-bold text-text-main">Progression</h3>
                            <span class="text-text-sub text-sm"><span id="currentProgress">0</span> / <span id="totalProgress">0</span></span>
                        </div>
                        <div class="w-full h-3 bg-bg-surface rounded-full overflow-hidden">
                            <div id="progressBar" class="h-full bg-gradient-to-r from-primary to-green-500 transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Current Track Info -->
                    <div id="currentTrackInfo" class="text-sm text-text-sub text-center mb-4">
                        En attente...
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div id="statsSection" class="hidden mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="text-center p-3 bg-green-500/10 rounded-lg border border-green-500/20">
                        <div class="text-2xl font-bold text-green-500" id="statDownloaded">0</div>
                        <div class="text-xs text-text-sub">Téléchargés</div>
                    </div>
                    <div class="text-center p-3 bg-blue-500/10 rounded-lg border border-blue-500/20">
                        <div class="text-2xl font-bold text-blue-500" id="statExists">0</div>
                        <div class="text-xs text-text-sub">Déjà présents</div>
                    </div>
                    <div class="text-center p-3 bg-yellow-500/10 rounded-lg border border-yellow-500/20">
                        <div class="text-2xl font-bold text-yellow-500" id="statSkipped">0</div>
                        <div class="text-xs text-text-sub">Ignorés</div>
                    </div>
                    <div class="text-center p-3 bg-red-500/10 rounded-lg border border-red-500/20">
                        <div class="text-2xl font-bold text-red-500" id="statErrors">0</div>
                        <div class="text-xs text-text-sub">Erreurs</div>
                    </div>
                </div>
            </div>

            <!-- Import Logs -->
            <div id="importLogsSection" class="hidden mb-6">
                <div class="bg-black rounded-lg overflow-hidden border border-border-main">
                    <div class="bg-bg-card px-4 py-3 border-b border-border-main flex items-center justify-between">
                        <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
                            <i class="fas fa-terminal text-primary"></i>
                            Logs d'importation
                        </h3>
                    </div>
                    <div class="p-4 max-h-96 overflow-y-auto custom-scrollbar font-mono text-xs" id="importLogs"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-button.active {
    color: var(--text-main);
}
.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary);
}
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script src="/assets/js/download.js?v=<?= time() ?>"></script>
<script src="/assets/js/import.js?v=<?= time() ?>"></script>

<script>
// Check for ongoing import on page load
document.addEventListener('DOMContentLoaded', () => {
    checkOngoingImport();
});

function checkOngoingImport() {
    const importState = localStorage.getItem('importState');
    
    if (importState) {
        const state = JSON.parse(importState);
        
        // Check if import was active recently (within last 5 minutes)
        const lastUpdate = new Date(state.lastUpdate);
        const now = new Date();
        const minutesSinceUpdate = (now - lastUpdate) / (1000 * 60);
        
        if (state.isActive && minutesSinceUpdate < 5) {
            // Show alert
            const alert = document.getElementById('importInProgressAlert');
            const summary = document.getElementById('importProgressSummary');
            
            summary.textContent = `(${state.current}/${state.total})`;
            alert.classList.remove('hidden');
            
            // Auto-switch to Spotify tab
            switchTab('spotify');
        } else if (!state.isActive || minutesSinceUpdate >= 5) {
            // Clear old state
            localStorage.removeItem('importState');
        }
    }
}

function resumeImportView() {
    // Switch to Spotify tab
    switchTab('spotify');
    
    // Show progress sections
    document.getElementById('progressSection')?.classList.remove('hidden');
    document.getElementById('statsSection')?.classList.remove('hidden');
    document.getElementById('importLogsSection')?.classList.remove('hidden');
    
    // Hide alert
    document.getElementById('importInProgressAlert').classList.add('hidden');
    
    // Scroll to progress
    setTimeout(() => {
        document.getElementById('progressSection')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300);
}

function stopImportFromAlert() {
    if (confirm('Voulez-vous vraiment arrêter l\'importation en cours ?')) {
        // Cancel import via import.js
        if (typeof cancelImport === 'function') {
            cancelImport();
        }
        
        // Clear state
        localStorage.removeItem('importState');
        
        // Hide alert
        document.getElementById('importInProgressAlert').classList.add('hidden');
    }
}

function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    
    // Show selected content
    if (tab === 'youtube') {
        document.getElementById('youtubeTab').classList.add('active');
        document.getElementById('youtubeContent').classList.remove('hidden');
        document.getElementById('spotifyContent').classList.add('hidden');
    } else {
        document.getElementById('spotifyTab').classList.add('active');
        document.getElementById('spotifyContent').classList.remove('hidden');
        document.getElementById('youtubeContent').classList.add('hidden');
    }
}

// YouTube Search functionality
async function searchYoutube(event) {
    event.preventDefault();
    
    const query = document.getElementById('youtubeQuery').value;
    const resultsDiv = document.getElementById('searchResults');
    
    console.log('=== YOUTUBE SEARCH FRONTEND ===');
    console.log('Query:', query);
    
    // Show loading
    resultsDiv.innerHTML = '<div class="flex flex-col items-center justify-center py-10"><div class="w-12 h-12 border-4 border-border-main border-t-primary rounded-full animate-spin"></div><p class="mt-4 text-text-sub">Recherche sur YouTube...</p></div>';
    
    try {
        const url = '/api/youtube/search?q=' + encodeURIComponent(query);
        console.log('Fetching URL:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success && data.results) {
            console.log('Found', data.results.length, 'results');
            displaySearchResults(data.results);
        } else {
            resultsDiv.innerHTML = '<div class="text-center py-10 text-text-sub"><i class="fas fa-exclamation-circle text-4xl mb-3"></i><p>No results found</p></div>';
        }
    } catch (error) {
        console.error('Search error:', error);
        resultsDiv.innerHTML = '<div class="text-center py-10 text-red-500"><i class="fas fa-exclamation-triangle text-4xl mb-3"></i><p>Error during search</p></div>';
    }
}

function displaySearchResults(results) {
    const resultsDiv = document.getElementById('searchResults');
    
    if (results.length === 0) {
        resultsDiv.innerHTML = '<div class="text-center py-10 text-text-sub"><p>No results found</p></div>';
        return;
    }
    
    resultsDiv.innerHTML = results.map(video => `
        <div class="flex items-center gap-4 p-4 bg-bg-surface rounded-lg hover:bg-bg-hover transition-all group">
            <img src="${video.thumbnail}" alt="${video.title}" class="w-32 h-20 object-cover rounded flex-shrink-0">
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-text-main mb-1 truncate group-hover:text-primary transition-colors">${video.title}</h3>
                <p class="text-sm text-text-sub">${video.channel}</p>
                <p class="text-xs text-text-sub mt-1">${formatDuration(video.duration)}</p>
            </div>
            <button 
                onclick="downloadVideo('${video.id}', '${video.title.replace(/'/g, "\\'")}', '${video.channel.replace(/'/g, "\\'")}')" 
                class="px-4 py-2 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors flex-shrink-0 inline-flex items-center gap-2"
            >
                <i class="fas fa-download"></i> Download
            </button>
        </div>
    `).join('');
}

function formatDuration(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
}

function downloadVideo(videoId, title, channel) {
    addToDownloadQueue(videoId, title, '');
}
</script>
