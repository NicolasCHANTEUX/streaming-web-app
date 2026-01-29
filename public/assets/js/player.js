// Audio Player Manager - Version Mobile Optimized 🚀

class AudioPlayer {
    constructor() {
        this.audio = document.getElementById('audioPlayer');
        this.playerBar = document.getElementById('playerBar');
        this.playPauseBtn = document.getElementById('playPauseBtn');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.progressSlider = document.getElementById('progressSlider');
        this.volumeSlider = document.getElementById('volumeSlider');
        this.currentTimeEl = document.getElementById('currentTime');
        this.totalTimeEl = document.getElementById('totalTime');
        this.playerTitle = document.getElementById('playerTitle');
        this.playerArtist = document.getElementById('playerArtist');
        this.playerCover = document.getElementById('playerCover');

        this.currentSongId = null;
        this.queue = [];
        this.currentIndex = 0;
        this.isPlaying = false;
        
        // Cache pour la chanson suivante (Pour éviter le délai réseau sur mobile)
        this.nextTrackCache = null;

        this.init();
    }

    init() {
        // Play/Pause button
        this.playPauseBtn.addEventListener('click', () => {
            if (this.isPlaying) {
                this.pause();
            } else {
                this.resume();
            }
        });

        // Previous/Next buttons
        this.prevBtn.addEventListener('click', () => this.previous());
        this.nextBtn.addEventListener('click', () => this.next());

        // Progress slider
        this.progressSlider.addEventListener('input', (e) => {
            if (this.audio.duration) {
                const time = (e.target.value / 100) * this.audio.duration;
                this.audio.currentTime = time;
            }
        });

        // Volume slider (Desktop)
        if (this.volumeSlider) {
            this.volumeSlider.addEventListener('input', (e) => {
                this.audio.volume = e.target.value / 100;
                localStorage.setItem('volume', e.target.value);
            });
        }

        // Audio events
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('ended', () => this.next());
        this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
        this.audio.addEventListener('play', () => this.onPlay());
        this.audio.addEventListener('pause', () => this.onPause());

        // Load saved volume
        const savedVolume = localStorage.getItem('volume') || 80;
        if (this.volumeSlider) this.volumeSlider.value = savedVolume;
        this.audio.volume = savedVolume / 100;

        // Initialize MediaSession API
        this.initMediaSession();
    }

    initMediaSession() {
        if ('mediaSession' in navigator) {
            // Handlers
            navigator.mediaSession.setActionHandler('play', () => this.resume());
            navigator.mediaSession.setActionHandler('pause', () => this.pause());
            navigator.mediaSession.setActionHandler('previoustrack', () => this.previous());
            navigator.mediaSession.setActionHandler('nexttrack', () => this.next());
            
            // Seek
            navigator.mediaSession.setActionHandler('seekbackward', (details) => {
                this.audio.currentTime = Math.max(this.audio.currentTime - (details.seekOffset || 10), 0);
            });
            navigator.mediaSession.setActionHandler('seekforward', (details) => {
                this.audio.currentTime = Math.min(this.audio.currentTime + (details.seekOffset || 10), this.audio.duration);
            });
        }
    }

    updateMediaSession(song) {
        if ('mediaSession' in navigator) {
            const coverUrl = song.cover_path ? new URL(song.cover_path, window.location.origin).href : null;
            
            // Détection du type d'image pour éviter les bugs iOS
            let type = 'image/jpeg';
            if (coverUrl && coverUrl.endsWith('.svg')) type = 'image/svg+xml';
            if (coverUrl && coverUrl.endsWith('.png')) type = 'image/png';

            navigator.mediaSession.metadata = new MediaMetadata({
                title: song.title || 'Unknown Title',
                artist: song.artist || 'Unknown Artist',
                artwork: [
                    {
                        src: coverUrl || window.location.origin + '/assets/images/default-cover.svg',
                        sizes: '512x512',
                        type: type
                    }
                ]
            });
        }
    }

    async fetchSongData(songId) {
        try {
            const response = await fetch(`/api/music/${songId}`);
            const data = await response.json();
            return data.success ? data.song : null;
        } catch (e) {
            console.error("Fetch error:", e);
            return null;
        }
    }

    // Pré-charge les infos de la piste suivante pour un switch instantané
    async preloadNextTrack() {
        if (this.queue.length === 0) return;
        
        let nextIndex = this.currentIndex + 1;
        if (nextIndex >= this.queue.length) nextIndex = 0; // Loop

        const nextId = this.queue[nextIndex];
        // On ne recharge pas si c'est déjà en cache
        if (this.nextTrackCache && this.nextTrackCache.id === nextId) return;

        const song = await this.fetchSongData(nextId);
        if (song) {
            this.nextTrackCache = { id: nextId, data: song };
            console.log("📦 Next track preloaded:", song.title);
        }
    }

