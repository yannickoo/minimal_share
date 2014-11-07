(function ($) {

  Drupal.behaviors.minimalShare = {
    attach: function (context, settings) {
      $('.minimal-share a', context).once('minimal-share', function() {
        var $this = $(this);

        $this.bind('click', function(e) {
          if ($this.hasClass('print')) {
            window.print();
          }
          else {
            var width = $this.data('width'), height = $this.data('height'), url = $this.attr('href');
            var wx = (screen.width - width) >> 1, wy = (screen.height - height) >> 1;
            window.open(url, '', "top=" + wy + ",left=" + wx + ",width=" + width + ",height=" + height);
          }

          e.preventDefault();
        });
      });
    }
  };

})(jQuery);
