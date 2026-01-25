<div class="playlist-detail-page">
    <div class="playlist-header">
        <div class="playlist-cover-large">
            <i class="fas fa-list-music"></i>
        </div>
        <div class="playlist-header-info">
            <h1><?= e($playlist->name) ?></h1>
            <?php if (!empty($playlist->description)): ?>
                <p><?= e($playlist->description) ?></p>
            <?php endif; ?>
            <div class="playlist-stats">
                <?= count($songs) ?> songs
            </div>
        </div>
    </div>

    <div class="playlist-actions">
        <button class="btn btn-primary" onclick="playAll()">
            <i class="fas fa-play"></i> Play All
        </button>
        <button class="btn btn-secondary" onclick="showAddSongsModal()">
            <i class="fas fa-plus"></i> Add Songs
        </button>
    </div>

    <?php if (!empty($songs)): ?>
        <div class="songs-list">
            <?php foreach ($songs as $song): ?>
                <div class="song-item" data-song-id="<?= e($song->id) ?>">
                    <div class="song-number"><?= e($song->position) ?></div>
                    <div class="song-cover">
                        <img src="<?= e($song->cover_path ?? asset('images/default-cover.svg')) ?>" alt="Cover">
                    </div>
                    <div class="song-info">
                        <div class="song-title"><?= e($song->title) ?></div>
                        <div class="song-artist"><?= e($song->artist) ?></div>
                    </div>
                    <div class="song-duration">
                        <?= gmdate("i:s", $song->duration ?? 0) ?>
                    </div>
                    <div class="song-actions">
                        <button onclick="Player.play(<?= e($song->id) ?>)" class="action-btn">
                            <i class="fas fa-play"></i>
                        </button>
                        <button onclick="removeSongFromPlaylist(<?= e($song->id) ?>)" class="action-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-music fa-3x"></i>
            <h2>This playlist is empty</h2>
            <p>Add songs to start building your playlist</p>
            <button class="btn btn-primary" onclick="showAddSongsModal()">
                <i class="fas fa-plus"></i> Add Songs
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
const playlistId = <?= e($playlist->id) ?>;

async function removeSongFromPlaylist(songId) {
    if (!confirm('Remove this song from the playlist?')) return;
    
    try {
        const response = await fetch(`/api/playlists/${playlistId}/remove-song`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'song_id=' + songId
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Error removing song');
        }
    } catch (error) {
        alert('An error occurred');
        console.error(error);
    }
}

function playAll() {
    // This will be implemented in the player.js
    const songIds = <?= json_encode(array_column($songs, 'id')) ?>;
    Player.playQueue(songIds);
}
</script>