    loadSongToPlayer(song) {
        // Update UI
        this.playerTitle.textContent = song.title;
        this.playerArtist.textContent = song.artist || 'Unknown Artist';
        this.playerCover.src = song.cover_path || '/assets/images/default-cover.svg';

        // Update System (Lock Screen)
        this.updateMediaSession(song);

        // Check like status
        if (typeof checkLikeStatus === 'function') checkLikeStatus(song.id);

        // Load Audio
        this.audio.src = `/stream/${song.id}`;
        this.currentSongId = song.id;

        // Show Bar
        this.playerBar.classList.remove('hidden');
        this.playerBar.style.display = 'flex';
        this.adjustLayoutForPlayer(true);
    }

    async play(songId, songData = null) {
        try {
            // Synchro Queue Index
            const queueIndex = this.queue.indexOf(String(songId));
            if (queueIndex !== -1) this.currentIndex = queueIndex;

            let song = songData;

            // Si on n'a pas les données, on va les chercher
            if (!song) {
                // Est-ce qu'on les a en cache ?
                if (this.nextTrackCache && String(this.nextTrackCache.id) === String(songId)) {
                    song = this.nextTrackCache.data;
                    console.log("⚡ Using preloaded data for:", song.title);
                } else {
                    song = await this.fetchSongData(songId);
                }
            }

            if (!song) return;

            this.loadSongToPlayer(song);

            // Play
            await this.audio.play();
            this.isPlaying = true;

            // Une fois que ça joue, on prépare la SUIVANTE
            this.preloadNextTrack();

        } catch (error) {
            console.error('Error playing song:', error);
        }
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
        if ('mediaSession' in navigator) navigator.mediaSession.playbackState = "paused";
    }

    resume() {
        if (this.audio.src) {
            this.audio.play();
            this.isPlaying = true;
            if ('mediaSession' in navigator) navigator.mediaSession.playbackState = "playing";
        }
    }

    previous() {
        if (this.queue.length === 0) return;
        
        if (this.audio.currentTime > 3) {
            this.audio.currentTime = 0;
            return;
        }

        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else {
            this.currentIndex = this.queue.length - 1;
        }
        
        // Pour previous, on n'a souvent pas de cache, donc fetch classique
        this.play(this.queue[this.currentIndex]);
    }

    next() {
        if (this.queue.length === 0) return;
        
        if (this.currentIndex < this.queue.length - 1) {
            this.currentIndex++;
        } else {
            this.currentIndex = 0;
        }
        
        // C'est ici que la magie opère : si on a préchargé, play() sera instantané
        // et le téléphone ne bloquera pas l'audio !
        this.play(this.queue[this.currentIndex]);
    }

    playQueue(songIds, startIndex = 0) {
        this.queue = songIds.map(String);
        this.currentIndex = startIndex;
        this.play(this.queue[this.currentIndex]);
    }

    updateProgress() {
        if (this.audio.duration) {
            const progress = (this.audio.currentTime / this.audio.duration) * 100;
            this.progressSlider.value = progress;
            this.currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
        }
    }

    updateDuration() {
        if (this.audio.duration) {
            this.totalTimeEl.textContent = this.formatTime(this.audio.duration);
        }
    }

    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    onPlay() {
        this.playPauseBtn.innerHTML = '<i class="fas fa-pause text-sm sm:text-base"></i>';
        if ('mediaSession' in navigator) navigator.mediaSession.playbackState = "playing";
    }

    onPause() {
        this.playPauseBtn.innerHTML = '<i class="fas fa-play text-sm sm:text-base"></i>';
        if ('mediaSession' in navigator) navigator.mediaSession.playbackState = "paused";
    }

    adjustLayoutForPlayer(isPlayerVisible) {
        const bottomNav = document.getElementById('bottomNav');
        const mainContent = document.getElementById('mainContent');
        
        if (isPlayerVisible && bottomNav) {
            bottomNav.style.bottom = '90px';
        }
        if (mainContent) {
            mainContent.style.paddingBottom = isPlayerVisible ? '150px' : '60px';
        }
    }
}

// Initialize player
const Player = new AudioPlayer();

// Global helpers
function playTrack(songId) {
    Player.play(songId);
}

function playQueue(songIds, startIndex = 0) {
    Player.playQueue(songIds, startIndex);
}
