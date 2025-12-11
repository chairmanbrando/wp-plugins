<?php

/**
 * Plugin Name: Debug File Viewer
 * Description: Adds a "tool" that displays <code>debug.log</code> in the back end of your site. Why? Because some hosts restrict access to this file, and going through SFTP to view it is a minor pain in the butt.
 * Version:     1.2.0
 * Author:      Stovepipe
 * Author URI:  https://stovepipeco.com/
 * Update URI:  false
 */

namespace Stovepipe;

if (! defined('ABSPATH')) exit;

// Nothing to do if they don't have both of these!
if (! defined('WP_DEBUG') || ! WP_DEBUG) return;
if (! defined('WP_DEBUG_LOG') || ! WP_DEBUG_LOG) return;

define('DFV_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
define('DFV_URL', untrailingslashit(plugin_dir_url(__FILE__)));

if (! defined('DFV_MAX_LINES')) {
    define('DFV_MAX_LINES', 200);
}

class DebugFileViewer {

    private static $filepath;
    private static $fileurl;
    private static $filesize;
    private static $maxlines;
    private static $curlines;

    public static function action() {
        add_action('wp_loaded',  __CLASS__ . '::wp_loaded');
        add_action('admin_menu', __CLASS__ . '::admin_menu');
    }

    // ----- @privates -------------------------------------------------------------------------- //

    private static function action_clean() {
        $remove = [
            '/^\[.+UTC\] \n/',
            '/.+auditor:.+\n/',
            '/.+Deprecated.+\n/',
            '/.+Notice.+\n/'
        ];

        $read  = fopen(self::$filepath, 'r');
        $write = fopen(self::$filepath . '.tmp', 'w');

        while (! feof($read)) {
            $line = fgets($read);
            $line = preg_replace($remove, '', $line);

            fputs($write, $line);
        }

        fclose($read);
        fclose($write);

        unlink(self::$filepath);
        rename(self::$filepath . '.tmp', self::$filepath);
        self::redirect();
    }

    private static function action_clear() {
        file_put_contents(self::$filepath, '');
        self::redirect();
    }

    // @todo Check if [g]zip capability exists and zip the backup.
    private static function action_fresh() {
        $new = (strrpos(self::$filepath, '.') === false)
             ? sprintf('%s-%s', self::$filepath, time())
             : str_replace('.', sprintf('-%s.', time()), self::$filepath);

        rename(self::$filepath, $new);
        self::redirect();
    }

    private static function count_lines() {
        $file = new \SplFileObject(self::$filepath, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key() + 1;
    }

    private static function file_size() {
        $bytes  = filesize(self::$filepath);
        $factor = floor((strlen($bytes) - 1) / 3);
        $units  = explode(' ', 'B K M G T P');

        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }

    private static function file_stats() {
        self::$filepath = (is_bool(WP_DEBUG_LOG)) ? WP_CONTENT_DIR . '/debug.log' : ABSPATH . WP_DEBUG_LOG;
        self::$fileurl  = (is_bool(WP_DEBUG_LOG)) ? WP_CONTENT_URL . '/debug.log' : trailingslashit(home_url()) . WP_DEBUG_LOG;

        if (! file_exists(self::$filepath)) {
            touch(self::$filepath);
        }

        self::$filesize = self::file_size();
        self::$curlines = self::count_lines();
        self::$maxlines = apply_filters('dfv_max_lines', DFV_MAX_LINES);
    }

    private static function read_lines($num) {
        $file = new \SplFileObject(self::$filepath, 'r');
        $file->seek(PHP_INT_MAX);

        try {
            $last  = $file->key();
            $start = max(0, $last - $num + 1);
            $count = ($last >= $start) ? ($last - $start + 1) : 0;

            if ($count <= 0) {
                $lines = [];
            } else {
                $lines = new \LimitIterator($file, $start, $count);
                $lines = iterator_to_array($lines);
            }
        } catch (\OutOfBoundsException $e) {
            $lines = [];
        }

        $file  = null;

        return apply_filters('dfv_read_lines', $lines);
    }

    private static function redirect() {
        if (wp_redirect(admin_url('tools.php?page=debug-file-viewer'))) {
            exit;
        }

        return false;
    }

    // ----- @hooks ----------------------------------------------------------------------------- //

    public static function wp_loaded() {
        if ('tools.php' !== $GLOBALS['pagenow']) return;
        if (! array_key_exists('page', $_GET)) return;
        if ('debug-file-viewer' !== $_GET['page']) return;

        self::file_stats();

        if (! array_key_exists('action', $_GET)) return;

        switch ($_GET['action']) {
            case 'dfv-clean' : self::action_clean(); break;
            case 'dfv-clear' : self::action_clear(); break;
            case 'dfv-fresh' : self::action_fresh(); break;
        }
    }

    public static function admin_menu() {
        if (! defined('WP_DEBUG') || ! WP_DEBUG) return;
        if (! defined('WP_DEBUG_LOG') || ! WP_DEBUG_LOG) return;

        add_submenu_page('tools.php', 'Debug Viewer', 'Debug File Viewer', 'manage_options', 'debug-file-viewer', __CLASS__ . '::admin_page');
    }

    public static function admin_page() {
        $lines = self::read_lines(DFV_MAX_LINES);

        $lines = array_map(function ($line) {
            $line = str_replace('<', '&lt;', $line);

            if (strpos($line, 'Fatal error') !== false || strpos($line, 'Parse error') !== false) {
                $line = "<mark>{$line}</mark>";
            }

            return $line;
        }, $lines);

        $lines = trim(implode('', array_filter($lines)));

        require DFV_DIR . '/admin.php';
    }

}

DebugFileViewer::action();

// add_action('admin_init', function () {
//     $message = 'You can uncomment this hook to have some gibberish data drop into your log file each '
//              . 'time an admin page is accessed. That way you can test the action buttons with ease.';
//     $cipher  = 'aes-128-gcm';
//
//     if (in_array($cipher, openssl_get_cipher_methods())) {
//         $iv  = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
//         $key = openssl_random_pseudo_bytes(32);
//
//         error_log(openssl_encrypt($message, $cipher, $key, 0, $iv, $tag));
//     }
// });
