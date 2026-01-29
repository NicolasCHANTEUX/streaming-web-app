// Audio Player Manager

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
            const time = (e.target.value / 100) * this.audio.duration;
            this.audio.currentTime = time;
        });

        // Volume slider
        this.volumeSlider.addEventListener('input', (e) => {
            this.audio.volume = e.target.value / 100;
            localStorage.setItem('volume', e.target.value);
        });

        // Audio events
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('ended', () => this.next());
        this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
        this.audio.addEventListener('play', () => this.onPlay());
        this.audio.addEventListener('pause', () => this.onPause());

        // Load saved volume
        const savedVolume = localStorage.getItem('volume') || 80;
        this.volumeSlider.value = savedVolume;
        this.audio.volume = savedVolume / 100;

        // Initialize MediaSession API for mobile controls
        this.initMediaSession();
    }

    initMediaSession() {
        if ('mediaSession' in navigator) {
            // Set up action handlers for mobile lock screen controls
            navigator.mediaSession.setActionHandler('play', () => {
                this.resume();
            });

            navigator.mediaSession.setActionHandler('pause', () => {
                this.pause();
            });

            navigator.mediaSession.setActionHandler('previoustrack', () => {
                this.previous();
            });

            navigator.mediaSession.setActionHandler('nexttrack', () => {
                this.next();
            });

            navigator.mediaSession.setActionHandler('seekbackward', (details) => {
                const skipTime = details.seekOffset || 10;
                this.audio.currentTime = Math.max(this.audio.currentTime - skipTime, 0);
            });

            navigator.mediaSession.setActionHandler('seekforward', (details) => {
                const skipTime = details.seekOffset || 10;
                this.audio.currentTime = Math.min(this.audio.currentTime + skipTime, this.audio.duration);
            });

            navigator.mediaSession.setActionHandler('seekto', (details) => {
                if (details.fastSeek && 'fastSeek' in this.audio) {
                    this.audio.fastSeek(details.seekTime);
                } else {
                    this.audio.currentTime = details.seekTime;
                }
                this.updateProgress();
            });
        }
    }

    updateMediaSession(song) {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: song.title || 'Unknown Title',
                artist: song.artist || 'Unknown Artist',
                album: song.album || 'Unknown Album',
                artwork: [
                    {
                        src: song.cover_path || '/assets/images/default-cover.svg',
                        sizes: '512x512',
                        type: 'image/jpeg'
                    }
                ]
            });
        }
    }

    async play(songId) {
        try {
            // Fetch song data
            const response = await fetch(`/api/music/${songId}`);
            const data = await response.json();

            if (!data.success) {
                console.error('Failed to load song');
                return;
            }

            const song = data.song;

            // Update player info
            this.playerTitle.textContent = song.title;
            this.playerArtist.textContent = song.artist || 'Unknown Artist';
            this.playerCover.src = song.cover_path || '/assets/images/default-cover.svg';

            // Update MediaSession metadata for mobile lock screen
            this.updateMediaSession(song);

            // Check like status
            if (typeof checkLikeStatus === 'function') {
                checkLikeStatus(songId);
            }

            // Load audio
            this.audio.src = `/stream/${songId}`;
            this.currentSongId = songId;

            // Show player bar
            this.playerBar.style.display = 'flex';
            this.playerBar.classList.remove('hidden');
            
            // Adjust navigation and main content when player is visible
            this.adjustLayoutForPlayer(true);

            // Play
            await this.audio.play();
            this.isPlaying = true;

        } catch (error) {
            console.error('Error playing song:', error);
        }
    }

    pause() {
        this.audio.pause();
        this.isPlaying = false;
    }

    resume() {
        if (this.audio.src) {
            this.audio.play();
            this.isPlaying = true;
        }
    }

    previous() {
        if (this.queue.length > 0 && this.currentIndex > 0) {
            this.currentIndex--;
            this.play(this.queue[this.currentIndex]);
        }
    }

    next() {
        if (this.queue.length > 0 && this.currentIndex < this.queue.length - 1) {
            this.currentIndex++;
            this.play(this.queue[this.currentIndex]);
        }
    }

    playQueue(songIds, startIndex = 0) {
        this.queue = songIds;
        this.currentIndex = startIndex;
        this.play(songIds[startIndex]);
    }

    updateProgress() {
        if (this.audio.duration) {
            const progress = (this.audio.currentTime / this.audio.duration) * 100;
            this.progressSlider.value = progress;

            // Update time display
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
        this.playPauseBtn.querySelector('i').className = 'fas fa-pause';
    }

    onPause() {
        this.playPauseBtn.querySelector('i').className = 'fas fa-play';
    }

    adjustLayoutForPlayer(isPlayerVisible) {
        const bottomNav = document.getElementById('bottomNav');
        const mainContent = document.getElementById('mainContent');
        
        if (isPlayerVisible) {
            // Player visible: move nav up by 90px (player height)
            bottomNav.style.bottom = '90px';
            // Add padding to main content: 60px (nav) + 90px (player) = 150px
            mainContent.style.paddingBottom = '150px';
        } else {
            // Player hidden: nav at bottom
            bottomNav.style.bottom = '0';
            // Only nav padding: 60px
            mainContent.style.paddingBottom = '60px';
        }
    }
}

// Initialize player
const Player = new AudioPlayer();

// Global play function for inline onclick
function playTrack(songId) {
    Player.play(songId);
}
