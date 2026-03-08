<?php

/**
 * Plugin Name: Image Shortlink Handler
 * Description: Early-loaded image rewrite handler thing. Sends `/i/` requests to the uploads folder in two ways.
 * Version:     1.2.3.4.5.6
 * Author:      chairmanbrando
 * Update URI:  false
 * Note: This is for the `mu-plugins` directory. By having it start with an "a"
 *       we get to execute quite early in the process. Yeah, you could probably
 *       make it run earlier by editing `wp-config.php`, but that's less cool.
 */

// Your host almost certainly has asset-looking file extensions (.png, .css, et
// al.) served statically. That's why we don't use 'em here. I suppose you could
// try making your own extension like `.yay` or something in those regexes. Who
// would do such a silly thing, though? 🤷‍♀️

// Fuller: /i/2026/03/whatevs -- Goes to specified month folder.
if (preg_match('#^/i/(\d{4}/\d{2}/[^.]+)$#', $_SERVER['REQUEST_URI'], $m)) {
    $base_path = WP_CONTENT_DIR . '/uploads/' . $m[1];
}
// Shorter: /i/whatevs[.yay] -- Ephemeral reference to this month's folder.
elseif (preg_match('#^/i/([^/.]+)(\.yay)?$#', $_SERVER['REQUEST_URI'], $m)) {
    $base_path = WP_CONTENT_DIR . '/uploads/' . date('Y/m') . '/' . $m[1];
}
// Nothing to see here.
else {
    return;
}

foreach (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'] as $ext) {
    $file = untrailingslashit($base_path) . '.' . $ext;

    if (file_exists($file)) {
        header('Content-Type: ' . mime_content_type($file));
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}
