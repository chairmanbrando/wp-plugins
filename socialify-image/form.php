<p><?= __('Yeah, social media sites probably process images and remove EXIF data, but in case they don\'t, this form will do it for you. It will also resize the image to a maximum of 1200px wide or 1000px tall lest you upload some 5MB giant for no real reason.') ?></p>

<form action="<?= get_permalink() ?>#done" method="post" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
    <label for="image">Upload an image...</label>
    <input id="image" type="file" name="image" accept="<?= implode(',', $this->mimes) ?>">

    <fieldset>
        <legend>Output type:</legend>
        <label>
            <input type="radio" name="output_type" value="JPEG" checked> JPEG
        </label>
        <label>
            <input type="radio" name="output_type" value="PNG"> PNG
        </label>
    </fieldset>

    <input type="hidden" name="socialify_form" value="Yarp!">
    <input type="submit" value="Submit">
</form>
