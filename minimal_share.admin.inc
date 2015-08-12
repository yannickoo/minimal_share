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
  $form['#attached']['library'][] = array('system', 'ui.sortable');
  $form['#attached']['css'][] = drupal_get_path('module', 'minimal_share') . '/css/minimal-share.css';
  $form['#attached']['css'][] = drupal_get_path('module', 'minimal_share') . '/css/minimal-share-icons.css';
  $form['#attached']['css'][] = drupal_get_path('module', 'minimal_share') . '/css/minimal-share-admin.css';
  $form['#attached']['js'][] = drupal_get_path('module', 'minimal_share') . '/js/minimal-share-admin.js';

  // Get all available service information.
  $settings = minimal_share_settings(FALSE);

  // Collect all bundles where a Minimal Share field is used.
  $extra_fields = array();
  foreach (entity_get_info() as $entity_type => $entity_info) {
    foreach ($entity_info['bundles'] as $bundle_name => $bundle) {
      $field_extra_fields = field_info_extra_fields($entity_type, $bundle_name, 'display');

      if (isset($field_extra_fields['minimal_share'])) {
        $extra_fields[$entity_info['label']][$bundle['label']]['minimal_share'] = $field_extra_fields['minimal_share'];
      }
    }
  }

  $form['minimal_share'] = array(
    '#type' => 'container',
  );

  $form['minimal_share']['preview'] = array(
    '#prefix' => '<div class="form-item">',
    '#markup' => '<label>' . t('Preview') . '</label><div id="minimal-share-preview"><p class="minimal-share"></p></div>',
    '#suffix' => '</div></div>',
  );

  $unicodes = array();

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

      $unicodes[$service_name] = '&#' . $service['unicode'] . ';';
    }

    $form['#attached']['js'][] = array(
      'data' => array('minimalShare' => array('unicodes' => $unicodes)),
      'type' => 'setting',
    );

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

    if (!empty($service['unicode'])) {
      $prefix = $form['minimal_share']['services'][$service_name]['custom']['#description'] ? ' ' : '';
      $description = $prefix . t('Use <code>[icon]</code> to display !unicode', array('!unicode' => '<span class="icon">&#' . $service['unicode'] . ';</span>'));

      $form['minimal_share']['services'][$service_name]['custom']['#description'] .= $description;
    }

    if (isset($service['params']) && count($service['params'])) {
      $form['minimal_share']['services'][$service_name]['params'] = array(
        '#type' => 'fieldset',
        '#title' => t('Override parameters'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#states' => array(
          'visible' => array(
            'input[name="minimal_share[services][' . $service_name . '][enabled]"]' => array('checked' => TRUE),
          ),
        ),
      );

      $override_params = $service['params'];
      unset($override_params['_override']);

      foreach ($override_params as $param => $value) {
        if (isset($service['params']['_override'][$param])) {
          $value = $service['params']['_override'][$param];
        }

        $form['minimal_share']['services'][$service_name]['params']['_override'][$param] = array(
          '#type' => 'textfield',
          '#title' => drupal_ucfirst($param),
          '#description' => t('Enter new value for %param parameter.', array('%param' => $param)),
          '#default_value' => $value,
        );
      }

      $override_options = array();
      foreach ($extra_fields as $entity_type => $bundles) {
        $clean_entity_type = minimal_share_machine_name($entity_type);

        foreach ($bundles as $bundle_name => $display) {
          $clear_bundle_name = minimal_share_machine_name($bundle_name);
          $override_options[$clean_entity_type][$clear_bundle_name] = $bundle_name;
        }
      }

      foreach ($extra_fields as $entity_type => $bundles) {
        $clean_entity_type = minimal_share_machine_name($entity_type);

        $form['minimal_share']['services'][$service_name]['params']['_override']['entity_types'][$clean_entity_type] = array(
          '#type' => 'fieldset',
          '#title' => $entity_type,
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
        );

        $entity_type_enabled = array();
        if (isset($service['params']['_override']['entity_types'][$clean_entity_type]['_enabled'])) {
          $entity_type_enabled = $service['params']['_override']['entity_types'][$clean_entity_type]['_enabled'];
        }

        $form['minimal_share']['services'][$service_name]['params']['_override']['entity_types'][$clean_entity_type]['_enabled'] = array(
          '#type' => 'checkboxes',
          '#title' => t('Override parameter'),
          '#options' => $override_options[$clean_entity_type],
          '#default_value' => $entity_type_enabled,
        );

        foreach ($bundles as $bundle_name => $display) {
          $clean_bundle_name = drupal_strtolower(drupal_clean_css_identifier($bundle_name, array(' ' => '_')));

          $form['minimal_share']['services'][$service_name]['params']['_override']['entity_types'][$clean_entity_type][$clean_bundle_name] = array(
            '#type' => 'fieldset',
            '#title' => $bundle_name,
            '#collapsible' => TRUE,
            '#collapsed' => TRUE,
            '#states' => array(
              'visible' => array(
                'input[name="minimal_share[services][' . $service_name . '][params][_override][entity_types][' . $clean_entity_type . '][_enabled][' . $clean_bundle_name . ']"]' => array('checked' => TRUE),
                ),
              'expanded' => array(
                'input[name="minimal_share[services][' . $service_name . '][params][_override][entity_types][' . $clean_entity_type . '][_enabled][' . $clean_bundle_name . ']"]' => array('checked' => TRUE),
              ),
            ),
          );

          foreach ($override_params as $param => $value) {
            $param_value = '';
            if (isset($service['params']['_override']['entity_types'][$clean_entity_type][$clean_bundle_name][$param])) {
              $param_value = $service['params']['_override']['entity_types'][$clean_entity_type][$clean_bundle_name][$param];
            }

            $form['minimal_share']['services'][$service_name]['params']['_override']['entity_types'][$clean_entity_type][$clean_bundle_name][$param] = array(
              '#type' => 'textfield',
              '#title' => drupal_ucfirst($param),
              '#description' => t('Enter new value for %param parameter.', array('%param' => $param)),
              '#default_value' => $param_value,
            );
          }
        }
      }

      $form['minimal_share']['services'][$service_name]['params']['token'] = array(
        '#type' => 'container',
        '#theme' => 'token_tree',
        '#token_types' => 'all',
        '#dialog' => TRUE,
      );
    }

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
      '#prefix' => '<p>' . t('Customize the style and copy the code into your stylesheet file.') . '</p>',
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
