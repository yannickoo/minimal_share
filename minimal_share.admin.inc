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
  $form['#attached']['js'][] = drupal_get_path('module', 'minimal_share') . '/js/minimal-share-admin.js';

    // Get all available service information.
  $settings = variable_get('minimal_share', array());
  $service_settings = isset($settings['services']) ? $settings['services'] : array();
  $services = array_merge_recursive(minimal_share_services(), $service_settings);

  $form['minimal_share'] = array(
    '#type' => 'container',
  );

  $form['minimal_share']['general'] = array(
    '#type' => 'fieldset',
    '#title' => t('General'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  $period = drupal_map_assoc(array(0, 60, 180, 300, 600, 900, 1800, 2700, 3600, 10800, 21600, 32400, 43200, 86400), 'format_interval');
  $period[0] = '<' . t('none') . '>';

  $form['minimal_share']['general']['cache_lifetime'] = array(
    '#type' => 'select',
    '#title' => t('Cache lifetime'),
    '#options' => $period,
    '#description' => t('Cached counts will not be re-fetched until at least this much time has elapsed. When no cache lifetime is selected the page load takes much more time because counts are fetched on each request then.'),
    '#default_value' => isset($settings['general']['cache_lifetime']) ? $settings['general']['cache_lifetime'] : 900,
  );

    $form['minimal_share']['general']['preview'] = array(
    '#prefix' => '<div class="form-item">',
    '#markup' => '<label>' . t('Preview') . '</label><div id="minimal-share-preview"><p class="minimal-share"></p></div>',
    '#suffix' => '</div></div>',
  );

  foreach ($services as $service_name => $service) {
    $form['minimal_share']['services'][$service_name] = array(
      '#type' => 'fieldset',
      '#title' => $service['title'],
    );

    $form['minimal_share']['services'][$service_name]['enable'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enable'),
      '#default_value' => isset($service['enable']) ? $service['enable'] : TRUE,
    );

    // We use a random number for demonstrating the count labels.
    $count = rand(1, 50);

    $label_options = array(
      'name' => $service['title'],
      'count' => $count,
      'name_count' => $service['title'] . ' (' . $count . ')',
      'custom' => t('Custom'),
    );

    // We wrap radio labels to apply styles correctly.
    $html_prefix = '<span class="minimal-share"><span class="'. $service_name . '">';
    $html_suffix = '</span></span>';

    foreach ($label_options as &$option) {
      $option = $html_prefix . $option . $html_suffix;
    }

    $form['minimal_share']['services'][$service_name]['label_type'] = array(
      '#type' => 'radios',
      '#title' => t('Label'),
      '#description' => t('Set the label for @service share links.', array('@service' => $service['title'])),
      '#options' => $label_options,
      '#default_value' => isset($service['label_type']) ? $service['label_type'] : 'name',
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][enable]"]' => array('checked' => TRUE),
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
          'input[name="minimal_share[services][' . $service_name . '][label_type]"]' => array('value' => 'name_count'),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['custom'] = array(
      '#type' => 'textfield',
      '#title' => t('Custom label'),
      '#description' => t('You can use <code>[count]</code> to get the count number.'),
      '#default_value' => isset($service['custom']) ? $service['custom'] : '',
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][label_type]"]' => array('value' => 'custom'),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['override_colors'] = array(
      '#type' => 'fieldset',
      '#title' => t('Override colors'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#states' => array(
        'visible' => array(
          'input[name="minimal_share[services][' . $service_name . '][enable]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $form['minimal_share']['services'][$service_name]['override_colors']['code'] = array(
      '#prefix' => '<p>' . t('You can easily change colors via CSS:') . '</p>',
      '#markup' => '<code style="white-space: pre;">.minimal-share > .' . $service_name . ' {
  background-color: #000;
}</code>',
    );
  }

  return system_settings_form($form);
}

