<?php use SpecialAccess as SA; ?>

<style><?= file_get_contents(SSA_DIR . '/assets/admin.css') ?></style>

<div class="wrap">
    <h1>Special Access Configuration</h1>

    <?php if ($stats) : ?>
        <h3>Stats</h3>
        <ul id="stats">
            <li>Passcodes: <?= $stats['count'] ?></li>
            <li>Last updated: <?= $stats['time'] ?></li>
        </ul>
    <?php endif; ?>

    <?php if ($eids && apply_filters('ssa_show_all_eids', true)) : ?>
        <h3>Passcodes</h3>
        <p class="passcodes"><?= implode(', ', $eids) ?></p>
    <?php endif; ?>

    <?php if ($log) : ?>
        <h3>Log</h3>
        <p>The full log is available on request. Below is the last 10 lines from it.</p>
        <pre class="log"><?= implode("\n", $log) ?></pre>
    <?php endif; ?>

    <hr>

    <h3>Passcodes</h3>
    <p>Upload a CSV containing the viable <abbr title="A passcode is a password, employee ID number, PIN, etc.">passcodes</abbr> for which access should be granted -- or input a single one if that's all you need. Note that doing either will <strong>fully overwrite</strong> the current list.</p>

    <form method="post" action="" enctype="multipart/form-data">
        <table class="form-table" role="presentation">
            <tr>
                <th>
                    <label for="file">Upload a Passcode List</label>
                </th>
                <td>
                    <input id="file" type="file" name="ssa_file">
                </td>
            </tr>
        </table>
        <input class="button button-primary" type="submit" value="Upload">
    </form>

    <?php if (SA::$error !== false) : ?>
        <p class="error"><?= SA::$error ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="single">Only need a single passcode?</label>
                </th>
                <td>
                    <input id="single" type="text" name="ssa_single">
                </td>
            </tr>
        </table>
        <input class="button button-primary" type="submit" value="Save">
    </form>

    <hr>

    <h3>Logo</h3>
    <p>If a logo for your site has been set in <a href="<?= admin_url('customize.php') ?> target="_blank">Customizer</a> > Site Identity, it will be used. If you want to override that, you can set one here by copying an image's URL from your Media Library.</p>

    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="url">Logo</label>
                </th>
                <td>
                    <input id="url" type="url" name="ssa_logo" value="<?= self::get_setting('logo') ?>" size="69">
                </td>
            </tr>
        </table>
        <input class="button button-primary" type="submit" value="Save">
    </form>

    <hr>

    <h3>Colors</h3>
    <p>You can add to this plugin's CSS via the <code>ssa_additional_css</code> filter, but if you just want to adjust the main background and foreground colors, you can do so here.</p>

    <form method="post" action="">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="background">Background</label>
                </th>
                <td>
                    <input id="background" type="text" name="ssa_background" value="<?= self::get_setting('background') ?>" pattern="^#(?:[0-9a-fA-F]{3}){1,2}$" title="^#(?:[0-9a-fA-F]{3}){1,2}$">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="foreground">Foreground</label>
                </th>
                <td>
                    <input id="foreground" type="text" name="ssa_foreground" value="<?= self::get_setting('foreground') ?>" pattern="^#(?:[0-9a-fA-F]{3}){1,2}$" title="^#(?:[0-9a-fA-F]{3}){1,2}$">
                </td>
            </tr>
        </table>
        <input class="button button-primary" type="submit" value="Save">
    </form>
</div>
