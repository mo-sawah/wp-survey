<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php echo $survey ? __('Edit Multi-Question Survey', 'wp-survey') : __('Add New Survey', 'wp-survey'); ?></h1>
    
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
                                <p class="description"><?php _e('This appears in the survey intro screen', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="description"><?php _e('Description', 'wp-survey'); ?></label></th>
                            <td>
                                <textarea id="description" name="description" rows="3" class="large-text"><?php echo $survey ? esc_textarea($survey->description) : ''; ?></textarea>
                                <p class="description"><?php _e('Brief description shown in the intro screen', 'wp-survey'); ?></p>
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
                                <p class="description"><?php _e('Recommended: 1200x400px - This image will be shown in the intro screen', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="facebook_page_url"><?php _e('Facebook Page URL', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="url" id="facebook_page_url" name="facebook_page_url" class="regular-text" value="<?php echo $survey && $survey->facebook_page_url ? esc_url($survey->facebook_page_url) : ''; ?>" placeholder="https://www.facebook.com/YourPageName">
                                <p class="description"><?php _e('Users will be asked to like/follow your page before submitting', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="survey_type"><?php _e('Survey Type', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="survey_type" name="survey_type">
                                    <option value="simple" <?php echo ($survey && $survey->survey_type === 'simple') ? 'selected' : ''; ?>><?php _e('Simple (Single Question)', 'wp-survey'); ?></option>
                                    <option value="multi-question" <?php echo ($survey && $survey->survey_type === 'multi-question') ? 'selected' : ''; ?>><?php _e('Multi-Question', 'wp-survey'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="display_mode"><?php _e('Display Mode', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="display_mode" name="display_mode">
                                    <option value="multi-step" <?php echo ($survey && $survey->display_mode === 'multi-step') ? 'selected' : ''; ?>><?php _e('Multi-Step (One question at a time)', 'wp-survey'); ?></option>
                                    <option value="all-questions" <?php echo ($survey && $survey->display_mode === 'all-questions') ? 'selected' : ''; ?>><?php _e('All Questions (Show all at once)', 'wp-survey'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="intro_enabled"><?php _e('Show Intro Screen', 'wp-survey'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="intro_enabled" name="intro_enabled" value="1" <?php echo ($survey && $survey->intro_enabled) ? 'checked' : ''; ?>>
                                    <?php _e('Display introduction screen before survey questions', 'wp-survey'); ?>
                                </label>
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
                    </p>
                </form>
            </div>
            
            <?php if ($survey): ?>
            <!-- Questions Section -->
            <div class="wp-survey-card">
                <div class="wp-survey-card-header">
                    <h2><?php _e('Survey Questions', 'wp-survey'); ?></h2>
                    <button type="button" class="button button-primary" id="add-question-btn"><?php _e('Add Question', 'wp-survey'); ?></button>
                </div>
                
                <div id="questions-container">
                    <?php if (empty($questions)): ?>
                        <p class="wp-survey-no-questions"><?php _e('No questions yet. Add your first question to get started.', 'wp-survey'); ?></p>
                    <?php else: ?>
                        <?php foreach ($questions as $q_index => $question): 
                            $question_choices = WP_Survey_Database::get_choices($survey->id, $question->id);
                        ?>
                        <div class="wp-survey-question-item" data-id="<?php echo $question->id; ?>">
                            <div class="wp-survey-question-header">
                                <span class="wp-survey-question-drag">⋮⋮</span>
                                <span class="wp-survey-question-number"><?php _e('Question', 'wp-survey'); ?> <?php echo ($q_index + 1); ?></span>
                                <div class="wp-survey-question-actions">
                                    <button type="button" class="button save-question-btn"><?php _e('Save Question', 'wp-survey'); ?></button>
                                    <button type="button" class="button delete-question-btn"><?php _e('Delete', 'wp-survey'); ?></button>
                                </div>
                            </div>
                            <div class="wp-survey-question-content">
                                <input type="text" class="question-text large-text" placeholder="<?php _e('Enter your question here...', 'wp-survey'); ?>" value="<?php echo esc_attr($question->question_text); ?>">
                                
                                <div class="wp-survey-question-choices">
                                    <h4><?php _e('Choices for this question:', 'wp-survey'); ?></h4>
                                    <button type="button" class="button add-choice-btn"><?php _e('Add Choice', 'wp-survey'); ?></button>
                                    
                                    <div class="choices-list">
                                        <?php if (empty($question_choices)): ?>
                                            <p class="wp-survey-no-choices"><?php _e('No choices yet', 'wp-survey'); ?></p>
                                        <?php else: ?>
                                            <?php foreach ($question_choices as $choice): ?>
                                            <div class="wp-survey-choice-item" data-id="<?php echo $choice->id; ?>" data-question-id="<?php echo $question->id; ?>">
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
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="wp-survey-admin-sidebar">
            <?php if ($survey): ?>
            <div class="wp-survey-card">
                <h3><?php _e('Shortcode', 'wp-survey'); ?></h3>
                <div class="wp-survey-shortcode-box">
                    <label><?php _e('Use this shortcode', 'wp-survey'); ?></label>
                    <code onclick="this.select()">[wp_survey id="<?php echo $survey->id; ?>"]</code>
                </div>
            </div>
            
            <div class="wp-survey-card">
                <h3><?php _e('Quick Info', 'wp-survey'); ?></h3>
                <div class="wp-survey-info-item">
                    <strong><?php _e('Survey Type:', 'wp-survey'); ?></strong><br>
                    <?php echo $survey->survey_type === 'multi-question' ? __('Multi-Question', 'wp-survey') : __('Simple', 'wp-survey'); ?>
                </div>
                <div class="wp-survey-info-item">
                    <strong><?php _e('Display Mode:', 'wp-survey'); ?></strong><br>
                    <?php echo $survey->display_mode === 'all-questions' ? __('All Questions', 'wp-survey') : __('Multi-Step', 'wp-survey'); ?>
                </div>
                <div class="wp-survey-info-item">
                    <strong><?php _e('Total Questions:', 'wp-survey'); ?></strong><br>
                    <?php echo count($questions); ?>
                </div>
            </div>
            <?php else: ?>
            <div class="wp-survey-card">
                <h3><?php _e('Next Steps', 'wp-survey'); ?></h3>
                <ol style="padding-left: 20px; line-height: 1.8;">
                    <li><?php _e('Fill in survey details', 'wp-survey'); ?></li>
                    <li><?php _e('Save the survey', 'wp-survey'); ?></li>
                    <li><?php _e('Add questions', 'wp-survey'); ?></li>
                    <li><?php _e('Add choices to each question', 'wp-survey'); ?></li>
                    <li><?php _e('Copy the shortcode', 'wp-survey'); ?></li>
                    <li><?php _e('Add it to any page', 'wp-survey'); ?></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>