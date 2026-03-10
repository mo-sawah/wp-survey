<?php
if (!defined('ABSPATH')) exit;

class WP_Survey_Admin {

    public function __construct() {
        add_action('admin_menu',                          [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts',               [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_wp_survey_delete',            [$this, 'ajax_delete_survey']);
        add_action('wp_ajax_wp_survey_save_choice',       [$this, 'ajax_save_choice']);
        add_action('wp_ajax_wp_survey_delete_choice',     [$this, 'ajax_delete_choice']);
        add_action('wp_ajax_wp_survey_save_question',     [$this, 'ajax_save_question']);
        add_action('wp_ajax_wp_survey_delete_question',   [$this, 'ajax_delete_question']);
        add_action('wp_ajax_wp_survey_export_emails',     [$this, 'ajax_export_emails']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('WP Survey', 'wp-survey'), __('WP Survey', 'wp-survey'),
            'manage_options', 'wp-survey', [$this, 'render_surveys_page'],
            'dashicons-chart-pie', 30
        );

        add_submenu_page('wp-survey', __('All Surveys', 'wp-survey'),   __('All Surveys', 'wp-survey'),   'manage_options', 'wp-survey',               [$this, 'render_surveys_page']);
        add_submenu_page('wp-survey', __('Add New', 'wp-survey'),       __('Add New', 'wp-survey'),       'manage_options', 'wp-survey-add',           [$this, 'render_add_edit_page']);
        add_submenu_page('wp-survey', __('Results', 'wp-survey'),       __('📋 Results', 'wp-survey'),    'manage_options', 'wp-survey-results',       [$this, 'render_results_page']);
        add_submenu_page('wp-survey', __('Analytics', 'wp-survey'),     __('📊 Analytics', 'wp-survey'),  'manage_options', 'wp-survey-analytics',     [$this, 'render_analytics_page']);
        add_submenu_page('wp-survey', __('Emails', 'wp-survey'),        __('Emails', 'wp-survey'),        'manage_options', 'wp-survey-emails',        [$this, 'render_emails_page']);
        add_submenu_page('wp-survey', __('Import / Export', 'wp-survey'), __('Import / Export', 'wp-survey'), 'manage_options', 'wp-survey-import-export', [$this, 'render_import_export_page']);
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wp-survey') === false) return;

        wp_enqueue_media();
        wp_enqueue_style('wp-survey-admin',  WP_SURVEY_PLUGIN_URL . 'assets/css/admin.css', [], WP_SURVEY_VERSION);
        wp_enqueue_script('wp-survey-admin', WP_SURVEY_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WP_SURVEY_VERSION, true);

        // Chart.js only on pages that need it
        if (strpos($hook, 'wp-survey-analytics') !== false || strpos($hook, 'wp-survey-results') !== false) {
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);
        }

        wp_localize_script('wp-survey-admin', 'wpSurvey', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wp_survey_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this?', 'wp-survey'),
                'error'         => __('An error occurred', 'wp-survey'),
                'saved'         => __('Saved successfully', 'wp-survey'),
            ]
        ]);
    }

    // ── PAGE RENDERERS ──────────────────────────────────────────────

    public function render_surveys_page() {
        $surveys = WP_Survey_Database::get_all_surveys();
        include WP_SURVEY_PLUGIN_DIR . 'admin/views/surveys-list.php';
    }

    public function render_add_edit_page() {
        // Handle form submission
        if (isset($_POST['wp_survey_save']) && check_admin_referer('wp_survey_save_nonce')) {
            $survey_id = isset($_POST['survey_id']) ? intval($_POST['survey_id']) : 0;

            $data = [
                'title'                => sanitize_text_field($_POST['title']),
                'description'          => sanitize_textarea_field($_POST['description']),
                'question'             => isset($_POST['question']) ? sanitize_textarea_field($_POST['question']) : '',
                'banner_image'         => esc_url_raw($_POST['banner_image']),
                'facebook_page_url'    => esc_url_raw($_POST['facebook_page_url']),
                'language'             => sanitize_text_field($_POST['language']),
                'survey_type'          => sanitize_text_field($_POST['survey_type']),
                'display_mode'         => sanitize_text_field($_POST['display_mode']),
                'intro_enabled'        => isset($_POST['intro_enabled']) ? 1 : 0,
                'allow_multiple_votes' => isset($_POST['allow_multiple_votes']) ? 1 : 0,
            ];

            if ($survey_id) {
                WP_Survey_Database::update_survey($survey_id, $data);
                $id = $survey_id;
            } else {
                $id = WP_Survey_Database::create_survey($data);
            }

            wp_redirect(admin_url('admin.php?page=wp-survey-add&id=' . $id . '&message=success'));
            exit;
        }

        $survey_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $survey    = $survey_id ? WP_Survey_Database::get_survey($survey_id) : null;

        if ($survey && $survey->survey_type === 'multi-question') {
            $questions = WP_Survey_Database::get_questions($survey_id);
            include WP_SURVEY_PLUGIN_DIR . 'admin/views/survey-edit-multi.php';
        } else {
            $choices = $survey_id ? WP_Survey_Database::get_choices($survey_id) : [];
            $stats   = $survey_id ? WP_Survey_Database::get_survey_stats($survey_id) : null;
            include WP_SURVEY_PLUGIN_DIR . 'admin/views/survey-edit.php';
        }
    }

    public function render_results_page() {
        $surveys     = WP_Survey_Database::get_all_surveys();
        $selected_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;

        if (!$selected_id && !empty($surveys)) {
            $selected_id = $surveys[0]->id;
        }

        $results = $selected_id ? WP_Survey_Database::get_results($selected_id) : null;

        include WP_SURVEY_PLUGIN_DIR . 'admin/views/survey-results.php';
    }

    public function render_analytics_page() {
        $surveys     = WP_Survey_Database::get_all_surveys();
        $selected_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;

        if (!$selected_id && !empty($surveys)) {
            $selected_id = $surveys[0]->id;
        }

        $analytics = $selected_id ? WP_Survey_Database::get_analytics($selected_id) : null;

        include WP_SURVEY_PLUGIN_DIR . 'admin/views/survey-analytics.php';
    }

    public function render_emails_page() {
        $surveys         = WP_Survey_Database::get_all_surveys();
        $selected_survey = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
        $emails          = $selected_survey ? WP_Survey_Database::get_responses($selected_survey) : [];

        include WP_SURVEY_PLUGIN_DIR . 'admin/views/emails-list.php';
    }

    // ── AJAX ────────────────────────────────────────────────────────

    public function ajax_delete_survey() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);
        WP_Survey_Database::delete_survey(intval($_POST['survey_id']));
        wp_send_json_success();
    }

    public function ajax_save_question() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);

        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $data = [
            'survey_id'      => intval($_POST['survey_id']),
            'question_text'  => sanitize_textarea_field($_POST['question_text']),
            'allow_multiple' => isset($_POST['allow_multiple']) ? intval($_POST['allow_multiple']) : 0,
            'max_choices'    => isset($_POST['max_choices']) ? intval($_POST['max_choices']) : 0,
            'sort_order'     => intval($_POST['sort_order']),
        ];

        $id = $question_id ? (WP_Survey_Database::update_question($question_id, $data) && $question_id) : WP_Survey_Database::create_question($data);
        wp_send_json_success(['id' => $question_id ?: $id]);
    }

    public function ajax_delete_question() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);
        WP_Survey_Database::delete_question(intval($_POST['question_id']));
        wp_send_json_success();
    }

    public function ajax_save_choice() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);

        $choice_id = isset($_POST['choice_id']) ? intval($_POST['choice_id']) : 0;
        $data = [
            'survey_id'     => intval($_POST['survey_id']),
            'question_id'   => isset($_POST['question_id']) ? intval($_POST['question_id']) : null,
            'title'         => sanitize_text_field($_POST['title']),
            'description_1' => sanitize_textarea_field($_POST['description_1']),
            'description_2' => sanitize_textarea_field($_POST['description_2']),
            'image_url'     => esc_url_raw($_POST['image_url']),
            'sort_order'    => intval($_POST['sort_order']),
        ];

        if ($choice_id) {
            WP_Survey_Database::update_choice($choice_id, $data);
            $id = $choice_id;
        } else {
            $id = WP_Survey_Database::create_choice($data);
        }

        wp_send_json_success(['id' => $id]);
    }

    public function ajax_delete_choice() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);
        WP_Survey_Database::delete_choice(intval($_POST['choice_id']));
        wp_send_json_success();
    }

    public function ajax_export_emails() {
        check_ajax_referer('wp_survey_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Unauthorized']);

        $survey_id = intval($_POST['survey_id']);
        $emails    = WP_Survey_Database::export_emails($survey_id);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="survey-emails-' . $survey_id . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Date']);
        foreach ($emails as $row) {
            fputcsv($output, [$row['name'], $row['email'], $row['created_at']]);
        }
        fclose($output);
        exit;
    }

    // ── IMPORT / EXPORT ─────────────────────────────────────────────

    public function render_import_export_page() {
        if (isset($_POST['wp_survey_import']) && check_admin_referer('wp_survey_import_nonce')) {
            $result = $this->handle_import();
            $redirect = $result['success']
                ? admin_url('admin.php?page=wp-survey-import-export&imported=success&survey_id=' . $result['survey_id'])
                : admin_url('admin.php?page=wp-survey-import-export&import_error=' . urlencode($result['message']));
            wp_redirect($redirect);
            exit;
        }
        if (isset($_POST['wp_survey_export']) && check_admin_referer('wp_survey_export_nonce')) {
            $this->handle_export();
        }
        include WP_SURVEY_PLUGIN_DIR . 'admin/views/survey-import-export.php';
    }

    private function handle_import() {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => __('File upload failed', 'wp-survey')];
        }

        $data = json_decode(file_get_contents($_FILES['import_file']['tmp_name']), true);
        if (!$data || !isset($data['survey']) || !isset($data['questions'])) {
            return ['success' => false, 'message' => __('Invalid JSON format', 'wp-survey')];
        }

        $survey_data = [
            'title'               => sanitize_text_field($data['survey']['title']),
            'description'         => sanitize_textarea_field($data['survey']['description']),
            'question'            => isset($data['survey']['question']) ? sanitize_textarea_field($data['survey']['question']) : '',
            'banner_image'        => isset($data['survey']['banner_image']) ? esc_url_raw($data['survey']['banner_image']) : '',
            'facebook_page_url'   => isset($data['survey']['facebook_page_url']) ? esc_url_raw($data['survey']['facebook_page_url']) : '',
            'language'            => sanitize_text_field($data['survey']['language']),
            'survey_type'         => sanitize_text_field($data['survey']['survey_type']),
            'display_mode'        => isset($data['survey']['display_mode']) ? sanitize_text_field($data['survey']['display_mode']) : 'multi-step',
            'intro_enabled'       => isset($data['survey']['intro_enabled']) ? intval($data['survey']['intro_enabled']) : 1,
            'allow_multiple_votes' => isset($data['survey']['allow_multiple_votes']) ? intval($data['survey']['allow_multiple_votes']) : 0,
        ];

        $survey_id = WP_Survey_Database::create_survey($survey_data);
        if (!$survey_id) return ['success' => false, 'message' => __('Failed to create survey', 'wp-survey')];

        if ($survey_data['survey_type'] === 'multi-question') {
            foreach ($data['questions'] as $q_index => $q) {
                $q_id = WP_Survey_Database::create_question([
                    'survey_id'      => $survey_id,
                    'question_text'  => sanitize_textarea_field($q['question_text']),
                    'allow_multiple' => isset($q['allow_multiple']) ? intval($q['allow_multiple']) : 0,
                    'sort_order'     => $q_index,
                ]);
                if ($q_id && isset($q['choices'])) {
                    foreach ($q['choices'] as $c_index => $c) {
                        WP_Survey_Database::create_choice([
                            'survey_id'     => $survey_id, 'question_id'   => $q_id,
                            'title'         => sanitize_text_field($c['title']),
                            'description_1' => isset($c['description_1']) ? sanitize_textarea_field($c['description_1']) : '',
                            'description_2' => isset($c['description_2']) ? sanitize_textarea_field($c['description_2']) : '',
                            'image_url'     => isset($c['image_url']) ? esc_url_raw($c['image_url']) : '',
                            'sort_order'    => $c_index,
                        ]);
                    }
                }
            }
        } elseif (isset($data['choices'])) {
            foreach ($data['choices'] as $c_index => $c) {
                WP_Survey_Database::create_choice([
                    'survey_id'     => $survey_id, 'question_id' => null,
                    'title'         => sanitize_text_field($c['title']),
                    'description_1' => isset($c['description_1']) ? sanitize_textarea_field($c['description_1']) : '',
                    'description_2' => isset($c['description_2']) ? sanitize_textarea_field($c['description_2']) : '',
                    'image_url'     => isset($c['image_url']) ? esc_url_raw($c['image_url']) : '',
                    'sort_order'    => $c_index,
                ]);
            }
        }

        return ['success' => true, 'survey_id' => $survey_id];
    }

    private function handle_export() {
        $survey_id = intval($_POST['survey_id']);
        $survey    = WP_Survey_Database::get_survey($survey_id);
        if (!$survey) wp_die(__('Survey not found', 'wp-survey'));

        $export = [
            'version'     => '1.0',
            'exported_at' => current_time('mysql'),
            'survey'      => [
                'title' => $survey->title, 'description' => $survey->description,
                'question' => $survey->question, 'banner_image' => $survey->banner_image,
                'facebook_page_url' => $survey->facebook_page_url, 'language' => $survey->language,
                'survey_type' => $survey->survey_type, 'display_mode' => $survey->display_mode,
                'intro_enabled' => $survey->intro_enabled,
                'allow_multiple_votes' => $survey->allow_multiple_votes ?? 0,
            ],
            'questions' => [],
        ];

        if ($survey->survey_type === 'multi-question') {
            foreach (WP_Survey_Database::get_questions($survey_id) as $q) {
                $choices_arr = [];
                foreach (WP_Survey_Database::get_choices($survey_id, $q->id) as $c) {
                    $choices_arr[] = ['title' => $c->title, 'description_1' => $c->description_1,
                                      'description_2' => $c->description_2, 'image_url' => $c->image_url];
                }
                $export['questions'][] = ['question_text' => $q->question_text,
                                           'allow_multiple' => $q->allow_multiple, 'choices' => $choices_arr];
            }
        } else {
            $export['choices'] = [];
            foreach (WP_Survey_Database::get_choices($survey_id) as $c) {
                $export['choices'][] = ['title' => $c->title, 'description_1' => $c->description_1,
                                         'description_2' => $c->description_2, 'image_url' => $c->image_url];
            }
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . sanitize_title($survey->title) . '-' . date('Y-m-d') . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
