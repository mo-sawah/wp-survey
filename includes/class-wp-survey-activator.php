<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Activator {
    
    public static function activate() {
        WP_Survey_Database::create_tables();
        
        // Add new columns if they don't exist (for existing installations)
        self::maybe_add_new_columns();
        
        add_option('wp_survey_version', WP_SURVEY_VERSION);
        
        flush_rewrite_rules();
    }
    
    private static function maybe_add_new_columns() {
        global $wpdb;
        
        // Check and add banner_image to surveys table
        $surveys_table = $wpdb->prefix . 'surveys';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE 'banner_image'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $surveys_table ADD COLUMN banner_image varchar(500) AFTER question");
        }
        
        // Check and add name to responses table
        $responses_table = $wpdb->prefix . 'survey_responses';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'name'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN name varchar(255) AFTER choice_id");
        }
    }
}
