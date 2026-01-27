<div>
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Search YouTube</h1>
        <p class="text-text-sub">Find and download music from YouTube</p>
    </div>

    <div class="mb-6">
        <form id="youtubeSearchForm" onsubmit="searchYoutube(event)" class="max-w-2xl">
            <div class="flex gap-2">
                <input 
                    type="text" 
                    id="youtubeQuery" 
                    name="q" 
                    placeholder="Search for songs, artists, albums..." 
                    required
                    class="flex-1 px-5 py-3 rounded-lg bg-bg-surface text-text-main outline-none focus:ring-2 focus:ring-primary transition-all"
                >
                <button type="submit" class="px-6 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <div id="searchResults" class="space-y-3"></div>
    
    <div id="downloadStatus" class="mt-6 hidden"></div>
</div>

<script>
async function searchYoutube(event) {
    event.preventDefault();
    
    const query = document.getElementById('youtubeQuery').value;
    const resultsDiv = document.getElementById('searchResults');
    
    // Show loading
    resultsDiv.innerHTML = '<div class="flex flex-col items-center justify-center py-10"><div class="w-12 h-12 border-4 border-border-main border-t-primary rounded-full animate-spin"></div><p class="mt-4 text-text-sub">Searching YouTube...</p></div>';
    
    try {
        const response = await fetch('/api/youtube/search?q=' + encodeURIComponent(query));
        const data = await response.json();
        
        if (data.success && data.results.length > 0) {
            resultsDiv.innerHTML = '';
            data.results.forEach(result => {
                resultsDiv.innerHTML += createYoutubeCard(result);
            });
        } else {
            resultsDiv.innerHTML = '<div class="text-center py-16 text-text-sub"><p>No results found</p></div>';
        }
    } catch (error) {
        resultsDiv.innerHTML = '<div class="p-4 rounded-lg bg-red-500/20 text-red-500 border border-red-500">An error occurred while searching</div>';
        console.error(error);
    }
}

function createYoutubeCard(result) {
    const duration = new Date(result.duration * 1000).toISOString().substr(14, 5);
    return `
        <div class="flex gap-4 bg-bg-surface rounded-lg p-4 items-center hover:bg-bg-hover transition-colors">
            <div class="relative w-40 h-24 flex-shrink-0 rounded overflow-hidden">
                <img src="${result.thumbnail}" alt="${escapeHtml(result.title)}" class="w-full h-full object-cover">
                <span class="absolute bottom-1 right-1 bg-black/80 text-white px-1.5 py-0.5 rounded text-xs">${duration}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-base mb-1 line-clamp-2">${escapeHtml(result.title)}</h3>
                <p class="text-sm text-text-sub">${escapeHtml(result.channel)}</p>
            </div>
            <div class="flex-shrink-0">
                <button onclick="downloadVideo('${result.id}', '${escapeHtml(result.title).replace(/'/g, "\\'")}')\" 
                        class="download-btn px-4 py-2 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors inline-flex items-center gap-2"
                        data-video-id="${result.id}">
                    <i class="fas fa-download"></i> 
                    <span class="btn-text">Download</span>
                    <div class="hidden spinner w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
            </div>
        </div>
    `;
}

async function downloadVideo(videoId, title) {
    // Get the button
    const button = document.querySelector(`.download-btn[data-video-id="${videoId}"]`);
    if (!button) return;
    
    // Show spinner
    button.disabled = true;
    button.querySelector('.btn-text').textContent = 'Downloading';
    button.querySelector('.spinner').classList.remove('hidden');
    
    // Add to download queue
    addToDownloadQueue(videoId, title);
    
    // Reset button after a short delay
    setTimeout(() => {
        button.disabled = false;
        button.querySelector('.btn-text').textContent = 'Download';
        button.querySelector('.spinner').classList.add('hidden');
    }, 1000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
