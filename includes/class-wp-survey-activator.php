<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Activator {
    
    public static function activate() {
        WP_Survey_Database::create_tables();
        
        add_option('wp_survey_version', WP_SURVEY_VERSION);
        
        flush_rewrite_rules();
    }
}
