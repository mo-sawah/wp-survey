<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-survey-container" data-survey-id="<?php echo $survey->id; ?>">
    <div class="wp-survey-card">
        <div class="wp-survey-header" <?php if ($survey->banner_image): ?>style="background-image: url('<?php echo esc_url($survey->banner_image); ?>'); background-size: cover; background-position: center;"<?php endif; ?>>
            <h1 class="wp-survey-title"><?php echo esc_html($survey->title); ?></h1>
            <?php if ($survey->description): ?>
            <p class="wp-survey-description"><?php echo esc_html($survey->description); ?></p>
            <?php endif; ?>
        </div>

        <div class="wp-survey-progress-wrapper">
            <div class="wp-survey-progress-text"><?php _e('Question 1 of 1 • 0% Complete', 'wp-survey'); ?></div>
            <div class="wp-survey-progress-bar">
                <div class="wp-survey-progress-fill" style="width: 0%;"></div>
            </div>
        </div>

        <div class="wp-survey-content">
            <h2 class="wp-survey-question"><?php echo esc_html($survey->question); ?></h2>

            <div class="wp-survey-choices">
                <?php foreach ($choices as $choice): ?>
                <label class="wp-survey-choice" data-choice-id="<?php echo $choice->id; ?>">
                    <input type="radio" name="survey-choice" value="<?php echo $choice->id; ?>">
                    <div class="wp-survey-choice-image">
                        <?php if ($choice->image_url): ?>
                            <img src="<?php echo esc_url($choice->image_url); ?>" alt="<?php echo esc_attr($choice->title); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="wp-survey-choice-content">
                        <div class="wp-survey-choice-title"><?php echo esc_html($choice->title); ?></div>
                        <?php if ($choice->description_1): ?>
                        <div class="wp-survey-choice-desc1"><?php echo esc_html($choice->description_1); ?></div>
                        <?php endif; ?>
                        <?php if ($choice->description_2): ?>
                        <div class="wp-survey-choice-desc2"><?php echo esc_html($choice->description_2); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="wp-survey-choice-check"></div>
                </label>
                <?php endforeach; ?>
            </div>

            <div class="wp-survey-email-section hidden">
                <label class="wp-survey-email-label"><?php _e('👤 Enter your name', 'wp-survey'); ?></label>
                <input type="text" class="wp-survey-name-input" placeholder="Your name" required>
                
                <label class="wp-survey-email-label" style="margin-top: 15px;"><?php _e('📧 Enter your email', 'wp-survey'); ?></label>
                <input type="email" class="wp-survey-email-input" placeholder="your@email.com" required>
                <div class="wp-survey-email-helper"><?php _e('🔒 Your information is safe with us and will never be shared', 'wp-survey'); ?></div>
            </div>

            <div class="wp-survey-error hidden"></div>

            <div class="wp-survey-buttons">
                <button type="submit" class="wp-survey-button wp-survey-button-primary" disabled>
                    <?php _e('Submit Vote', 'wp-survey'); ?> →
                </button>
            </div>
        </div>
    </div>
</div>
