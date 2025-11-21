jQuery(document).ready(function($) {
    
    $('.wp-survey-container, .wp-survey-widget').each(function() {
        var $container = $(this);
        var surveyId = $container.data('survey-id');
        
        // Check if already voted via cookie
        if (getCookie('wp_survey_voted_' + surveyId)) {
            showAlreadyVoted();
            return;
        }
        
        var $choices = $container.find('.wp-survey-choice');
        var $emailSection = $container.find('.wp-survey-email-section');
        var $nameInput = $container.find('.wp-survey-name-input');
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
                $progressFill.css('width', '33%');
                $progressText.text('Question 1 of 1 • 33% Complete');
            }
            
            // Enable submit if both fields are valid
            checkFormValidity();
        });
        
        // Handle name input
        $nameInput.on('input', function() {
            updateProgress();
            checkFormValidity();
        });
        
        // Handle email input
        $emailInput.on('input', function() {
            updateProgress();
            checkFormValidity();
        });
        
        function updateProgress() {
            if (!$progressFill.length) return;
            
            var hasChoice = selectedChoiceId !== null;
            var hasName = $nameInput.val().trim() !== '';
            var hasEmail = $emailInput.val().trim() !== '' && isValidEmail($emailInput.val());
            
            var progress = 0;
            if (hasChoice) progress = 33;
            if (hasChoice && hasName) progress = 66;
            if (hasChoice && hasName && hasEmail) progress = 100;
            
            $progressFill.css('width', progress + '%');
            $progressText.text('Question 1 of 1 • ' + progress + '% Complete');
        }
        
        function checkFormValidity() {
            var name = $nameInput.val().trim();
            var email = $emailInput.val().trim();
            
            if (name && email && isValidEmail(email) && selectedChoiceId) {
                $submitBtn.prop('disabled', false);
            } else {
                $submitBtn.prop('disabled', true);
            }
        }
        
        // Handle form submission
        $submitBtn.on('click', function(e) {
            e.preventDefault();
            
            if (!selectedChoiceId) {
                showError('Please select an option');
                return;
            }
            
            var name = $nameInput.val().trim();
            if (!name) {
                showError('Please enter your name');
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
                name: name,
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
        
        function showAlreadyVoted() {
            var alreadyVotedHtml = `
                <div class="wp-survey-success">
                    <div class="wp-survey-success-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2>Already Voted!</h2>
                    <p>You have already participated in this survey. Thank you!</p>
                </div>
            `;
            
            if ($container.hasClass('wp-survey-widget')) {
                $container.find('.wp-survey-content').html(alreadyVotedHtml);
            } else {
                $container.find('.wp-survey-card').html(alreadyVotedHtml);
            }
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
        
        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    });
});
