<?php

/**
 * Plugin Name: Admin Shortcut
 * Description: Takes you to the back end when you press Escape (like Squarespace) if nothing else on the page is focused.
 * Version:     1.0.0
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

add_action('wp_footer', function () {
?>
    <script>
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (document.querySelectorAll(':focus').length) return;

            window.location.href = '<?= admin_url() ?>';
            e.preventDefault();
        });
    </script>
<?php
});
