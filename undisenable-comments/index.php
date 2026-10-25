<?php

/**
 * Plugin Name: Disable Comments
 * Description: Disables comments pretty much everywhere.
 * Version:     1.1.1
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 * Note:        The silly folder name is to make mine unique from the others on the wp.org repo.
 */

if (! defined('ABSPATH')) exit;

class DisableComments {

    public static function action() {
        // Remove links from various admin things.
        add_action('add_admin_bar_menus', __CLASS__ . '::add_admin_bar_menus');
        add_action('admin_init',          __CLASS__ . '::admin_init', 99);
        add_action('admin_menu',          __CLASS__ . '::admin_menu', 99);

        // Close comments to viewers.
        add_filter('comments_open', '__return_false', 99);
        add_filter('pings_open',    '__return_false', 99);

        // Remove any existing comments from display.
        add_filter('comments_array', '__return_empty_array', 99);
    }

    public static function add_admin_bar_menus() {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }

     public static function admin_init() {
        // Redirect users trying to get at the comments page.
        if ($GLOBALS['pagenow'] === 'edit-comments.php') {
            wp_safe_redirect(admin_url());
            exit;
        }

        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        // Disable support for comments and trackbacks in all registered post types.
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    public static function admin_menu() {
        remove_menu_page('edit-comments.php');
    }

}

add_action('plugins_loaded', function () {
    DisableComments::action();
});
