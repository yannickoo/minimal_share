<?php

/**
 * @file
 * Administrative interface for Minimal Share.
 */

/**
 * Configuration form.
 */
function minimal_share_config_form($form, &$form_state) {
  $form['#tree'] = TRUE;
  $form['#attached']['css'][] = drupal_get_path('module', 'minimal_share') . '/css/minimal-share.css';
  $form['#attached']['css'][] = drupal_get_path('module', 'minimal_share') . '/css/minimal-share-icons.css';
  $form['#attached']['css'][] = drupal_get_path('module', 'minimal_share') . '/css/minimal-share-admin.css';
  $form['#attached']['js'][] = drupal_get_path('module', 'minimal_share') . '/libs/html5sortable/jquery.sortable.js';
  $form['#attached']['js'][] = drupal_get_path('module', 'minimal_share') . '/js/minimal-share-admin.js';

  // Get all available service information.
  $settings = minimal_share_settings();

  $form['minimal_share'] = array(
    '#type' => 'container',
  );

  $form['minimal_share']['preview'] = array(
    '#prefix' => '<div class="form-item">',
    '#markup' => '<label>' . t('Preview') . '</label><div id="minimal-share-preview"><p class="minimal-share"></p></div>',
    '#suffix' => '</div></div>',
  );

  foreach ($settings['services'] as $service_name => $service) {
    $form['minimal_share']['services'][$service_name] = array(
      '#type' => 'fieldset',
      '#title' => $service['title'],
    );

    $form['minimal_share']['services'][$service_name]['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable'),
      '#default_value' => $service['enabled'],
    );

    // We use a random number for demonstrating the count labels.
    $count = rand(1, 50);

    $label_options = array(
      'name' => $service['title'],
      'icon' => '',
      'name_count' => $service['title'] . ' (' . $count . ')',
      'custom' => t('Custom'),
    );

    // Add 'count' radio only if there is a count callback set.
    if (!isset($service['count_callback']) || !$service['count_callback']) {
      unset($label_options['name_count']);
    }

    // Remove icon feature if there is no icon for the current service.
    if (!isset($service['icon']) || !$service['icon']) {
      unset($label_options['icon']);
    }

    foreach ($label_options as $key => &$option) {
      $fake_link_class = $service_name;

      if ($key == 'icon') {
        $fake_link_class .= ' icon';
      }

      // We wrap radio labels to apply styles correctly.
      $option = '<span class="minimal-share"><span class="' . $fake_link_class . '">' . $option . '</span></span>';
    }

    $form['minimal_share']['services'][$service_name]['label_type'] = array(
      '#type' => 'radios',
      '#title' => t('Label'),
      '#description' => t('Set the label for @service share links.', array('@service' => $service['title'])),
      '#options' => $label_options,
      '#default_value' => isset($service['label_type']) ? $service['label_type'] : 'name',
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][enabled]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['hide_zero'] = array(
      '#type' => 'checkbox',
      '#title' => t('Hide zero'),
      '#description' => t('Hides the count when it is zero.'),
      '#default_value' => isset($service['hide_zero']) ? $service['hide_zero'] : 1,
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][enabled]"]' => array('checked' => TRUE),
          'input[name="minimal_share[services][' . $service_name . '][label_type]"]' => array('value' => 'name_count'),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['custom'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom label'),
      '#description' => minimal_share_count_callback_exists($service) ? t('You can use <code>[count]</code> to get the count number.') : '',
      '#default_value' => isset($service['custom']) ? $service['custom'] : '',
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][enabled]"]' => array('checked' => TRUE),
          'input[name="minimal_share[services][' . $service_name . '][label_type]"]' => array('value' => 'custom'),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['weight'] = array(
      '#type' => 'hidden',
    );

    $form['minimal_share']['services'][$service_name]['override_style'] = array(
      '#type' => 'fieldset',
      '#title' => t('Override style'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][enabled]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['override_style']['code'] = array(
      '#prefix' => '<p>' . t('Customize the style and copy the code.') . '</p>',
      '#markup' => '<style contenteditable style="display: block; white-space: pre;">.minimal-share > .' . $service_name . ' {
  background-color: #;
}</style>',
    );
  }

  $form['minimal_share']['advanced'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $period = drupal_map_assoc(array(0, 60, 180, 300, 600, 900, 1800, 2700, 3600, 10800, 21600, 32400, 43200, 86400), 'format_interval');
  $period[0] = '<' . t('none') . '>';

  $form['minimal_share']['advanced']['cache_lifetime'] = array(
    '#type' => 'select',
    '#title' => t('Cache lifetime'),
    '#options' => $period,
    '#description' => t('Cached counts will not be re-fetched until at least this much time has elapsed. When no cache lifetime is selected the page load takes much more time because counts are fetched on each request then.'),
    '#default_value' => isset($settings['advanced']['cache_lifetime']) ? $settings['advanced']['cache_lifetime'] : 900,
  );

  return system_settings_form($form);
}
