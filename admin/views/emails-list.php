<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Survey Emails', 'wp-survey'); ?></h1>
    <hr class="wp-header-end">
    
    <div style="margin: 20px 0;">
        <form method="get" action="">
            <input type="hidden" name="page" value="wp-survey-emails">
            <label for="survey_id"><?php _e('Select Survey:', 'wp-survey'); ?></label>
            <select name="survey_id" id="survey_id" onchange="this.form.submit()">
                <option value=""><?php _e('-- Select a Survey --', 'wp-survey'); ?></option>
                <?php foreach ($surveys as $survey): ?>
                <option value="<?php echo $survey->id; ?>" <?php selected($selected_survey, $survey->id); ?>>
                    <?php echo esc_html($survey->title); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <?php if ($selected_survey && !empty($emails)): ?>
    <div style="margin-bottom: 15px;">
        <a href="<?php echo wp_nonce_url(admin_url('admin-ajax.php?action=wp_survey_export_emails&survey_id=' . $selected_survey), 'wp_survey_nonce', 'nonce'); ?>" 
           class="button button-primary">
            <?php _e('Export to CSV', 'wp-survey'); ?>
        </a>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Name', 'wp-survey'); ?></th>
                <th><?php _e('Email', 'wp-survey'); ?></th>
                <th><?php _e('Choice', 'wp-survey'); ?></th>
                <th><?php _e('IP Address', 'wp-survey'); ?></th>
                <th><?php _e('Date', 'wp-survey'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            global $wpdb;
            $choices_table = $wpdb->prefix . 'survey_choices';
            
            foreach ($emails as $response): 
                $choice = $wpdb->get_row($wpdb->prepare(
                    "SELECT title FROM $choices_table WHERE id = %d",
                    $response->choice_id
                ));
            ?>
            <tr>
                <td><strong><?php echo esc_html($response->name ? $response->name : '-'); ?></strong></td>
                <td><?php echo esc_html($response->email); ?></td>
                <td><?php echo $choice ? esc_html($choice->title) : '-'; ?></td>
                <td><?php echo esc_html($response->ip_address); ?></td>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($response->created_at))); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px;">
        <strong><?php _e('Total Responses:', 'wp-survey'); ?></strong> <?php echo count($emails); ?>
    </p>
    
    <?php elseif ($selected_survey): ?>
    <div class="notice notice-info">
        <p><?php _e('No responses yet for this survey.', 'wp-survey'); ?></p>
    </div>
    <?php else: ?>
    <div class="notice notice-info">
        <p><?php _e('Please select a survey to view emails.', 'wp-survey'); ?></p>
    </div>
    <?php endif; ?>
</div>
