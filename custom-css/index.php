<?php

/**
 * Plugin Name: Easy Custom CSS
 * Description: Adds a <code>custom.css</code> in this plugin's directory which (a) is automatically included and (2) you can then edit using the <a href="/wp-admin/plugin-editor.php?file=custom-css%2Fcustom.css&plugin=custom-css%2Findex.php">back-end plugin editor</a>.
 * Version:     1.1.6
 * Author:      chairmanbrando
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

define('ECC_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
define('ECC_URL', untrailingslashit(plugin_dir_url(__FILE__)));

add_action('admin_head', function () {
	if (in_array($GLOBALS['pagenow'], ['plugin-editor.php', 'theme-editor.php'])) {
		printf('<style>%s</style>', 'pre.CodeMirror-line { white-space: pre }');

		if (isset($_GET['file']) && strpos($_GET['file'], 'custom.css') !== false) {
			printf('<style>%s</style>', '.active-plugin-edit-warning { display: none }');
		}
	}
});

add_action('admin_menu', function () {
	add_submenu_page('themes.php', 'Custom CSS', 'Custom CSS', 'manage_options', '/plugin-editor.php?file=custom-css%2Fcustom.css&plugin=custom-css%2Findex.php');
});


add_action('plugins_loaded', function () {
	if (! file_exists(ECC_DIR . '/custom.css')) {
		touch(ECC_DIR . '/custom.css');
	}
});

add_action('wp_enqueue_scripts', function () {
	if (file_exists(ECC_DIR . '/custom.css')) {
		wp_enqueue_style('ecc-custom', ECC_URL . '/custom.css', [], filemtime(ECC_DIR . '/custom.css'));
	}
}, 99);
