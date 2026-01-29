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
<script src="/assets/js/infinite-scroll.js"></script>
