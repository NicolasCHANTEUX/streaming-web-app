<div class="pb-5">
    <section class="text-center py-10">
        <h1 class="text-4xl font-bold mb-2.5">Welcome to Your Music Library</h1>
        <p class="text-text-sub text-lg">Stream your personal collection or discover new music</p>
    </section>

    <?php if (!empty($recentSongs)): ?>
        <section class="mb-10">
            <h2 class="text-2xl font-bold mb-5">Recently Added</h2>
            <div class="bg-bg-surface rounded-lg overflow-hidden">
                <?php foreach ($recentSongs as $song): ?>
                    <?php component('music-card', ['track' => $song]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($playlists)): ?>
        <section>
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-bold">Your Playlists</h2>
                <a href="/playlists" class="text-text-sub text-sm hover:text-text-main transition-colors">See all</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach (array_slice($playlists, 0, 6) as $playlist): ?>
                    <?php component('playlist-card', ['playlist' => $playlist]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($recentSongs) && empty($playlists)): ?>
        <div class="text-center py-16 text-text-sub">
            <i class="fas fa-music text-5xl mb-5 text-border-main"></i>
            <h2 class="text-text-main text-2xl mb-2.5">Your library is empty</h2>
            <p class="mb-5">Start by adding music from YouTube or upload your own files</p>
            <a href="/search" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">
                <i class="fas fa-plus"></i> Add Music
            </a>
        </div>
    <?php endif; ?>
</div>
