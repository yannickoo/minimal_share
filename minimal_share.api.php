<?php

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Register a new service.
 *
 * This hook is invoked by minimal_share_services().
 *
 * @return array
 *   An associative array of all defined services.
 */
function hook_minimal_share_services() {
  $services = array();

  $services['drupalorg'] = array(
    'title' => t('Drupal.org'),
    'url' => 'https://www.drupal.org/share?url=[url]&title=[title]',
    'size' => array('width' => '600', 'height' => '500'),
  );

  return $services;
}


/**
 * Modify available services.
 *
 * This hook is invoked by minimal_share_settings().
 *
 * @param array $services
 *   An associative array of all defined services.
 */
function hook_minimal_share_services_alter(array &$services) {
  // Change width of the popup:
  $services['drupalorg']['size']['width'] = '500';

  // Change link label:
  $services['drupalorg']['title'] = 'Drupal';

  // Disable a service:
  $services['drupalorg']['enabled'] = FALSE;

  // Change weight of links:
  $services['drupalorg']['weight'] = 10;
}
