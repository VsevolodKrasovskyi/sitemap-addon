<?php
/*
Plugin Name: WP Sitemap Addon
Description: Group pages for your sitemap by simply inserting a simple shortcode [wp_sitemap_group_pages parent="{page_id}"] or [wp_sitemap_parent_no_children]
Version: 1.4
Author: Netpeak 
Author URI: https://netpeak.bg
*/


if ( !class_exists( 'WP_GitHub_Updater' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/update-plugin/updater.php';
}

if ( class_exists( 'WP_GitHub_Updater' ) ) {
    new WP_GitHub_Updater(array(
        'slug' => plugin_basename(__FILE__), 
        'proper_folder_name' => 'sitemap-addon', 
        'api_url' => 'https://github.com/VsevolodKrasovskyi/sitemap-addon.git', 
        'raw_url' => 'https://raw.github.com/VsevolodKrasovskyi/sitemap-addon/master', 
        'github_url' => 'https://github.com/VsevolodKrasovskyi/sitemap-addon/tree/main', 
        'zip_url' => 'https://github.com/VsevolodKrasovskyi/sitemap-addon/zipball/master', 
        'sslverify' => true, 
        'requires' => '3.0', 
        'tested' => '5.8', 
        'readme' => 'README.md', 
        'access_token' => '', 
    ));
}




// Ensure that the parent plugin is active
if (in_array('wp-sitemap-page/wp-sitemap-page.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    function wpsitemap_custom_grouped_pages($atts) {
        $atts = shortcode_atts(array(
            'parent' => 0,
        ), $atts, 'wp_sitemap_group_pages');

        $parent_id = intval($atts['parent']);
        
        if (!$parent_id) {
            return 'Invalid parent ID';
        }

        $output = '<ul>' . wpsitemap_get_page_and_children($parent_id) . '</ul>';
        
        return $output;
    }

    function wpsitemap_get_page_and_children($parent_id) {
        $output = '';

        $parent_page = get_post($parent_id);
        if ($parent_page) {
            $output .= '<li><a href="' . get_permalink($parent_page->ID) . '">' . $parent_page->post_title . '</a>';

            $child_pages = get_pages(array(
                'child_of' => $parent_id,
                'sort_column' => 'post_title',
                'sort_order' => 'ASC'
            ));

            if (!empty($child_pages)) {
                $output .= '<ul>';
                foreach ($child_pages as $child_page) {
                    $output .= wpsitemap_get_page_and_children($child_page->ID);
                }
                $output .= '</ul>';
            }

            $output .= '</li>';
        }

        return $output;
    }

    function wpsitemap_parent_no_children() {
        $pages = get_pages(array(
            'sort_column' => 'post_title',
            'sort_order' => 'ASC',
            'parent' => 0
        ));

        $parent_no_children_pages = array_filter($pages, function($page) {
            $children = get_pages(array(
                'child_of' => $page->ID,
                'post_status' => 'publish'
            ));
            return empty($children);
        });

        if (empty($parent_no_children_pages)) {
            return 'No parent pages without children found';
        }

        $output = '<ul>';
        foreach ($parent_no_children_pages as $page) {
            $output .= '<li><a href="' . get_permalink($page->ID) . '">' . $page->post_title . '</a></li>';
        }
        $output .= '</ul>';

        return $output;
    }

    add_shortcode('wp_sitemap_group_pages', 'wpsitemap_custom_grouped_pages');
    add_shortcode('wp_sitemap_parent_no_children', 'wpsitemap_parent_no_children');
}
?>
