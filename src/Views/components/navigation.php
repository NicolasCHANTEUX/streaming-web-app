<nav id="bottomNav" class="fixed bottom-0 left-0 right-0 h-nav bg-bg-surface flex justify-around items-center border-t border-border-main z-40">
    <a href="/" class="flex flex-col items-center gap-1 text-text-sub py-2.5 px-4 transition-colors <?= ($_SERVER['REQUEST_URI'] == '/') ? 'text-primary' : 'hover:text-text-main' ?>">
        <i class="fas fa-home text-xl"></i>
        <span class="text-xs">Home</span>
    </a>
    
    <a href="/music" class="flex flex-col items-center gap-1 text-text-sub py-2.5 px-4 transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'], '/music') ? 'text-primary' : 'hover:text-text-main' ?>">
        <i class="fas fa-music text-xl"></i>
        <span class="text-xs">Library</span>
    </a>
    
    <a href="/add" class="flex flex-col items-center gap-1 text-text-sub py-2.5 px-4 transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'], '/add') ? 'text-primary' : 'hover:text-text-main' ?>">
        <i class="fas fa-plus-circle text-xl"></i>
        <span class="text-xs">Add</span>
    </a>
    
    <a href="/liked" class="flex flex-col items-center gap-1 text-text-sub py-2.5 px-4 transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'], '/liked') ? 'text-primary' : 'hover:text-text-main' ?>">
        <i class="fas fa-heart text-xl"></i>
        <span class="text-xs">Liked</span>
    </a>
    
    <a href="/playlists" class="flex flex-col items-center gap-1 text-text-sub py-2.5 px-4 transition-colors <?= str_starts_with($_SERVER['REQUEST_URI'], '/playlists') ? 'text-primary' : 'hover:text-text-main' ?>">
        <i class="fas fa-list text-xl"></i>
        <span class="text-xs">Playlists</span>
    </a>
</nav>
