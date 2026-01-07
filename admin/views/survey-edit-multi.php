<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php _e('Edit Multi-Question Survey', 'wp-survey'); ?></h1>
    
    <?php if (isset($_GET['saved'])): ?>
    <div class="notice notice-success">
        <p><?php _e('Survey saved successfully!', 'wp-survey'); ?></p>
    </div>
    <?php endif; ?>
    
    <form method="post" action="" id="wp-survey-form">
        <?php wp_nonce_field('wp_survey_bulk_save_nonce'); ?>
        <input type="hidden" name="wp_survey_bulk_save" value="1">
        <input type="hidden" name="survey_id" value="<?php echo $survey->id; ?>">
        
        <div class="wp-survey-admin-container">
            <div class="wp-survey-admin-main">
                <!-- Survey Details Card -->
                <div class="wp-survey-card">
                    <h2><?php _e('Survey Details', 'wp-survey'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="title"><?php _e('Survey Title', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="text" id="title" name="title" class="large-text" 
                                       value="<?php echo esc_attr($survey->title); ?>" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="description"><?php _e('Description', 'wp-survey'); ?></label></th>
                            <td>
                                <textarea id="description" name="description" class="large-text" rows="3"><?php echo esc_textarea($survey->description); ?></textarea>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="display_mode"><?php _e('Display Mode', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="display_mode" name="display_mode" class="regular-text">
                                    <option value="multi-step" <?php selected($survey->display_mode, 'multi-step'); ?>>
                                        <?php _e('Multi-Step (One question at a time)', 'wp-survey'); ?>
                                    </option>
                                    <option value="all-questions" <?php selected($survey->display_mode, 'all-questions'); ?>>
                                        <?php _e('All Questions (Show all at once)', 'wp-survey'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="intro_enabled"><?php _e('Show Intro Screen', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="checkbox" id="intro_enabled" name="intro_enabled" value="1" 
                                       <?php checked($survey->intro_enabled, 1); ?>>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="banner_image"><?php _e('Banner Image', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="text" id="banner_image" name="banner_image" class="large-text" 
                                       value="<?php echo esc_url($survey->banner_image); ?>">
                                <button type="button" class="button" id="upload-banner-btn">
                                    <?php _e('Upload Banner Image', 'wp-survey'); ?>
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="facebook_page_url"><?php _e('Facebook Page URL', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="text" id="facebook_page_url" name="facebook_page_url" class="large-text" 
                                       value="<?php echo esc_url($survey->facebook_page_url); ?>">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Questions Card -->
                <div class="wp-survey-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0;"><?php _e('Survey Questions', 'wp-survey'); ?></h2>
                        <button type="button" id="add-question-btn" class="button button-primary">
                            <?php _e('Add Question', 'wp-survey'); ?>
                        </button>
                    </div>
                    
                    <div id="questions-container">
                        <?php if (empty($questions)): ?>
                        <p class="description" style="text-align: center; padding: 40px 0; color: #666;">
                            <?php _e('No questions yet. Add your first question to get started.', 'wp-survey'); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php foreach ($questions as $q_index => $question): 
                            $choices = WP_Survey_Database::get_choices($survey->id, $question->id);
                        ?>
                        <div class="wp-survey-question-item" data-id="<?php echo $question->id; ?>">
                            <div class="wp-survey-question-header">
                                <span class="wp-survey-question-drag">⋮⋮</span>
                                <span class="wp-survey-question-number"><?php _e('Question', 'wp-survey'); ?> <?php echo ($q_index + 1); ?></span>
                                <div class="wp-survey-question-actions">
                                    <button type="button" class="button delete-question-btn"><?php _e('Delete', 'wp-survey'); ?></button>
                                </div>
                            </div>
                            <div class="wp-survey-question-content">
                                <input type="hidden" name="questions[<?php echo $q_index; ?>][id]" value="<?php echo $question->id; ?>">
                                <input type="text" name="questions[<?php echo $q_index; ?>][question_text]" 
                                       class="question-text large-text" 
                                       placeholder="<?php _e('Enter your question here...', 'wp-survey'); ?>" 
                                       value="<?php echo esc_attr($question->question_text); ?>" required>
                                
                                <div class="wp-survey-question-settings">
                                    <label class="wp-survey-toggle">
                                        <input type="checkbox" name="questions[<?php echo $q_index; ?>][allow_multiple]" 
                                               class="question-allow-multiple" value="1"
                                               <?php echo ($question->allow_multiple) ? 'checked' : ''; ?>>
                                        <span class="wp-survey-toggle-slider"></span>
                                        <span class="wp-survey-toggle-label"><?php _e('Allow Multiple Choices', 'wp-survey'); ?></span>
                                    </label>
                                    <p class="description"><?php _e('Enable this to let users select multiple answers for this question', 'wp-survey'); ?></p>
                                </div>
                                
                                <div class="wp-survey-question-choices">
                                    <label><?php _e('Choices for this question:', 'wp-survey'); ?></label>
                                    <button type="button" class="button button-small add-choice-btn"><?php _e('Add Choice', 'wp-survey'); ?></button>
                                    
                                    <div class="wp-survey-choices-list">
                                        <?php if (empty($choices)): ?>
                                        <p class="description no-choices-message"><?php _e('No choices yet', 'wp-survey'); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php foreach ($choices as $c_index => $choice): ?>
                                        <div class="wp-survey-choice-item" data-id="<?php echo $choice->id; ?>">
                                            <span class="wp-survey-choice-drag">⋮⋮</span>
                                            <input type="hidden" name="questions[<?php echo $q_index; ?>][choices][<?php echo $c_index; ?>][id]" value="<?php echo $choice->id; ?>">
                                            <input type="text" name="questions[<?php echo $q_index; ?>][choices][<?php echo $c_index; ?>][title]" 
                                                   class="regular-text" placeholder="<?php _e('Choice Title', 'wp-survey'); ?>" 
                                                   value="<?php echo esc_attr($choice->title); ?>" required>
                                            <button type="button" class="button button-small delete-choice-btn"><?php _e('Delete', 'wp-survey'); ?></button>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Save Button -->
                <div class="wp-survey-save-bar">
                    <button type="submit" class="button button-primary button-hero" style="padding: 10px 40px; font-size: 16px;">
                        <span class="dashicons dashicons-yes" style="margin-top: 4px;"></span>
                        <?php _e('Save Everything', 'wp-survey'); ?>
                    </button>
                    <p class="description" style="margin-left: 20px; line-height: 40px;">
                        <?php _e('This will save all changes: survey details, questions, choices, and settings', 'wp-survey'); ?>
                    </p>
                </div>
            </div>
            
            <div class="wp-survey-admin-sidebar">
                <div class="wp-survey-card">
                    <h3><?php _e('Shortcode', 'wp-survey'); ?></h3>
                    <input type="text" class="regular-text" readonly 
                           value="[wp_survey id=&quot;<?php echo $survey->id; ?>&quot;]"
                           onclick="this.select()">
                    <p class="description"><?php _e('Use this shortcode', 'wp-survey'); ?></p>
                </div>
                
                <div class="wp-survey-card">
                    <h3><?php _e('Quick Info', 'wp-survey'); ?></h3>
                    <p><strong><?php _e('Total Questions:', 'wp-survey'); ?></strong> <?php echo count($questions); ?></p>
                    <p><strong><?php _e('Display Mode:', 'wp-survey'); ?></strong> 
                        <?php echo $survey->display_mode === 'multi-step' ? __('Multi-Step', 'wp-survey') : __('All Questions', 'wp-survey'); ?>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var questionIndex = <?php echo count($questions); ?>;
    
    // Add Question
    $('#add-question-btn').on('click', function() {
        questionIndex++;
        var html = `
            <div class="wp-survey-question-item" data-id="0">
                <div class="wp-survey-question-header">
                    <span class="wp-survey-question-drag">⋮⋮</span>
                    <span class="wp-survey-question-number"><?php _e('Question', 'wp-survey'); ?> ${questionIndex}</span>
                    <div class="wp-survey-question-actions">
                        <button type="button" class="button delete-question-btn"><?php _e('Delete', 'wp-survey'); ?></button>
                    </div>
                </div>
                <div class="wp-survey-question-content">
                    <input type="hidden" name="questions[${questionIndex-1}][id]" value="0">
                    <input type="text" name="questions[${questionIndex-1}][question_text]" 
                           class="question-text large-text" 
                           placeholder="<?php _e('Enter your question here...', 'wp-survey'); ?>" required>
                    
                    <div class="wp-survey-question-settings">
                        <label class="wp-survey-toggle">
                            <input type="checkbox" name="questions[${questionIndex-1}][allow_multiple]" 
                                   class="question-allow-multiple" value="1">
                            <span class="wp-survey-toggle-slider"></span>
                            <span class="wp-survey-toggle-label"><?php _e('Allow Multiple Choices', 'wp-survey'); ?></span>
                        </label>
                        <p class="description"><?php _e('Enable this to let users select multiple answers for this question', 'wp-survey'); ?></p>
                    </div>
                    
                    <div class="wp-survey-question-choices">
                        <label><?php _e('Choices for this question:', 'wp-survey'); ?></label>
                        <button type="button" class="button button-small add-choice-btn"><?php _e('Add Choice', 'wp-survey'); ?></button>
                        
                        <div class="wp-survey-choices-list">
                            <p class="description no-choices-message"><?php _e('No choices yet', 'wp-survey'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#questions-container').append(html);
        $('#questions-container .description').first().remove();
    });
    
    // Delete Question
    $(document).on('click', '.delete-question-btn', function() {
        if (confirm('<?php _e('Are you sure you want to delete this question?', 'wp-survey'); ?>')) {
            $(this).closest('.wp-survey-question-item').fadeOut(300, function() {
                $(this).remove();
                if ($('.wp-survey-question-item').length === 0) {
                    $('#questions-container').html('<p class="description" style="text-align: center; padding: 40px 0; color: #666;"><?php _e('No questions yet. Add your first question to get started.', 'wp-survey'); ?></p>');
                }
            });
        }
    });
    
    // Add Choice
    $(document).on('click', '.add-choice-btn', function() {
        var $question = $(this).closest('.wp-survey-question-item');
        var $choicesList = $question.find('.wp-survey-choices-list');
        var questionIdx = $question.index();
        var choiceIdx = $choicesList.find('.wp-survey-choice-item').length;
        
        var html = `
            <div class="wp-survey-choice-item" data-id="0">
                <span class="wp-survey-choice-drag">⋮⋮</span>
                <input type="hidden" name="questions[${questionIdx}][choices][${choiceIdx}][id]" value="0">
                <input type="text" name="questions[${questionIdx}][choices][${choiceIdx}][title]" 
                       class="regular-text" placeholder="<?php _e('Choice Title', 'wp-survey'); ?>" required>
                <button type="button" class="button button-small delete-choice-btn"><?php _e('Delete', 'wp-survey'); ?></button>
            </div>
        `;
        
        $choicesList.find('.no-choices-message').remove();
        $choicesList.append(html);
    });
    
    // Delete Choice
    $(document).on('click', '.delete-choice-btn', function() {
        var $choicesList = $(this).closest('.wp-survey-choices-list');
        $(this).closest('.wp-survey-choice-item').fadeOut(300, function() {
            $(this).remove();
            if ($choicesList.find('.wp-survey-choice-item').length === 0) {
                $choicesList.html('<p class="description no-choices-message"><?php _e('No choices yet', 'wp-survey'); ?></p>');
            }
        });
    });
});
</script>