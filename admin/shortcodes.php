<?php
function wpsitemap_render_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('WP Sitemap Shortcodes', 'wp-sitemap-addon'); ?></h1>
        <h2><?php _e('Available Shortcodes', 'wp-sitemap-addon'); ?></h2>
        <ul>
            <li><code>[wp_sitemap_group_pages parent="1"]</code> - <?php _e('Display sitemap starting from parent page with ID 1', 'wp-sitemap-addon'); ?></li>
            <li><code>[wp_sitemap_parent_no_children]</code> - <?php _e('Display parent pages with no children', 'wp-sitemap-addon'); ?></li>
            <li><code>[wp_sitemap_group_pages_exclude exclude="10,20"]</code> - <?php _e('Display sitemap starting excluding pages with IDs 10 and 20', 'wp-sitemap-addon'); ?></li>
        </ul>
    </div>
    <?php
}
