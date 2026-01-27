// Download Queue Management
const downloadQueue = [];
let isProcessing = false;

class DownloadItem {
    constructor(videoId, title, customTitle = '') {
        this.id = Date.now() + Math.random();
        this.videoId = videoId;
        this.title = title;
        this.customTitle = customTitle;
        this.status = 'waiting'; // waiting, downloading, completed, error
        this.progress = 0;
        this.error = null;
    }
}

function addToDownloadQueue(videoId, title, customTitle = '') {
    const item = new DownloadItem(videoId, title, customTitle);
    downloadQueue.push(item);
    
    updateDownloadQueueUI();
    showDownloadQueue();
    
    if (!isProcessing) {
        processQueue();
    }
    
    return item;
}

function updateDownloadQueueUI() {
    const container = document.getElementById('downloadQueueItems');
    
    if (downloadQueue.length === 0) {
        container.innerHTML = '<p class="text-text-sub text-center">Aucun téléchargement</p>';
        return;
    }
    
    container.innerHTML = downloadQueue.map(item => `
        <div class="bg-bg-card rounded-lg p-3 space-y-2" data-download-id="${item.id}">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate">${item.title}</div>
                    <div class="text-xs text-text-sub">${getStatusText(item.status)}</div>
                </div>
                ${getStatusIcon(item)}
            </div>
            ${item.status === 'downloading' ? `
                <div class="relative w-full h-1.5 bg-bg-main rounded-full overflow-hidden">
                    <div class="absolute inset-y-0 left-0 bg-primary transition-all duration-300" 
                         style="width: ${item.progress}%"></div>
                </div>
            ` : ''}
            ${item.error ? `
                <div class="text-xs text-red-400">${item.error}</div>
            ` : ''}
        </div>
    `).join('');
}

function getStatusIcon(item) {
    switch (item.status) {
        case 'waiting':
            return '<div class="flex-shrink-0 w-5 h-5 border-2 border-text-sub border-t-transparent rounded-full animate-spin"></div>';
        case 'downloading':
            return '<div class="flex-shrink-0 w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>';
        case 'completed':
            return '<i class="fas fa-check-circle text-green-500"></i>';
        case 'error':
            return '<i class="fas fa-exclamation-circle text-red-500"></i>';
        default:
            return '';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'waiting': return 'En attente...';
        case 'downloading': return 'Téléchargement en cours...';
        case 'completed': return 'Terminé';
        case 'error': return 'Erreur';
        default: return '';
    }
}

async function processQueue() {
    if (isProcessing || downloadQueue.length === 0) {
        return;
    }
    
    isProcessing = true;
    
    while (downloadQueue.length > 0) {
        const item = downloadQueue.find(i => i.status === 'waiting');
        
        if (!item) break;
        
        item.status = 'downloading';
        item.progress = 0;
        updateDownloadQueueUI();
        
        try {
            // Simulate progress updates
            const progressInterval = setInterval(() => {
                if (item.progress < 90) {
                    item.progress += Math.random() * 15;
                    updateDownloadQueueUI();
                }
            }, 500);
            
            const response = await fetch('/api/youtube/download', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `video_id=${encodeURIComponent(item.videoId)}&custom_title=${encodeURIComponent(item.customTitle)}`
            });
            
            clearInterval(progressInterval);
            
            const result = await response.json();
            
            if (result.success) {
                item.status = 'completed';
                item.progress = 100;
                
                // Remove from queue after 3 seconds
                setTimeout(() => {
                    const index = downloadQueue.indexOf(item);
                    if (index > -1) {
                        downloadQueue.splice(index, 1);
                        updateDownloadQueueUI();
                    }
                }, 3000);
                
                showNotification('Téléchargement terminé !', 'success');
            } else {
                throw new Error(result.error || 'Download failed');
            }
        } catch (error) {
            console.error('Download error:', error);
            item.status = 'error';
            item.error = error.message;
            showNotification('Erreur de téléchargement', 'error');
        }
        
        updateDownloadQueueUI();
    }
    
    isProcessing = false;
}

function showDownloadQueue() {
    const panel = document.getElementById('downloadQueue');
    panel.classList.remove('hidden');
    setTimeout(() => {
        panel.style.transform = 'translateX(0)';
    }, 10);
}

function toggleDownloadQueue() {
    const panel = document.getElementById('downloadQueue');
    
    if (panel.style.transform === 'translateX(0px)' || panel.style.transform === '') {
        panel.style.transform = 'translateX(100%)';
        setTimeout(() => panel.classList.add('hidden'), 300);
    } else {
        panel.classList.remove('hidden');
        setTimeout(() => {
            panel.style.transform = 'translateX(0)';
        }, 10);
    }
}

// Expose functions globally
window.addToDownloadQueue = addToDownloadQueue;
window.toggleDownloadQueue = toggleDownloadQueue;
