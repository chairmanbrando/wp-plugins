<style><?= file_get_contents(DFV_DIR . '/admin.css') ?></style>

<div class="wrap">
    <h1><?= __('Debug File Viewer') ?></h1>
    <p><?= __(sprintf('Since you have both <code>WP_DEBUG</code> and <code>WP_DEBUG_LOG</code> enabled, some of your debug file is shown below. This is currently limited to last %s lines because because of reasons. You can change this by adding a <code>DFV_MAX_LINES</code> constant to your config file or with the <code>dfv_max_lines</code> filter.', self::$maxlines)) ?></p>
    <p><?= __('For your convenience, (a) the output has been scrolled to the bottom if needed to show the most recent items, and (2) lines that contain a fatal or parse error have been highlighted. If you need to see the full file, use the view or download buttons below.') ?></p>
    <h2><?= self::$filepath ?>: <?= self::$curlines ?> lines @ <?= self::$filesize ?> file size</h2>
    <pre id="log"><?= $lines ?></pre>
    <h2><?= __('Actions') ?></h2>
    <p><?= __("Note: Some hosts block web access to <code>.log</code> files or at least the default <code>debug.log</code> file. If that's the case on your host, the view and download buttons below won't work. Even if not blocked, the view button may download the file anyway.") ?></p>
    <a class="button" href="<?= add_query_arg('action', 'dfv-fresh') ?>"><?= __('Start New Debug File') ?></a>
    <p><?= __('Renames your current debug file using the current <code>time()</code> and starts a fresh one.') ?></p>
    <a class="button" href="<?= self::$fileurl ?>" target="_blank"><?= __('View Full Debug File') ?></a>
    <p><?= __('Opens up the full debug file in a new tab.') ?></p>
    <a class="button" href="<?= self::$fileurl ?>" download><?= __('Download Debug File') ?></a>
    <p><?= __('Does what it says on the tin.') ?></p>
    <a href="<?= add_query_arg('action', 'dfv-clean') ?>" class="button"><?= __('Clean Debug File') ?></a>
    <p><?= __('Remove deprecations, notices, and empty lines from the debug file &mdash; leaving only errors, warnings, and purposeful calls to <code>error_log()</code>. Warning: This is not reversible.') ?></p>
    <a class="button" href="<?= add_query_arg('action', 'dfv-clear') ?>"><?= __('Empty Debug File') ?></a>
    <p><?= __('Empties your current debug file. Warning: This is not reversible.') ?></p>
</div>

<script>
    jQuery(($) => $('#log').scrollTop(9999));
</script>
