<div class="py-8">
    <div class="flex items-center gap-4 mb-8">
        <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center">
            <i class="fas fa-heart text-3xl text-white"></i>
        </div>
        <div>
            <h1 class="text-3xl font-bold mb-1">Titres Likés</h1>
            <p class="text-text-sub"><?= count($songs) ?> titre<?= count($songs) > 1 ? 's' : '' ?></p>
        </div>
    </div>

    <?php if (empty($songs)): ?>
        <div class="text-center py-16">
            <i class="far fa-heart text-6xl text-text-sub mb-4"></i>
            <p class="text-text-sub text-lg">Aucun titre liké pour le moment</p>
            <p class="text-text-sub text-sm mt-2">Commencez à liker vos musiques préférées !</p>
            <a href="/music" class="inline-block mt-6 px-6 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-colors">
                Explorer la bibliothèque
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            <?php foreach ($songs as $song): ?>
                <?php component('music-card', ['song' => $song]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
