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

            // Load audio
            this.audio.src = `/stream/${songId}`;
            this.currentSongId = songId;

            // Show player bar
            this.playerBar.style.display = 'flex';
            this.playerBar.classList.remove('hidden');

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
}

// Initialize player
const Player = new AudioPlayer();

// Global play function for inline onclick
function playTrack(songId) {
    Player.play(songId);
}
