<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php _e('Import / Export Surveys', 'wp-survey'); ?></h1>
    
    <div class="wp-survey-admin-container">
        <div class="wp-survey-admin-main">
            <!-- IMPORT SECTION -->
            <div class="wp-survey-card">
                <h2><?php _e('Import Survey', 'wp-survey'); ?></h2>
                <p><?php _e('Upload a JSON file to import a complete survey with all questions and choices.', 'wp-survey'); ?></p>
                
                <form id="wp-survey-import-form" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('wp_survey_import_nonce'); ?>
                    <input type="hidden" name="wp_survey_import" value="1">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="import_file"><?php _e('Select JSON File', 'wp-survey'); ?></label></th>
                            <td>
                                <input type="file" id="import_file" name="import_file" accept=".json" required>
                                <p class="description"><?php _e('Upload a .json file exported from WP Survey', 'wp-survey'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large">
                            <?php _e('Import Survey', 'wp-survey'); ?>
                        </button>
                    </p>
                </form>
                
                <?php if (isset($_GET['imported']) && $_GET['imported'] === 'success'): ?>
                <div class="notice notice-success">
                    <p>
                        <strong><?php _e('Survey imported successfully!', 'wp-survey'); ?></strong>
                        <a href="<?php echo admin_url('admin.php?page=wp-survey-edit&id=' . intval($_GET['survey_id'])); ?>">
                            <?php _e('Edit Survey', 'wp-survey'); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['import_error'])): ?>
                <div class="notice notice-error">
                    <p><?php echo esc_html(urldecode($_GET['import_error'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- EXPORT SECTION -->
            <div class="wp-survey-card">
                <h2><?php _e('Export Survey', 'wp-survey'); ?></h2>
                <p><?php _e('Export an existing survey to a JSON file that can be imported later or shared.', 'wp-survey'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('wp_survey_export_nonce'); ?>
                    <input type="hidden" name="wp_survey_export" value="1">
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="export_survey"><?php _e('Select Survey', 'wp-survey'); ?></label></th>
                            <td>
                                <select id="export_survey" name="survey_id" class="regular-text" required>
                                    <option value=""><?php _e('-- Select a Survey --', 'wp-survey'); ?></option>
                                    <?php
                                    $surveys = WP_Survey_Database::get_all_surveys();
                                    foreach ($surveys as $survey):
                                    ?>
                                    <option value="<?php echo $survey->id; ?>">
                                        <?php echo esc_html($survey->title); ?> 
                                        (<?php echo $survey->survey_type === 'multi-question' ? __('Multi-Question', 'wp-survey') : __('Simple', 'wp-survey'); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large">
                            <?php _e('Export Survey', 'wp-survey'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <div class="wp-survey-admin-sidebar">
            <div class="wp-survey-card">
                <h3><?php _e('Import File Format', 'wp-survey'); ?></h3>
                <p><?php _e('The JSON file should contain:', 'wp-survey'); ?></p>
                <ul style="padding-left: 20px; line-height: 1.8;">
                    <li><?php _e('Survey details (title, description, type)', 'wp-survey'); ?></li>
                    <li><?php _e('Questions with allow_multiple setting', 'wp-survey'); ?></li>
                    <li><?php _e('Choices for each question', 'wp-survey'); ?></li>
                    <li><?php _e('Images (optional)', 'wp-survey'); ?></li>
                </ul>
            </div>
            
            <div class="wp-survey-card">
                <h3><?php _e('Tips', 'wp-survey'); ?></h3>
                <ul style="padding-left: 20px; line-height: 1.8;">
                    <li><?php _e('Export surveys for backup', 'wp-survey'); ?></li>
                    <li><?php _e('Share surveys between sites', 'wp-survey'); ?></li>
                    <li><?php _e('Import creates a new survey', 'wp-survey'); ?></li>
                    <li><?php _e('Images must be re-uploaded', 'wp-survey'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>