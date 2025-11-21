<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Public {
    
    public function __construct() {
        add_shortcode('wp_survey', [$this, 'render_survey']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_ajax_wp_survey_submit', [$this, 'ajax_submit_vote']);
        add_action('wp_ajax_nopriv_wp_survey_submit', [$this, 'ajax_submit_vote']);
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style('wp-survey-frontend', WP_SURVEY_PLUGIN_URL . 'assets/css/frontend.css', [], WP_SURVEY_VERSION);
        wp_enqueue_script('wp-survey-frontend', WP_SURVEY_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], WP_SURVEY_VERSION, true);
        
        wp_localize_script('wp-survey-frontend', 'wpSurveyPublic', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_survey_public_nonce')
        ]);
    }
    
    public function render_survey($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'type' => 'full'
        ], $atts);
        
        $survey_id = intval($atts['id']);
        $type = sanitize_text_field($atts['type']);
        
        if (!$survey_id) {
            return '<p>' . __('Survey ID is required', 'wp-survey') . '</p>';
        }
        
        $survey = WP_Survey_Database::get_survey($survey_id);
        if (!$survey) {
            return '<p>' . __('Survey not found', 'wp-survey') . '</p>';
        }
        
        $choices = WP_Survey_Database::get_choices($survey_id);
        if (empty($choices)) {
            return '<p>' . __('No choices available', 'wp-survey') . '</p>';
        }
        
        ob_start();
        include WP_SURVEY_PLUGIN_DIR . 'public/views/survey-' . ($type === 'widget' ? 'widget' : 'full') . '.php';
        return ob_get_clean();
    }
    
    public function ajax_submit_vote() {
        check_ajax_referer('wp_survey_public_nonce', 'nonce');
        
        $survey_id = intval($_POST['survey_id']);
        $choice_id = intval($_POST['choice_id']);
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        
        // Check cookie to prevent duplicate voting
        $cookie_name = 'wp_survey_voted_' . $survey_id;
        if (isset($_COOKIE[$cookie_name])) {
            wp_send_json_error(['message' => __('You have already voted in this survey', 'wp-survey')]);
        }
        
        if (empty($name)) {
            wp_send_json_error(['message' => __('Please enter your name', 'wp-survey')]);
        }
        
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Invalid email address', 'wp-survey')]);
        }
        
        if (WP_Survey_Database::has_voted($survey_id, $email)) {
            wp_send_json_error(['message' => __('You have already voted in this survey', 'wp-survey')]);
        }
        
        WP_Survey_Database::save_response($survey_id, $choice_id, $name, $email);
        
        // Set cookie for 30 days
        setcookie($cookie_name, '1', time() + (30 * 24 * 60 * 60), '/');
        
        wp_send_json_success(['message' => __('Thank you for your vote!', 'wp-survey')]);
    }
}
