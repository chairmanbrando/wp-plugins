<?php

class SocialifyImages {

    private $images;
    private $mimes;

    function __construct() {
        $this->mimes = apply_filters('socialify_mimes', [
            'image/heic',
            'image/webp',
            'image/png',
            'image/jpeg'
        ]);

        $this->images = [];

        add_shortcode('socialify_form', [$this, 'socialify_form']);
    }

    // ----- @publics ------------------------------------------------------- //

    function socialify_form() {
        if (isset($_POST['socialify_form'])) {
            $this->handle_upload();
        }

        ob_start();
        include __DIR__ . '/part-form.php';

        if ($this->images) {
            include __DIR__ . '/part-output.php';
        }

        return apply_filters('socialify_form', ob_get_clean());
    }

    // ----- @privates ------------------------------------------------------ //

    // @todo I should probably disallow turning photos into PNGs, eh? 🤔
    private function handle_upload() {
        if ($_FILES['images']['error'][0]) return;

        $type = (isset($_POST['output_type'])) ? $_POST['output_type'] : 'JPEG';
        $type = (! in_array($type, ['JPEG', 'PNG'])) ? 'JPEG' : $type;

        $files = $this->rearrange_files($_FILES['images']);

        foreach ($files as $file) {
            $image = new SocialifyImage(
                apply_filters('socialify_maxw', 1200),
                apply_filters('socialify_maxh', 1000),
                apply_filters('socialify_save_copy', false)
            );

            $image->process($file, $type);
            $this->images[] = $image;
        }
    }

    /**
     * `$_FILES` is sorted by key by default. This function makes it a numbered
     * array so it's more intuitive and easier to loop through.
     *
     * See: https://www.php.net/manual/en/features.file-upload.post-method.php#91479
     */
    private function rearrange_files($files) {
        $keys = array_keys($files);
        $redo = [];

        for ($i = 0; $i < sizeof($files['name']); $i++) {
            foreach ($keys as $key) {
                $redo[$i][$key] = $files[$key][$i];
            }
        }

        return $redo;
    }

}
