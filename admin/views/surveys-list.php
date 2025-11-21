<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Surveys', 'wp-survey'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=wp-survey-add'); ?>" class="page-title-action"><?php _e('Add New', 'wp-survey'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (empty($surveys)): ?>
        <div class="wp-survey-empty-state">
            <div class="wp-survey-empty-icon">📊</div>
            <h2><?php _e('No surveys yet', 'wp-survey'); ?></h2>
            <p><?php _e('Create your first survey to start collecting feedback from your audience.', 'wp-survey'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=wp-survey-add'); ?>" class="button button-primary button-hero"><?php _e('Create Your First Survey', 'wp-survey'); ?></a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Title', 'wp-survey'); ?></th>
                    <th><?php _e('Question', 'wp-survey'); ?></th>
                    <th><?php _e('Language', 'wp-survey'); ?></th>
                    <th><?php _e('Responses', 'wp-survey'); ?></th>
                    <th><?php _e('Shortcode', 'wp-survey'); ?></th>
                    <th><?php _e('Created', 'wp-survey'); ?></th>
                    <th><?php _e('Actions', 'wp-survey'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($surveys as $survey): 
                    $stats = WP_Survey_Database::get_survey_stats($survey->id);
                ?>
                <tr>
                    <td><strong><?php echo esc_html($survey->title); ?></strong></td>
                    <td><?php echo esc_html(wp_trim_words($survey->question, 10)); ?></td>
                    <td><?php echo esc_html(strtoupper($survey->language)); ?></td>
                    <td><?php echo esc_html($stats['total_votes']); ?></td>
                    <td>
                        <code class="wp-survey-shortcode" onclick="this.select()">[wp_survey id="<?php echo $survey->id; ?>"]</code>
                    </td>
                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($survey->created_at))); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=wp-survey-add&id=' . $survey->id); ?>" class="button button-small"><?php _e('Edit', 'wp-survey'); ?></a>
                        <button class="button button-small wp-survey-delete" data-id="<?php echo $survey->id; ?>"><?php _e('Delete', 'wp-survey'); ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
