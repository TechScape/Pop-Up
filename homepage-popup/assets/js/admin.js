/**
 * HomePage Pop Up — Admin Settings JavaScript
 * Author: Sajid Khan
 * Version: 1.0.0
 *
 * Handles the WordPress Media Library uploader integration.
 */

/* global wp, jQuery */
(function ($) {
  'use strict';

  var mediaFrame; // Cached media modal frame.

  /**
   * Open the WordPress media library and let the user choose an image.
   */
  function openMediaLibrary() {
    // If the frame already exists, re-open it.
    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title:    'Select or Upload Popup Image',
      button:   { text: 'Use This Image' },
      library:  { type: 'image' },
      multiple: false,
    });

    // When an image is selected.
    mediaFrame.on('select', function () {
      var attachment = mediaFrame.state().get('selection').first().toJSON();
      var imageUrl   = attachment.sizes && attachment.sizes.medium
        ? attachment.sizes.medium.url
        : attachment.url;

      // Update hidden input with attachment ID.
      $('#hpp_image_id').val(attachment.id);

      // Update preview area.
      var $preview = $('#hpp-image-preview');
      $preview.html(
        $('<img />', {
          src:   imageUrl,
          alt:   'Popup Image',
          style: 'width:100%;height:100%;object-fit:cover;',
        })
      );

      // Show the remove button.
      $('#hpp-remove-btn').show();
    });

    mediaFrame.open();
  }

  /**
   * Remove the selected image.
   */
  function removeImage() {
    $('#hpp_image_id').val('');
    $('#hpp-image-preview').html('');
    $('#hpp-remove-btn').hide();
  }

  /**
   * Highlight changed fields to give live feedback.
   */
  function highlightOnChange() {
    $('#hpp-settings-form :input').on('change input', function () {
      $(this).closest('td').addClass('hpp-field-changed');
    });
  }

  /**
   * Animate settings save confirmation.
   */
  function setupSaveAnimation() {
    $('#hpp-settings-form').on('submit', function () {
      var $btn = $('.hpp-save-btn');
      $btn.val('Saving…').prop('disabled', true);
    });
  }

  // ── DOM Ready ──────────────────────────────────────────────────────────

  $(function () {
    // Upload button.
    $('#hpp-upload-btn').on('click', function (e) {
      e.preventDefault();
      openMediaLibrary();
    });

    // Remove button.
    $('#hpp-remove-btn').on('click', function (e) {
      e.preventDefault();
      removeImage();
    });

    highlightOnChange();
    setupSaveAnimation();
  });
})(jQuery);
