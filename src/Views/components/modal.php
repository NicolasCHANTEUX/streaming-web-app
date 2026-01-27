<!-- Modal Container -->
<div id="modalOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) window.closeModal()">
    <div class="bg-bg-surface rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-5 border-b border-border-main">
            <h3 id="modalTitle" class="text-lg font-semibold text-text-main">Modal</h3>
            <button onclick="window.closeModal()" class="text-text-sub hover:text-text-main transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div id="modalBody" class="p-5">
            <!-- Content will be injected here -->
        </div>
        
        <!-- Modal Footer -->
        <div id="modalFooter" class="flex justify-end gap-3 p-5 border-t border-border-main">
            <!-- Buttons will be injected here -->
        </div>
    </div>
</div>

<!-- Download Queue Panel -->
<div id="downloadQueue" class="fixed bottom-player right-0 w-80 bg-bg-surface border-l border-t border-border-main shadow-xl transform translate-x-full transition-transform duration-300 z-40 max-h-96 overflow-y-auto hidden">
    <div class="p-4 border-b border-border-main flex items-center justify-between">
        <h3 class="font-semibold text-text-main">Téléchargements</h3>
        <button onclick="toggleDownloadQueue()" class="text-text-sub hover:text-text-main">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="downloadQueueItems" class="p-4 space-y-3">
        <!-- Download items will be added here -->
    </div>
</div>
