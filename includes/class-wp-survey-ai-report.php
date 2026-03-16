<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP Survey AI Report Generator
 * Produces a 4-section structured analysis via OpenRouter.
 * Results stored in wp_options keyed by survey ID.
 */
class WP_Survey_AI_Report {

    const OPTION_REPORT  = 'wps_ai_report_';     // + survey_id
    const OPTION_STATUS  = 'wps_ai_report_status_'; // + survey_id → 'idle'|'running'|'done'|'error'
    const OR_URL         = 'https://openrouter.ai/api/v1/chat/completions';

    private string $api_key;
    private string $model;
    private string $language;
    private string $custom_prompt;

    public function __construct() {
        $this->api_key      = get_option( 'wps_ai_api_key', '' );
        $this->model        = get_option( 'wps_ai_model', 'anthropic/claude-3.5-sonnet' );
        $this->language     = get_option( 'wps_ai_language', 'English' );
        $this->custom_prompt = get_option( 'wps_ai_custom_prompt', '' );
    }

    // ── Public API ────────────────────────────────────────────────────────────

    public static function get_status( int $survey_id ): string {
        return get_option( self::OPTION_STATUS . $survey_id, 'idle' );
    }

    public static function get_report( int $survey_id ): ?array {
        $r = get_option( self::OPTION_REPORT . $survey_id );
        return $r ?: null;
    }

    public static function delete_report( int $survey_id ): void {
        delete_option( self::OPTION_REPORT  . $survey_id );
        delete_option( self::OPTION_STATUS  . $survey_id );
    }

