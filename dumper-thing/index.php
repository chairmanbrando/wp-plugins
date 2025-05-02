<?php

/**
 * Plugin Name: Dumper Thing
 * Description: Adds a few simple methods and wrapper functions (<code>dump[acfhlx]?()</code>) for checking on the value of a thing or three. If Xdebug exists, it'll use its <code>xdebug_var_dump()</code>.
 * Version:     1.1.5
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

namespace Brando {

    class Dumper {

        public static $everyone  = false;
        public static $usexdebug = true;

        public static function action() {
            $fn = function () {
                $style = file_get_contents(__DIR__ . '/style.css');
                $style = str_replace("\n", '', preg_replace(';^\s+;m', '', $style));

                echo '<style>', $style, '</style>';
            };

            add_action('wp_head',    $fn);
            add_action('admin_head', $fn);
        }

        /** Convert a variable for cleaner dump output. */
        private static function dumpify($var) {
            if (is_null($var))                  $var = 'null';
            if (is_bool($var) && $var)          $var = 'true';
            if (is_bool($var) && ! $var)        $var = 'false';
            if (is_string($var) && empty($var)) $var = '""';
            if (is_array($var) && empty($var))  $var = '[]';

            if (is_object($var) && property_exists($var, 'post_content')) {
                $var->post_content = '[REDACTED]';
            }

            if (is_array($var) && ! empty($var)) {
                foreach ($var as &$item) {
                    if (is_object($item) && property_exists($item, 'post_content')) {
                        $item->post_content = '[REDACTED]';
                    }
                }
            }

            return $var;
        }

        /** Print a variable or variables in a `<pre>` tag. */
        public static function dump(...$vars) {
            if (! self::$everyone && ! current_user_can('administrator')) return;

            foreach ($vars as $var) {
                if (apply_filters('dumper_use_xdebug', self::$usexdebug)) {
                    if (function_exists('xdebug_var_dump')) {
                        @ini_set('xdebug.var_display_max_depth', 10);
                        self::dump_xdebug($var);
                        continue;
                    }
                }

                $var = self::dumpify($var);

                ob_start();
                print_r($var);

                $output = ob_get_clean();
                $output = htmlspecialchars($output);

                printf('<pre class="dump">%s</pre>', $output);
            }
        }

        /** Print a variable or variables in a `<pre>` tag in the admin "footer" area. */
        public static function dump_admin(...$vars) {
            if (! is_admin()) return;

            foreach ($vars as $var) {
                add_action('admin_footer', function () use ($var) {
                    self::dump($var);
                });
            }
        }

        /** Print a variable or variables to the JS console. */
        public static function dump_console(...$vars) {
            ob_start();

            foreach ($vars as $var) {
                var_export($var);
            }

            $ob = ob_get_clean();
            $ob = preg_replace(';\s+;', ' ', $ob);

            printf('<script>console.log(\'%s\')</script>', addslashes($ob));
        }

        /** Print a variable or variables in a `<pre>` tag above the theme's header. */
        public static function dump_header(...$vars) {
            if (is_admin()) return;

            foreach ($vars as $var) {
                add_action('wp_body_open', function () use ($var) {
                    self::dump($var);
                });
            }
        }

        /** Print a variable or variables in a `<pre>` tag below the theme's footer. */
        public static function dump_footer(...$vars) {
            if (is_admin()) return;

            foreach ($vars as $var) {
                add_action('wp_footer', function () use ($var) {
                    self::dump($var);
                });
            }
        }

        /** Print a variable or variables directly to the log file. */
        public static function dump_log(...$vars) {
            foreach ($vars as $var) {
                error_log(var_export($var, true));
            }
        }

        /** Let Xdebug print a variable or variables instead... but with cleaner outout! */
        public static function dump_xdebug($var) {
            ob_start();
            xdebug_var_dump($var); // `__LINE__ - X` should point to this!

            $output  = ob_get_clean();
            $where   = __FILE__ . ':' . __LINE__ - 3;
            $pattern = "<small>{$where}:</small>\n?";

            echo preg_replace(";{$pattern};", '', $output);
        }

    }

    add_action('plugins_loaded', function () {
        Dumper::action();
    });

}

// Double namespaces (officially "strongly discouraged") because I wanted to use one file... Fun!
// This one is the global namespace, of course, which we need to pollute with wrappers. If we don't
// then me calling this stuff simple is a bit of a fib.
namespace {

    add_action('plugins_loaded', function () {
        if (! function_exists('dump')) {
            function dump(...$vars) {
                call_user_func_array('Brando\Dumper::dump', $vars);
            }
        }

        if (! function_exists('dumpa')) {
            function dumpa(...$vars) {
                call_user_func_array('Brando\Dumper::dump_admin', $vars);
            }
        }

        if (! function_exists('dumpc')) {
            function dumpc(...$vars) {
                call_user_func_array('Brando\Dumper::dump_console', $vars);
            }
        }

        if (! function_exists('dumpf')) {
            function dumpf(...$vars) {
                call_user_func_array('Brando\Dumper::dump_footer', $vars);
            }
        }

        if (! function_exists('dumph')) {
            function dumph(...$vars) {
                call_user_func_array('Brando\Dumper::dump_header', $vars);
            }
        }

        if (! function_exists('dumpl')) {
            function dumpl(...$vars) {
                call_user_func_array('Brando\Dumper::dump_log', $vars);
            }
        }

        if (! function_exists('dumpx')) {
            function dumpx(...$vars) {
                call_user_func_array('Brando\Dumper::dump_xdebug', $vars);
            }
        }
    });

}
