<?php

/**
 * Plugin Name: 404 to Home
 * Description: Redirects 404s to the home page for the sake of simplicity.
 * Version:     1.1.0
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

// @todo Attaching the banner to `wp_body_open` may not work in practice due to how the page cache
// is handled when `wordpress_*` cookies are in play. If their existence allows the page cache to be
// gotten around, nothing more is needed. If it doesn't then the banner will need to be prepended to
// the body tag with JS instead of PHP.

if (! defined('ABSPATH')) exit;

// Guard clauses out the wazanus!
add_action('template_redirect', function () {
    if (is_admin()) return;

    if (defined('DOING_CRON') && DOING_CRON) return;
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) return;

    // Any other specific things we might ignore?
    if (isset($_SERVER['REQUEST_URI'])) {
        if (strpos($_SERVER['REQUEST_URI'], '.xml') !== false) return;
    }

    if ($GLOBALS['wp_query']->is_404() === false) return;

    // All referrer things *apparently* get reset on redirect, so we'll drop the dead URL in a very
    // short-lived cookie and use it to produce a banner along the top with a message.
    if (wp_redirect(home_url(), 301)) {
        $url = home_url($_SERVER['REQUEST_URI']);

        // Cookies have to be named `wordpress_*` to get around certain caching mechanisms.
        setcookie('wordpress_404_url', $url, [
            'path'     => COOKIEPATH,
            'expires'  => time() + 5,
            'secure'   => is_ssl(),
            'httponly' => true
        ]);

        exit;
    }
}, PHP_INT_MAX);

// Add basic styles for the below banner.
add_action('wp_head', function () {
    if (! apply_filters('404_show_banner', true)) return;

    ?>
    <style>
        .banner.not-found {
            background: var(--wp--preset--color--subtle-background, #eee);
            color: var(--wp--preset--color--black, #000);
            padding: 0.875rem;
            text-align: center;

            code {
                background: rgba(0, 0, 0, 0.1);
            }
        }
    </style>
    <?php
});

// Add the banner alerting the user to the 404.
add_action('wp_body_open', function () {
    if (! apply_filters('404_show_banner', true)) return;
    if (! isset($_COOKIE['wordpress_404_url']))   return;
    if (empty($_COOKIE['wordpress_404_url']))     return;

    $uri = str_replace(home_url(), '', $_COOKIE['wordpress_404_url']);
    $msg = sprintf('The requested URI <code>%s</code> was not found on this server. If you followed a link, the requested page was likely moved or deleted.', esc_url($uri));

    ?>
    <div class="not-found banner">
        <?= __(apply_filters('404_banner_msg', $msg, $uri)) ?>
    </div>
    <?php
}, 5);
