<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Database {
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Use proper WordPress prefix
        $surveys_table = $wpdb->prefix . 'surveys';
        $choices_table = $wpdb->prefix . 'survey_choices';
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create surveys table
        $sql = "CREATE TABLE IF NOT EXISTS $surveys_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            question text NOT NULL,
            banner_image varchar(500),
            status varchar(20) DEFAULT 'active',
            language varchar(10) DEFAULT 'en',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create choices table
        $sql = "CREATE TABLE IF NOT EXISTS $choices_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            description_1 text,
            description_2 text,
            image_url varchar(500),
            sort_order int(11) DEFAULT 0,
            vote_count int(11) DEFAULT 0,
            PRIMARY KEY (id),
            KEY survey_id (survey_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create responses table
        $sql = "CREATE TABLE IF NOT EXISTS $responses_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            survey_id bigint(20) NOT NULL,
            choice_id bigint(20) NOT NULL,
            name varchar(255),
            email varchar(255) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY survey_id (survey_id),
            KEY choice_id (choice_id),
            KEY email (email)
        ) $charset_collate;";
        dbDelta($sql);
    }
    
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
            'question' => sanitize_textarea_field($data['question']),
            'banner_image' => isset($data['banner_image']) ? esc_url_raw($data['banner_image']) : '',
            'language' => sanitize_text_field($data['language'])
        ]);
        
        return $wpdb->insert_id;
    }
    
    public static function update_survey($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'surveys';
        
        return $wpdb->update($table, [
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description']),
            'question' => sanitize_textarea_field($data['question']),
            'banner_image' => isset($data['banner_image']) ? esc_url_raw($data['banner_image']) : '',
            'language' => sanitize_text_field($data['language'])
        ], ['id' => $id]);
    }
    
    public static function delete_survey($id) {
        global $wpdb;
        $surveys_table = $wpdb->prefix . 'surveys';
        $choices_table = $wpdb->prefix . 'survey_choices';
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        $wpdb->delete($responses_table, ['survey_id' => $id]);
        $wpdb->delete($choices_table, ['survey_id' => $id]);
        $wpdb->delete($surveys_table, ['id' => $id]);
    }
    
    public static function get_choices($survey_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_choices';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE survey_id = %d ORDER BY sort_order ASC",
            $survey_id
        ));
    }
    
    public static function create_choice($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_choices';
        
        $wpdb->insert($table, [
            'survey_id' => intval($data['survey_id']),
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
    
    public static function save_response($survey_id, $choice_id, $name, $email) {
        global $wpdb;
        $table = $wpdb->prefix . 'survey_responses';
        $choices_table = $wpdb->prefix . 'survey_choices';
        
        $wpdb->insert($table, [
            'survey_id' => intval($survey_id),
            'choice_id' => intval($choice_id),
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
        ]);
        
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
    
    public static function get_survey_stats($survey_id) {
        global $wpdb;
        $choices_table = $wpdb->prefix . 'survey_choices';
        $responses_table = $wpdb->prefix . 'survey_responses';
        
        $total_votes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $responses_table WHERE survey_id = %d",
            $survey_id
        ));
        
        $unique_voters = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT email) FROM $responses_table WHERE survey_id = %d",
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
