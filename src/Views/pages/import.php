<div class="pb-8">
    <!-- Header avec gradient -->
    <div class="relative mb-8 pb-8 pt-12 px-6 rounded-xl overflow-hidden">
        <!-- Gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-600/20 via-primary/10 to-transparent"></div>
        
        <!-- Content -->
        <div class="relative z-10">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-600 to-green-500 flex items-center justify-center shadow-2xl">
                    <i class="fab fa-spotify text-3xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-4xl md:text-5xl font-black text-text-main">Import Spotify</h1>
                    <p class="text-text-sub text-base mt-1">Importez vos titres aimés depuis un export CSV</p>
                </div>
            </div>

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
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-text-main">Progression</h3>
                    <span class="text-text-sub font-mono text-sm">
                        <span id="currentProgress">0</span> / <span id="totalProgress">0</span>
                    </span>
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-bg-surface rounded-full h-3 mb-4 overflow-hidden">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-primary to-pink-600 transition-all duration-300" style="width: 0%"></div>
                </div>

                <!-- Current Track Info -->
                <div id="currentTrackInfo" class="text-sm text-text-sub text-center">
                    En attente...
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div id="statsSection" class="hidden mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-bg-card border border-border-main rounded-lg p-4 text-center">
                    <div class="text-2xl font-black text-green-500 mb-1" id="statDownloaded">0</div>
                    <div class="text-xs text-text-sub">Téléchargés</div>
                </div>
                <div class="bg-bg-card border border-border-main rounded-lg p-4 text-center">
                    <div class="text-2xl font-black text-blue-500 mb-1" id="statExists">0</div>
                    <div class="text-xs text-text-sub">Déjà présents</div>
                </div>
                <div class="bg-bg-card border border-border-main rounded-lg p-4 text-center">
                    <div class="text-2xl font-black text-yellow-500 mb-1" id="statSkipped">0</div>
                    <div class="text-xs text-text-sub">Ignorés</div>
                </div>
                <div class="bg-bg-card border border-border-main rounded-lg p-4 text-center">
                    <div class="text-2xl font-black text-red-500 mb-1" id="statErrors">0</div>
                    <div class="text-xs text-text-sub">Erreurs</div>
                </div>
            </div>
        </div>

        <!-- Import Logs -->
        <div id="importLogsSection" class="hidden">
            <div class="bg-black rounded-lg overflow-hidden border border-border-main">
                <div class="bg-bg-card px-4 py-3 border-b border-border-main flex items-center justify-between">
                    <h3 class="text-sm font-bold text-text-main flex items-center gap-2">
                        <i class="fas fa-terminal text-primary"></i>
                        Journal d'importation
                    </h3>
                    <button onclick="clearLogs()" class="text-xs text-text-sub hover:text-text-main transition-colors">
                        <i class="fas fa-trash"></i> Vider
                    </button>
                </div>
                <div id="importLogs" class="p-4 h-96 overflow-y-auto font-mono text-xs space-y-1"></div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/import.js') ?>?v=<?= time() ?>"></script>
