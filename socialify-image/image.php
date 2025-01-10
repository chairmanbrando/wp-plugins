<?php if (! $this->mime || ! $this->blob) return; ?>

<style>
    figure img {
        max-width: 100%;
    }
</style>

<h3 id="done"><?= __('Click on the image to download it!') ?></h3>
<p><?= __('Note: Your operating system may inject some basic EXIF stuff back in upon downloading, but all the personally identifiable stuff is gone!') ?></p>

<?php

$data = sprintf('data:%s;base64,%s', $this->mime, $this->blob);

$template = <<<END
    <figure>
        <a href="%s" download="socialified.%s">
            <img class="alignnone" src="%s" alt="%s">
        </a>
    </figure>
END;

printf($template, $data, strtolower($this->type), $data, __('Your uploaded image but smaller and without EXIF data.'));
