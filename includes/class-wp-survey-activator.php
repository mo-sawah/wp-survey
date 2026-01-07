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
        
        // ============================================
        // UPDATE SURVEYS TABLE
        // ============================================
        $surveys_table = $wpdb->prefix . 'surveys';
        
        // Check and add banner_image
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE 'banner_image'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $surveys_table ADD COLUMN banner_image varchar(500) AFTER question");
        }
        
        // Check and add facebook_page_url
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE 'facebook_page_url'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $surveys_table ADD COLUMN facebook_page_url varchar(500) AFTER banner_image");
        }
        
        // Check and add survey_type
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE 'survey_type'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $surveys_table ADD COLUMN survey_type varchar(20) DEFAULT 'simple' AFTER language");
        }
        
        // Check and add display_mode
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE 'display_mode'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $surveys_table ADD COLUMN display_mode varchar(20) DEFAULT 'multi-step' AFTER survey_type");
        }
        
        // Check and add intro_enabled
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE 'intro_enabled'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $surveys_table ADD COLUMN intro_enabled tinyint(1) DEFAULT 1 AFTER display_mode");
        }
        
        // ============================================
        // UPDATE CHOICES TABLE
        // ============================================
        $choices_table = $wpdb->prefix . 'survey_choices';
        
        // Check and add question_id
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $choices_table LIKE 'question_id'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $choices_table ADD COLUMN question_id bigint(20) DEFAULT NULL AFTER survey_id");
            $wpdb->query("ALTER TABLE $choices_table ADD KEY question_id (question_id)");
        }
        
        // ============================================
        // UPDATE RESPONSES TABLE
        // ============================================
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        // Check and add name
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'name'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN name varchar(255) AFTER choice_id");
        }
        
        // Check and add question_id
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'question_id'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN question_id bigint(20) DEFAULT NULL AFTER survey_id");
            $wpdb->query("ALTER TABLE $responses_table ADD KEY question_id (question_id)");
        }
        
        // Check and add session_id
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'session_id'");
        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN session_id varchar(255) AFTER choice_id");
            $wpdb->query("ALTER TABLE $responses_table ADD KEY session_id (session_id)");
        }
        
        // ============================================
        // UPDATE QUESTIONS TABLE
        // ============================================
        $questions_table = $wpdb->prefix . 'survey_questions';
        
        // ============================================
        // UPDATE QUESTIONS TABLE
        // ============================================
        $questions_table = $wpdb->prefix . 'survey_questions';
        
        // Check if questions table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$questions_table'");
        if ($table_exists) {
            // Check and add allow_multiple to questions table
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $questions_table LIKE 'allow_multiple'");
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $questions_table ADD COLUMN allow_multiple tinyint(1) DEFAULT 0 AFTER question_text");
            }

            // START NEW CODE: Check and add max_choices
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $questions_table LIKE 'max_choices'");
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $questions_table ADD COLUMN max_choices int(11) DEFAULT 0 AFTER allow_multiple");
            }
            // END NEW CODE
        }
    }
}