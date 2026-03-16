<?php if (!defined('ABSPATH')) exit;

// ── AI Settings save handling ────────────────────────────────────────────────
if (isset($_POST['wps_save_ai_settings']) && check_admin_referer('wps_ai_settings_nonce')) {
    update_option('wps_ai_api_key',       sanitize_text_field($_POST['wps_ai_api_key']));
    update_option('wps_ai_model',         sanitize_text_field($_POST['wps_ai_model']));
    update_option('wps_ai_language',      sanitize_text_field($_POST['wps_ai_language']));
    update_option('wps_ai_custom_prompt', sanitize_textarea_field($_POST['wps_ai_custom_prompt']));
    $ai_saved = true;
}

$ai_key     = get_option('wps_ai_api_key', '');
$ai_model   = get_option('wps_ai_model', 'anthropic/claude-3.5-sonnet');
$ai_lang    = get_option('wps_ai_language', 'English');
$ai_prompt  = get_option('wps_ai_custom_prompt', '');

$default_prompt = "You are an expert political analyst and survey researcher with deep knowledge of Cypriot society, politics, and public opinion. Analyze the survey data rigorously and produce thorough, data-driven insights. Your analysis should be professional, balanced, and suitable for publication in an official report. Draw meaningful conclusions from statistical distributions and identify patterns that matter. Be specific with numbers and percentages throughout your analysis.";
?>

<div class="wrap">
<h1><?php _e('Import / Export & AI Report Settings', 'wp-survey'); ?></h1>

