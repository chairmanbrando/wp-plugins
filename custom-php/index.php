<?php

/**
 * Plugin Name: Easy Custom PHP
 * Description: Adds a <code>custom.php</code> in this plugin's directory which (a) is automatically included and (2) you can then edit using the <a href="/wp-admin/plugin-editor.php?file=custom-php%2Fcustom.php&plugin=custom-php%2Findex.php">back-end plugin editor</a>.
 * Version:     1.1.6
 * Author:      chairmanbrando
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

define('ECP_DIR', untrailingslashit(plugin_dir_path(__FILE__)));

add_action('admin_head', function () {
	if (in_array($GLOBALS['pagenow'], ['plugin-editor.php', 'theme-editor.php'])) {
		printf('<style>%s</style>', 'pre.CodeMirror-line { white-space: pre }');

		if (isset($_GET['file']) && strpos($_GET['file'], 'custom.php') !== false) {
			printf('<style>%s</style>', '.active-plugin-edit-warning { display: none }');
		}
	}
});

add_action('admin_menu', function () {
	add_submenu_page('plugins.php', 'Custom PHP', 'Custom PHP', 'manage_options', '/plugin-editor.php?file=custom-php%2Fcustom.php&plugin=custom-php%2Findex.php');
});

add_action('after_setup_theme', function () {
	if (file_exists(ECP_DIR . '/custom.php')) {
		require ECP_DIR . '/custom.php';
	}
});

add_action('plugins_loaded', function () {
	if (! file_exists(ECP_DIR . '/custom.php')) {
		touch(ECP_DIR . '/custom.php');
	}
});
