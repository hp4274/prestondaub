$(function () {
  // Get the form.
  var form = $("#contact-form");

  // Set up an event listener for the contact form.
  $(form).submit(function (e) {
    // Stop the browser from submitting the form.
    e.preventDefault();

    // Serialize the form data.
    var formData = $(form).serialize();

    // Show loading toast
    Toast.info("Sending your message...", 10000, false);

    // Submit the form using AJAX.
    $.ajax({
      type: "POST",
      url: $(form).attr("action"),
      data: formData,
    })
      .done(function (response) {
        // Clear all toasts
        Toast.clearAll();

        // Show success toast
        Toast.success(
          response ||
            "Thank you for your inquiry! We will contact you shortly.",
        );

        // Clear the form
        form[0].reset();
      })
      .fail(function (data) {
        // Clear all toasts
        Toast.clearAll();

        // Show error toast
        var errorMsg =
          data.responseText ||
          "Oops! An error occurred and your message could not be sent.";
        Toast.error(errorMsg);
      });
  });
});
