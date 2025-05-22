<?php

/**
 * Plugin Name: Sort by Modified Date
 * Description: Adds columns to pages and posts to sort by their modified date. It also adds a column for word count because I was curious about such data.
 * Version:     1.2.1
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

if (! defined('ABSPATH')) exit;

class SortByModifiedDate {

    public static function action() {
        add_action('admin_head',                           __CLASS__ . '::admin_head');
        add_filter('manage_posts_columns',                 __CLASS__ . '::column_manage');
        add_filter('manage_pages_columns',                 __CLASS__ . '::column_manage');
        add_action('manage_posts_custom_column',           __CLASS__ . '::column_display', 10, 2);
        add_action('manage_pages_custom_column',           __CLASS__ . '::column_display', 10, 2);
        add_filter('manage_edit-post_sortable_columns',    __CLASS__ . '::column_sortable');
        add_filter('manage_edit-page_sortable_columns',    __CLASS__ . '::column_sortable');
        add_filter('manage_edit-product_sortable_columns', __CLASS__ . '::column_sortable');
    }

    public static function admin_head() {
        echo <<<STYLE
            <style>
                .wp-list-table.fixed .column-date,
                .wp-list-table.fixed .column-modified {
                    width: 13%;
                }
                .wp-list-table.fixed .column-categories,
                .wp-list-table.fixed .column-tags {
                    width: 7%;
                }
                .wp-list-table.fixed .column-word-count {
                    width: 6%;
                }
            </style>
        STYLE;
    }

    public static function column_display($column, $pid) {
        if ($column === 'modified') {
            printf(__('Modified<br>%s at %s'), get_the_modified_date('Y/m/d'), get_the_modified_time());
        }

        if ($column === 'word-count') {
            $content = get_post_field('post_content', $GLOBALS['post']);
            $count   = str_word_count(strip_tags(strip_shortcodes($content)));

            echo apply_filters('sbmd_word_count', $count, $content);
        }
    }

    public static function column_manage($columns) {
        return array_merge($columns, [
            'modified'   => __('Modified'),
            'word-count' => __('Words')
        ]);
    }

    public static function column_sortable($columns) {
        return array_merge($columns, [
            'modified'   => 'modified',
            'word-count' => 'word-count'
        ]);
    }

}

add_action('plugins_loaded', function () {
    SortByModifiedDate::action();
});
