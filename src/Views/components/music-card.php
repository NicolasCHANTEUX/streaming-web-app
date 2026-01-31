<?php
/**
 * Music card component (track row style)
 * @var object $track - Song object with id, title, artist, cover_path, duration
 * @var int $index - Optional track number
 * @var bool $showIndex - Whether to show track number instead of cover
 */
?>
<div class="group flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg hover:bg-bg-card transition-all cursor-pointer" 
     data-song-id="<?= e($track->id) ?>"
     onclick="Player.play(<?= e($track->id) ?>)">
    
    <!-- Index or Play button -->
    <?php if (isset($showIndex) && $showIndex): ?>
        <div class="w-6 sm:w-8 mr-2 sm:mr-4 text-center flex-shrink-0">
            <span class="group-hover:hidden text-text-sub font-medium text-xs sm:text-base"><?= $index ?></span>
            <i class="fas fa-play hidden group-hover:inline-block text-text-main text-xs sm:text-sm"></i>
        </div>
    <?php endif; ?>
    
    <!-- Cover - Plus petit sur mobile -->
    <img 
        src="<?= e($track->cover_path ?? asset('images/default-cover.svg')) ?>" 
        alt="<?= e($track->title) ?>"
        class="w-9 h-9 sm:w-10 sm:h-10 rounded object-cover mr-2 sm:mr-4 flex-shrink-0 shadow-md"
    >
    
    <!-- Title & Artist - Plus d'espace pour le titre sur mobile -->
    <div class="flex-1 min-w-0 mr-2 sm:mr-4">
        <div class="text-sm sm:text-base font-medium mb-0.5 line-clamp-1 text-text-main group-hover:text-primary transition-colors <?= isset($isPlaying) && $isPlaying ? 'text-primary' : '' ?>">
            <?= e($track->title) ?>
        </div>
        <div class="text-xs sm:text-sm text-text-sub truncate">
            <?= e($track->artist ?? 'Unknown Artist') ?>
        </div>
    </div>
    
    <!-- Duration -->
    <?php if (isset($track->duration) && $track->duration > 0): ?>
        <div class="text-sm text-text-sub mr-2 sm:mr-6 flex-shrink-0 hidden sm:block">
            <?= gmdate("i:s", $track->duration) ?>
        </div>
    <?php endif; ?>
    
    <!-- Actions - Toujours visibles sur mobile, hover sur desktop -->
    <div class="flex gap-0.5 sm:gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
        <button class="like-btn text-text-sub hover:text-primary p-1.5 sm:p-2 transition-colors rounded-full hover:bg-bg-surface" 
                onclick="toggleLike(<?= e($track->id) ?>, this)" 
                data-song-id="<?= e($track->id) ?>"
                title="Ajouter aux favoris">
            <i class="far fa-heart text-sm sm:text-base"></i>
        </button>
        <button class="text-text-sub hover:text-text-main p-1.5 sm:p-2 transition-colors rounded-full hover:bg-bg-surface" 
                data-song-id="<?= e($track->id) ?>"
                data-song-title="<?= htmlspecialchars($track->title, ENT_QUOTES, 'UTF-8') ?>"
                data-song-artist="<?= htmlspecialchars($track->artist ?? '', ENT_QUOTES, 'UTF-8') ?>"
                onclick="showSongOptions(this.dataset.songId, this.dataset.songTitle, this.dataset.songArtist)" 
                title="Plus d'options">
            <i class="fas fa-ellipsis-v text-sm sm:text-base"></i>
        </button>
    </div>
</div>
