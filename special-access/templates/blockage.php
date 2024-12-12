<?php

use SpecialAccess as SA;

$logo = SA::get_setting('logo');
$bg   = SA::get_setting('background');
$fg   = SA::get_setting('foreground');

?>

<!doctype html>
<html <?php language_attributes() ?>>

<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <title><?= bloginfo('name') ?> &mdash; <?= apply_filters('ssa_title', 'Passcode Required') ?></title>

    <link rel="shortcut icon" href="<?= SSA_URL ?>/assets/no.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= SSA_URL ?>/assets/style.css?<?= filemtime(SSA_DIR . '/assets/style.css') ?>">

    <style>
        :root {
            <?php if ($bg) echo "--ssa-background: {$bg};"; ?>
            <?php if ($fg) echo "--ssa-main-color: {$fg};"; ?>
        }
    </style>

    <?= ($css = apply_filters('ssa_additional_css', '')) ? sprintf('<style>%s</style>', $css) : ''; ?>
</head>

<body>
    <main>
        <?php if ($logo) : ?>
            <figure><img src="<?= $logo ?>" alt="<?php bloginfo('name') ?> logo"></figure>
        <?php elseif (has_custom_logo()) : ?>
            <figure><?= get_custom_logo() ?></figure>
        <?php else : ?>
            <figure><img src="<?= SSA_URL ?>/assets/sao.png" alt="Special Access logo"></figure>
        <?php endif ?>
        <form action="" method="post">
            <p><?= apply_filters('ssa_proceed_phrase', 'A password is required to proceed.') ?></p>
            <div class="field">
                <input type="text" name="eid" placeholder="<?= apply_filters('ssa_placeholder', '') ?>" autofocus>
                <input type="submit" value="Submit">
            </div>
        </form>
    </main>
</body>
</html>
