<?php if (!defined('ABSPATH')) exit; ?>

<div class="wp-survey-multi-container" data-survey-id="<?php echo $survey->id; ?>" data-mode="multi-step">
    
    <?php if ($survey->intro_enabled): ?>
    <!-- Intro Screen -->
    <div class="wp-survey-intro-screen active">
        <div class="wp-survey-card">
            <?php if ($survey->banner_image): ?>
            <div class="wp-survey-intro-banner">
                <img src="<?php echo esc_url($survey->banner_image); ?>" alt="<?php echo esc_attr($survey->title); ?>">
            </div>
            <?php endif; ?>
            
            <div class="wp-survey-intro-content">
                <h1 class="wp-survey-intro-title"><?php echo esc_html($survey->title); ?></h1>
                
                <?php if ($survey->description): ?>
                <p class="wp-survey-intro-description"><?php echo esc_html($survey->description); ?></p>
                <?php endif; ?>
                
                <div class="wp-survey-intro-meta">
                    <div class="wp-survey-intro-stat">
                        <span class="wp-survey-intro-stat-icon">📋</span>
                        <span class="wp-survey-intro-stat-text"><?php echo count($questions_data); ?> <?php _e('Questions', 'wp-survey'); ?></span>
                    </div>
                    <div class="wp-survey-intro-stat">
                        <span class="wp-survey-intro-stat-icon">⏱️</span>
                        <span class="wp-survey-intro-stat-text"><?php echo ceil(count($questions_data) * 0.5); ?> <?php _e('min', 'wp-survey'); ?></span>
                    </div>
                </div>
                
                <button class="wp-survey-button wp-survey-button-primary wp-survey-start-btn">
                    <?php _e('Start Survey', 'wp-survey'); ?> →
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Survey Questions -->
    <div class="wp-survey-questions-screen <?php echo !$survey->intro_enabled ? 'active' : ''; ?>">
        <div class="wp-survey-card">
            
            <!-- Progress Bar -->
            <div class="wp-survey-progress-wrapper">
                <div class="wp-survey-progress-text">
                    <?php _e('Question', 'wp-survey'); ?> <span class="wp-survey-current-question">1</span> <?php _e('of', 'wp-survey'); ?> <?php echo count($questions_data); ?>
                </div>
                <div class="wp-survey-progress-bar">
                    <div class="wp-survey-progress-fill" style="width: <?php echo (1 / count($questions_data)) * 100; ?>%;"></div>
                </div>
            </div>
            
            <!-- Questions Container -->
            <div class="wp-survey-content">
                <?php foreach ($questions_data as $index => $q_data): ?>
                <div class="wp-survey-question-step <?php echo $index === 0 ? 'active' : ''; ?>" 
                    data-question-index="<?php echo $index; ?>" 
                    data-question-id="<?php echo $q_data['question']->id; ?>"
                    data-allow-multiple="<?php echo $q_data['question']->allow_multiple ? '1' : '0'; ?>"
                    data-max-choices="<?php echo esc_attr(isset($q_data['question']->max_choices) ? $q_data['question']->max_choices : 0); ?>"> <h2 class="wp-survey-question"><?php echo esc_html($q_data['question']->question_text); ?></h2>
                    
                    <h2 class="wp-survey-question"><?php echo esc_html($q_data['question']->question_text); ?></h2>
                    
                    <?php if ($q_data['question']->allow_multiple): ?>
                    <p class="wp-survey-question-hint"><?php _e('Select all that apply', 'wp-survey'); ?></p>
                    <?php endif; ?>
                    
                    <div class="wp-survey-choices">
                        <?php foreach ($q_data['choices'] as $choice): ?>
                        <label class="wp-survey-choice" data-choice-id="<?php echo $choice->id; ?>">
                            <?php if ($q_data['question']->allow_multiple): ?>
                            <input type="checkbox" 
                                   name="question-<?php echo $q_data['question']->id; ?>[]" 
                                   value="<?php echo $choice->id; ?>">
                            <?php else: ?>
                            <input type="radio" 
                                   name="question-<?php echo $q_data['question']->id; ?>" 
                                   value="<?php echo $choice->id; ?>">
                            <?php endif; ?>
                            <?php if ($choice->image_url): ?>
                            <div class="wp-survey-choice-image">
                                <img src="<?php echo esc_url($choice->image_url); ?>" alt="<?php echo esc_attr($choice->title); ?>">
                            </div>
                            <?php endif; ?>
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
                </div>
                <?php endforeach; ?>
                
                <!-- Facebook Section (shown before final submission) -->
                <?php if ($survey->facebook_page_url): ?>
                <div class="wp-survey-facebook-section hidden">
                    <div class="wp-survey-facebook-box">
                        <div class="wp-survey-facebook-icon">👍</div>
                        <h3 class="wp-survey-facebook-title"><?php _e('One Last Step!', 'wp-survey'); ?></h3>
                        <p class="wp-survey-facebook-desc"><?php _e('Follow our Facebook page to submit your responses', 'wp-survey'); ?></p>
                        
                        <div class="wp-survey-facebook-plugin">
                            <iframe src="https://www.facebook.com/plugins/page.php?href=<?php echo urlencode($survey->facebook_page_url); ?>&tabs=&width=340&height=130&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=false&appId" width="340" height="130" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                        </div>
                        
                        <label class="wp-survey-facebook-confirm">
                            <input type="checkbox" class="wp-survey-facebook-checkbox">
                            <span><?php _e('✓ I followed the page', 'wp-survey'); ?></span>
                        </label>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="wp-survey-error hidden"></div>
                
                <!-- Navigation Buttons -->
                <div class="wp-survey-buttons">
                    <button type="button" class="wp-survey-button wp-survey-button-secondary wp-survey-prev-btn" style="display: none;">
                        ← <?php _e('Previous', 'wp-survey'); ?>
                    </button>
                    <button type="button" class="wp-survey-button wp-survey-button-primary wp-survey-next-btn" disabled>
                        <?php _e('Next', 'wp-survey'); ?> →
                    </button>
                    <button type="submit" class="wp-survey-button wp-survey-button-primary wp-survey-submit-btn" style="display: none;" disabled>
                        <?php _e('Submit Survey', 'wp-survey'); ?> ✓
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Screen -->
    <div class="wp-survey-success-screen">
        <div class="wp-survey-card">
            <div class="wp-survey-success">
                <div class="wp-survey-success-icon">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2><?php _e('Thank You! 🎉', 'wp-survey'); ?></h2>
                <p><?php _e('Your responses have been recorded successfully. We appreciate your participation!', 'wp-survey'); ?></p>
            </div>
        </div>
    </div>
    
</div>