<?php

/**
 * Plugin Name: Templates Subfolder
 * Description: Extends the checking the <code>get_query_template()</code> function does to allow all your templates, not just page templates, to live in a theme subfolder. Why? Because it looks nicer, of course. This subfolder is <code>templates</code> by default, but you can use the <code>templates_subfolder</code> filter to change it.
 * Version:     1.0.0
 * Author:      chairmanbrando
 * Author URI:  https://chairmanbrando.github.io/
 * Update URI:  false
 */

$get_query_templates = [
    '404_template_hierarchy',
    'archive_template_hierarchy',
    'attachment_template_hierarchy',
    'author_template_hierarchy',
    'category_template_hierarchy',
    'date_template_hierarchy',
    'embed_template_hierarchy',
    'frontpage_template_hierarchy',
    'home_template_hierarchy',
    'index_template_hierarchy',
    'page_template_hierarchy',
    'paged_template_hierarchy',
    'privacypolicy_template_hierarchy',
    'search_template_hierarchy',
    'single_template_hierarchy',
    'singular_template_hierarchy',
    'tag_template_hierarchy',
    'taxonomy_template_hierarchy'
];

/**
 * Call `add_filter()` for multiple hooks in one swoop of fellness. They're all called with the same
 * priority and number of arguments, so mixing and matching isn't viable.
 */
if (! function_exists('add_filters')) {
    function add_filters($filters, $callback, $priority = 10, $args = 1) {
        if (! is_array($filters)) {
            $filters = explode(',', $filters);
            $filters = array_map('trim', $filters);
        }

        foreach ($filters as $filter) {
            add_filter($filter, $callback, $priority, $args);
        }
    }
}

/**
 * Allows theme templates to be stored in a `templates` folder. Yes, page-specific templates could
 * already go in a `page-templates` folder, but this hook lets *all* templates go into a folder.
 *
 * There seems to be no otherwise straightforward way to mess with WP's template hierarchy. This is
 * in large part due to `locate_template()` running no filters whatsoever. All we get access to is
 * `template_include` which is after the template-picking has been done!
 *
 * Hence the array of filter names above: It is, according to the WP source, all of the hook names
 * that can be run in its `get_query_template()` function.
 *
 * @see `get_query_template()` in `wp-includes/template.php`
 */
add_filters($get_query_templates, function ($templates) {
    $more = array_map(function ($template) {
        return apply_filters('templates_subfolder', 'templates') . "/{$template}";
    }, $templates);

    return array_merge($more, $templates);
});

unset($get_query_templates);
