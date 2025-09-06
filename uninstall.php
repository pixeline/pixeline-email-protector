<?php

/**
 * Uninstall cleanup for Pixeline Email Protector
 *
 * Removes plugin options from the database when the plugin is uninstalled.
 *
 * @package PixelineEmailProtector
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$option_name = 'p_email_protector_options';

if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    $original_blog_id = get_current_blog_id();

    foreach ($blog_ids as $blog_id) {
        switch_to_blog((int) $blog_id);
        delete_option($option_name);
    }

    switch_to_blog($original_blog_id);
    delete_site_option($option_name);
} else {
    delete_option($option_name);
}