    /**
     * Generate the full report. Runs synchronously (set_time_limit extended).
     * Called from the AJAX handler.
     */
    public function generate( int $survey_id ): array {
        if ( empty( $this->api_key ) ) {
            return [ 'success' => false, 'error' => 'OpenRouter API key not set.' ];
        }

        update_option( self::OPTION_STATUS . $survey_id, 'running' );

        // Load survey data
        $survey    = WP_Survey_Database::get_survey( $survey_id );
        if ( ! $survey ) {
            update_option( self::OPTION_STATUS . $survey_id, 'error' );
            return [ 'success' => false, 'error' => 'Survey not found.' ];
        }

        $survey_data = $this->build_survey_data_string( $survey_id, $survey );
        $lang_instr  = "Respond entirely in {$this->language}. ";
        $sys         = $lang_instr . $this->build_system_prompt( $survey );
        if ( $this->custom_prompt ) {
            $sys .= "\n\nAdditional instructions: " . $this->custom_prompt;
        }

        $report = [];
        $errors = [];

        // ── Call 1: Executive Overview ────────────────────────────────────────
        $r1 = $this->call( $sys, $this->prompt_overview( $survey_data, $survey ) );
        if ( $r1['success'] ) {
            $report['overview'] = $r1['data'];
        } else {
            $errors[] = 'Overview: ' . $r1['error'];
        }

        // ── Call 2: Question-by-Question ──────────────────────────────────────
        $r2 = $this->call( $sys, $this->prompt_questions( $survey_data ) );
        if ( $r2['success'] ) {
            $report['questions'] = $r2['data'];
        } else {
            $errors[] = 'Questions: ' . $r2['error'];
        }

        // ── Call 3: Cross-Analysis (multi-Q surveys) ──────────────────────────
        if ( $survey->survey_type === 'multi-question' ) {
            $r3 = $this->call( $sys, $this->prompt_cross( $survey_data ) );
            if ( $r3['success'] ) {
                $report['cross'] = $r3['data'];
            } else {
                $errors[] = 'Cross: ' . $r3['error'];
            }
        }

        // ── Call 4: Conclusions ───────────────────────────────────────────────
        $r4 = $this->call( $sys, $this->prompt_conclusions( $survey_data, $report ) );
        if ( $r4['success'] ) {
            $report['conclusions'] = $r4['data'];
        } else {
            $errors[] = 'Conclusions: ' . $r4['error'];
        }

        if ( empty( $report ) ) {
            update_option( self::OPTION_STATUS . $survey_id, 'error' );
            return [ 'success' => false, 'error' => implode( '; ', $errors ) ];
        }

        // Store metadata
        global $wpdb;
        $report['_meta'] = [
            'generated_at' => current_time( 'mysql' ),
            'model'        => $this->model,
            'language'     => $this->language,
            'survey_title' => $survey->title,
            'survey_id'    => $survey_id,
            'total_votes'  => (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}survey_responses WHERE survey_id = %d", $survey_id
            ) ),
            'errors'       => $errors,
        ];

        update_option( self::OPTION_REPORT . $survey_id, $report );
        update_option( self::OPTION_STATUS . $survey_id, 'done' );

        return [ 'success' => true, 'report' => $report ];
    }

    // ── Prompt builders ───────────────────────────────────────────────────────

    private function build_system_prompt( $survey ): string {
        return "You are an expert political analyst and survey researcher with deep knowledge of Cypriot society, politics, and public opinion. " .
               "You analyze survey data rigorously and produce thorough, data-driven insights. " .
               "Your analysis is professional, balanced, and suitable for publication in an official report. " .
               "You draw meaningful conclusions from statistical distributions and identify patterns that matter. " .
               "Never fabricate data — only analyze what is given. Be specific with numbers and percentages. " .
               "Format your response as clean JSON only — no markdown, no explanation outside the JSON.";
    }

    private function build_survey_data_string( int $survey_id, $survey ): string {
        global $wpdb;

        $rt = $wpdb->prefix . 'survey_responses';
        $lines = [];
        $lines[] = "SURVEY: {$survey->title}";
        if ( $survey->description ) $lines[] = "Description: {$survey->description}";
        $lines[] = "Type: {$survey->survey_type}";

        $total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $rt WHERE survey_id = %d", $survey_id ) );
        $first = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(created_at) FROM $rt WHERE survey_id = %d", $survey_id ) );
        $last  = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(created_at) FROM $rt WHERE survey_id = %d", $survey_id ) );

        $lines[] = "Total votes: $total";
        $lines[] = "Period: " . ( $first ? date( 'Y-m-d', strtotime( $first ) ) : 'N/A' ) . " to " . ( $last ? date( 'Y-m-d', strtotime( $last ) ) : 'N/A' );
        $lines[] = "";
        $lines[] = "RESULTS:";

        $ct = $wpdb->prefix . 'survey_choices';

        if ( $survey->survey_type === 'multi-question' ) {
            $questions = WP_Survey_Database::get_questions( $survey_id );
            foreach ( $questions as $qi => $q ) {
                $q_total = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM $rt WHERE survey_id=%d AND question_id=%d", $survey_id, $q->id
                ) );
                $lines[] = "";
                $lines[] = "Q" . ( $qi + 1 ) . ": {$q->question_text} ({$q_total} votes)";
                $choices = WP_Survey_Database::get_choices( $survey_id, $q->id );
                foreach ( $choices as $c ) {
                    $v   = (int) $wpdb->get_var( $wpdb->prepare(
                        "SELECT COUNT(*) FROM $rt WHERE survey_id=%d AND question_id=%d AND choice_id=%d",
                        $survey_id, $q->id, $c->id
                    ) );
                    $pct = $q_total > 0 ? round( $v / $q_total * 100, 1 ) : 0;
                    $lines[] = "  - {$c->title}: {$v} votes ({$pct}%)";
                }
            }
        } else {
            $lines[] = "Question: {$survey->question}";
            $choices = WP_Survey_Database::get_choices( $survey_id );
            foreach ( $choices as $c ) {
                $v   = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM $rt WHERE survey_id=%d AND choice_id=%d", $survey_id, $c->id
                ) );
                $pct = $total > 0 ? round( $v / $total * 100, 1 ) : 0;
                $lines[] = "  - {$c->title}: {$v} votes ({$pct}%)";
            }
        }

        return implode( "\n", $lines );
    }

    private function prompt_overview( string $data, $survey ): string {
        return "Analyze this survey data and return a JSON object with these exact keys:\n\n" .
               "{\n" .
               "  \"executive_summary\": \"3-4 paragraph comprehensive overview of the survey and its main findings\",\n" .
               "  \"participation_note\": \"1 sentence about the sample size and what it represents\",\n" .
               "  \"key_findings\": [\"finding 1\", \"finding 2\", \"finding 3\", \"finding 4\", \"finding 5\"]\n" .
               "}\n\n" .
               "Survey data:\n{$data}\n\n" .
               "Return only the JSON object, nothing else.";
    }

    private function prompt_questions( string $data ): string {
        return "Analyze each question in this survey data in depth. Return a JSON object:\n\n" .
               "{\n" .
               "  \"questions\": [\n" .
               "    {\n" .
               "      \"question\": \"exact question text\",\n" .
               "      \"leading_choice\": \"winning option name\",\n" .
               "      \"leading_pct\": \"winning percentage as string e.g. 52.3%\",\n" .
               "      \"analysis\": \"2-3 paragraph deep analysis of this question's results, what they mean, what drives voter behavior\",\n" .
               "      \"notable\": \"1 sentence on the most surprising or significant aspect of this result\"\n" .
               "    }\n" .
               "  ]\n" .
               "}\n\n" .
               "Survey data:\n{$data}\n\n" .
               "Return only the JSON object, nothing else.";
    }

    private function prompt_cross( string $data ): string {
        return "Look across ALL questions together and identify patterns, correlations, and voter segments. Return a JSON object:\n\n" .
               "{\n" .
               "  \"patterns\": \"2 paragraphs describing observable patterns across questions — who votes which way on multiple questions\",\n" .
               "  \"voter_segments\": \"2 paragraphs describing the likely voter segments that emerge from the combined results\",\n" .
               "  \"correlations\": [\"correlation insight 1\", \"correlation insight 2\", \"correlation insight 3\"]\n" .
               "}\n\n" .
               "Survey data:\n{$data}\n\n" .
               "Return only the JSON object, nothing else.";
    }

    private function prompt_conclusions( string $data, array $report_so_far ): string {
        $prev = json_encode( $report_so_far, JSON_UNESCAPED_UNICODE );
        return "Based on all analysis so far, write the conclusions section. Return a JSON object:\n\n" .
               "{\n" .
               "  \"main_conclusion\": \"2-3 paragraph definitive conclusion about what this survey tells us about public opinion\",\n" .
               "  \"implications\": \"1-2 paragraphs on the political or social implications of these results\",\n" .
               "  \"recommendations\": [\"recommendation 1\", \"recommendation 2\", \"recommendation 3\"]\n" .
               "}\n\n" .
               "Survey data:\n{$data}\n\n" .
               "Prior analysis:\n{$prev}\n\n" .
               "Return only the JSON object, nothing else.";
    }

    // ── OpenRouter call ───────────────────────────────────────────────────────

    private function call( string $system, string $user ): array {
        $body = [
            'model'       => $this->model,
            'max_tokens'  => 2000,
            'temperature' => 0.3,
            'messages'    => [
                [ 'role' => 'system', 'content' => $system ],
                [ 'role' => 'user',   'content' => $user ],
            ],
        ];

        $response = wp_remote_post( self::OR_URL, [
            'timeout' => 90,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
                'HTTP-Referer'  => get_site_url(),
                'X-Title'       => 'WP Survey Analysis',
            ],
            'body' => wp_json_encode( $body ),
        ] );

        if ( is_wp_error( $response ) ) {
            return [ 'success' => false, 'error' => $response->get_error_message() ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $json = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            return [ 'success' => false, 'error' => $json['error']['message'] ?? "HTTP $code" ];
        }

        $content = $json['choices'][0]['message']['content'] ?? '';
        // Strip markdown fences if present
        $content = preg_replace( '/^```(?:json)?\s*/m', '', $content );
        $content = preg_replace( '/```\s*$/m', '', $content );
        $content = trim( $content );

        // Extract JSON if surrounded by text
        if ( ! str_starts_with( $content, '{' ) ) {
            if ( preg_match( '/\{[\s\S]*\}/s', $content, $m ) ) $content = $m[0];
        }

        $data = json_decode( $content, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return [ 'success' => false, 'error' => 'Invalid JSON from AI: ' . substr( $content, 0, 200 ) ];
        }

        return [ 'success' => true, 'data' => $data ];
    }
}
