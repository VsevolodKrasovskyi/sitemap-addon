<?php
/*
Plugin Name: WP Sitemap Addon
Description: Group pages for your sitemap by simply inserting a simple shortcode [wp_sitemap_group_pages parent="{page_id}"] or [wp_sitemap_parent_no_children]
Version: 1.3.2
Author: Netpeak 
Text Domain: wp-sitemap-addon
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

    //INCLUDE
    // Add button to plugin row meta
    function wpsitemap_plugin_row_meta($links, $file) {
        if (strpos($file, plugin_basename(__FILE__)) !== false) {
            $new_links = array(
                '<a href="' . admin_url('admin.php?page=wpsitemap_shortcodes') . '">Shortcodes</a>'
            );
            $links = array_merge($links, $new_links);
        }
        return $links;
    }
    add_filter('plugin_row_meta', 'wpsitemap_plugin_row_meta', 10, 2);

    // Create admin page for shortcodes
    function wpsitemap_add_admin_menu() {
        add_submenu_page(
            null, // No parent slug, so it won't show up in the sidebar
            __('WP Sitemap Shortcodes', 'wp-sitemap-addon'), // Page title
            __('Sitemap Shortcodes', 'wp-sitemap-addon'),    // Menu title
            'manage_options',        // Capability
            'wpsitemap_shortcodes',  // Menu slug
            'wpsitemap_render_admin_page' // Callback function
        );
    
    }
    add_action('admin_menu', 'wpsitemap_add_admin_menu');

    // Include the shortcodes admin page
    require_once plugin_dir_path(__FILE__) . 'admin/shortcodes.php';



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
    function wpsitemap_custom_grouped_pages_exclude($atts) {
        $atts = shortcode_atts(array(
            'parent' => 0,
            'exclude' => ''
        ), $atts, 'wp_sitemap_group_pages_exclude');
    
        $parent_id = intval($atts['parent']);
        $exclude_ids = array_map('intval', explode(',', $atts['exclude']));
    
        if (!$parent_id) {
            // If no parent is specified, start from the top-level pages
            $pages = get_pages(array(
                'sort_column' => 'post_title',
                'sort_order' => 'ASC',
                'parent' => 0
            ));
            $output = '<ul>';
            foreach ($pages as $page) {
                if (!in_array($page->ID, $exclude_ids)) {
                    $output .= wpsitemap_get_page_and_children_exclude($page->ID, $exclude_ids);
                }
            }
            $output .= '</ul>';
        } else {
            $output = '<ul>' . wpsitemap_get_page_and_children_exclude($parent_id, $exclude_ids) . '</ul>';
        }
    
        return $output;
    }
    
    function wpsitemap_get_page_and_children_exclude($parent_id, $exclude_ids) {
        $output = '';
    
        $parent_page = get_post($parent_id);
        if ($parent_page && !in_array($parent_id, $exclude_ids)) {
            $output .= '<li><a href="' . get_permalink($parent_page->ID) . '">' . $parent_page->post_title . '</a>';
    
            $child_pages = get_pages(array(
                'child_of' => $parent_id,
                'sort_column' => 'post_title',
                'sort_order' => 'ASC'
            ));
    
            if (!empty($child_pages)) {
                $output .= '<ul>';
                foreach ($child_pages as $child_page) {
                    if (!in_array($child_page->ID, $exclude_ids)) {
                        $output .= wpsitemap_get_page_and_children_exclude($child_page->ID, $exclude_ids);
                    }
                }
                $output .= '</ul>';
            }
    
            $output .= '</li>';
        }
    
        return $output;
    }
    
    add_shortcode('wp_sitemap_group_pages_exclude', 'wpsitemap_custom_grouped_pages_exclude');
    



    
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
