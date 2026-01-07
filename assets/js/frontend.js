jQuery(document).ready(function ($) {
  // ============================================
  // SIMPLE SURVEY (Backward Compatibility)
  // ============================================
  $(".wp-survey-container, .wp-survey-widget").each(function () {
    var $container = $(this);
    var surveyId = $container.data("survey-id");

    // Check if already voted via cookie
    if (getCookie("wp_survey_voted_" + surveyId)) {
      showAlreadyVoted($container);
      return;
    }

    var $choices = $container.find(".wp-survey-choice");
    var $facebookSection = $container.find(".wp-survey-facebook-section");
    var $facebookCheckbox = $container.find(".wp-survey-facebook-checkbox");
    var $submitBtn = $container.find(".wp-survey-button-primary");
    var $progressFill = $container.find(".wp-survey-progress-fill");
    var $progressText = $container.find(".wp-survey-progress-text");
    var $error = $container.find(".wp-survey-error");
    var selectedChoiceId = null;

    $submitBtn.prop("disabled", true);

    $choices.on("click", function () {
      $choices.removeClass("selected");
      $(this).addClass("selected");
      $(this).find('input[type="radio"]').prop("checked", true);
      selectedChoiceId = $(this).data("choice-id");

      $facebookSection.removeClass("hidden").addClass("show");

      if ($progressFill.length) {
        $progressFill.css("width", "50%");
        $progressText.text("Question 1 of 1 • 50% Complete");
      }

      if ($facebookCheckbox.length === 0 || $facebookCheckbox.is(":checked")) {
        $submitBtn.prop("disabled", false);
        if ($progressFill.length) {
          $progressFill.css("width", "100%");
          $progressText.text("Question 1 of 1 • 100% Complete");
        }
      } else {
        $submitBtn.prop("disabled", true);
      }
    });

    $facebookCheckbox.on("change", function () {
      if ($(this).is(":checked") && selectedChoiceId) {
        $submitBtn.prop("disabled", false);
        if ($progressFill.length) {
          $progressFill.css("width", "100%");
          $progressText.text("Question 1 of 1 • 100% Complete");
        }
      } else {
        $submitBtn.prop("disabled", true);
        if ($progressFill.length) {
          $progressFill.css("width", "50%");
          $progressText.text("Question 1 of 1 • 50% Complete");
        }
      }
    });

    $submitBtn.on("click", function (e) {
      e.preventDefault();

      if (!selectedChoiceId) {
        showError($error, "Please select a choice");
        return;
      }

      $submitBtn.prop("disabled", true).text("Submitting...");
      $error.addClass("hidden");

      $.post(
        wpSurveyPublic.ajaxurl,
        {
          action: "wp_survey_submit",
          nonce: wpSurveyPublic.nonce,
          survey_id: surveyId,
          choice_id: selectedChoiceId,
        },
        function (response) {
          if (response.success) {
            showSuccess($container);
          } else {
            showError($error, response.data.message || "An error occurred");
            $submitBtn.prop("disabled", false).text("Submit Vote");
          }
        }
      ).fail(function () {
        showError($error, "Network error. Please try again.");
        $submitBtn.prop("disabled", false).text("Submit Vote");
      });
    });
  });

  // ============================================
  // MULTI-QUESTION SURVEY
  // ============================================
  $(".wp-survey-multi-container").each(function () {
    var $container = $(this);
    var surveyId = $container.data("survey-id");
    var mode = $container.data("mode");

    // Check if already voted via cookie
    if (getCookie("wp_survey_voted_" + surveyId)) {
      $container
        .find(
          ".wp-survey-intro-screen, .wp-survey-questions-screen, .wp-survey-all-questions-screen"
        )
        .removeClass("active");
      $container.find(".wp-survey-success-screen").addClass("active");
      return;
    }

    if (mode === "multi-step") {
      initMultiStepSurvey($container, surveyId);
    } else {
      initAllQuestionsSurvey($container, surveyId);
    }
  });

  // ============================================
  // MULTI-STEP SURVEY LOGIC
  // ============================================
  function initMultiStepSurvey($container, surveyId) {
    var $startBtn = $container.find(".wp-survey-start-btn");
    var $introScreen = $container.find(".wp-survey-intro-screen");
    var $questionsScreen = $container.find(".wp-survey-questions-screen");
    var $successScreen = $container.find(".wp-survey-success-screen");
    var $questionSteps = $container.find(".wp-survey-question-step");
    var $prevBtn = $container.find(".wp-survey-prev-btn");
    var $nextBtn = $container.find(".wp-survey-next-btn");
    var $submitBtn = $container.find(".wp-survey-submit-btn");
    var $progressFill = $container.find(".wp-survey-progress-fill");
    var $progressText = $container.find(".wp-survey-current-question");
    var $facebookSection = $container.find(".wp-survey-facebook-section");
    var $facebookCheckbox = $container.find(".wp-survey-facebook-checkbox");
    var $error = $container.find(".wp-survey-error");

    var currentStep = 0;
    var totalSteps = $questionSteps.length;
    var responses = {};

    // Start survey
    $startBtn.on("click", function () {
      $introScreen.removeClass("active");
      $questionsScreen.addClass("active");
    });

    // Handle choice selection
    $questionSteps.each(function () {
      var $step = $(this);
      var questionId = $step.data("question-id");
      var allowMultiple = $step.data("allow-multiple");

      $step.find(".wp-survey-choice").on("click", function () {
        var $choice = $(this);
        var choiceId = $choice.data("choice-id");

        if (allowMultiple) {
          // Multiple choice - toggle selection
          $choice.toggleClass("selected");
          $choice
            .find('input[type="checkbox"]')
            .prop("checked", $choice.hasClass("selected"));

          // Update responses array
          var selectedChoices = [];
          $step.find(".wp-survey-choice.selected").each(function () {
            selectedChoices.push($(this).data("choice-id"));
          });

          if (selectedChoices.length > 0) {
            responses[questionId] = selectedChoices;
          } else {
            delete responses[questionId];
          }
        } else {
          // Single choice - radio button behavior
          $step.find(".wp-survey-choice").removeClass("selected");
          $choice.addClass("selected");
          $choice.find('input[type="radio"]').prop("checked", true);
          responses[questionId] = [choiceId];
        }

        // Enable next/submit button
        if (currentStep < totalSteps - 1) {
          $nextBtn.prop("disabled", false);
        } else {
          checkFinalStep();
        }
      });
    });

    // Next button
    $nextBtn.on("click", function () {
      if (currentStep < totalSteps - 1) {
        $questionSteps.eq(currentStep).removeClass("active");
        currentStep++;
        $questionSteps.eq(currentStep).addClass("active");

        updateProgress();
        updateButtons();
      }
    });

    // Previous button
    $prevBtn.on("click", function () {
      if (currentStep > 0) {
        $facebookSection.addClass("hidden");
        $questionSteps.eq(currentStep).removeClass("active");
        currentStep--;
        $questionSteps.eq(currentStep).addClass("active");

        updateProgress();
        updateButtons();
      }
    });

    // Facebook checkbox
    $facebookCheckbox.on("change", function () {
      checkFinalStep();
    });

    // Submit button
    $submitBtn.on("click", function () {
      if (Object.keys(responses).length !== totalSteps) {
        showError($error, "Please answer all questions");
        return;
      }

      $submitBtn.prop("disabled", true).text("Submitting...");

      var responsesArray = [];
      for (var qId in responses) {
        var choiceIds = responses[qId];
        // Handle both single choice (array with one item) and multiple choices (array with multiple items)
        if (Array.isArray(choiceIds)) {
          choiceIds.forEach(function (choiceId) {
            responsesArray.push({
              question_id: qId,
              choice_id: choiceId,
            });
          });
        }
      }

      $.post(
        wpSurveyPublic.ajaxurl,
        {
          action: "wp_survey_submit_multi",
          nonce: wpSurveyPublic.nonce,
          survey_id: surveyId,
          responses: JSON.stringify(responsesArray),
        },
        function (response) {
          if (response.success) {
            $questionsScreen.removeClass("active");
            $successScreen.addClass("active");
          } else {
            showError($error, response.data.message || "An error occurred");
            $submitBtn.prop("disabled", false).text("Submit Survey ✓");
          }
        }
      ).fail(function () {
        showError($error, "Network error. Please try again.");
        $submitBtn.prop("disabled", false).text("Submit Survey ✓");
      });
    });

    function updateProgress() {
      var progress = ((currentStep + 1) / totalSteps) * 100;
      $progressFill.css("width", progress + "%");
      $progressText.text(currentStep + 1);
    }

    function updateButtons() {
      // Previous button
      if (currentStep === 0) {
        $prevBtn.hide();
      } else {
        $prevBtn.show();
      }

      // Check if current question is answered
      var currentQuestionId = $questionSteps
        .eq(currentStep)
        .data("question-id");
      var isAnswered = responses.hasOwnProperty(currentQuestionId);

      // Next/Submit button
      if (currentStep === totalSteps - 1) {
        $nextBtn.hide();
        checkFinalStep();
        $submitBtn.show();
      } else {
        $submitBtn.hide();
        $nextBtn.show();
        $nextBtn.prop("disabled", !isAnswered);
      }
    }

    function checkFinalStep() {
      if (
        currentStep === totalSteps - 1 &&
        Object.keys(responses).length === totalSteps
      ) {
        if ($facebookCheckbox.length > 0) {
          $facebookSection.removeClass("hidden").addClass("show");
          $submitBtn.prop("disabled", !$facebookCheckbox.is(":checked"));
        } else {
          $submitBtn.prop("disabled", false);
        }
      }
    }
  }

  // ============================================
  // ALL-QUESTIONS SURVEY LOGIC
  // ============================================
  function initAllQuestionsSurvey($container, surveyId) {
    var $startBtn = $container.find(".wp-survey-start-btn");
    var $introScreen = $container.find(".wp-survey-intro-screen");
    var $allQuestionsScreen = $container.find(
      ".wp-survey-all-questions-screen"
    );
    var $successScreen = $container.find(".wp-survey-success-screen");
    var $questionBlocks = $container.find(".wp-survey-question-block");
    var $submitBtn = $container.find(".wp-survey-submit-all-btn");
    var $progressFill = $container.find(".wp-survey-progress-fill");
    var $answeredCount = $container.find(".wp-survey-answered-count");
    var $facebookSection = $container.find(".wp-survey-facebook-section");
    var $facebookCheckbox = $container.find(".wp-survey-facebook-checkbox");
    var $error = $container.find(".wp-survey-error");

    var totalQuestions = $questionBlocks.length;
    var responses = {};

    // Start survey
    $startBtn.on("click", function () {
      $introScreen.removeClass("active");
      $allQuestionsScreen.addClass("active");
    });

    // Handle choice selection
    $questionBlocks.each(function () {
      var $block = $(this);
      var questionId = $block.data("question-id");
      var allowMultiple = $block.data("allow-multiple");

      $block.find(".wp-survey-choice").on("click", function () {
        var $choice = $(this);
        var choiceId = $choice.data("choice-id");

        if (allowMultiple) {
          // Multiple choice - toggle selection
          $choice.toggleClass("selected");
          $choice
            .find('input[type="checkbox"]')
            .prop("checked", $choice.hasClass("selected"));

          // Update responses array
          var selectedChoices = [];
          $block.find(".wp-survey-choice.selected").each(function () {
            selectedChoices.push($(this).data("choice-id"));
          });

          if (selectedChoices.length > 0) {
            responses[questionId] = selectedChoices;
          } else {
            delete responses[questionId];
          }
        } else {
          // Single choice - radio button behavior
          $block.find(".wp-survey-choice").removeClass("selected");
          $choice.addClass("selected");
          $choice.find('input[type="radio"]').prop("checked", true);
          responses[questionId] = [choiceId];
        }

        updateProgress();
        checkAllAnswered();
      });
    });

    // Facebook checkbox
    $facebookCheckbox.on("change", function () {
      checkAllAnswered();
    });

    // Submit button
    $submitBtn.on("click", function () {
      if (Object.keys(responses).length !== totalQuestions) {
        showError($error, "Please answer all questions");
        return;
      }

      $submitBtn.prop("disabled", true).text("Submitting...");

      var responsesArray = [];
      for (var qId in responses) {
        var choiceIds = responses[qId];
        // Handle both single choice (array with one item) and multiple choices (array with multiple items)
        if (Array.isArray(choiceIds)) {
          choiceIds.forEach(function (choiceId) {
            responsesArray.push({
              question_id: qId,
              choice_id: choiceId,
            });
          });
        }
      }

      $.post(
        wpSurveyPublic.ajaxurl,
        {
          action: "wp_survey_submit_multi",
          nonce: wpSurveyPublic.nonce,
          survey_id: surveyId,
          responses: JSON.stringify(responsesArray),
        },
        function (response) {
          if (response.success) {
            $allQuestionsScreen.removeClass("active");
            $successScreen.addClass("active");
          } else {
            showError($error, response.data.message || "An error occurred");
            $submitBtn.prop("disabled", false).text("Submit Survey ✓");
          }
        }
      ).fail(function () {
        showError($error, "Network error. Please try again.");
        $submitBtn.prop("disabled", false).text("Submit Survey ✓");
      });
    });

    function updateProgress() {
      var answeredCount = Object.keys(responses).length;
      var progress = (answeredCount / totalQuestions) * 100;
      $progressFill.css("width", progress + "%");
      $answeredCount.text(answeredCount);
    }

    function checkAllAnswered() {
      if (Object.keys(responses).length === totalQuestions) {
        if ($facebookCheckbox.length > 0) {
          $facebookSection.removeClass("hidden").addClass("show");
          $submitBtn.prop("disabled", !$facebookCheckbox.is(":checked"));
        } else {
          $submitBtn.prop("disabled", false);
        }
      } else {
        $submitBtn.prop("disabled", true);
      }
    }
  }

  // ============================================
  // HELPER FUNCTIONS
  // ============================================
  function showError($errorEl, message) {
    $errorEl.text(message).removeClass("hidden");
  }

  function showAlreadyVoted($container) {
    var alreadyVotedHtml = `
            <div class="wp-survey-success">
                <div class="wp-survey-success-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2>You Have Already Voted!</h2>
                <p>You have already participated in this survey. Thank you!</p>
            </div>
        `;

    if ($container.hasClass("wp-survey-widget")) {
      $container.find(".wp-survey-content").html(alreadyVotedHtml);
    } else {
      $container.find(".wp-survey-card").html(alreadyVotedHtml);
    }
  }

  function showSuccess($container) {
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

    if ($container.hasClass("wp-survey-widget")) {
      $container.find(".wp-survey-content").html(successHtml);
    } else {
      $container.find(".wp-survey-card").html(successHtml);
    }
  }

  function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }
});
