<?php

/**
 * Plugin Name: Remember Me
 * Description: I'll never let go, Jack. (Checks the "Remember Me" box on the login screen so you don't have to!)
 * Author: chairmanbrando
 * Author URI: https://chairmanbrando.github.io/
 * Version: 1.0
 * Update URI: false
 * Note: Even if it could, this plugin will never check the stupid "pineapple is delicious on pizza" input on wordpress.com sites. Matt has lost his damned mind.
 * Link: https://en.wikipedia.org/wiki/WP_Engine#WordPress_dispute_and_lawsuit
 */

add_action('login_footer', function () {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', (e) => {
            document.querySelector('#rememberme').checked = true;
        });
    </script>
    <?php
});
