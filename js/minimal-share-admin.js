(function ($) {

  Drupal.behaviors.minimalShareAdmin = {
    attach: function (context, settings) {
      $('#minimal-share-config-form', context).once('minimal-share', function() {
        var $preview = $('#minimal-share-preview > .minimal-share', this);
        var $selectedLabelType = $('input[name$="[label_type]"]', this);

        $selectedLabelType.filter(':checked').once('minimal-share', function() {
          var $this = $(this);
          var $label = $this.siblings();
          var $wrapper = $label.children('span');
          var $fakeLink = $wrapper.children('span');
          var fakeLinkLabel = $fakeLink.html();
          var fakeLinkType = $fakeLink.attr('class');

          $preview.append('<span class="' + fakeLinkType + '">' + fakeLinkLabel + '</span> ');
        });

        $preview.sortable().bind('dragstart', function(e) {
          var $dragging = $(e.target);
          var $placeholder = $('.sortable-placeholder');
          var $style = $preview.children('style');
          var css = '.sortable-placeholder { width: ' + $dragging.outerWidth() + 'px; height: ' + $dragging.outerHeight() + 'px; }';

          if (!$style.length) {
            $style = $('<style />').appendTo($preview);
          }

          $style.html(css);
        }).bind('sortupdate', function(e, ui) {
          var $this = $(this);
          var $fakeLinks = $this.children('span');
          var $weightFields = $('input[name$="[weight]"]');

          $weightFields.each(function() {
            var $this = $(this);
            var nameParts = $this.attr('name').split('][')
            var serviceType = nameParts[nameParts.length - 2];
            var $fakeLink = $fakeLinks.filter('.' + serviceType);

            var index = $fakeLinks.filter(':visible').index($fakeLink);

            $this.val(index);
          });
        }).trigger('sortupdate');

        $selectedLabelType.bind('change', function() {
          var $this = $(this);
          var $label = $this.siblings();
          var $wrapper = $label.children('span');
          var $fakeLink = $wrapper.children('span');
          var fakeLinkLabel = $fakeLink.html();
          var fakeLinkType = $fakeLink.attr('class').split(' ');
          var $fakeLinkPreview = $preview.children('.' + fakeLinkType[0]);

          // Add or remove the icon class base on the new selection.
          if (typeof fakeLinkType[1] !== 'undefined') {
            $fakeLinkPreview.addClass(fakeLinkType[1]);
          }
          else {
            $fakeLinkPreview.attr('class', fakeLinkType);
          }

          $fakeLinkPreview.text(fakeLinkLabel);
        });

        var $enableCheckboxes = $('input[name$="[enabled]"');

        $enableCheckboxes.change(function() {
          var $this = $(this);
          var enabled = $this.is(':checked');
          var nameParts = $this.attr('name').split('][')
          var serviceType = nameParts[nameParts.length - 2];

          if (!enabled) {
            $preview.find('> .' + serviceType).hide();
          }
          else {
            $preview.append($preview.find('> .' + serviceType).show()).trigger('sortupdate');
          }
        }).trigger('change');

        var $customLabels = $('input[name$="[custom]"]', this);

        // Override text of "Custom" radio label when custom label is used.
        $customLabels.each(function() {
          var $this = $(this);
          var value = $this.val();
          var $customRadio = $this.parents('.fieldset-wrapper').find('[value="custom"]');
          var $customRadioLabel = $customRadio.siblings().find('span > span');

          value = Drupal.behaviors.minimalShareAdmin.replaceCountToken(value, $this);

          if ($customRadio.attr('checked') && value) {
            $customRadioLabel.data('orig-title', $customRadioLabel.text());
            $customRadioLabel.text(value);
          }
        });

        // Update "Custom" radio label when custom label is changed.
        $customLabels.keyup(function(e) {
          var $this = $(this);
          var value = $this.val();
          var $customRadio = $this.parents('.fieldset-wrapper').find('[value="custom"]');
          var $customRadioLabel = $customRadio.siblings().find('span > span');

          value = Drupal.behaviors.minimalShareAdmin.replaceCountToken(value, $this);

          if (value && !$customRadioLabel.data('orig-title')) {
            $customRadioLabel.data('orig-title', $customRadioLabel.text());
          }

          if (value) {
            $customRadioLabel.text(value);
            $preview.children('.' + $customRadioLabel.attr('class')).text(value);
          }
          else {
            $customRadioLabel.text($customRadioLabel.data('orig-title'));
          }
        });
      });
    },
    /**
     * Replace [count] token for label types.
     */
    replaceCountToken: function(value, $elm) {
      if (value.indexOf('[count]')) {
        var $countRadio = $elm.parents('.fieldset-wrapper').find('[value="count"]');
        var $countRadioLabel = $countRadio.siblings().children('span');
        var count = $countRadioLabel.text();

        value = value.replace('[count]', count);

        return value;
      }
    }
  };

})(jQuery);
