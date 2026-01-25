<?php
/**
 * Playlist card component
 * @var object $playlist - Playlist object
 */
?>
<a href="/playlists/<?= e($playlist->id) ?>" class="block bg-bg-surface rounded-lg p-5 hover:bg-bg-hover hover:-translate-y-1 transition-all no-underline">
    <div class="w-full aspect-square bg-gradient-to-br from-primary to-green-700 rounded-lg flex items-center justify-center mb-4">
        <i class="fas fa-list-music text-5xl text-white"></i>
    </div>
    
    <h3 class="font-semibold text-base mb-1 truncate"><?= e($playlist->name) ?></h3>
    <?php if (!empty($playlist->description)): ?>
        <p class="text-sm text-text-sub line-clamp-2"><?= e($playlist->description) ?></p>
    <?php endif; ?>
</a>
