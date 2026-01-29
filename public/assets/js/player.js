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

        // Initialize MediaSession API for mobile controls
        this.initMediaSession();
    }

    initMediaSession() {
        if ('mediaSession' in navigator) {
            console.log('🎧 Media Session API: Initializing handlers');
            
            // Set up action handlers for mobile lock screen controls
            navigator.mediaSession.setActionHandler('play', () => {
                console.log('🎧 Media Session: Play command');
                this.resume();
            });

            navigator.mediaSession.setActionHandler('pause', () => {
                console.log('🎧 Media Session: Pause command');
                this.pause();
            });

            navigator.mediaSession.setActionHandler('previoustrack', () => {
                console.log('🎧 Media Session: Previous track command');
                this.previous();
            });

            navigator.mediaSession.setActionHandler('nexttrack', () => {
                console.log('🎧 Media Session: Next track command');
                this.next();
            });

            navigator.mediaSession.setActionHandler('seekbackward', (details) => {
                console.log('🎧 Media Session: Seek backward');
                const skipTime = details.seekOffset || 10;
                this.audio.currentTime = Math.max(this.audio.currentTime - skipTime, 0);
            });

            navigator.mediaSession.setActionHandler('seekforward', (details) => {
                console.log('🎧 Media Session: Seek forward');
                const skipTime = details.seekOffset || 10;
                this.audio.currentTime = Math.min(this.audio.currentTime + skipTime, this.audio.duration);
            });

            navigator.mediaSession.setActionHandler('seekto', (details) => {
                console.log('🎧 Media Session: Seek to', details.seekTime);
                if (details.fastSeek && 'fastSeek' in this.audio) {
                    this.audio.fastSeek(details.seekTime);
                } else {
                    this.audio.currentTime = details.seekTime;
                }
                this.updateProgress();
            });
            
            console.log('✅ Media Session API: All handlers registered');
        } else {
            console.warn('⚠️ Media Session API not supported in this browser');
        }
    }

    updateMediaSession(song) {
        if ('mediaSession' in navigator) {
            // CORRECTION: On force l'URL absolue pour l'image (nécessaire pour iOS/Android)
            const coverUrl = song.cover_path ? new URL(song.cover_path, window.location.origin).href : null;
            
            navigator.mediaSession.metadata = new MediaMetadata({
                title: song.title || 'Unknown Title',
                artist: song.artist || 'Unknown Artist',
                artwork: [
                    {
                        src: coverUrl || window.location.origin + '/assets/images/default-cover.svg',
                        sizes: '512x512',
                        type: 'image/jpeg'
                    }
                ]
            });
            
            console.log('🎵 Media Session metadata updated:', song.title, 'by', song.artist);
        }
    }

    async play(songId) {
        try {
            console.log('▶️ Playing song ID:', songId, 'Queue:', this.queue, 'Current index:', this.currentIndex);
            
            // CORRECTION: Synchroniser l'index si la chanson est dans la file d'attente
            // Cela permet à next/previous de savoir où on est
            const queueIndex = this.queue.indexOf(String(songId));
            if (queueIndex !== -1) {
                this.currentIndex = queueIndex;
                console.log('✅ Song found in queue at index:', queueIndex);
            } else {
                console.log('ℹ️ Song not in queue, playing standalone');
            }
            
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
        // On met à jour l'état de lecture pour le système
        if ('mediaSession' in navigator) {
            navigator.mediaSession.playbackState = "paused";
        }
    }

    resume() {
        if (this.audio.src) {
            this.audio.play();
            this.isPlaying = true;
            if ('mediaSession' in navigator) {
                navigator.mediaSession.playbackState = "playing";
            }
        }
    }

    previous() {
        console.log('⏮️ Previous track requested. Queue:', this.queue.length, 'Current index:', this.currentIndex);
        
        if (this.queue.length === 0) {
            console.warn('⚠️ No queue available for previous track');
            return;
        }
        
        // Si on est à plus de 3 sec, on recommence le morceau (comportement standard)
        if (this.audio.currentTime > 3) {
            console.log('⏮️ Restarting current track (>3s played)');
            this.audio.currentTime = 0;
            return;
        }
        
        if (this.currentIndex > 0) {
            this.currentIndex--;
            console.log('⏮️ Playing previous track at index:', this.currentIndex);
            this.play(this.queue[this.currentIndex]);
        } else {
            // Si on est au début, revenir à la fin (loop)
            this.currentIndex = this.queue.length - 1;
            console.log('⏮️ Looping to last track at index:', this.currentIndex);
            this.play(this.queue[this.currentIndex]);
        }
    }

    next() {
        console.log('⏭️ Next track requested. Queue:', this.queue.length, 'Current index:', this.currentIndex);
        
        if (this.queue.length === 0) {
            console.warn('⚠️ No queue available for next track');
            return;
        }
        
        if (this.currentIndex < this.queue.length - 1) {
            this.currentIndex++;
            console.log('⏭️ Playing next track at index:', this.currentIndex);
            this.play(this.queue[this.currentIndex]);
        } else {
            // Si on est à la fin, revenir au début (loop)
            this.currentIndex = 0;
            console.log('⏭️ Looping to first track at index:', this.currentIndex);
            this.play(this.queue[this.currentIndex]);
        }
    }

    playQueue(songIds, startIndex = 0) {
        // IMPORTANT: C'est ici qu'on remplit la file d'attente
        // Assure-toi que les IDs sont bien stockés de manière uniforme (String)
        this.queue = songIds.map(String);
        this.currentIndex = startIndex;
        console.log('🎼 Queue loaded:', this.queue.length, 'songs, starting at index:', startIndex);
        this.play(this.queue[this.currentIndex]);
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
