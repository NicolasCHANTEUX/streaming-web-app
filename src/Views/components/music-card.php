<?php
/**
 * Music card component (track row style) - OPTIMISÉ MOBILE & VOITURE
 * @var object $track - Song object with id, title, artist, cover_path, duration
 * @var int $index - Optional track number
 * @var bool $showIndex - Whether to show track number instead of cover
 */
?>
<div class="group flex items-center gap-3 px-3 py-3 md:px-4 md:py-3.5 rounded-lg hover:bg-bg-card transition-all cursor-pointer" 
     data-song-id="<?= e($track->id) ?>"
     onclick="Player.play(<?= e($track->id) ?>)">
    
    <!-- Index or Play button -->
    <?php if (isset($showIndex) && $showIndex): ?>
        <div class="w-10 text-center flex-shrink-0">
            <span class="group-hover:hidden text-text-sub font-semibold text-sm"><?= $index ?></span>
            <i class="fas fa-play hidden group-hover:inline-block text-primary text-base"></i>
        </div>
    <?php endif; ?>
    
    <!-- Cover (optimisé mobile) -->
    <img 
        src="<?= e($track->cover_path ?? asset('images/default-cover.svg')) ?>" 
        alt="<?= e($track->title) ?>"
        class="w-11 h-11 md:w-12 md:h-12 rounded object-cover flex-shrink-0 shadow-md"
    >
    
    <!-- Title & Artist - HIÉRARCHIE CLAIRE -->
    <div class="flex-1 min-w-0 mr-2">
        <!-- Titre : GROS et BLANC -->
        <div class="text-base md:text-[17px] font-bold mb-0.5 truncate text-text-main group-hover:text-primary transition-colors leading-tight <?= isset($isPlaying) && $isPlaying ? 'text-primary' : '' ?>">
            <?= e($track->title) ?>
        </div>
        <!-- Artiste : PETIT et GRIS -->
        <div class="text-xs md:text-sm text-text-sub/80 truncate font-medium">
            <?= e($track->artist ?? 'Unknown Artist') ?>
        </div>
    </div>
    
    <!-- Duration - MASQUÉE SUR MOBILE -->
    <?php if (isset($track->duration) && $track->duration > 0): ?>
        <div class="text-sm text-text-sub mr-3 flex-shrink-0 hidden lg:block font-medium">
            <?= gmdate("i:s", $track->duration) ?>
        </div>
    <?php endif; ?>
    
    <!-- Actions - TOUJOURS VISIBLES, CIBLES 44px MINIMUM -->
    <div class="flex gap-1 flex-shrink-0" onclick="event.stopPropagation()">
        <!-- Bouton Like : TOUJOURS visible, 44x44px minimum -->
        <button class="like-btn text-text-sub hover:text-red-500 p-2.5 md:p-3 transition-colors rounded-full hover:bg-bg-surface active:scale-95 min-w-[44px] min-h-[44px] flex items-center justify-center" 
                onclick="toggleLike(<?= e($track->id) ?>, this)" 
                data-song-id="<?= e($track->id) ?>"
                title="Ajouter aux favoris">
            <i class="far fa-heart text-lg md:text-xl"></i>
        </button>
        <!-- Menu Options : TOUJOURS visible, 44x44px minimum -->
        <button class="text-text-sub hover:text-text-main p-2.5 md:p-3 transition-colors rounded-full hover:bg-bg-surface active:scale-95 min-w-[44px] min-h-[44px] flex items-center justify-center" 
                data-song-id="<?= e($track->id) ?>"
                data-song-title="<?= htmlspecialchars($track->title, ENT_QUOTES, 'UTF-8') ?>"
                data-song-artist="<?= htmlspecialchars($track->artist ?? '', ENT_QUOTES, 'UTF-8') ?>"
                onclick="showSongOptions(this.dataset.songId, this.dataset.songTitle, this.dataset.songArtist)" 
                title="Plus d'options">
            <i class="fas fa-ellipsis-v text-lg md:text-xl"></i>
        </button>
    </div>
</div>
