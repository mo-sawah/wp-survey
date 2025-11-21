jQuery(document).ready(function($) {
    
    // Save Survey
    $('#wp-survey-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'wp_survey_save',
            nonce: wpSurvey.nonce,
            survey_id: $('input[name="survey_id"]').val(),
            title: $('#title').val(),
            description: $('#description').val(),
            question: $('#question').val(),
            language: $('#language').val()
        };
        
        $.post(wpSurvey.ajaxurl, formData, function(response) {
            if (response.success) {
                if ($('input[name="survey_id"]').val() == '0') {
                    window.location.href = '?page=wp-survey-add&id=' + response.data.id;
                } else {
                    showNotice(wpSurvey.strings.saved, 'success');
                }
            } else {
                showNotice(response.data.message || wpSurvey.strings.error, 'error');
            }
        });
    });
    
    // Delete Survey
    $(document).on('click', '.wp-survey-delete', function(e) {
        e.preventDefault();
        
        if (!confirm(wpSurvey.strings.confirmDelete)) return;
        
        var surveyId = $(this).data('id');
        var $row = $(this).closest('tr');
        
        $.post(wpSurvey.ajaxurl, {
            action: 'wp_survey_delete',
            nonce: wpSurvey.nonce,
            survey_id: surveyId
        }, function(response) {
            if (response.success) {
                $row.fadeOut(function() {
                    $(this).remove();
                });
            } else {
                alert(response.data.message || wpSurvey.strings.error);
            }
        });
    });
    
    // Add Choice
    $('#add-choice-btn').on('click', function() {
        var surveyId = $('input[name="survey_id"]').val();
        var order = $('#choices-container .wp-survey-choice-item').length;
        
        var html = `
            <div class="wp-survey-choice-item" data-id="0">
                <div class="wp-survey-choice-drag">⋮⋮</div>
                <div class="wp-survey-choice-image-preview">
                    <div class="wp-survey-no-image">📷</div>
                </div>
                <div class="wp-survey-choice-content">
                    <input type="text" class="choice-title" placeholder="Choice Title">
                    <input type="text" class="choice-desc1" placeholder="Description 1">
                    <input type="text" class="choice-desc2" placeholder="Description 2">
                    <input type="hidden" class="choice-image-url" value="">
                </div>
                <div class="wp-survey-choice-actions">
                    <button type="button" class="button upload-image-btn">Image</button>
                    <button type="button" class="button save-choice-btn">Save</button>
                    <button type="button" class="button delete-choice-btn">Delete</button>
                </div>
            </div>
        `;
        
        $('#choices-container').append(html);
        $('.wp-survey-no-choices').remove();
    });
    
    // Upload Image
    $(document).on('click', '.upload-image-btn', function(e) {
        e.preventDefault();
        
        var $item = $(this).closest('.wp-survey-choice-item');
        var $imageInput = $item.find('.choice-image-url');
        var $imagePreview = $item.find('.wp-survey-choice-image-preview');
        
        var frame = wp.media({
            title: 'Select Image',
            button: { text: 'Use Image' },
            multiple: false,
            library: { type: 'image' }
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $imageInput.val(attachment.url);
            $imagePreview.html('<img src="' + attachment.url + '" alt="">');
        });
        
        frame.open();
    });
    
    // Save Choice
    $(document).on('click', '.save-choice-btn', function() {
        var $item = $(this).closest('.wp-survey-choice-item');
        var surveyId = $('input[name="survey_id"]').val();
        var choiceId = $item.data('id');
        
        var data = {
            action: 'wp_survey_save_choice',
            nonce: wpSurvey.nonce,
            choice_id: choiceId,
            survey_id: surveyId,
            title: $item.find('.choice-title').val(),
            description_1: $item.find('.choice-desc1').val(),
            description_2: $item.find('.choice-desc2').val(),
            image_url: $item.find('.choice-image-url').val(),
            sort_order: $item.index()
        };
        
        $.post(wpSurvey.ajaxurl, data, function(response) {
            if (response.success) {
                $item.data('id', response.data.id);
                showNotice(wpSurvey.strings.saved, 'success');
            } else {
                alert(response.data.message || wpSurvey.strings.error);
            }
        });
    });
    
    // Delete Choice
    $(document).on('click', '.delete-choice-btn', function() {
        if (!confirm(wpSurvey.strings.confirmDelete)) return;
        
        var $item = $(this).closest('.wp-survey-choice-item');
        var choiceId = $item.data('id');
        
        if (choiceId > 0) {
            $.post(wpSurvey.ajaxurl, {
                action: 'wp_survey_delete_choice',
                nonce: wpSurvey.nonce,
                choice_id: choiceId
            }, function(response) {
                if (response.success) {
                    $item.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || wpSurvey.strings.error);
                }
            });
        } else {
            $item.remove();
        }
    });
    
    // Export Emails
    $('#export-emails-btn').on('click', function() {
        var surveyId = $('input[name="survey_id"]').val();
        window.location.href = wpSurvey.ajaxurl + '?action=wp_survey_export_emails&survey_id=' + surveyId + '&nonce=' + wpSurvey.nonce;
    });
    
    // Helper Functions
    function showNotice(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
