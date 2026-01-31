<?php
// Sécurisation des données - Supporte à la fois les objets et les tableaux
$musicIndex = isset($index) ? $index + 1 : 1;

// Fonction helper pour accéder aux propriétés objet ou tableau
function getMusicProp($music, $prop, $default = null) {
    if (is_object($music)) {
        return $music->$prop ?? $default;
    }
    return $music[$prop] ?? $default;
}

$musicId = getMusicProp($music, 'id', 0);
$musicTitle = htmlspecialchars(getMusicProp($music, 'title', 'Titre inconnu'));
$musicArtist = htmlspecialchars(getMusicProp($music, 'artist', 'Artiste inconnu'));
$coverPath = getMusicProp($music, 'cover_path', '');
// On gère l'image par défaut si pas de cover
$musicCover = !empty($coverPath) ? '/assets/images/covers/' . $coverPath : '/assets/images/default-cover.svg';

// Échappement pour les fonctions JS (play, options...)
$jsTitle = addslashes(getMusicProp($music, 'title', ''));
$jsArtist = addslashes(getMusicProp($music, 'artist', ''));
$jsCover = addslashes($coverPath);
?>

<div class="group relative flex items-center gap-3 p-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all mb-2 music-row">
    
    <div class="w-6 md:w-10 flex justify-center shrink-0">
        <span class="text-text-sub text-xs md:text-sm font-medium group-hover:hidden">
            <?= $musicIndex ?>
        </span>
        <button onclick="playMusic(<?= $musicId ?>)" class="hidden group-hover:flex text-white hover:text-primary transition-colors transform hover:scale-110">
            <i class="fas fa-play text-xs md:text-sm"></i>
        </button>
    </div>

    <div class="flex items-center gap-3 flex-1 min-w-0">
        
        <div class="relative shrink-0 w-10 h-10 md:w-12 md:h-12">
            <img src="<?= $musicCover ?>" 
                 alt="<?= $musicTitle ?>" 
                 class="w-full h-full object-cover rounded-md shadow-sm" 
                 loading="lazy">
        </div>

        <div class="flex flex-col min-w-0 justify-center overflow-hidden">
            <h4 class="text-white font-medium text-sm truncate pr-2 group-hover:text-primary transition-colors">
                <?= $musicTitle ?>
            </h4>
            <p class="text-text-sub text-xs truncate">
                <?= $musicArtist ?>
            </p>
        </div>
    </div>

    <div class="flex items-center gap-1 md:gap-4 shrink-0">
        
        <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition active:scale-90"
                onclick="event.stopPropagation(); toggleLike(this, <?= $musicId ?>)">
            <i class="fas fa-heart text-primary text-sm md:text-base"></i>
        </button>

        <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition text-text-sub hover:text-white"
                onclick="openSongOptions(event, <?= $musicId ?>, '<?= $jsTitle ?>', '<?= $jsArtist ?>', '<?= $jsCover ?>')">
            <i class="fas fa-ellipsis-v text-xs md:text-sm"></i>
        </button>
    </div>

</div>