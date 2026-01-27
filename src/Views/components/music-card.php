<?php
/**
 * Music card component (track row style)
 * @var object $track - Song object with id, title, artist, cover_path, duration
 */
?>
<div class="flex items-center px-4 py-2.5 active:bg-border-main transition-colors" data-song-id="<?= e($track->id) ?>">
    <img 
        src="<?= e($track->cover_path ?? asset('images/default-cover.svg')) ?>" 
        alt="<?= e($track->title) ?>"
        class="w-12 h-12 rounded object-cover mr-3 cursor-pointer"
        onclick="Player.play(<?= e($track->id) ?>)"
    >
    
    <div class="flex-1 min-w-0 cursor-pointer" onclick="Player.play(<?= e($track->id) ?>)">
        <div class="text-base mb-1 truncate <?= isset($isPlaying) && $isPlaying ? 'text-primary' : '' ?>">
            <?= e($track->title) ?>
        </div>
        <div class="text-sm text-text-sub truncate">
            <?= e($track->artist ?? 'Unknown Artist') ?>
            <?php if (isset($track->duration) && $track->duration > 0): ?>
                <span class="mx-1">•</span>
                <span><?= gmdate("i:s", $track->duration) ?></span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="flex gap-2 ml-2">
        <button class="like-btn text-text-sub hover:text-primary p-2.5 transition-colors" 
                onclick="toggleLike(<?= e($track->id) ?>, this)" 
                data-song-id="<?= e($track->id) ?>"
                title="Ajouter aux favoris">
            <i class="far fa-heart"></i>
        </button>
        <button class="text-text-sub hover:text-text-main p-2.5 transition-colors" 
                onclick="showPlaylistModal(<?= e($track->id) ?>)" 
                title="Ajouter à une playlist">
            <i class="fas fa-plus"></i>
        </button>
        <button class="text-text-sub hover:text-text-main p-2.5 transition-colors" 
                onclick="showOptions(<?= e($track->id) ?>)" 
                title="Plus d'options">
            <i class="fas fa-ellipsis-v"></i>
        </button>
    </div>
</div>
