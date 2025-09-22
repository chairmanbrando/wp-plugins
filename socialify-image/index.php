<?php

/**
 * Plugin Name: Socialify Images
 * Description: Upload a bulky image full of EXIF data and get back a 1200px JPEG or PNG cleaned of extraneous data.
 * Author: chairmanbrando
 * Version: 1.0.0
 * Update URI: false
 * Requires PHP: 7.4
 */

require 'class.images.php';
require 'class.image.php';

add_action('init', function () {
    new SocialifyImages;
});

/**
 * Until WP v6.8 drops, there's a bug in the `convert_smilies()` function where
 * if you have a huge amount of data on the page, as this plugin can if you
 * haphazardly upload a photo and then output it as a PNG for no good reason,
 * then it can crash `preg_split()`. There's no error checking that the result
 * from the `preg_split()` call is legit, so the next `count()` call errors out.
 *
 * `convert_smilies()` was added in June 2003, so it's possible, even likely,
 * that this bug has existed for more than 20 years. The relevant bug report,
 * though, was only opened in August 2020. The world may never know...
 *
 * As such, until v6.8 is out and I can say this plugin requires it, I'm forced
 * to turn "smilie" conversion off for any page that contains this plugin's
 * shortcode. Since `remove_filter()` didn't work for such things, probably due
 * to block themes operating in their own "special" way, the relevant option is
 * set to false and put back to whatever it was later. Nice hacks, bro.
 */
add_action('wp', function () {
    if (! isset($GLOBALS['post'])) return;

    if (has_shortcode($GLOBALS['post']->post_content, 'socialify_form')) {
        $smilies = get_option('use_smilies');

        update_option('use_smilies', false);

        add_action('wp_footer', function () use ($smilies) {
            update_option('use_smilies', $smilies);
        });
    }
});
