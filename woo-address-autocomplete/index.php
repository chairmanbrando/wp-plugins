<?php

/**
 * Plugin Name: WooCommerce Address Autocomplete
 * Description: Hooks the WooCommerce checkout fields to Google's Places API for autocompletion. Requires a GMaps API key with the Places API enabled.
 * Version:     1.0.1
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

define('WAA_PATH', plugin_dir_path(__FILE__));
define('WAA_URL',  plugin_dir_url(__FILE__));
## @todo Define `WAA_API_KEY` in your env, WP config, or here if need be.

add_action('wp_enqueue_scripts', function () {
    if (! function_exists('is_checkout')) return;

    if (is_checkout()) {
        wp_enqueue_script('address-autocomplete', WAA_URL . 'script.js', [], true);
        wp_enqueue_script('google-places', 'https://maps.googleapis.com/maps/api/js?key=' . WAA_API_KEY . '&libraries=places&callback=initPlaces', ['address-autocomplete'], null, true);
    }
});
