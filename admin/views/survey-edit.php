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
                        <tr>
                            <th><label for="question"><?php _e('Question', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="text" id="question" name="question" class="regular-text" value="<?php echo $survey ? esc_attr($survey->question) : ''; ?>" required>
                                <p class="description"><?php _e('The main question users will answer', 'wp-survey'); ?></p>
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
                <div class="wp-survey-shortcode-box">
                    <label><?php _e('Widget', 'wp-survey'); ?></label>
                    <code onclick="this.select()">[wp_survey id="<?php echo $survey->id; ?>" type="widget"]</code>
                </div>
            </div>
            
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
            <?php else: ?>
            <div class="wp-survey-card">
                <h3><?php _e('Next Steps', 'wp-survey'); ?></h3>
                <ol style="padding-left: 20px; line-height: 1.8;">
                    <li><?php _e('Save the survey details', 'wp-survey'); ?></li>
                    <li><?php _e('Add survey choices with images', 'wp-survey'); ?></li>
                    <li><?php _e('Copy the shortcode', 'wp-survey'); ?></li>
                    <li><?php _e('Add it to any page or post', 'wp-survey'); ?></li>
                </ol>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
