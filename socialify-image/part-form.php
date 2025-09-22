<form action="<?= get_permalink() ?>#done" method="post" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
    <label for="images">Upload an image or three…</label>
    <input id="images" type="file" name="images[]" accept="<?= implode(',', $this->mimes) ?>" multiple>

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
