(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.minimalShareAdmin = {
    attach: function (context) {
      var randomShareCount = Math.floor(Math.random() * 50) + 1;

      $('.js-minimal-share__provider-wrapper', context).once('minimal-share').each(function () {
        var $this = $(this);
        var $customLabelInput = $this.find('.js-minimal_share__label-custom');

        $customLabelInput.on('input', function () {
          var $input = $(this);
          var value = $input.val();

          var $customLabelType = $this.find('label[for$="-label-type-custom"] span');
          var originalTitle = $customLabelType.data('orig-title');

          if (!originalTitle) {
            $customLabelType.data('orig-title', $customLabelType.text());
          }

          if (!value) {
            $customLabelType.text($customLabelType.data('orig-title'));
            return;
          }

          if (value.indexOf('[count]') !== -1) {
            value = value.replace(/\[count\]/g, drupalSettings.minimalShare);
          }

          $customLabelType.text(value);
        }).trigger('input');
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
