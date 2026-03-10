<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Activator {

    public static function activate() {
        WP_Survey_Database::create_tables();
        self::run_migrations();
        add_option('wp_survey_version', WP_SURVEY_VERSION);
        update_option('wp_survey_db_version', WP_SURVEY_VERSION);
        flush_rewrite_rules();
    }

    /**
     * Public so wp_survey_init() can call it on every load
     * when the stored db_version is behind the current version.
     */
    public static function run_migrations() {
        global $wpdb;

        // ── SURVEYS TABLE ──────────────────────────────────────────
        $t = $wpdb->prefix . 'surveys';

        $cols = [
            'banner_image'         => "ALTER TABLE $t ADD COLUMN banner_image varchar(500) AFTER question",
            'facebook_page_url'    => "ALTER TABLE $t ADD COLUMN facebook_page_url varchar(500) AFTER banner_image",
            'survey_type'          => "ALTER TABLE $t ADD COLUMN survey_type varchar(20) DEFAULT 'simple' AFTER language",
            'display_mode'         => "ALTER TABLE $t ADD COLUMN display_mode varchar(20) DEFAULT 'multi-step' AFTER survey_type",
            'intro_enabled'        => "ALTER TABLE $t ADD COLUMN intro_enabled tinyint(1) DEFAULT 1 AFTER display_mode",
            'allow_multiple_votes' => "ALTER TABLE $t ADD COLUMN allow_multiple_votes tinyint(1) DEFAULT 0 AFTER intro_enabled",
        ];

        foreach ($cols as $col => $sql) {
            if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE '$col'"))) {
                $wpdb->query($sql);
            }
        }

        // ── CHOICES TABLE ──────────────────────────────────────────
        $t = $wpdb->prefix . 'survey_choices';
        if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE 'question_id'"))) {
            $wpdb->query("ALTER TABLE $t ADD COLUMN question_id bigint(20) DEFAULT NULL AFTER survey_id");
            $wpdb->query("ALTER TABLE $t ADD KEY question_id (question_id)");
        }

        // ── RESPONSES TABLE ────────────────────────────────────────
        $t = $wpdb->prefix . 'survey_responses';
        if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE 'name'"))) {
            $wpdb->query("ALTER TABLE $t ADD COLUMN name varchar(255) AFTER choice_id");
        }
        if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE 'question_id'"))) {
            $wpdb->query("ALTER TABLE $t ADD COLUMN question_id bigint(20) DEFAULT NULL AFTER survey_id");
            $wpdb->query("ALTER TABLE $t ADD KEY question_id (question_id)");
        }
        if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE 'session_id'"))) {
            $wpdb->query("ALTER TABLE $t ADD COLUMN session_id varchar(255) AFTER choice_id");
            $wpdb->query("ALTER TABLE $t ADD KEY session_id (session_id)");
        }

        // ── QUESTIONS TABLE ────────────────────────────────────────
        $t = $wpdb->prefix . 'survey_questions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$t'")) {
            if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE 'allow_multiple'"))) {
                $wpdb->query("ALTER TABLE $t ADD COLUMN allow_multiple tinyint(1) DEFAULT 0 AFTER question_text");
            }
            if (empty($wpdb->get_results("SHOW COLUMNS FROM $t LIKE 'max_choices'"))) {
                $wpdb->query("ALTER TABLE $t ADD COLUMN max_choices int(11) DEFAULT 0 AFTER allow_multiple");
            }
        }
    }
}
