<?php
// On s'assure d'avoir des variables par défaut pour éviter les erreurs
$musicIndex = isset($index) ? $index + 1 : 1;
$musicId = $music['id'] ?? 0;
$musicTitle = htmlspecialchars($music['title'] ?? 'Titre inconnu');
$musicArtist = htmlspecialchars($music['artist'] ?? 'Artiste inconnu');
// Correction chemin image
$musicCover = !empty($music['cover_path']) ? '/assets/images/covers/' . $music['cover_path'] : '/assets/images/default-cover.svg';

// Échappement pour JS
$jsTitle = addslashes($music['title'] ?? '');
$jsArtist = addslashes($music['artist'] ?? '');
$jsCover = addslashes($music['cover_path'] ?? '');
?>

<div class="group relative flex items-center gap-4 p-3 rounded-lg hover:bg-white/10 transition-all duration-200 border border-transparent hover:border-white/5 hover:shadow-lg music-row">
    
    <div class="w-8 md:w-12 flex justify-center shrink-0">
        <span class="text-text-sub font-medium group-hover:hidden text-sm md:text-base">
            <?= $musicIndex ?>
        </span>
        <button onclick="playMusic(<?= $musicId ?>)" class="hidden group-hover:flex text-white hover:text-primary transition-colors transform hover:scale-110">
            <i class="fas fa-play text-sm md:text-base"></i>
        </button>
    </div>

    <div class="flex items-center gap-4 flex-1 min-w-0"> <div class="relative shrink-0 w-12 h-12 md:w-14 md:h-14">
            <img src="<?= $musicCover ?>" 
                 alt="<?= $musicTitle ?>" 
                 class="w-full h-full object-cover rounded-md shadow-md group-hover:shadow-lg transition-shadow" 
                 loading="lazy">
             <div class="absolute inset-0 bg-black/20 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
        </div>

        <div class="flex flex-col min-w-0 justify-center">
            <h4 class="text-white font-semibold text-sm md:text-base truncate pr-2 group-hover:text-primary transition-colors">
                <?= $musicTitle ?>
            </h4>
            <p class="text-text-sub text-xs md:text-sm truncate md:hidden">
                <?= $musicArtist ?>
            </p>
        </div>
    </div>

    <div class="hidden md:flex flex-[2] items-center">
        <a href="#" class="text-text-sub text-sm hover:text-white hover:underline truncate transition-colors">
            <?= $musicArtist ?>
        </a>
    </div>

    <div class="flex items-center gap-4 md:gap-6 justify-end pr-2 md:pr-4">
        
        <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition text-text-sub hover:text-primary active:scale-90"
                onclick="event.stopPropagation(); toggleLike(this, <?= $musicId ?>)"
                title="Ajouter aux favoris">
            <?php 
                // Petite astuce : si on est sur la page Liked, le coeur est forcément plein par défaut
                $isLiked = true; // À adapter si utilisé ailleurs
            ?>
            <i class="<?= $isLiked ? 'fas text-primary' : 'far' ?> fa-heart"></i>
        </button>

        <span class="text-xs text-text-sub hidden sm:block font-mono">3:45</span>

        <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition text-text-sub hover:text-white"
                onclick="openSongOptions(event, <?= $musicId ?>, '<?= $jsTitle ?>', '<?= $jsArtist ?>', '<?= $jsCover ?>')"
                title="Plus d'options">
            <i class="fas fa-ellipsis-h"></i>
        </button>
    </div>

</div>