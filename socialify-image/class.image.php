<?php

class SocialifyImage {

    private $config;

    // The image processor and the image itself.
    private $magic;
    private $image;

    // Sometimes shit breaks.
    public $error;

    public function __construct($maxw, $maxh, $copy) {
        $this->config = new stdClass;
        $this->config->maxw = $maxw;
        $this->config->maxh = $maxh;
        $this->config->copy = $copy;

        // To be filled out during/after processing.
        $this->image = new stdClass;
        $this->image->temp = null;
        $this->image->blob = null;
        $this->image->mime = null;
        $this->image->type = null;
        $this->image->name = null;
    }

    // ----- @publics ------------------------------------------------------- //

    public function get_data() {
        return $this->image;
    }

    public function process($image, $outputType) {
        if ($image['error']) {
            $this->error = $image['error'];
            return false;
        }

        $this->image->temp = $image['tmp_name'];
        $this->magic       = new Imagick;

        $this->magic->readImage($this->image->temp);

        if (! $this->magic->valid()) {
            $this->error = true;
            return false;
        }

        $this->resize($outputType);
        $this->strip_exif();

        $this->image->blob = base64_encode($this->magic->getImageBlob());
        $this->image->mime = $this->magic->getImageMimeType();
        $this->image->type = $outputType;
        $this->image->name = sanitize_title($image['name']) . '-stripped';

        if ($this->config->copy) {
            $this->save_copy();
        }

        return $this->magic->clear();
    }

    // ----- @privates ------------------------------------------------------ //

    private function resize($type, $quality = 75) {
        $ow = $nw = $this->magic->getImageWidth();
        $oh = $nh = $this->magic->getImageHeight();

        $this->magic->setImageFormat($type);

        if ($type === 'JPEG') {
            $this->magic->setCompressionQuality(
                apply_filters('socialify_quality', $quality)
            );
        }

        // Set new width and height while keeping aspect ratio.
        if ($ow / $oh > 1 && $ow > $this->config->maxw) {
            $nw = $this->config->maxw;
            $nh = ($this->config->maxw / $ow) * $oh;
        }

        if ($ow / $oh <= 1 && $oh > $this->config->maxh) {
            $nh = $this->config->maxh;
            $nw = ($this->config->maxh / $oh) * $ow;
        }

        if ($nw !== $ow) {
            // Lanczos is slow but much better than `scaleImage()`.
            $this->magic->resizeImage(intval($nw), intval($nh), Imagick::FILTER_LANCZOS, 1);
        }
    }

    private function save_copy() {
        $to = wp_upload_dir()['basedir'] . '/socialify';

        if (! file_exists($to)) {
            mkdir($to);
        }

        $name = sprintf('%s--%s', sanitize_title($_SERVER['REMOTE_ADDR']), $this->image->name);
        $to   = "{$to}/{$name}";

        return move_uploaded_file($this->image->temp, $to);
    }

    private function strip_exif() {
        $profiles = $this->magic->getImageProfiles('icc', true);

        // This kills *everything* including a color profile we want to keep.
        $this->magic->stripImage();

        if ($profiles) {
            $this->magic->profileImage('icc', $profiles['icc']);
        }
    }

}
