<div class="fixed bottom-0 left-0 right-0 h-player bg-bg-surface border-t border-border-main z-50 hidden" id="playerBar">
    <div class="flex items-center h-full px-2 sm:px-5 gap-2 sm:gap-5">
        <!-- Player Info -->
        <div class="flex items-center gap-2 sm:gap-4 min-w-0 flex-shrink max-w-[35%] sm:max-w-[200px]">
            <img src="" alt="Cover" id="playerCover" class="w-10 h-10 sm:w-14 sm:h-14 rounded object-cover flex-shrink-0">
            <div class="flex-1 min-w-0 overflow-hidden">
                <div class="font-semibold text-xs sm:text-sm truncate" id="playerTitle">No track playing</div>
                <div class="text-xs text-text-sub truncate" id="playerArtist">-</div>
            </div>
        </div>

        <!-- Player Controls & Progress -->
        <div class="flex-1 flex flex-col items-center gap-1 sm:gap-2 min-w-0">
            <!-- Controls -->
            <div class="flex gap-2 sm:gap-4 items-center">
                <button class="text-text-main hover:text-primary transition-colors w-7 h-7 sm:w-9 sm:h-9 flex items-center justify-center" id="prevBtn">
                    <i class="fas fa-step-backward text-sm sm:text-lg"></i>
                </button>
                
                <button class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-text-main text-bg-main flex items-center justify-center hover:scale-110 transition-transform flex-shrink-0" id="playPauseBtn">
                    <i class="fas fa-play text-sm sm:text-base"></i>
                </button>
                
                <button class="text-text-main hover:text-primary transition-colors w-7 h-7 sm:w-9 sm:h-9 flex items-center justify-center" id="nextBtn">
                    <i class="fas fa-step-forward text-sm sm:text-lg"></i>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 w-full max-w-2xl">
                <span class="text-xs text-text-sub min-w-[32px] sm:min-w-[40px]" id="currentTime">0:00</span>
                <div class="flex-1 min-w-0">
                    <input 
                        type="range" 
                        id="progressSlider" 
                        min="0" 
                        max="100" 
                        value="0" 
                        class="w-full h-1 bg-border-main rounded-full appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-3 [&::-webkit-slider-thumb]:h-3 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-text-main [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:w-3 [&::-moz-range-thumb]:h-3 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-text-main [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:cursor-pointer"
                    >
                </div>
                <span class="text-xs text-text-sub min-w-[32px] sm:min-w-[40px]" id="totalTime">0:00</span>
            </div>
        </div>

        <!-- Volume Control & Like Button -->
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <!-- Like Button -->
            <button class="text-text-sub hover:text-primary transition-colors w-7 h-7 sm:w-9 sm:h-9 flex items-center justify-center" id="likeBtn" title="Ajouter aux favoris">
                <i class="far fa-heart text-base sm:text-lg"></i>
            </button>
            
            <!-- Volume (desktop only) -->
            <div class="hidden lg:flex items-center gap-2.5">
                <i class="fas fa-volume-up text-text-sub"></i>
                <input 
                    type="range" 
                    id="volumeSlider" 
                    min="0" 
                    max="100" 
                    value="80" 
                    class="w-24 h-1 bg-border-main rounded-full appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-3 [&::-webkit-slider-thumb]:h-3 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-text-main [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:w-3 [&::-moz-range-thumb]:h-3 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-text-main [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:cursor-pointer"
                >
            </div>
        </div>

        <audio id="audioPlayer" preload="metadata"></audio>
    </div>
</div>
