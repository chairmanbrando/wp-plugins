<?php

/**
 * Plugin Name: ACF Theme Colors
 * Description: Pulls your <code>theme.json</code> color palette into the ACF color picker.
 * Version: 0.1.0
 * Author: chairmanbrando
 * Author URI: https://chairmanbrando.org/
 * Update URI: false
 */

if (! defined('ABSPATH')) exit;

add_action('acf/input/admin_footer',function () {
	if (! wp_theme_has_theme_json()) return;

	$colors = wp_get_global_settings(['color', 'palette']);
	$colors = wp_list_pluck($colors['theme'], 'color');

	if (! sizeof($colors)) return;

	?>
	<script>
		(function ($) {
			acf.add_filter('color_picker_args', function ($args, $field) {
				const colors = <?= json_encode($colors) ?>;

				if (Array.isArray($args.palettes)) {
					$args.palettes = $args.palettes.concat(colors);
				} else {
					$args.palettes = colors;
				}

				return $args;
			});
		})(jQuery);
	</script>
	<?php
});
