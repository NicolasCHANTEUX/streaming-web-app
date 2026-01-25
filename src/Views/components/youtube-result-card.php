<?php
/**
 * YouTube result card component
 * @var array $result - YouTube video data
 */
?>
<div class="youtube-result-card">
    <div class="youtube-thumbnail">
        <img src="<?= e($result['thumbnail']) ?>" alt="<?= e($result['title']) ?>">
        <span class="youtube-duration">
            <?= gmdate("i:s", $result['duration']) ?>
        </span>
    </div>
    
    <div class="youtube-info">
        <h3 class="youtube-title"><?= e($result['title']) ?></h3>
        <p class="youtube-channel"><?= e($result['channel']) ?></p>
    </div>
    
    <div class="youtube-actions">
        <button 
            class="btn btn-primary download-btn" 
            onclick="downloadVideo('<?= e($result['id']) ?>', '<?= e(addslashes($result['title'])) ?>')"
        >
            <i class="fas fa-download"></i>
            Download
        </button>
    </div>
</div>
