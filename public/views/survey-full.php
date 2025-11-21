<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-survey-container" data-survey-id="<?php echo $survey->id; ?>">
    <div class="wp-survey-card">
        <div class="wp-survey-header">
            <?php if ($survey->banner_image): ?>
            <div class="wp-survey-banner-image">
                <img src="<?php echo esc_url($survey->banner_image); ?>" alt="<?php echo esc_attr($survey->title); ?>">
            </div>
            <?php endif; ?>
            <div class="wp-survey-header-content">
                <h1 class="wp-survey-title"><?php echo esc_html($survey->title); ?></h1>
                <?php if ($survey->description): ?>
                <p class="wp-survey-description"><?php echo esc_html($survey->description); ?></p>
                <?php endif; ?>
            </div>
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

            <div class="wp-survey-facebook-section hidden">
                <?php if ($survey->facebook_page_url): ?>
                <div class="wp-survey-facebook-box">
                    <div class="wp-survey-facebook-icon">👍</div>
                    <h3 class="wp-survey-facebook-title"><?php _e('Follow our Facebook page to submit your vote!', 'wp-survey'); ?></h3>
                    <p class="wp-survey-facebook-desc"><?php _e('To enter the draw for an authentic Cyprus League jersey by Stoiximan, simply like our Facebook page and cast your vote below. Only those who complete both steps will be eligible.', 'wp-survey'); ?></p>
                    
                    <div class="wp-survey-facebook-plugin">
                        <iframe src="https://www.facebook.com/plugins/page.php?href=<?php echo urlencode($survey->facebook_page_url); ?>&tabs=&width=340&height=130&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=false&appId" width="340" height="130" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                    </div>
                    
                    <label class="wp-survey-facebook-confirm">
                        <input type="checkbox" class="wp-survey-facebook-checkbox">
                        <span><?php _e('✓ I have followed the page', 'wp-survey'); ?></span>
                    </label>
                </div>
                <?php else: ?>
                <div class="wp-survey-facebook-box">
                    <p class="wp-survey-facebook-desc"><?php _e('Click Submit Vote to record your choice', 'wp-survey'); ?></p>
                </div>
                <?php endif; ?>
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
