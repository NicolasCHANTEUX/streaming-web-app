<?php
/**
 * Loading spinner component
 * @var string $message - Optional loading message
 */
?>
<div class="loading-spinner">
    <div class="spinner"></div>
    <?php if (isset($message)): ?>
        <p><?= e($message) ?></p>
    <?php endif; ?>
</div>
