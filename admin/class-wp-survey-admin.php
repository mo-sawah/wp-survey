<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Admin {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_wp_survey_save', [$this, 'ajax_save_survey']);
        add_action('wp_ajax_wp_survey_delete', [$this, 'ajax_delete_survey']);
        add_action('wp_ajax_wp_survey_save_choice', [$this, 'ajax_save_choice']);
        add_action('wp_ajax_wp_survey_delete_choice', [$this, 'ajax_delete_choice']);
        add_action('wp_ajax_wp_survey_export_emails', [$this, 'ajax_export_emails']);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('WP Survey', 'wp-survey'),
            __('WP Survey', 'wp-survey'),
            'manage_options',
            'wp-survey',
            [$this, 'render_surveys_page'],
            'dashicons-clipboard',
            30
        );
        
        add_submenu_page(
            'wp-survey',
            __('All Surveys', 'wp-survey'),
            __('All Surveys', 'wp-survey'),
            'manage_options',
            'wp-survey',
            [$this, 'render_surveys_page']
        );
        
        add_submenu_page(
            'wp-survey',
            __('Add New', 'wp-survey'),
            __('Add New', 'wp-survey'),
            'manage_options',
            'wp-survey-add',
            [$this, 'render_add_edit_page']
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wp-survey') === false) return;
        
        wp_enqueue_media();
        wp_enqueue_style('wp-survey-admin', WP_SURVEY_PLUGIN_URL . 'assets/css/admin.css', [], WP_SURVEY_VERSION);
        wp_enqueue_script('wp-survey-admin', WP_SURVEY_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WP_SURVEY_VERSION, true);
        
        wp_localize_script('wp-survey-admin', 'wpSurvey', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_survey_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this?', 'wp-survey'),
                'error' => __('An error occurred', 'wp-survey'),
                'saved' => __('Saved successfully', 'wp-survey')
            ]
        ]);
    }
    
    public function render_surveys_page() {
        $surveys = WP_Survey_Database::get_all_surveys();
        include WP_SURVEY_PLUGIN_DIR . 'admin/views/surveys-list.php';
    }
    
    public function render_add_edit_page() {
        $survey_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $survey = $survey_id ? WP_Survey_Database::get_survey($survey_id) : null;
        $choices = $survey_id ? WP_Survey_Database::get_choices($survey_id) : [];
        $stats = $survey_id ? WP_Survey_Database::get_survey_stats($survey_id) : null;
        
        include WP_SURVEY_PLUGIN_DIR . 'admin/views/survey-edit.php';
    }
    
    public function ajax_save_survey() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $survey_id = isset($_POST['survey_id']) ? intval($_POST['survey_id']) : 0;
        
        $data = [
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'question' => sanitize_textarea_field($_POST['question']),
            'language' => sanitize_text_field($_POST['language'])
        ];
        
        if ($survey_id) {
            WP_Survey_Database::update_survey($survey_id, $data);
            $id = $survey_id;
        } else {
            $id = WP_Survey_Database::create_survey($data);
        }
        
        wp_send_json_success(['id' => $id, 'message' => 'Survey saved successfully']);
    }
    
    public function ajax_delete_survey() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $survey_id = intval($_POST['survey_id']);
        WP_Survey_Database::delete_survey($survey_id);
        
        wp_send_json_success(['message' => 'Survey deleted successfully']);
    }
    
    public function ajax_save_choice() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $choice_id = isset($_POST['choice_id']) ? intval($_POST['choice_id']) : 0;
        
        $data = [
            'survey_id' => intval($_POST['survey_id']),
            'title' => sanitize_text_field($_POST['title']),
            'description_1' => sanitize_textarea_field($_POST['description_1']),
            'description_2' => sanitize_textarea_field($_POST['description_2']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'sort_order' => intval($_POST['sort_order'])
        ];
        
        if ($choice_id) {
            WP_Survey_Database::update_choice($choice_id, $data);
            $id = $choice_id;
        } else {
            $id = WP_Survey_Database::create_choice($data);
        }
        
        wp_send_json_success(['id' => $id, 'message' => 'Choice saved successfully']);
    }
    
    public function ajax_delete_choice() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $choice_id = intval($_POST['choice_id']);
        WP_Survey_Database::delete_choice($choice_id);
        
        wp_send_json_success(['message' => 'Choice deleted successfully']);
    }
    
    public function ajax_export_emails() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $survey_id = intval($_POST['survey_id']);
        $emails = WP_Survey_Database::export_emails($survey_id);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="survey-emails-' . $survey_id . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Email', 'Date']);
        
        foreach ($emails as $row) {
            fputcsv($output, [$row['email'], $row['created_at']]);
        }
        
        fclose($output);
        exit;
    }
}
