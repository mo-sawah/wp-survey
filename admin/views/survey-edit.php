<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php echo $survey ? __('Edit Survey', 'wp-survey') : __('Add New Survey', 'wp-survey'); ?></h1>
    
    <div class="wp-survey-admin-container">
        <div class="wp-survey-admin-main">
            <div class="wp-survey-card">
                <h2><?php _e('Survey Details', 'wp-survey'); ?></h2>
                
                <?php if (isset($_GET['message']) && $_GET['message'] === 'success'): ?>
                <div class="notice notice-success"><p><?php _e('Survey saved successfully!', 'wp-survey'); ?></p></div>
                <?php endif; ?>
                
                <form id="wp-survey-form" method="post" action="">
                    <?php wp_nonce_field('wp_survey_save_nonce'); ?>
                    <input type="hidden" name="wp_survey_save" value="1">
                    <input type="hidden" name="survey_id" value="<?php echo $survey ? $survey->id : '0'; ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="title"><?php _e('Survey Title', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="text" id="title" name="title" class="regular-text" value="<?php echo $survey ? esc_attr($survey->title) : ''; ?>" required>
                                <p class="description"><?php _e('This appears in the survey header', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="description"><?php _e('Description', 'wp-survey'); ?></label></th>
                            <td>
                                <textarea id="description" name="description" rows="3" class="large-text"><?php echo $survey ? esc_textarea($survey->description) : ''; ?></textarea>
                                <p class="description"><?php _e('Brief description shown below the title', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        
                        <!-- NEW: Survey Type Selection -->
                        <tr>
                            <th><label for="survey_type"><?php _e('Survey Type', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="survey_type" name="survey_type" class="regular-text">
                                    <option value="simple" <?php echo (!$survey || $survey->survey_type === 'simple') ? 'selected' : ''; ?>>
                                        <?php _e('Simple Survey (Single Question)', 'wp-survey'); ?>
                                    </option>
                                    <option value="multi-question" <?php echo ($survey && $survey->survey_type === 'multi-question') ? 'selected' : ''; ?>>
                                        <?php _e('Multi-Question Survey (Multiple Questions)', 'wp-survey'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php _e('Simple: One question with multiple choices (original style)', 'wp-survey'); ?><br>
                                    <?php _e('Multi-Question: Multiple questions with intro screen and progress tracking', 'wp-survey'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Show only for Simple surveys -->
                        <tr class="simple-survey-field">
                            <th><label for="question"><?php _e('Question', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="text" id="question" name="question" class="regular-text" value="<?php echo $survey ? esc_attr($survey->question) : ''; ?>">
                                <p class="description"><?php _e('The main question users will answer (for simple surveys)', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        
                        <!-- Show only for Multi-Question surveys -->
                        <tr class="multi-question-field" style="display: none;">
                            <th><label for="display_mode"><?php _e('Display Mode', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="display_mode" name="display_mode" class="regular-text">
                                    <option value="multi-step" <?php echo (!$survey || $survey->display_mode === 'multi-step') ? 'selected' : ''; ?>>
                                        <?php _e('Multi-Step (One question at a time)', 'wp-survey'); ?>
                                    </option>
                                    <option value="all-questions" <?php echo ($survey && $survey->display_mode === 'all-questions') ? 'selected' : ''; ?>>
                                        <?php _e('All Questions (Show all at once)', 'wp-survey'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('How questions should be displayed to users', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        
                        <tr class="multi-question-field" style="display: none;">
                            <th><label for="intro_enabled"><?php _e('Show Intro Screen', 'wp-survey'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="intro_enabled" name="intro_enabled" value="1" <?php echo (!$survey || $survey->intro_enabled) ? 'checked' : ''; ?>>
                                    <?php _e('Display introduction screen with banner, title, and description before questions', 'wp-survey'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="banner_image"><?php _e('Banner Image', 'wp-survey'); ?></label></th>
                            <td>
                                <div style="margin-bottom: 10px;">
                                    <?php if ($survey && $survey->banner_image): ?>
                                        <img src="<?php echo esc_url($survey->banner_image); ?>" style="max-width: 300px; height: auto; border-radius: 8px;" id="banner-preview">
                                    <?php else: ?>
                                        <img src="" style="max-width: 300px; height: auto; border-radius: 8px; display: none;" id="banner-preview">
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="banner_image" name="banner_image" value="<?php echo $survey && $survey->banner_image ? esc_url($survey->banner_image) : ''; ?>">
                                <button type="button" class="button" id="upload-banner-btn"><?php _e('Upload Banner Image', 'wp-survey'); ?></button>
                                <button type="button" class="button" id="remove-banner-btn" style="<?php echo !($survey && $survey->banner_image) ? 'display:none;' : ''; ?>"><?php _e('Remove', 'wp-survey'); ?></button>
                                <p class="description"><?php _e('Recommended: 1200x400px - This image will be shown in the survey header', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="facebook_page_url"><?php _e('Facebook Page URL', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="url" id="facebook_page_url" name="facebook_page_url" class="regular-text" value="<?php echo $survey && $survey->facebook_page_url ? esc_url($survey->facebook_page_url) : ''; ?>" placeholder="https://www.facebook.com/YourPageName">
                                <p class="description"><?php _e('Enter your Facebook page URL. Users will be asked to like/follow your page to submit their vote.', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="language"><?php _e('Language', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="language" name="language">
                                    <option value="en" <?php echo ($survey && $survey->language === 'en') ? 'selected' : ''; ?>>English</option>
                                    <option value="el" <?php echo ($survey && $survey->language === 'el') ? 'selected' : ''; ?>>Ελληνικά (Greek)</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large"><?php _e('Save Survey', 'wp-survey'); ?></button>
                        <?php if ($survey && $survey->survey_type === 'multi-question'): ?>
                            <span style="margin-left: 15px; color: #666;">
                                <?php _e('After saving, you can add questions below', 'wp-survey'); ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </form>
            </div>
            
            <?php if ($survey): ?>
                <?php if ($survey->survey_type === 'simple'): ?>
                    <!-- SIMPLE SURVEY: Show Choices -->
                    <div class="wp-survey-card">
                        <div class="wp-survey-card-header">
                            <h2><?php _e('Survey Choices', 'wp-survey'); ?></h2>
                            <button type="button" class="button button-primary" id="add-choice-btn"><?php _e('Add Choice', 'wp-survey'); ?></button>
                        </div>
                        
                        <div id="choices-container">
                            <?php if (empty($choices)): ?>
                                <p class="wp-survey-no-choices"><?php _e('No choices yet. Add your first choice to get started.', 'wp-survey'); ?></p>
                            <?php else: ?>
                                <?php foreach ($choices as $index => $choice): ?>
                                <div class="wp-survey-choice-item" data-id="<?php echo $choice->id; ?>">
                                    <div class="wp-survey-choice-drag">⋮⋮</div>
                                    <div class="wp-survey-choice-image-preview">
                                        <?php if ($choice->image_url): ?>
                                            <img src="<?php echo esc_url($choice->image_url); ?>" alt="">
                                        <?php else: ?>
                                            <div class="wp-survey-no-image">📷</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wp-survey-choice-content">
                                        <input type="text" class="choice-title" placeholder="<?php _e('Choice Title', 'wp-survey'); ?>" value="<?php echo esc_attr($choice->title); ?>">
                                        <input type="text" class="choice-desc1" placeholder="<?php _e('Description 1', 'wp-survey'); ?>" value="<?php echo esc_attr($choice->description_1); ?>">
                                        <input type="text" class="choice-desc2" placeholder="<?php _e('Description 2', 'wp-survey'); ?>" value="<?php echo esc_attr($choice->description_2); ?>">
                                        <input type="hidden" class="choice-image-url" value="<?php echo esc_url($choice->image_url); ?>">
                                    </div>
                                    <div class="wp-survey-choice-actions">
                                        <button type="button" class="button upload-image-btn"><?php _e('Image', 'wp-survey'); ?></button>
                                        <button type="button" class="button save-choice-btn"><?php _e('Save', 'wp-survey'); ?></button>
                                        <button type="button" class="button delete-choice-btn"><?php _e('Delete', 'wp-survey'); ?></button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- MULTI-QUESTION SURVEY: Redirect message -->
                    <div class="wp-survey-card">
                        <div class="notice notice-info" style="margin: 0;">
                            <p>
                                <strong><?php _e('Multi-Question Survey Detected!', 'wp-survey'); ?></strong><br>
                                <?php _e('This survey uses the multi-question format. Redirecting to the multi-question editor...', 'wp-survey'); ?>
                            </p>
                        </div>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    </script>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="wp-survey-admin-sidebar">
            <?php if ($survey): ?>
            <div class="wp-survey-card">
                <h3><?php _e('Shortcodes', 'wp-survey'); ?></h3>
                <div class="wp-survey-shortcode-box">
                    <label><?php _e('Full Page', 'wp-survey'); ?></label>
                    <code onclick="this.select()">[wp_survey id="<?php echo $survey->id; ?>"]</code>
                </div>
                <?php if ($survey->survey_type === 'simple'): ?>
                <div class="wp-survey-shortcode-box">
                    <label><?php _e('Widget', 'wp-survey'); ?></label>
                    <code onclick="this.select()">[wp_survey id="<?php echo $survey->id; ?>" type="widget"]</code>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($survey->survey_type === 'simple'): ?>
            <div class="wp-survey-card">
                <h3><?php _e('Statistics', 'wp-survey'); ?></h3>
                <div class="wp-survey-stat">
                    <div class="wp-survey-stat-number"><?php echo $stats['total_votes']; ?></div>
                    <div class="wp-survey-stat-label"><?php _e('Total Votes', 'wp-survey'); ?></div>
                </div>
                <div class="wp-survey-stat">
                    <div class="wp-survey-stat-number"><?php echo $stats['unique_voters']; ?></div>
                    <div class="wp-survey-stat-label"><?php _e('Unique Voters', 'wp-survey'); ?></div>
                </div>
                
                <?php if (!empty($stats['choices'])): ?>
                <div class="wp-survey-results">
                    <h4><?php _e('Results', 'wp-survey'); ?></h4>
                    <?php foreach ($stats['choices'] as $choice): 
                        $percentage = $stats['total_votes'] > 0 ? round(($choice->vote_count / $stats['total_votes']) * 100, 1) : 0;
                    ?>
                    <div class="wp-survey-result-item">
                        <div class="wp-survey-result-label"><?php echo esc_html($choice->title); ?></div>
                        <div class="wp-survey-result-bar">
                            <div class="wp-survey-result-fill" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <div class="wp-survey-result-stats"><?php echo $choice->vote_count; ?> (<?php echo $percentage; ?>%)</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <button type="button" class="button button-secondary button-large" id="export-emails-btn" style="width: 100%; margin-top: 15px;">
                    <?php _e('Export Emails', 'wp-survey'); ?> (CSV)
                </button>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="wp-survey-card">
                <h3><?php _e('Next Steps', 'wp-survey'); ?></h3>
                <ol style="padding-left: 20px; line-height: 1.8;">
                    <li><?php _e('Choose survey type (Simple or Multi-Question)', 'wp-survey'); ?></li>
                    <li><?php _e('Fill in survey details', 'wp-survey'); ?></li>
                    <li><?php _e('Save the survey', 'wp-survey'); ?></li>
                    <li><?php _e('Add choices or questions', 'wp-survey'); ?></li>
                    <li><?php _e('Copy the shortcode', 'wp-survey'); ?></li>
                    <li><?php _e('Add it to any page or post', 'wp-survey'); ?></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Show/hide fields based on survey type
    function toggleSurveyTypeFields() {
        var surveyType = $('#survey_type').val();
        
        if (surveyType === 'multi-question') {
            $('.simple-survey-field').hide();
            $('.multi-question-field').show();
            $('#question').removeAttr('required');
        } else {
            $('.simple-survey-field').show();
            $('.multi-question-field').hide();
            $('#question').attr('required', 'required');
        }
    }
    
    // Initial state
    toggleSurveyTypeFields();
    
    // On change
    $('#survey_type').on('change', toggleSurveyTypeFields);
});
</script>