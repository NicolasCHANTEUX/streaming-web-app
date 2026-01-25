<div>
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Music Library</h1>
        <button onclick="scanLibrary()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-transparent text-text-main border border-border-main font-semibold hover:border-text-main transition-colors">
            <i class="fas fa-sync"></i> Scan Directory
        </button>
    </div>

    <?php if (!empty($songs)): ?>
        <div class="bg-bg-surface rounded-lg overflow-hidden">
            <?php foreach ($songs as $song): ?>
                <?php component('music-card', ['track' => $song]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 text-text-sub">
            <i class="fas fa-folder-open text-5xl mb-5 text-border-main"></i>
            <h2 class="text-text-main text-2xl mb-2.5">No music found</h2>
            <p class="mb-5">Add music to your library to get started</p>
            <a href="/search" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">Add Music</a>
        </div>
    <?php endif; ?>
</div>
