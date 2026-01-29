<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#121212">
    <?= csrf_meta() ?>
    <title><?= e($title ?? 'Music Streaming') ?></title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= asset('css/output.css') ?>">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-bg-main text-text-main font-sans">
    <?php component('header'); ?>
    
    <main id="mainContent" class="pt-24 pb-nav px-5 max-w-7xl mx-auto min-h-screen">
        <?php 
        if (isset($content)) {
            view($content, $data ?? []);
        }
        ?>
    </main>

    <?php component('player-bar'); ?>
    <?php component('navigation'); ?>
    <?php component('modal'); ?>
    <?php component('song-options-modal'); ?>

    <!-- Scripts -->
    <script src="<?= asset('js/player.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/app.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/modal.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/download.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/song-options.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
