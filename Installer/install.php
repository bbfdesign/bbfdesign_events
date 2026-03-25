<?php

declare(strict_types=1);

/**
 * BBF Events – Installation Routine
 * Creates required directories for media storage.
 */

$mediaBase = PFAD_ROOT . 'mediafiles/bbfdesign_events/';
$dirs = ['images', 'gallery', 'videos', 'downloads', 'partners'];

foreach ($dirs as $dir) {
    $path = $mediaBase . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// Create .htaccess to prevent PHP execution in media directory
$htaccess = $mediaBase . '.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "# Prevent PHP execution\n<FilesMatch \"\\.php$\">\n    Deny from all\n</FilesMatch>\n");
}
