<header class="fixed top-0 left-0 right-0 h-header bg-bg-surface z-50 border-b border-border-main">
    <div class="flex items-center justify-between h-full px-5 max-w-7xl mx-auto">
        <div class="flex items-center gap-2.5 text-2xl font-bold text-primary">
            <i class="fas fa-music text-3xl"></i>
            <span>MyMusic</span>
        </div>
        
        <div class="flex-1 max-w-md mx-5 hidden md:block">
            <form action="/music/search" method="GET" class="flex gap-1">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Search your library..." 
                    value="<?= e($_GET['q'] ?? '') ?>"
                    class="flex-1 px-4 py-2 rounded-full bg-bg-hover text-text-main outline-none focus:bg-bg-surface transition-colors"
                >
                <button type="submit" class="px-5 py-2 rounded-full bg-primary text-white hover:bg-primary-hover transition-opacity">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="flex gap-2.5">
            <a href="/search" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-semibold hover:bg-primary-hover hover:scale-105 transition-all">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">Add Music</span>
            </a>
        </div>
    </div>
</header>
