<?php

/**
 * Plugin Name: Socialify Images
 * Description: Upload a bulky image full of EXIF data and get back a 1200px JPEG or PNG cleaned of extraneous data.
 * Author: chairmanbrando
 * Version: 0.1.0
 * Update URI: false
 * Requires PHP: 7.4
 */

class SocialifyImages {

    private $mimes;
    private $blob;
    private $mime;
    private $type;
    private $name;
    private $maxw;
    private $maxh;

    function __construct() {
        $this->mimes = apply_filters('socialify_mimes', [
            'image/heic', 'image/webp', 'image/png', 'image/jpeg'
        ]);

        $this->maxw = apply_filters('socialify_maxw', 1200);
        $this->maxh = apply_filters('socialify_maxh', 1000);

         // To be filled after upload and processing.
        $this->blob = null;
        $this->mime = null;
        $this->type = null;
        $this->name = null;

        add_shortcode('socialify_form', [$this, 'socialify_form']);
    }

    // ----- @privates ------------------------------------------------------ //

    private function handle_upload() {
        if ($_FILES['image']['error']) return false;

        // @@ I should probably disallow turning photos into PNGs, eh? 🤔
        $type  = (isset($_POST['output_type'])) ? $_POST['output_type'] : 'JPEG';
        $magic = new Imagick;

        if (! in_array($type, ['JPEG', 'PNG'])) {
            $type = 'JPEG';
        }

        $magic->readImage($_FILES['image']['tmp_name']);

        if (apply_filters('socialify_save_copy', false)) {
            $this->save_copy();
        }

        if (! $magic->valid()) return false;

        $this->resize($magic, $type);
        $this->strip_exif($magic);

        $this->blob = base64_encode($magic->getImageBlob());
        $this->mime = $magic->getImageMimeType();
        $this->type = $type;
        $this->name = sanitize_title($_FILES['image']['name']) . '-socialified';

        return $magic->clear();
    }

    private function resize(&$image, $type) {
        $ow = $image->getImageWidth();
        $oh = $image->getImageHeight();

        $image->setImageFormat($type);

        if ($type === 'JPEG') {
            $image->setCompressionQuality(86);
        }

        // Set new width and height while keeping aspect ratio.
        if ($ow > $this->maxw || $oh > $this->maxh) {
            if ($ow > $this->maxw) {
                $nw = $this->maxw;
                $nh = ($this->maxw / $ow) * $oh;
            } else {
                $nh = $this->maxh;
                $nw = ($this->maxh / $oh) * $ow;
            }

            // Lanczos is slow but much better than `scaleImage()`.
            $image->resizeImage($nw, $nh, Imagick::FILTER_LANCZOS, 1);
        }
    }

    private function save_copy() {
        $to = wp_upload_dir()['basedir'] . '/socialify';

        if (! file_exists($to)) {
            mkdir($to);
        }

        $name = sprintf('%s--%s', sanitize_title($_SERVER['REMOTE_ADDR']), $_FILES['image']['name']);
        $to   = "{$to}/{$name}";

        return move_uploaded_file($_FILES['image']['tmp_name'], $to);
    }

    private function strip_exif(&$image) {
        $profiles = $image->getImageProfiles('icc', true);

        // This kills *everything* including a color profile we want to keep.
        $image->stripImage();

        if ($profiles) {
            $image->profileImage('icc', $profiles['icc']);
        }
    }

    // ----- @publics ------------------------------------------------------- //

    function socialify_form() {
        if (isset($_POST['socialify_form'])) {
            $this->handle_upload();
        }

        ob_start();
        include __DIR__ . '/form.php';

        if ($this->blob) {
            include __DIR__ . '/image.php';
        }

        return apply_filters('socialify_form', ob_get_clean());
    }

}

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
 * Yes, `convert_smilies()` was added in June 2003, so it's possible this bug
 * has existed for more than 20 years. The relevant bug report, though, was
 * opened in August 2020. The world may never know...
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
