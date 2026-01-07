jQuery(document).ready(function ($) {
  // Survey Type Toggle
  function toggleSurveyTypeFields() {
    var surveyType = $("#survey_type").val();

    if (surveyType === "multi-question") {
      $(".simple-survey-field").hide();
      $(".multi-question-field").show();
      $("#question").removeAttr("required");
    } else {
      $(".simple-survey-field").show();
      $(".multi-question-field").hide();
      $("#question").attr("required", "required");
    }
  }

  // Initial state on page load
  if ($("#survey_type").length) {
    toggleSurveyTypeFields();
    $("#survey_type").on("change", toggleSurveyTypeFields);
  }

  // Upload Banner Image
  $("#upload-banner-btn").on("click", function (e) {
    e.preventDefault();

    var frame = wp.media({
      title: "Select Banner Image",
      button: { text: "Use Image" },
      multiple: false,
      library: { type: "image" },
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $("#banner_image").val(attachment.url);
      $("#banner-preview").attr("src", attachment.url).show();
      $("#remove-banner-btn").show();
    });

    frame.open();
  });

  // Remove Banner Image
  $("#remove-banner-btn").on("click", function (e) {
    e.preventDefault();
    $("#banner_image").val("");
    $("#banner-preview").attr("src", "").hide();
    $(this).hide();
  });

  // Delete Survey
  $(document).on("click", ".wp-survey-delete", function (e) {
    e.preventDefault();

    if (!confirm(wpSurvey.strings.confirmDelete)) return;

    var surveyId = $(this).data("id");
    var $row = $(this).closest("tr");

    $.post(
      wpSurvey.ajaxurl,
      {
        action: "wp_survey_delete",
        nonce: wpSurvey.nonce,
        survey_id: surveyId,
      },
      function (response) {
        if (response.success) {
          $row.fadeOut(function () {
            $(this).remove();
          });
        } else {
          alert(response.data.message || wpSurvey.strings.error);
        }
      }
    );
  });

  // ============================================
  // SIMPLE SURVEY - Add Choice
  // ============================================
  $("#add-choice-btn").on("click", function () {
    var surveyId = $('input[name="survey_id"]').val();
    var order = $("#choices-container .wp-survey-choice-item").length;

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

    $("#choices-container").append(html);
    $(".wp-survey-no-choices").remove();
  });

  // ============================================
  // MULTI-QUESTION SURVEY - Add Question
  // ============================================
  $("#add-question-btn").on("click", function () {
    var surveyId = $('input[name="survey_id"]').val();
    var order = $("#questions-container .wp-survey-question-item").length;
    var questionNumber = order + 1;

    var html = `
            <div class="wp-survey-question-item" data-id="0">
                <div class="wp-survey-question-content">
                    <input type="text" class="question-text large-text" placeholder="Enter your question here...">
                    
                    <div class="wp-survey-question-settings">
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <label class="wp-survey-toggle">
                                <input type="checkbox" class="question-allow-multiple">
                                <span class="wp-survey-toggle-slider"></span>
                                <span class="wp-survey-toggle-label">Allow Multiple Choices</span>
                            </label>

                            <div class="wp-survey-max-choices-box" style="display: none;">
                                <label style="font-weight: 600; font-size: 13px;">
                                    Max Selection:
                                    <input type="number" class="question-max-choices small-text" min="0" value="0" style="width: 60px; margin-left: 5px;">
                                </label>
                                <span class="description" style="font-size: 11px; display: block; margin-top: 2px;">0 = Unlimited</span>
                            </div>
                        </div>
                        <p class="description">Enable this to let users select multiple answers for this question</p>
                    </div>
                    </div>
            </div>
        `;

    $("#questions-container").append(html);
    $(".wp-survey-no-questions").remove();
  });

  // Save Question
  $(document).on("click", ".save-question-btn", function () {
    var $item = $(this).closest(".wp-survey-question-item");
    var surveyId = $('input[name="survey_id"]').val();
    var questionId = $item.data("id");

    var data = {
      action: "wp_survey_save_question",
      nonce: wpSurvey.nonce,
      question_id: questionId,
      survey_id: surveyId,
      question_text: $item.find(".question-text").val(),
      allow_multiple: $item.find(".question-allow-multiple").is(":checked")
        ? 1
        : 0,
      max_choices: $item.find(".question-max-choices").val(), // <--- ADD THIS LINE
      sort_order: $item.index(),
    };

    $.post(wpSurvey.ajaxurl, data, function (response) {
      if (response.success) {
        $item.data("id", response.data.id);
        showNotice(wpSurvey.strings.saved, "success");
      } else {
        alert(response.data.message || wpSurvey.strings.error);
      }
    });
  });

  // Delete Question
  $(document).on("click", ".delete-question-btn", function () {
    if (!confirm(wpSurvey.strings.confirmDelete)) return;

    var $item = $(this).closest(".wp-survey-question-item");
    var questionId = $item.data("id");

    if (questionId > 0) {
      $.post(
        wpSurvey.ajaxurl,
        {
          action: "wp_survey_delete_question",
          nonce: wpSurvey.nonce,
          question_id: questionId,
        },
        function (response) {
          if (response.success) {
            $item.fadeOut(function () {
              $(this).remove();
              updateQuestionNumbers();
            });
          } else {
            alert(response.data.message || wpSurvey.strings.error);
          }
        }
      );
    } else {
      $item.remove();
      updateQuestionNumbers();
    }
  });

  // ============================================
  // CHOICES - Add Choice (for both simple and multi-question)
  // ============================================
  $(document).on("click", ".add-choice-btn", function () {
    var surveyId = $('input[name="survey_id"]').val();
    var $questionItem = $(this).closest(".wp-survey-question-item");
    var $choicesList = $(this).siblings(".choices-list");

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

    $choicesList.append(html);
    $choicesList.find(".wp-survey-no-choices").remove();
  });

  // Upload Image
  $(document).on("click", ".upload-image-btn", function (e) {
    e.preventDefault();

    var $item = $(this).closest(".wp-survey-choice-item");
    var $imageInput = $item.find(".choice-image-url");
    var $imagePreview = $item.find(".wp-survey-choice-image-preview");

    var frame = wp.media({
      title: "Select Image",
      button: { text: "Use Image" },
      multiple: false,
      library: { type: "image" },
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $imageInput.val(attachment.url);
      $imagePreview.html('<img src="' + attachment.url + '" alt="">');
    });

    frame.open();
  });

  // Save Choice
  $(document).on("click", ".save-choice-btn", function () {
    var $item = $(this).closest(".wp-survey-choice-item");
    var surveyId = $('input[name="survey_id"]').val();
    var choiceId = $item.data("id");
    var questionId =
      $item.data("question-id") ||
      $item.closest(".wp-survey-question-item").data("id") ||
      null;

    var data = {
      action: "wp_survey_save_choice",
      nonce: wpSurvey.nonce,
      choice_id: choiceId,
      survey_id: surveyId,
      question_id: questionId,
      title: $item.find(".choice-title").val(),
      description_1: $item.find(".choice-desc1").val(),
      description_2: $item.find(".choice-desc2").val(),
      image_url: $item.find(".choice-image-url").val(),
      sort_order: $item.index(),
    };

    $.post(wpSurvey.ajaxurl, data, function (response) {
      if (response.success) {
        $item.data("id", response.data.id);
        if (questionId) {
          $item.data("question-id", questionId);
        }
        showNotice(wpSurvey.strings.saved, "success");
      } else {
        alert(response.data.message || wpSurvey.strings.error);
      }
    });
  });

  // Delete Choice
  $(document).on("click", ".delete-choice-btn", function () {
    if (!confirm(wpSurvey.strings.confirmDelete)) return;

    var $item = $(this).closest(".wp-survey-choice-item");
    var choiceId = $item.data("id");

    if (choiceId > 0) {
      $.post(
        wpSurvey.ajaxurl,
        {
          action: "wp_survey_delete_choice",
          nonce: wpSurvey.nonce,
          choice_id: choiceId,
        },
        function (response) {
          if (response.success) {
            $item.fadeOut(function () {
              $(this).remove();
            });
          } else {
            alert(response.data.message || wpSurvey.strings.error);
          }
        }
      );
    } else {
      $item.remove();
    }
  });

  // Export Emails
  $("#export-emails-btn").on("click", function () {
    var surveyId = $('input[name="survey_id"]').val();
    window.location.href =
      wpSurvey.ajaxurl +
      "?action=wp_survey_export_emails&survey_id=" +
      surveyId +
      "&nonce=" +
      wpSurvey.nonce;
  });

  // ============================================
  // Helper Functions
  // ============================================
  function showNotice(message, type) {
    var $notice = $(
      '<div class="notice notice-' +
        type +
        ' is-dismissible"><p>' +
        message +
        "</p></div>"
    );
    $(".wrap h1").after($notice);

    setTimeout(function () {
      $notice.fadeOut(function () {
        $(this).remove();
      });
    }, 3000);
  }

  function updateQuestionNumbers() {
    $("#questions-container .wp-survey-question-item").each(function (index) {
      $(this)
        .find(".wp-survey-question-number")
        .text("Question " + (index + 1));
    });
  }
  // Toggle Max Choices Box Visibility
  $(document).on("change", ".question-allow-multiple", function () {
    var $box = $(this)
      .closest(".wp-survey-question-settings")
      .find(".wp-survey-max-choices-box");
    if ($(this).is(":checked")) {
      $box.show();
    } else {
      $box.hide();
    }
  });
});
