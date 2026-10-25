<?php

/**
 * Plugin Name: Password Overrides Template
 * Description: Passwords only block <code>the_content()</code> by default. Any custom templating around that still shows. That's silly, and this plugin "fixes" that.
 * Version:     1.0.0
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

add_filter('template_include', function ($template) {
    if (! post_password_required()) return $template;

    switch (get_post_type()) {
        case 'page' : $template = locate_template('page.php'); break;
        case 'post' : $template = locate_template('single.php'); break;
        default :     $template = locate_template(get_post_type() . '.php');
    }

    if (! $template) {
        $template = locate_template('index.php');
    }

    // Hook this and load in your specialized interstitial template.
    return apply_filters('password_template', $template, get_post_type());
}, 999);
