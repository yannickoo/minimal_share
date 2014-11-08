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
    'url' => 'https://www.drupal.org/share.php?u=[url]&t=[title]',
    'size' => array('width' => '600', 'height' => '500'),
  );

  return $services;
}


/**
 * Modify available services.
 *
 * This hook is invoked by minimal_share_services().
 *
 * @param array $services
 *   An associative array of all defined services.
 */
function hook_minimal_share_services_alter(array &$services) {
  $services['drupalorg']['size']['width'] = '500';
}
