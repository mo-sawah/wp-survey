<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Activator {
    
    public static function activate() {
        WP_Survey_Database::create_tables();
        self::maybe_add_new_columns();
        add_option('wp_survey_version', WP_SURVEY_VERSION);
        flush_rewrite_rules();
    }
    
    private static function maybe_add_new_columns() {
        global $wpdb;
        
        // ============================================
        // SURVEYS TABLE
        // ============================================
        $surveys_table = $wpdb->prefix . 'surveys';
        
        $checks = [
            'banner_image'         => "ALTER TABLE $surveys_table ADD COLUMN banner_image varchar(500) AFTER question",
            'facebook_page_url'    => "ALTER TABLE $surveys_table ADD COLUMN facebook_page_url varchar(500) AFTER banner_image",
            'survey_type'          => "ALTER TABLE $surveys_table ADD COLUMN survey_type varchar(20) DEFAULT 'simple' AFTER language",
            'display_mode'         => "ALTER TABLE $surveys_table ADD COLUMN display_mode varchar(20) DEFAULT 'multi-step' AFTER survey_type",
            'intro_enabled'        => "ALTER TABLE $surveys_table ADD COLUMN intro_enabled tinyint(1) DEFAULT 1 AFTER display_mode",
            'allow_multiple_votes' => "ALTER TABLE $surveys_table ADD COLUMN allow_multiple_votes tinyint(1) DEFAULT 0 AFTER intro_enabled",
        ];
        
        foreach ($checks as $column => $sql) {
            $exists = $wpdb->get_results("SHOW COLUMNS FROM $surveys_table LIKE '$column'");
            if (empty($exists)) {
                $wpdb->query($sql);
            }
        }
        
        // ============================================
        // CHOICES TABLE
        // ============================================
        $choices_table = $wpdb->prefix . 'survey_choices';
        
        $col = $wpdb->get_results("SHOW COLUMNS FROM $choices_table LIKE 'question_id'");
        if (empty($col)) {
            $wpdb->query("ALTER TABLE $choices_table ADD COLUMN question_id bigint(20) DEFAULT NULL AFTER survey_id");
            $wpdb->query("ALTER TABLE $choices_table ADD KEY question_id (question_id)");
        }
        
        // ============================================
        // RESPONSES TABLE
        // ============================================
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        $response_checks = [
            'name'       => "ALTER TABLE $responses_table ADD COLUMN name varchar(255) AFTER choice_id",
            'question_id' => null, // handled separately for key
            'session_id'  => null, // handled separately for key
        ];
        
        $col = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'name'");
        if (empty($col)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN name varchar(255) AFTER choice_id");
        }
        
        $col = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'question_id'");
        if (empty($col)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN question_id bigint(20) DEFAULT NULL AFTER survey_id");
            $wpdb->query("ALTER TABLE $responses_table ADD KEY question_id (question_id)");
        }
        
        $col = $wpdb->get_results("SHOW COLUMNS FROM $responses_table LIKE 'session_id'");
        if (empty($col)) {
            $wpdb->query("ALTER TABLE $responses_table ADD COLUMN session_id varchar(255) AFTER choice_id");
            $wpdb->query("ALTER TABLE $responses_table ADD KEY session_id (session_id)");
        }
        
        // ============================================
        // QUESTIONS TABLE
        // ============================================
        $questions_table = $wpdb->prefix . 'survey_questions';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$questions_table'");
        if ($table_exists) {
            $col = $wpdb->get_results("SHOW COLUMNS FROM $questions_table LIKE 'allow_multiple'");
            if (empty($col)) {
                $wpdb->query("ALTER TABLE $questions_table ADD COLUMN allow_multiple tinyint(1) DEFAULT 0 AFTER question_text");
            }
            
            $col = $wpdb->get_results("SHOW COLUMNS FROM $questions_table LIKE 'max_choices'");
            if (empty($col)) {
                $wpdb->query("ALTER TABLE $questions_table ADD COLUMN max_choices int(11) DEFAULT 0 AFTER allow_multiple");
            }
        }
    }
}
