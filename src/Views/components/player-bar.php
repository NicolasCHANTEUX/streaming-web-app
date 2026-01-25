<div class="fixed bottom-0 left-0 right-0 h-player bg-bg-surface border-t border-border-main z-50 hidden" id="playerBar">
    <div class="flex items-center h-full px-5 gap-5">
        <!-- Player Info -->
        <div class="flex items-center gap-4 min-w-[200px]">
            <img src="" alt="Cover" id="playerCover" class="w-14 h-14 rounded object-cover">
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-sm truncate" id="playerTitle">No track playing</div>
                <div class="text-xs text-text-sub truncate" id="playerArtist">-</div>
            </div>
        </div>

        <!-- Player Controls & Progress -->
        <div class="flex-1 flex flex-col items-center gap-2">
            <!-- Controls -->
            <div class="flex gap-4 items-center">
                <button class="text-text-main hover:text-primary transition-colors w-9 h-9 flex items-center justify-center" id="prevBtn">
                    <i class="fas fa-step-backward text-lg"></i>
                </button>
                
                <button class="w-10 h-10 rounded-full bg-text-main text-bg-main flex items-center justify-center hover:scale-110 transition-transform" id="playPauseBtn">
                    <i class="fas fa-play text-base"></i>
                </button>
                
                <button class="text-text-main hover:text-primary transition-colors w-9 h-9 flex items-center justify-center" id="nextBtn">
                    <i class="fas fa-step-forward text-lg"></i>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="flex items-center gap-2.5 w-full max-w-2xl">
                <span class="text-xs text-text-sub min-w-[40px]" id="currentTime">0:00</span>
                <div class="flex-1">
                    <input 
                        type="range" 
                        id="progressSlider" 
                        min="0" 
                        max="100" 
                        value="0" 
                        class="w-full h-1 bg-border-main rounded-full appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-3 [&::-webkit-slider-thumb]:h-3 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-text-main [&::-webkit-slider-thumb]:cursor-pointer [&::-moz-range-thumb]:w-3 [&::-moz-range-thumb]:h-3 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-text-main [&::-moz-range-thumb]:border-0 [&::-moz-range-thumb]:cursor-pointer"
                    >
                </div>
                <span class="text-xs text-text-sub min-w-[40px]" id="totalTime">0:00</span>
            </div>
        </div>

        <!-- Volume Control -->
        <div class="hidden md:flex items-center gap-2.5 min-w-[150px]">
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

        <audio id="audioPlayer" preload="metadata"></audio>
    </div>
</div>
