jQuery(document).ready(function($) {
    
    $('.wp-survey-container, .wp-survey-widget').each(function() {
        var $container = $(this);
        var surveyId = $container.data('survey-id');
        var $choices = $container.find('.wp-survey-choice');
        var $emailSection = $container.find('.wp-survey-email-section');
        var $emailInput = $container.find('.wp-survey-email-input');
        var $submitBtn = $container.find('.wp-survey-button-primary');
        var $progressFill = $container.find('.wp-survey-progress-fill');
        var $progressText = $container.find('.wp-survey-progress-text');
        var $error = $container.find('.wp-survey-error');
        var selectedChoiceId = null;
        
        // Handle choice selection
        $choices.on('click', function() {
            $choices.removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
            selectedChoiceId = $(this).data('choice-id');
            
            // Show email section
            $emailSection.removeClass('hidden').addClass('show');
            
            // Update progress
            if ($progressFill.length) {
                $progressFill.css('width', '50%');
                $progressText.text('Question 1 of 1 • 50% Complete');
            }
            
            // Enable submit if email is valid
            if ($emailInput.val() && isValidEmail($emailInput.val())) {
                $submitBtn.prop('disabled', false);
            }
        });
        
        // Handle email input
        $emailInput.on('input', function() {
            var email = $(this).val();
            
            if (email && isValidEmail(email) && selectedChoiceId) {
                $submitBtn.prop('disabled', false);
                
                if ($progressFill.length) {
                    $progressFill.css('width', '100%');
                    $progressText.text('Question 1 of 1 • 100% Complete');
                }
            } else {
                $submitBtn.prop('disabled', true);
                
                if ($progressFill.length && selectedChoiceId) {
                    $progressFill.css('width', '50%');
                    $progressText.text('Question 1 of 1 • 50% Complete');
                }
            }
        });
        
        // Handle form submission
        $submitBtn.on('click', function(e) {
            e.preventDefault();
            
            if (!selectedChoiceId) {
                showError('Please select an option');
                return;
            }
            
            var email = $emailInput.val();
            if (!isValidEmail(email)) {
                showError('Please enter a valid email address');
                return;
            }
            
            $submitBtn.prop('disabled', true).text('Submitting...');
            $error.addClass('hidden');
            
            $.post(wpSurveyPublic.ajaxurl, {
                action: 'wp_survey_submit',
                nonce: wpSurveyPublic.nonce,
                survey_id: surveyId,
                choice_id: selectedChoiceId,
                email: email
            }, function(response) {
                if (response.success) {
                    showSuccess();
                } else {
                    showError(response.data.message || 'An error occurred');
                    $submitBtn.prop('disabled', false).text('Submit Vote');
                }
            }).fail(function() {
                showError('Network error. Please try again.');
                $submitBtn.prop('disabled', false).text('Submit Vote');
            });
        });
        
        function showError(message) {
            $error.text(message).removeClass('hidden');
        }
        
        function showSuccess() {
            var successHtml = `
                <div class="wp-survey-success">
                    <div class="wp-survey-success-icon">
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2>Thank You for Your Vote! 🎉</h2>
                    <p>Your response has been recorded successfully. We appreciate your time!</p>
                </div>
            `;
            
            if ($container.hasClass('wp-survey-widget')) {
                $container.find('.wp-survey-content').html(successHtml);
            } else {
                $container.find('.wp-survey-card').html(successHtml);
            }
        }
        
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
    });
});
