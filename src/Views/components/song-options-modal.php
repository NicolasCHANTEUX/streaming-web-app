<!-- Modale des options de musique -->
<div id="songOptionsModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-bg-card rounded-2xl shadow-2xl max-w-md w-full border border-border animate-scale-in">
        <!-- En-tête avec titre de la chanson -->
        <div class="p-6 border-b border-border">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-primary/20 to-purple-600/10 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-music text-primary text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="songOptionsTitle" class="text-lg font-bold text-text-main truncate mb-1">Titre de la chanson</h3>
                    <p id="songOptionsArtist" class="text-sm text-text-sub truncate">Artiste</p>
                </div>
                <button onclick="closeSongOptions()" class="text-text-sub hover:text-text-main transition-colors p-2 rounded-full hover:bg-bg-surface">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Liste des options -->
        <div class="p-3">
            <!-- Éditer le titre -->
            <button onclick="editSongInfo()" class="w-full flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-bg-surface transition-all group text-left border border-transparent hover:border-primary/20">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors flex-shrink-0">
                    <i class="fas fa-edit text-primary"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-text-main mb-0.5">Modifier les informations</div>
                    <div class="text-xs text-text-sub">Titre, artiste, album...</div>
                </div>
                <i class="fas fa-chevron-right text-text-sub text-sm"></i>
            </button>

            <!-- Ajouter à une playlist -->
            <button onclick="addToPlaylistFromOptions()" class="w-full flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-bg-surface transition-all group text-left border border-transparent hover:border-primary/20">
                <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center group-hover:bg-green-500/20 transition-colors flex-shrink-0">
                    <i class="fas fa-list-ul text-green-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-text-main mb-0.5">Ajouter à une playlist</div>
                    <div class="text-xs text-text-sub">Organiser votre bibliothèque</div>
                </div>
                <i class="fas fa-chevron-right text-text-sub text-sm"></i>
            </button>

            <!-- Voir les détails -->
            <button onclick="showSongDetails()" class="w-full flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-bg-surface transition-all group text-left border border-transparent hover:border-primary/20">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500/20 transition-colors flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-text-main mb-0.5">Détails de la chanson</div>
                    <div class="text-xs text-text-sub">Durée, format, taille...</div>
                </div>
                <i class="fas fa-chevron-right text-text-sub text-sm"></i>
            </button>

            <!-- Partager -->
            <button onclick="shareSong()" class="w-full flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-bg-surface transition-all group text-left border border-transparent hover:border-primary/20">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-500/20 transition-colors flex-shrink-0">
                    <i class="fas fa-share-alt text-purple-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-text-main mb-0.5">Partager</div>
                    <div class="text-xs text-text-sub">Copier le lien</div>
                </div>
                <i class="fas fa-chevron-right text-text-sub text-sm"></i>
            </button>

            <div class="my-2 border-t border-border"></div>

            <!-- Supprimer (zone dangereuse) -->
            <button onclick="deleteSong()" class="w-full flex items-center gap-4 px-4 py-3 rounded-lg hover:bg-red-500/10 transition-all group text-left border border-transparent hover:border-red-500/20">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center group-hover:bg-red-500/20 transition-colors flex-shrink-0">
                    <i class="fas fa-trash text-red-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-red-500 mb-0.5">Supprimer définitivement</div>
                    <div class="text-xs text-text-sub">Cette action est irréversible</div>
                </div>
                <i class="fas fa-chevron-right text-red-500 text-sm"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modale d'édition des informations -->
<div id="editSongModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-bg-card rounded-2xl shadow-2xl max-w-lg w-full border border-border animate-scale-in">
        <div class="p-6 border-b border-border">
            <h3 class="text-xl font-bold text-text-main">Modifier les informations</h3>
        </div>

        <form id="editSongForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-text-main mb-2">Titre</label>
                <input type="text" id="editSongTitle" class="w-full bg-bg-surface border border-border rounded-lg px-4 py-3 text-text-main focus:outline-none focus:border-primary transition-colors" placeholder="Titre de la chanson">
            </div>

            <div>
                <label class="block text-sm font-medium text-text-main mb-2">Artiste</label>
                <input type="text" id="editSongArtist" class="w-full bg-bg-surface border border-border rounded-lg px-4 py-3 text-text-main focus:outline-none focus:border-primary transition-colors" placeholder="Nom de l'artiste">
            </div>

            <div>
                <label class="block text-sm font-medium text-text-main mb-2">Album</label>
                <input type="text" id="editSongAlbum" class="w-full bg-bg-surface border border-border rounded-lg px-4 py-3 text-text-main focus:outline-none focus:border-primary transition-colors" placeholder="Nom de l'album">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditSong()" class="flex-1 px-6 py-3 bg-bg-surface text-text-main rounded-lg hover:bg-bg-hover transition-colors font-medium">
                    Annuler
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors font-medium">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}
</style>
