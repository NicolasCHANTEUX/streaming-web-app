<?php
/**
 * Music card component for playlists (with remove button)
 * @var object $track - Song object with id, title, artist, cover_path, duration
 * @var int $index - Optional track number
 * @var bool $showIndex - Whether to show track number instead of cover
 * @var int $playlistId - The playlist ID for removal
 */
?>
<div class="group flex items-center px-4 py-3 rounded-lg hover:bg-bg-card transition-all cursor-pointer" 
     data-song-id="<?= e($track->id) ?>"
     onclick="Player.play(<?= e($track->id) ?>)">
    
    <!-- Index or Play button -->
    <?php if (isset($showIndex) && $showIndex): ?>
        <div class="w-8 mr-4 text-center flex-shrink-0">
            <span class="group-hover:hidden text-text-sub font-medium"><?= $index ?></span>
            <i class="fas fa-play hidden group-hover:inline-block text-text-main text-sm"></i>
        </div>
    <?php endif; ?>
    
    <!-- Cover (smaller, more music-list style) -->
    <img 
        src="<?= e($track->cover_path ?? asset('images/default-cover.svg')) ?>" 
        alt="<?= e($track->title) ?>"
        class="w-10 h-10 rounded object-cover mr-4 flex-shrink-0 shadow-md"
    >
    
    <!-- Title & Artist -->
    <div class="flex-1 min-w-0 mr-4">
        <div class="text-base font-medium mb-0.5 truncate text-text-main group-hover:text-primary transition-colors <?= isset($isPlaying) && $isPlaying ? 'text-primary' : '' ?>">
            <?= e($track->title) ?>
        </div>
        <div class="text-sm text-text-sub truncate">
            <?= e($track->artist ?? 'Unknown Artist') ?>
        </div>
    </div>
    
    <!-- Duration -->
    <?php if (isset($track->duration) && $track->duration > 0): ?>
        <div class="text-sm text-text-sub mr-6 flex-shrink-0 hidden sm:block">
            <?= gmdate("i:s", $track->duration) ?>
        </div>
    <?php endif; ?>
    
    <!-- Actions (compact, visible on hover) -->
    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
        <button class="like-btn text-text-sub hover:text-primary p-2 transition-colors rounded-full hover:bg-bg-surface" 
                onclick="toggleLike(<?= e($track->id) ?>, this)" 
                data-song-id="<?= e($track->id) ?>"
                title="Ajouter aux favoris">
            <i class="far fa-heart text-base"></i>
        </button>
        <button class="text-text-sub hover:text-red-500 p-2 transition-colors rounded-full hover:bg-bg-surface" 
                onclick="removeSongFromPlaylist(<?= e($track->id) ?>)" 
                title="Retirer de la playlist">
            <i class="fas fa-trash text-base"></i>
        </button>
        <button class="text-text-sub hover:text-text-main p-2 transition-colors rounded-full hover:bg-bg-surface" 
                onclick="showSongOptions(<?= e($track->id) ?>, '<?= e($track->title) ?>', '<?= e($track->artist ?? '') ?>')" 
                title="Plus d'options">
            <i class="fas fa-ellipsis-v text-base"></i>
        </button>
    </div>
</div>
