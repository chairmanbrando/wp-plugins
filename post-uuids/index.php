<?php

/**
 * Plugin Name: UUIDs for Styling
 * Description: Adds a <code>uuid-*</code> body class to every post. This is mostly for sites where post and page duplication is the standard editing method. Said <code>uuid-*</code> class is guaranteed to be unique... unless you happen to create two posts on the same microsecond. See the readme for more details.
 * Version:     0.1.0
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

// Change this if you want. 🤷‍♀️
define('UUID_KEY', '_uuid_baby');

/**
 * @param WP_Post|int|falsey $post
 */
function get_post_uuid($post = 0) {
	$post = get_post($post);

	if (! $post) return false;

	$uuid = get_post_meta($post->ID, UUID_KEY, true);

	return ($uuid) ? $uuid : false;
}

/**
 * @param int $postid
 */
add_action('save_post', function ($postid) {
	if ($parentid = wp_is_post_revision($postid)) {
		$postid = $parentid;
	}

	if (get_post_uuid($postid)) return;

	update_post_meta($postid, UUID_KEY, md5(uniqid('', true)));
});

/**
 * @param array[string] $classes
 */
add_filter('body_class', function ($classes) {
	if (isset($GLOBALS['post'])) {
		if (is_singular()) {
			if ($uuid = get_post_uuid()) {
				$classes[] = "uuid-{$uuid}";
			}
		}
	}

	return $classes;
});

/**
 * @param array[string] $classes
 * @param array[string] $more
 * @param int $postid
 */
add_filter('post_class', function ($classes, $more, $postid) {
	if (is_admin()) return $classes;

	if ($uuid = get_post_uuid($postid)) {
		$classes[] = "uuid-{$uuid}";
	}

	return $classes;
}, 10, 3);
