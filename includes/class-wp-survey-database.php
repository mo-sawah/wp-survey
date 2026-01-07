<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Database {
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $surveys_table = $wpdb->prefix . 'surveys';
        $questions_table = $wpdb->prefix . 'survey_questions';
        $choices_table = $wpdb->prefix . 'survey_choices';
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create surveys table
        $sql = "CREATE TABLE IF NOT EXISTS $surveys_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            question text,
            banner_image varchar(500),
            facebook_page_url varchar(500),
            status varchar(20) DEFAULT 'active',
            language varchar(10) DEFAULT 'en',
            survey_type varchar(20) DEFAULT 'simple',
            display_mode varchar(20) DEFAULT 'multi-step',
            intro_enabled tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create questions table (for multi-question surveys)
        $sql = "CREATE TABLE IF NOT EXISTS $questions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) NOT NULL,
            question_text text NOT NULL,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY survey_id (survey_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create choices table
        $sql = "CREATE TABLE IF NOT EXISTS $choices_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) NOT NULL,
            question_id bigint(20) DEFAULT NULL,
            title varchar(255) NOT NULL,
            description_1 text,
            description_2 text,
            image_url varchar(500),
            sort_order int(11) DEFAULT 0,
            vote_count int(11) DEFAULT 0,
            PRIMARY KEY (id),
            KEY survey_id (survey_id),
            KEY question_id (question_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create responses table
        $sql = "CREATE TABLE IF NOT EXISTS $responses_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) NOT NULL,
            question_id bigint(20) DEFAULT NULL,
            choice_id bigint(20) NOT NULL,
            session_id varchar(255),
            name varchar(255),
            email varchar(255) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY survey_id (survey_id),
            KEY question_id (question_id),
            KEY choice_id (choice_id),
            KEY session_id (session_id),
            KEY email (email)
        ) $charset_collate;";
        dbDelta($sql);
    }
    
    // ============================================
    // SURVEY METHODS
    // ============================================
    
    public static function get_survey($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'surveys';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function get_all_surveys() {
        global $wpdb;
        $table = $wpdb->prefix . 'surveys';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    }
    
    public static function create_survey($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'surveys';
        
        $wpdb->insert($table, [
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description']),
            'question' => isset($data['question']) ? sanitize_textarea_field($data['question']) : '',
            'banner_image' => isset($data['banner_image']) ? esc_url_raw($data['banner_image']) : '',
            'facebook_page_url' => isset($data['facebook_page_url']) ? esc_url_raw($data['facebook_page_url']) : '',
            'language' => sanitize_text_field($data['language']),
            'survey_type' => isset($data['survey_type']) ? sanitize_text_field($data['survey_type']) : 'simple',
            'display_mode' => isset($data['display_mode']) ? sanitize_text_field($data['display_mode']) : 'multi-step',
            'intro_enabled' => isset($data['intro_enabled']) ? intval($data['intro_enabled']) : 1
        ]);
        
        return $wpdb->insert_id;
    }
    
    public static function update_survey($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'surveys';
        
        return $wpdb->update($table, [
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description']),
            'question' => isset($data['question']) ? sanitize_textarea_field($data['question']) : '',
            'banner_image' => isset($data['banner_image']) ? esc_url_raw($data['banner_image']) : '',
            'facebook_page_url' => isset($data['facebook_page_url']) ? esc_url_raw($data['facebook_page_url']) : '',
            'language' => sanitize_text_field($data['language']),
            'survey_type' => isset($data['survey_type']) ? sanitize_text_field($data['survey_type']) : 'simple',
            'display_mode' => isset($data['display_mode']) ? sanitize_text_field($data['display_mode']) : 'multi-step',
            'intro_enabled' => isset($data['intro_enabled']) ? intval($data['intro_enabled']) : 1
        ], ['id' => $id]);
    }
    
    public static function delete_survey($id) {
        global $wpdb;
        $surveys_table = $wpdb->prefix . 'surveys';
        $questions_table = $wpdb->prefix . 'survey_questions';
        $choices_table = $wpdb->prefix . 'survey_choices';
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        $wpdb->delete($responses_table, ['survey_id' => $id]);
        $wpdb->delete($choices_table, ['survey_id' => $id]);
        $wpdb->delete($questions_table, ['survey_id' => $id]);
        $wpdb->delete($surveys_table, ['id' => $id]);
    }
    
    // ============================================
    // QUESTION METHODS (for multi-question surveys)
    // ============================================
    
    public static function get_questions($survey_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_questions';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE survey_id = %d ORDER BY sort_order ASC",
            $survey_id
        ));
    }
    
    public static function create_question($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_questions';
        
        $wpdb->insert($table, [
            'survey_id' => intval($data['survey_id']),
            'question_text' => sanitize_textarea_field($data['question_text']),
            'sort_order' => intval($data['sort_order'])
        ]);
        
        return $wpdb->insert_id;
    }
    
    public static function update_question($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_questions';
        
        return $wpdb->update($table, [
            'question_text' => sanitize_textarea_field($data['question_text']),
            'sort_order' => intval($data['sort_order'])
        ], ['id' => $id]);
    }
    
    public static function delete_question($id) {
        global $wpdb;
        $questions_table = $wpdb->prefix . 'survey_questions';
        $choices_table = $wpdb->prefix . 'survey_choices';
        
        // Delete all choices for this question
        $wpdb->delete($choices_table, ['question_id' => $id]);
        
        // Delete the question
        return $wpdb->delete($questions_table, ['id' => $id]);
    }
    
    // ============================================
    // CHOICE METHODS
    // ============================================
    
    public static function get_choices($survey_id, $question_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_choices';
        
        if ($question_id !== null) {
            // Get choices for a specific question
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE survey_id = %d AND question_id = %d ORDER BY sort_order ASC",
                $survey_id,
                $question_id
            ));
        } else {
            // Get choices for simple survey (backward compatibility)
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE survey_id = %d AND question_id IS NULL ORDER BY sort_order ASC",
                $survey_id
            ));
        }
    }
    
    public static function create_choice($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_choices';
        
        $wpdb->insert($table, [
            'survey_id' => intval($data['survey_id']),
            'question_id' => isset($data['question_id']) ? intval($data['question_id']) : null,
            'title' => sanitize_text_field($data['title']),
            'description_1' => sanitize_textarea_field($data['description_1']),
            'description_2' => sanitize_textarea_field($data['description_2']),
            'image_url' => esc_url_raw($data['image_url']),
            'sort_order' => intval($data['sort_order'])
        ]);
        
        return $wpdb->insert_id;
    }
    
    public static function update_choice($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_choices';
        
        return $wpdb->update($table, [
            'title' => sanitize_text_field($data['title']),
            'description_1' => sanitize_textarea_field($data['description_1']),
            'description_2' => sanitize_textarea_field($data['description_2']),
            'image_url' => esc_url_raw($data['image_url']),
            'sort_order' => intval($data['sort_order'])
        ], ['id' => $id]);
    }
    
    public static function delete_choice($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_choices';
        return $wpdb->delete($table, ['id' => $id]);
    }
    
    // ============================================
    // RESPONSE METHODS
    // ============================================
    
    public static function save_response($survey_id, $choice_id, $name = '', $email = '', $question_id = null, $session_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_responses';
        $choices_table = $wpdb->prefix . 'survey_choices';
        
        $wpdb->insert($table, [
            'survey_id' => intval($survey_id),
            'question_id' => $question_id ? intval($question_id) : null,
            'choice_id' => intval($choice_id),
            'session_id' => $session_id ? sanitize_text_field($session_id) : null,
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
        ]);
        
        // Update vote count
        $wpdb->query($wpdb->prepare(
            "UPDATE $choices_table SET vote_count = vote_count + 1 WHERE id = %d",
            $choice_id
        ));
        
        return $wpdb->insert_id;
    }
    
    public static function get_responses($survey_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_responses';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE survey_id = %d ORDER BY created_at DESC",
            $survey_id
        ));
    }
    
    public static function has_voted($survey_id, $email) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_responses';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE survey_id = %d AND email = %s",
            $survey_id,
            sanitize_email($email)
        ));
        
        return $count > 0;
    }
    
    public static function has_voted_by_session($survey_id, $session_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_responses';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT question_id) FROM $table WHERE survey_id = %d AND session_id = %s",
            $survey_id,
            sanitize_text_field($session_id)
        ));
        
        return $count;
    }
    
    // ============================================
    // STATISTICS METHODS
    // ============================================
    
    public static function get_survey_stats($survey_id) {
        global $wpdb;
        $choices_table = $wpdb->prefix . 'survey_choices';
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        $total_votes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $responses_table WHERE survey_id = %d",
            $survey_id
        ));
        
        $unique_voters = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT COALESCE(session_id, email)) FROM $responses_table WHERE survey_id = %d",
            $survey_id
        ));
        
        $choices = $wpdb->get_results($wpdb->prepare(
            "SELECT id, title, vote_count FROM $choices_table WHERE survey_id = %d ORDER BY vote_count DESC",
            $survey_id
        ));
        
        return [
            'total_votes' => $total_votes,
            'unique_voters' => $unique_voters,
            'choices' => $choices
        ];
    }
    
    public static function export_emails($survey_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_responses';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT name, email, created_at FROM $table WHERE survey_id = %d ORDER BY created_at DESC",
            $survey_id
        ), ARRAY_A);
    }
}