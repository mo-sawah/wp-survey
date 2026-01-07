<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Public {
    
    public function __construct() {
        add_shortcode('wp_survey', [$this, 'render_survey']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_ajax_wp_survey_submit', [$this, 'ajax_submit_vote']);
        add_action('wp_ajax_nopriv_wp_survey_submit', [$this, 'ajax_submit_vote']);
        add_action('wp_ajax_wp_survey_submit_multi', [$this, 'ajax_submit_multi_vote']);
        add_action('wp_ajax_nopriv_wp_survey_submit_multi', [$this, 'ajax_submit_multi_vote']);
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
            'type' => 'full' // full or widget (for simple surveys)
        ], $atts);
        
        $survey_id = intval($atts['id']);
        $type = sanitize_text_field($atts['type']);
        
        if (!$survey_id) {
            return '<p>' . __('Survey ID required', 'wp-survey') . '</p>';
        }
        
        $survey = WP_Survey_Database::get_survey($survey_id);
        if (!$survey) {
            return '<p>' . __('Survey not found', 'wp-survey') . '</p>';
        }
        
        // Check survey type
        if ($survey->survey_type === 'multi-question') {
            return $this->render_multi_question_survey($survey);
        } else {
            // Simple survey (backward compatibility)
            return $this->render_simple_survey($survey, $type);
        }
    }
    
    private function render_simple_survey($survey, $type) {
        $choices = WP_Survey_Database::get_choices($survey->id);
        if (empty($choices)) {
            return '<p>' . __('No choices available', 'wp-survey') . '</p>';
        }
        
        ob_start();
        include WP_SURVEY_PLUGIN_DIR . 'public/views/survey-' . ($type === 'widget' ? 'widget' : 'full') . '.php';
        return ob_get_clean();
    }
    
    private function render_multi_question_survey($survey) {
        $questions = WP_Survey_Database::get_questions($survey->id);
        if (empty($questions)) {
            return '<p>' . __('No questions available', 'wp-survey') . '</p>';
        }
        
        // Get choices for each question
        $questions_data = [];
        foreach ($questions as $question) {
            $choices = WP_Survey_Database::get_choices($survey->id, $question->id);
            if (!empty($choices)) {
                $questions_data[] = [
                    'question' => $question,
                    'choices' => $choices
                ];
            }
        }
        
        if (empty($questions_data)) {
            return '<p>' . __('No questions with choices available', 'wp-survey') . '</p>';
        }
        
        ob_start();
        
        if ($survey->display_mode === 'all-questions') {
            include WP_SURVEY_PLUGIN_DIR . 'public/views/survey-multi-all.php';
        } else {
            include WP_SURVEY_PLUGIN_DIR . 'public/views/survey-multi-step.php';
        }
        
        return ob_get_clean();
    }
    
    public function ajax_submit_vote() {
        check_ajax_referer('wp_survey_public_nonce', 'nonce');
        
        $survey_id = intval($_POST['survey_id']);
        $choice_id = intval($_POST['choice_id']);
        
        // Check cookie for duplicate vote prevention
        $cookie_name = 'wp_survey_voted_' . $survey_id;
        if (isset($_COOKIE[$cookie_name])) {
            wp_send_json_error(['message' => __('You have already voted in this survey', 'wp-survey')]);
        }
        
        WP_Survey_Database::save_response($survey_id, $choice_id, '', '');
        
        // Set cookie for 30 days
        setcookie($cookie_name, '1', time() + (30 * 24 * 60 * 60), '/');
        
        wp_send_json_success(['message' => __('Thank you for your vote!', 'wp-survey')]);
    }
    
    public function ajax_submit_multi_vote() {
        check_ajax_referer('wp_survey_public_nonce', 'nonce');
        
        $survey_id = intval($_POST['survey_id']);
        $responses = isset($_POST['responses']) ? json_decode(stripslashes($_POST['responses']), true) : [];
        
        if (empty($responses)) {
            wp_send_json_error(['message' => __('No responses provided', 'wp-survey')]);
        }
        
        // Generate session ID for this submission
        $session_id = wp_generate_password(32, false);
        
        // Check cookie for duplicate vote prevention
        $cookie_name = 'wp_survey_voted_' . $survey_id;
        if (isset($_COOKIE[$cookie_name])) {
            wp_send_json_error(['message' => __('You have already voted in this survey', 'wp-survey')]);
        }
        
        // Save all responses
        foreach ($responses as $response) {
            $question_id = intval($response['question_id']);
            $choice_id = intval($response['choice_id']);
            
            WP_Survey_Database::save_response(
                $survey_id,
                $choice_id,
                '',
                '',
                $question_id,
                $session_id
            );
        }
        
        // Set cookie for 30 days
        setcookie($cookie_name, '1', time() + (30 * 24 * 60 * 60), '/');
        
        wp_send_json_success(['message' => __('Thank you for completing the survey!', 'wp-survey')]);
    }
}