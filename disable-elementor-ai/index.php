<?php

/**
 * Plugin Name: Disable Elementor AI
 * Description: The AI upsells are getting a <em>bit</em> out of hand, eh?
 * Author: chairmanbrando
 * Version: 1.0.0
 * Update URI: false
 * Requires PHP: 7.4
 */

// For whatever reason, returning `false` isn't good enough. 🤷‍♀️
add_filter('get_user_option_elementor_enable_ai', fn () => '0', PHP_INT_MAX);
