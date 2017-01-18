(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.minimalShare = {
    attach: function (context) {
      $('.minimal-share a', context).once('minimal-share').each(function () {
        var $this = $(this);
        var size = false;
        var provider = $this.data('ms');

        if (drupalSettings.minimalShare.sizes[provider]) {
          size = drupalSettings.minimalShare.sizes[provider];
        }

        // Show hidden mobile share links.
        if ($this.hasClass('ms-mobile-only') && (('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch)) {
          $this.addClass('ms-show');
        }

        $this.bind('click', function (e) {
          if (provider === 'print') {
            window.print();
            e.preventDefault();
          }
          else if (size) {
            var width = size.width;
            var height = size.height;
            var url = $this.attr('href');
            var wx = (screen.width - width) >> 1;
            var wy = (screen.height - height) >> 1;

            window.open(url, '', 'top=' + wy + ',left=' + wx + ',width=' + width + ',height=' + height);

            e.preventDefault();
            e.stopPropagation();
          }
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
