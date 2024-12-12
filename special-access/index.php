<?php

/**
 * Plugin Name: Special Access
 * Description: Upload a CSV containing passcodes. These can represent employee IDs, passwords, or whatever else. People will only be able to visit the site if they input one of them -- or if they log in, of course.
 * Version:     1.1.1
 * Author:      chairmanbrando
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

define('SSA_DIR',       untrailingslashit(plugin_dir_path(__FILE__)));
define('SSA_URL',       untrailingslashit(plugin_dir_url(__FILE__)));
define('SSA_EIDS',      'ssa_option_eids');
define('SSA_PREFIX',    'ssa_option_');
define('SSA_DB_TABLE',  $GLOBALS['wpdb']->prefix . 'ssa_logs');
define('SSA_COOKIE',    'wordpress_ssa_eid'); // Starting with `wordpress_` gets around various server-level caches.
define('SSA_KEEP_LOGS', false); // Enable before plugin activation to track usage.

class SpecialAccess {

    public static $error;

    public static function action() {
        add_action('admin_init',       __CLASS__ . '::admin_init');
        add_action('admin_menu',       __CLASS__ . '::admin_menu');
        add_action('init',             __CLASS__ . '::init');
        add_action('template_include', __CLASS__ . '::blockage', 99);

        self::$error = false;
    }

    // ----- @hooks ----------------------------------------------------------------------------- //

    public static function admin_init() {
        if (! empty($_FILES) && array_key_exists('ssa_file', $_FILES)) {
            self::process_upload($_FILES['ssa_file']);
        }

        if (any_keys_exist(['ssa_single', 'ssa_logo', 'ssa_background', 'ssa_foreground'], $_POST)) {
            self::process_settings();
        }
    }

    public static function admin_menu() {
        add_submenu_page('tools.php', 'Special Access', 'Special Access', 'manage_options', 'special-access', __CLASS__ . '::admin_page');
    }

    public static function admin_page() {
        $stats = get_option(SSA_PREFIX . 'STATS');
        $eids  = get_option(SSA_EIDS);
        $log   = self::log_read();

        if ($eids) {
            natcasesort($eids);
        }

        // Will we actually see any 46-character IPv6s? That'll throw off the alignment real good!
        $log = array_map(function ($line) {
            return sprintf('%s %s %s',
                str_pad($line->ip, 18),
                str_pad(self::time_to_date($line->timestamp), 26),
                str_pad($line->employee, strlen($line->employee) + 2)
            );
        }, array_filter($log));

        require SSA_DIR . '/templates/admin.php';
    }

    public static function init() {
        if (! empty($_POST) && array_key_exists('eid', $_POST)) {
            self::process_login($_POST['eid']);
        }
    }

    public static function blockage($template) {
        if (is_user_logged_in()) {
            return $template;
        }

        if (! array_key_exists(SSA_COOKIE, $_COOKIE) || empty($_COOKIE[SSA_COOKIE])) {
            return SSA_DIR . '/templates/blockage.php';
        }

        return $template;
    }

    // ----- @publics --------------------------------------------------------------------------- //

    public static function get_setting($setting) {
        if (! $setting) return null;

        $settings = get_option(SSA_PREFIX . 'SETTINGS');

        if (! array_key_exists($setting, $settings)) return null;

        return $settings[$setting];
    }

    // ----- @privates -------------------------------------------------------------------------- //

    private static function csv_to_array($data) {
        $data = str_replace("\n", '', $data);
        $data = explode(',', $data);
        $data = array_map('trim', $data);
        $data = array_filter($data);
        $data = array_unique($data);

        return $data;
    }

    private static function dump($var) {
        echo '<pre class="ssa">';
        var_dump($var);
        echo '</pre>';
    }

    private static function get_settings() {
        return get_option(SSA_PREFIX . 'SETTINGS');
    }

    private static function log($eid) {
        return $GLOBALS['wpdb']->insert(SSA_DB_TABLE, [
            'timestamp' => time(),
            'employee'  => $eid,
            'ip'        => apply_filters('ssa_obscure_ips', $_SERVER['REMOTE_ADDR'])
        ]);
    }

    private static function log_read() {
        if (! SSA_KEEP_LOGS) return [];

        $table = SSA_DB_TABLE;

        // No `prepare` because we're writing the query.
        $log = $GLOBALS['wpdb']->get_results("
            SELECT * FROM {$table}
            ORDER BY id DESC
            LIMIT 10
        ");

        $log = array_reverse($log);

        return $log;
    }

    private static function process_login($eid) {
        $eid = sanitize_text_field($eid);

        if (! $eid) return;

        $all = (array) get_option(SSA_EIDS);

        if (in_array($eid, $all, true)) {
            setcookie(SSA_COOKIE, $eid, [
                'path'     => COOKIEPATH,
                'expires'  => time() + DAY_IN_SECONDS,
                'secure'   => is_ssl(),
                'httponly' => true
            ]);

            if (SSA_KEEP_LOGS) {
                self::log($eid);
            }

            if (wp_safe_redirect(home_url())) {
                exit;
            }
        }
    }

    private static function process_upload($file) {
        if (! empty($file['error'])) {
            return (self::$error = self::upload_error($file['error']));
        }

        $data = file_get_contents($file['tmp_name']);
        $data = trim(str_replace('\"', '', $data));
        $data = self::csv_to_array($data);

        unlink($file['tmp_name']); // We're not keeping a copy around.

        $stats = [
            'time'  => self::time_to_date(),
            'count' => sizeof($data)
        ];

        update_option(SSA_PREFIX . 'STATS', $stats);
        update_option(SSA_EIDS, $data);
    }

    private static function process_settings() {
        foreach (['ssa_logo', 'ssa_background', 'ssa_foreground'] as $key) {
            if (array_key_exists($key, $_POST)) {
                $_POST[$key] = strip_tags($_POST[$key]);
            }
        }

        if (array_key_exists('ssa_single', $_POST)) {
            $stats = [
                'time'  => self::time_to_date(),
                'count' => 1
            ];

            update_option(SSA_PREFIX . 'STATS', $stats);
            update_option(SSA_EIDS, [$_POST['ssa_single']]);
        }

        $settings = self::get_settings();

        foreach (['ssa_logo', 'ssa_background', 'ssa_foreground'] as $key) {
            if (! array_key_exists($key, $_POST)) continue;

            $skey = str_replace('ssa_', '', $key);

            if (! empty($_POST[$key])) {
                $settings[$skey] = $_POST[$key];
            }

            if (empty($_POST[$key])) {
                unset($settings[$skey]);
            }
        }

        unset($settings['single']);

        update_option(SSA_PREFIX . 'SETTINGS', $settings);
    }

    private static function time_to_date($time = null, $format = 'M j, Y @ g:i a') {
        if (! $time) {
            $time = time();
        }

        return date($format, $time + (int) get_option('gmt_offset') * HOUR_IN_SECONDS);
    }

    private static function upload_error($error) {
        $errors = [
            0 => 'There is no error. The file uploaded with success.',
            1 => 'The uploaded file exceeds the `upload_max_filesize` directive in `php.ini`.',
            2 => 'The uploaded file exceeds the `MAX_FILE_SIZE` directive that was specified in the HTML form.',
            3 => 'The uploaded file was only partially uploaded.',
            4 => 'No file was uploaded.',
            6 => 'Missing a temporary folder.',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        ];

        if (isset($errors[$error])) {
            return $errors[$error];
        }

        return 'Unknown issue has occurred. 🤷‍♀️';
    }

}

SpecialAccess::action();

// A global helper since an equivalent doesn't seem provided by PHP.
function any_keys_exist($keys, $array) {
    foreach ($keys as $key) {
        if (array_key_exists($key, $array)) {
            return true;
        }
    }

    return false;
}

register_activation_hook(__FILE__, function () {
    if (! SSA_KEEP_LOGS) return;

    $chars = $GLOBALS['wpdb']->get_charset_collate();
    $table = SSA_DB_TABLE;

    $sql = <<<SQL
        CREATE TABLE {$table} (
            id int(20) unsigned NOT NULL AUTO_INCREMENT,
            timestamp int(32) unsigned NOT NULL,
            employee varchar(20) NOT NULL,
            ip varchar(46) NOT NULL,
            PRIMARY KEY (id)
        ) {$chars};
    SQL;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta(trim($sql));
});
