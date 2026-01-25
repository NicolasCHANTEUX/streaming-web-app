<div>
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Your Playlists</h1>
        <button onclick="showCreatePlaylistModal()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">
            <i class="fas fa-plus"></i> Create Playlist
        </button>
    </div>

    <?php if (!empty($playlists)): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($playlists as $playlist): ?>
                <?php component('playlist-card', ['playlist' => $playlist]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 text-text-sub">
            <i class="fas fa-list text-5xl mb-5 text-border-main"></i>
            <h2 class="text-text-main text-2xl mb-2.5">No playlists yet</h2>
            <p class="mb-5">Create your first playlist to organize your music</p>
            <button onclick="showCreatePlaylistModal()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">
                <i class="fas fa-plus"></i> Create Playlist
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="createPlaylistModal" class="fixed inset-0 bg-black/80 z-[1000] hidden items-center justify-center">
    <div class="bg-bg-surface p-8 rounded-xl max-w-md w-11/12 relative">
        <span onclick="closeCreatePlaylistModal()" class="absolute top-4 right-5 text-3xl text-text-sub hover:text-text-main cursor-pointer">&times;</span>
        <h2 class="text-2xl font-bold mb-6">Create New Playlist</h2>
        <form id="createPlaylistForm" onsubmit="createPlaylist(event)">
            <div class="mb-5">
                <label for="playlistName" class="block mb-2 text-text-sub text-sm">Name</label>
                <input type="text" id="playlistName" name="name" required class="w-full px-4 py-3 rounded-lg bg-bg-main text-text-main outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div class="mb-5">
                <label for="playlistDescription" class="block mb-2 text-text-sub text-sm">Description (optional)</label>
                <textarea id="playlistDescription" name="description" rows="3" class="w-full px-4 py-3 rounded-lg bg-bg-main text-text-main outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors">Create</button>
        </form>
    </div>
</div>

<script>
function showCreatePlaylistModal() {
    document.getElementById('createPlaylistModal').classList.remove('hidden');
    document.getElementById('createPlaylistModal').classList.add('flex');
}

function closeCreatePlaylistModal() {
    document.getElementById('createPlaylistModal').classList.add('hidden');
    document.getElementById('createPlaylistModal').classList.remove('flex');
    document.getElementById('createPlaylistForm').reset();
}

async function createPlaylist(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
        const response = await fetch('/api/playlists/create', {
            method: 'POST',
            body: new URLSearchParams(formData)
        });
        const data = await response.json();
        if (data.success) {
            window.location.href = '/playlists/' + data.playlist_id;
        } else {
            alert('Error creating playlist: ' + data.error);
        }
    } catch (error) {
        alert('An error occurred');
        console.error(error);
    }
}
</script>
