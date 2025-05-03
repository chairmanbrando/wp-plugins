<?php

/**
 * Plugin Name: Admin Shortcuts
 * Description: (1) Takes you to the back end when you press Escape if nothing else on the page is focused. (2) Opens the post being edited in a new preview tab if you hit Cmd/Ctrl-P.
 * Version:     1.1.0
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

/**
 * While in the Gutenberg editor, hit Cmd/Ctrl-P to open a preview tab.
 *
 * @todo Check if a draft has been made or the preview will 404.
 */
add_action('admin_footer', function () {
    if (in_array($GLOBALS['pagenow'], ['post.php', 'post-new.php'], true)) : ?>
        <script>
            document.body.addEventListener('keydown', (e) => {
                if (e.metaKey || e.ctrlKey) { // Cmd || Ctrl
                    if (e.key.toLowerCase() === 'p') {
                        e.preventDefault();
                        window.open(wp.data.select('core/editor').getEditedPostPreviewLink(), '_blank');
                    }
                }
            });
        </script>
    <?php endif;
});

/**
 * Pressing Escape while no elements are focused will forward you to the back
 * end of this site.
 */
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
