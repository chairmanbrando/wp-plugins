<?php if (! sizeof($this->images)) return ?>

<style>
    figure img {
        max-width: 100%;
    }
</style>

<h3 id="done"><?= __('Click on the image to download it!') ?></h3>
<p><?= __('Note: Your operating system may inject some basic EXIF stuff back in upon downloading, but all the personally identifiable stuff is gone!') ?></p>

<?php

$template = <<<END
    <figure>
        <a href="%s" download="%s.%s">
            <img class="alignnone" src="%s" alt="%s">
        </a>
    </figure>
END;

foreach ($this->images as $image) {
    if ($image->error) continue;

    $image = $image->get_data();
    $data  = sprintf('data:%s;base64,%s', $image->mime, $image->blob);

    $output = sprintf(
        apply_filters('socialify_template', $template),
        $data,
        $image->name,
        strtolower($image->type),
        $data,
        __('Your uploaded image but smaller and without EXIF data.')
    );

    echo apply_filters('socialify_output', $output);
}