<?php if (!empty($ai_saved)): ?>
<div class="notice notice-success is-dismissible"><p><?php _e('AI settings saved.', 'wp-survey'); ?></p></div>
<?php endif;
if (isset($_GET['imported']) && $_GET['imported'] === 'success'): ?>
<div class="notice notice-success is-dismissible"><p><?php _e('Survey imported successfully!', 'wp-survey'); ?></p></div>
<?php endif;
if (isset($_GET['import_error'])): ?>
<div class="notice notice-error"><p><?php echo esc_html(urldecode($_GET['import_error'])); ?></p></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; max-width:1100px; margin-top:20px;">

    <!-- ── AI Report Settings ─────────────────────────────────────── -->
    <div style="grid-column:1/-1;">
    <div class="wps-ie-card" style="border-top:4px solid #6366f1;">
        <div class="wps-ie-card-header" style="background:linear-gradient(135deg,#1e1b4b,#312e81); color:#fff;">
            <h2 style="color:#fff; margin:0;">🤖 <?php _e('AI Report Settings', 'wp-survey'); ?></h2>
            <p style="color:#a5b4fc; margin:4px 0 0; font-size:13px;"><?php _e('Configure OpenRouter AI for the "Generate Report" feature on the Results page.', 'wp-survey'); ?></p>
        </div>
        <div class="wps-ie-card-body">
            <form method="post" action="">
                <?php wp_nonce_field('wps_ai_settings_nonce'); ?>
                <input type="hidden" name="wps_save_ai_settings" value="1">

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                    <!-- API Key -->
                    <div style="grid-column:1/-1;" class="wps-field">
                        <label class="wps-label"><?php _e('OpenRouter API Key', 'wp-survey'); ?> <span style="color:#dc2626">*</span></label>
                        <div style="display:flex; gap:8px;">
                            <input type="password" id="wps-ai-key" name="wps_ai_api_key"
                                value="<?php echo esc_attr($ai_key); ?>"
                                placeholder="sk-or-v1-..."
                                class="regular-text" style="flex:1;">
                            <button type="button" onclick="
                                var f=document.getElementById('wps-ai-key');
                                f.type=f.type==='password'?'text':'password';
                                this.textContent=f.type==='password'?'👁':'🙈';
                            " class="button">👁</button>
                        </div>
                        <p class="description"><?php _e('Get your key from', 'wp-survey'); ?> <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a></p>
                    </div>

                    <!-- Model -->
                    <div class="wps-field">
                        <label class="wps-label"><?php _e('AI Model', 'wp-survey'); ?></label>
                        <input type="text" name="wps_ai_model" value="<?php echo esc_attr($ai_model); ?>"
                            class="regular-text" placeholder="anthropic/claude-3.5-sonnet">
                        <p class="description"><?php _e('Any model ID from', 'wp-survey'); ?> <a href="https://openrouter.ai/models" target="_blank">openrouter.ai/models</a></p>
                        <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;">
                            <?php
                            $presets = [
                                'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet ⭐',
                                'anthropic/claude-3-haiku'    => 'Claude 3 Haiku (fast)',
                                'openai/gpt-4o'               => 'GPT-4o',
                                'openai/gpt-4o-mini'          => 'GPT-4o Mini',
                                'google/gemini-flash-1.5'     => 'Gemini Flash',
                                'meta-llama/llama-3.1-8b-instruct' => 'Llama 3.1 8B (free)',
                            ];
                            foreach ($presets as $id => $label):
                            ?>
                            <button type="button" class="button button-small wps-model-preset"
                                data-model="<?php echo esc_attr($id); ?>"
                                style="<?php echo $ai_model === $id ? 'background:#6366f1;color:#fff;border-color:#6366f1;' : ''; ?>">
                                <?php echo esc_html($label); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Language -->
                    <div class="wps-field">
                        <label class="wps-label"><?php _e('Report Language', 'wp-survey'); ?></label>
                        <select name="wps_ai_language" class="regular-text">
                            <?php
                            $langs = ['English' => 'English', 'Greek' => 'Ελληνικά (Greek)', 'Arabic' => 'Arabic', 'French' => 'French', 'German' => 'German', 'Spanish' => 'Spanish', 'Turkish' => 'Turkish'];
                            foreach ($langs as $val => $label):
                            ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php selected($ai_lang, $val); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('The entire report will be written in this language.', 'wp-survey'); ?></p>
                    </div>

                    <!-- Custom prompt -->
                    <div style="grid-column:1/-1;" class="wps-field">
                        <label class="wps-label">
                            <?php _e('Custom Analysis Instructions', 'wp-survey'); ?>
                            <span style="color:#9ca3af; font-weight:400;"><?php _e('(optional)', 'wp-survey'); ?></span>
                        </label>
                        <textarea name="wps_ai_custom_prompt" rows="4" class="large-text"
                            placeholder="<?php esc_attr_e('e.g. Focus on demographic implications. Pay special attention to the generational divide in the results. Highlight any surprising reversals from expected opinion patterns.', 'wp-survey'); ?>"><?php echo esc_textarea($ai_prompt ?: $default_prompt); ?></textarea>
                        <p class="description"><?php _e('These instructions are appended to the AI system prompt. The default prompt above is pre-filled as a starting point — edit it as needed.', 'wp-survey'); ?></p>
                        <button type="button" class="button button-small" style="margin-top:6px;"
                            onclick="document.querySelector('[name=wps_ai_custom_prompt]').value=<?php echo wp_json_encode($default_prompt); ?>">
                            ↩ <?php _e('Reset to default', 'wp-survey'); ?>
                        </button>
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid #e5e7eb; display:flex; align-items:center; gap:12px;">
                    <?php submit_button(__('Save AI Settings', 'wp-survey'), 'primary', 'wps_save_ai_settings', false, ['style' => 'margin:0;']); ?>
                    <span style="font-size:12px; color:#9ca3af;"><?php _e('After saving, go to WP Survey → Results → Generate Report', 'wp-survey'); ?></span>
                </div>
            </form>
        </div>
    </div>
    </div>

    <!-- ── Import ─────────────────────────────────────────────────── -->
    <div class="wps-ie-card" style="border-top:4px solid #10b981;">
        <div class="wps-ie-card-header">
            <h2>📥 <?php _e('Import Survey', 'wp-survey'); ?></h2>
        </div>
        <div class="wps-ie-card-body">
            <p class="description"><?php _e('Import a survey from a JSON file exported from this plugin.', 'wp-survey'); ?></p>
            <form method="post" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('wp_survey_import_nonce'); ?>
                <div class="wps-field">
                    <label class="wps-label"><?php _e('JSON File', 'wp-survey'); ?></label>
                    <input type="file" name="import_file" accept=".json" class="regular-text">
                </div>
                <div style="margin-top:16px;">
                    <?php submit_button(__('Import Survey', 'wp-survey'), 'secondary', 'wp_survey_import', false); ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Export ─────────────────────────────────────────────────── -->
    <div class="wps-ie-card" style="border-top:4px solid #6366f1;">
        <div class="wps-ie-card-header">
            <h2>📤 <?php _e('Export Survey', 'wp-survey'); ?></h2>
        </div>
        <div class="wps-ie-card-body">
            <p class="description"><?php _e('Export survey structure as JSON (questions and choices, not votes).', 'wp-survey'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('wp_survey_export_nonce'); ?>
                <div class="wps-field">
                    <label class="wps-label"><?php _e('Select Survey', 'wp-survey'); ?></label>
                    <select name="survey_id" class="regular-text">
                        <option value=""><?php _e('— Choose survey —', 'wp-survey'); ?></option>
                        <?php
                        $surveys = WP_Survey_Database::get_all_surveys();
                        foreach ($surveys as $s):
                        ?>
                        <option value="<?php echo $s->id; ?>">#<?php echo $s->id; ?> — <?php echo esc_html($s->title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-top:16px;">
                    <?php submit_button(__('Export Survey', 'wp-survey'), 'secondary', 'wp_survey_export', false); ?>
                </div>
            </form>
        </div>
    </div>

</div><!-- grid -->
</div><!-- wrap -->

<style>
.wps-ie-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,0.06); }
.wps-ie-card-header { padding:16px 20px; background:#f9fafb; border-bottom:1px solid #f3f4f6; }
.wps-ie-card-header h2 { margin:0; font-size:16px; }
.wps-ie-card-body { padding:20px; }
.wps-field { margin-bottom:16px; }
.wps-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
</style>

<script>
document.querySelectorAll('.wps-model-preset').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelector('[name=wps_ai_model]').value = this.dataset.model;
        document.querySelectorAll('.wps-model-preset').forEach(function(b) {
            b.style.background = ''; b.style.color = ''; b.style.borderColor = '';
        });
        this.style.background = '#6366f1'; this.style.color = '#fff'; this.style.borderColor = '#6366f1';
    });
});
</script>