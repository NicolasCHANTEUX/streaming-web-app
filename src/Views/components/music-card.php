<?php
/**
 * Music card component (track row style)
 * @var object $track - Song object with id, title, artist, cover_path, duration
 * @var int $index - Optional track number
 * @var bool $showIndex - Whether to show track number instead of cover
 */
?>
<div class="group flex items-center px-4 sm:px-5 py-3 sm:py-3.5 rounded-lg hover:bg-bg-card transition-all cursor-pointer border border-transparent hover:border-border-main/50" 
     data-song-id="<?= e($track->id) ?>"
     onclick="Player.play(<?= e($track->id) ?>)">
    
    <!-- Index or Play button -->
    <?php if (isset($showIndex) && $showIndex): ?>
        <div class="w-7 sm:w-8 mr-4 sm:mr-5 text-center flex-shrink-0">
            <span class="group-hover:hidden text-text-sub font-medium text-sm sm:text-base"><?= $index ?></span>
            <i class="fas fa-play hidden group-hover:inline-block text-text-main text-xs sm:text-sm"></i>
        </div>
    <?php endif; ?>
    
    <!-- Cover - Carré avec beaux angles arrondis -->
    <img 
        src="<?= e($track->cover_path ?? asset('images/default-cover.svg')) ?>" 
        alt="<?= e($track->title) ?>"
        class="w-11 h-11 sm:w-12 sm:h-12 rounded-lg object-cover mr-4 sm:mr-5 flex-shrink-0 shadow-sm"
    >
    
    <!-- Title & Artist - Plus d'espace pour le titre sur mobile -->
    <div class="flex-1 min-w-0 mr-3 sm:mr-4">
        <div class="text-sm sm:text-base font-medium mb-0.5 line-clamp-1 text-text-main group-hover:text-primary transition-colors <?= isset($isPlaying) && $isPlaying ? 'text-primary' : '' ?>">
            <?= e($track->title) ?>
        </div>
        <div class="text-xs sm:text-sm text-text-sub truncate">
            <?= e($track->artist ?? 'Unknown Artist') ?>
        </div>
    </div>
    
    <!-- Duration -->
    <?php if (isset($track->duration) && $track->duration > 0): ?>
        <div class="text-sm text-text-sub mr-4 sm:mr-6 flex-shrink-0 hidden sm:block">
            <?= gmdate("i:s", $track->duration) ?>
        </div>
    <?php endif; ?>
    
    <!-- Actions - Toujours visibles sur mobile avec bon espacement -->
    <div class="flex gap-2 sm:gap-2.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
        <button class="like-btn text-text-sub hover:text-primary p-2 transition-colors rounded-full hover:bg-bg-surface" 
                onclick="toggleLike(<?= e($track->id) ?>, this)" 
                data-song-id="<?= e($track->id) ?>"
                title="Ajouter aux favoris">
            <i class="far fa-heart text-base sm:text-lg"></i>
        </button>
        <button class="text-text-sub hover:text-text-main p-2 transition-colors rounded-full hover:bg-bg-surface" 
                data-song-id="<?= e($track->id) ?>"
                data-song-title="<?= htmlspecialchars($track->title, ENT_QUOTES, 'UTF-8') ?>"
                data-song-artist="<?= htmlspecialchars($track->artist ?? '', ENT_QUOTES, 'UTF-8') ?>"
                onclick="showSongOptions(this.dataset.songId, this.dataset.songTitle, this.dataset.songArtist)" 
                title="Plus d'options">
            <i class="fas fa-ellipsis-v text-base sm:text-lg"></i>
        </button>
    </div>
</div>
