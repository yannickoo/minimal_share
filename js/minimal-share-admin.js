(function ($) {

  Drupal.behaviors.minimalShareAdmin = {
    attach: function (context, settings) {
      $('#minimal-share-config-form', context).once('minimal-share', function() {
        var $preview = $('#minimal-share-preview > .minimal-share', this);
        var $selectedLabelType = $('input[name$="[label_type]"]', this);

        $selectedLabelType.filter(':checked').once('minimal-share', function() {
          var $this = $(this);
          var $label = $this.siblings();
          var label = $label.text();
          var serviceType = $label.find('span > span').attr('class');

          $preview.append('<span class="' + serviceType + '">' + label + '</span> ');
        });

        $selectedLabelType.bind('change', function() {
          var $this = $(this);
          var $label = $this.siblings();
          var label = $label.text();
          var serviceType = $label.find('span > span').attr('class');

          $preview.find('> .' + serviceType).text(label);
        });

        var $enableCheckboxes = $('input[name$="[enable]"');

        $enableCheckboxes.change(function() {
          var $this = $(this);
          var enabled = $this.is(':checked');
          var nameParts = $this.attr('name').split('][')
          var serviceType = nameParts[nameParts.length - 2];

          if (!enabled) {
            $preview.find('> .' + serviceType).hide();
          }
          else {
            $preview.find('> .' + serviceType).show();
          }
        }).trigger('change');

        var $customLabels = $('input[name$="[custom]"]', this);

        // Override text of "Custom" radio label when custom label is used.
        $customLabels.each(function() {
          var $this = $(this);
          var value = $this.val();
          var $customRadio = $this.parents('.fieldset-wrapper').find('[value="custom"]');
          var $customRadioLabel = $customRadio.siblings().find('> span > span');

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
          var $customRadioLabel = $customRadio.siblings().find('> span > span');

          value = Drupal.behaviors.minimalShareAdmin.replaceCountToken(value, $this);

          if (value && !$customRadioLabel.data('orig-title')) {
            $customRadioLabel.data('orig-title', $customRadioLabel.text());
          }

          if (value) {
            $customRadioLabel.text(value);
            $preview.find('> .' + $customRadioLabel.attr('class')).text(value);
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
        var $countRadioLabel = $countRadio.siblings().find('> span > span');
        var count = $countRadioLabel.text();

        value = value.replace('[count]', count);

        return value;
      }
    }
  };

})(jQuery);
